<?php

namespace App\Services\Platform;

use App\Models\CompanyBillingAccount;
use App\Models\CompanyBillingEvent;
use App\Models\CompanyBillingReceipt;
use App\Models\CompanyMember;
use App\Models\CompanyWorkspace;
use App\Models\PlatformPlan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CompanyBillingService
{
    public function __construct(private StripeTestGateway $gateway) {}

    private function fail(string $key): never
    {
        throw ValidationException::withMessages(['billing' => __('billing.'.$key)]);
    }

    public function owner(User $u): CompanyWorkspace
    {
        $w = app(CompanyMarketplaceService::class)->actor($u);
        abort_unless(CompanyMember::where('company_workspace_id', $w->id)->where('user_id', $u->id)->where('role', 'owner')->exists(), 403);

        return $w;
    }

    public function account(CompanyWorkspace $w): CompanyBillingAccount
    {
        return CompanyBillingAccount::firstOrCreate(['company_workspace_id' => $w->id], ['customer_key' => (string) Str::uuid()]);
    }

    private function snapshot(PlatformPlan $p): array
    {
        return $p->only(['code', 'name_ar', 'name_en', 'monthly_amount_minor', 'currency', 'seats', 'active_opportunities', 'monthly_applications']);
    }

    public function checkout(User $u, string $code): string
    {
        $w = $this->owner($u);
        abort_unless($this->gateway->ready(), 503, __('billing.unavailable'));
        $p = PlatformPlan::where('code', $code)->where('is_available', true)->where('monthly_amount_minor', '>', 0)->firstOrFail();
        $priceId = $this->gateway->id(config('platform_billing.prices.'.$code), 'price_');
        $price = $this->gateway->call('GET', 'prices/'.$priceId);
        $this->gateway->testObject($price);
        if (($price['id'] ?? null) !== $priceId || ($price['active'] ?? null) !== true || ($price['currency'] ?? null) !== strtolower($p->currency) || ($price['unit_amount'] ?? null) !== $p->monthly_amount_minor || data_get($price, 'recurring.interval') !== 'month' || data_get($price, 'recurring.interval_count') !== 1 || ! in_array($price['tax_behavior'] ?? null, ['exclusive', 'inclusive'], true)) {
            $this->fail('price_mismatch');
        }
        $account = $this->account($w);
        if (! $account->stripe_customer_id) {
            $customer = $this->gateway->call('POST', 'customers', ['metadata' => ['hag_workspace_id' => (string) $w->id]], 'hag-customer-'.$account->customer_key);
            $this->gateway->testObject($customer);
            $id = $this->gateway->id($customer['id'] ?? null, 'cus_');
            DB::transaction(function () use ($account, $id): void {
                $a = CompanyBillingAccount::whereKey($account->id)->lockForUpdate()->firstOrFail();
                if ($a->stripe_customer_id && $a->stripe_customer_id !== $id) {
                    throw new \RuntimeException('Customer binding conflict.');
                }$a->update(['stripe_customer_id' => $id]);
            });
        }
        // Sync before opening another checkout: a missed webhook must not create a second subscription.
        $this->sync($account->fresh());
        $account->refresh();
        if ($account->checkout_session_id && $account->checkout_expires_at?->isPast()) {
            $previous = $this->gateway->call('GET', 'checkout/sessions/'.$this->gateway->id($account->checkout_session_id, 'cs_test_'));
            $this->gateway->testObject($previous);
            if (($previous['customer'] ?? null) !== $account->stripe_customer_id) {
                throw new \RuntimeException('Session binding conflict.');
            }
            if (($previous['status'] ?? null) === 'open' || (($previous['status'] ?? null) === 'complete' && ($previous['subscription'] ?? null) !== $account->stripe_subscription_id)) {
                $this->fail('pending_checkout');
            }
        }
        $attempt = DB::transaction(function () use ($u, $w, $account, $p, $priceId) {
            CompanyWorkspace::whereKey($w->id)->lockForUpdate()->firstOrFail();
            $this->owner($u->fresh());
            $a = CompanyBillingAccount::whereKey($account->id)->lockForUpdate()->firstOrFail();
            if (! in_array($a->status, ['canceled', 'incomplete_expired', 'none'], true)) {
                $this->fail('already_subscribed');
            }
            if ($a->attempt_key && $a->checkout_expires_at?->isFuture()) {
                if ($a->plan_code !== $p->code) {
                    $this->fail('pending_checkout');
                }

                return $a;
            }
            $a->update(['attempt_key' => (string) Str::uuid(), 'plan_code' => $p->code, 'plan_snapshot' => $this->snapshot($p), 'price_id' => $priceId, 'checkout_session_id' => null, 'checkout_url' => null, 'checkout_expires_at' => now('UTC')->addHour()]);

            return $a->fresh();
        });
        if ($attempt->checkout_url) {
            return $this->gateway->checkoutUrl($attempt->checkout_url);
        }
        $session = $this->gateway->call('POST', 'checkout/sessions', [
            'mode' => 'subscription', 'customer' => $attempt->stripe_customer_id, 'client_reference_id' => (string) $w->id,
            'line_items' => [['price' => $attempt->price_id, 'quantity' => 1]], 'success_url' => route('company.billing').'?checkout=returned', 'cancel_url' => route('company.billing').'?checkout=canceled',
            'expires_at' => $attempt->checkout_expires_at->timestamp, 'billing_address_collection' => 'required', 'customer_update' => ['address' => 'auto', 'name' => 'auto'],
            'tax_id_collection' => ['enabled' => 'true'], 'automatic_tax' => ['enabled' => 'true'], 'allow_promotion_codes' => 'false',
        ], 'hag-checkout-'.$attempt->attempt_key);
        $this->gateway->testObject($session);
        $id = $this->gateway->id($session['id'] ?? null, 'cs_test_');
        $url = $this->gateway->checkoutUrl($session['url'] ?? '');
        if (($session['customer'] ?? null) !== $attempt->stripe_customer_id || ($session['mode'] ?? null) !== 'subscription') {
            throw new \RuntimeException('Checkout binding mismatch.');
        }
        DB::transaction(function () use ($attempt, $id, $url): void {
            $a = CompanyBillingAccount::whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            if ($a->attempt_key !== $attempt->attempt_key) {
                throw new \RuntimeException('Checkout attempt changed.');
            }$a->update(['checkout_session_id' => $id, 'checkout_url' => $url]);
        });

        return $url;
    }

    public function sync(CompanyBillingAccount $account): void
    {
        abort_unless($this->gateway->ready(), 503);
        for ($attempt = 0; $attempt < 5; $attempt++) {
            // Provider requests must never hold a database transaction open.
            $before = CompanyBillingAccount::findOrFail($account->id);
            if (! $before->stripe_customer_id) {
                return;
            }
            $list = $this->gateway->call('GET', 'subscriptions', ['customer' => $before->stripe_customer_id, 'status' => 'all', 'limit' => 100]);
            if (! is_array($list['data'] ?? null) || ($list['has_more'] ?? true) !== false) {
                throw new \RuntimeException('Incomplete subscription listing.');
            }
            $invoices = [];
            foreach ($list['data'] as $subscription) {
                $this->gateway->testObject($subscription);
                if (($subscription['customer'] ?? null) !== $before->stripe_customer_id) {
                    throw new \RuntimeException('Subscription customer mismatch.');
                }
            }
            $open = array_values(array_filter($list['data'], fn ($s) => ! in_array($s['status'] ?? '', ['canceled', 'incomplete_expired'], true)));
            if (count($open) === 1 && in_array($open[0]['status'] ?? '', ['active', 'past_due'], true)) {
                $latest = $open[0]['latest_invoice'] ?? null;
                $invoiceId = is_array($latest) ? ($latest['id'] ?? null) : $latest;
                if ($invoiceId) {
                    $invoices[$invoiceId] = $this->gateway->call('GET', 'invoices/'.$this->gateway->id($invoiceId, 'in_'));
                    $this->gateway->testObject($invoices[$invoiceId]);
                }
            }
            $applied = $this->databaseWrite(function () use ($before, $list, $invoices): bool {
                // SQLite has no row-level FOR UPDATE. Reserve its writer before
                // reading, so this transaction never upgrades a stale read lock.
                if (DB::connection()->getDriverName() === 'sqlite') {
                    CompanyBillingAccount::whereKey($before->id)->toBase()->update(['id' => DB::raw('id')]);
                }
                CompanyWorkspace::whereKey($before->company_workspace_id)->lockForUpdate()->firstOrFail();
                $a = CompanyBillingAccount::whereKey($before->id)->lockForUpdate()->firstOrFail();
                if ($a->getRawOriginal() !== $before->getRawOriginal()) {
                    return false; // Another request won; fetch fresh provider state.
                }
                if (! is_array($list['data'] ?? null) || ($list['has_more'] ?? true) !== false) {
                    throw new \RuntimeException('Incomplete subscription listing.');
                }
                $valid = [];
                foreach ($list['data'] as $s) {
                    $this->gateway->testObject($s);
                    if (($s['customer'] ?? null) !== $a->stripe_customer_id) {
                        throw new \RuntimeException('Subscription customer mismatch.');
                    }$valid[] = $s;
                }
                $open = array_values(array_filter($valid, fn ($s) => ! in_array($s['status'] ?? '', ['canceled', 'incomplete_expired'], true)));
                if (count($open) > 1) {
                    $a->update(['status' => 'conflict', 'paid_until' => null]);

                    return true;
                }
                $sub = $open[0] ?? collect($valid)->firstWhere('id', $a->stripe_subscription_id);
                if (! $sub && $a->stripe_subscription_id) {
                    throw new \RuntimeException('Known subscription missing from provider response.');
                }
                if (! $sub) {
                    $a->update(['status' => 'none', 'paid_until' => null, 'last_synced_at' => now('UTC')]);

                    return true;
                }
                $subId = $this->gateway->id($sub['id'] ?? null, 'sub_');
                $status = $sub['status'] ?? 'unknown';
                if (in_array($status, ['canceled', 'incomplete_expired'], true)) {
                    $a->update(['stripe_subscription_id' => $subId, 'status' => $status, 'paid_until' => null, 'cancel_at_period_end' => false, 'last_synced_at' => now('UTC')]);

                    return true;
                }$items = data_get($sub, 'items.data', []);
                $item = count($items) === 1 ? $items[0] : [];
                if (! $a->plan_snapshot || data_get($item, 'price.id') !== $a->price_id || ($item['quantity'] ?? null) !== 1) {
                    $a->update(['stripe_subscription_id' => $subId, 'status' => 'invalid_price', 'paid_until' => null, 'last_synced_at' => now('UTC')]);

                    return true;
                }
                $end = (int) ($item['current_period_end'] ?? 0);
                $start = (int) ($item['current_period_start'] ?? 0);
                $until = $a->paid_until;
                if ($until && ($until->timestamp > $end)) {
                    $until = CarbonImmutable::createFromTimestampUTC($end);
                }
                $latest = $sub['latest_invoice'] ?? null;
                $invoiceId = is_array($latest) ? ($latest['id'] ?? null) : $latest;
                if ($invoiceId && in_array($status, ['active', 'past_due'], true)) {
                    $invoice = $invoices[$invoiceId];
                    $this->gateway->testObject($invoice);
                    $bound = data_get($invoice, 'parent.subscription_details.subscription') ?? ($invoice['subscription'] ?? null);
                    $line = data_get($invoice, 'lines.data.0', []);
                    $proof = ($invoice['id'] ?? null) === $invoiceId && ($invoice['customer'] ?? null) === $a->stripe_customer_id && $bound === $subId
                     && ($invoice['status'] ?? null) === 'paid' && is_int($invoice['amount_paid'] ?? null) && $invoice['amount_paid'] > 0
                     && ($invoice['currency'] ?? null) === strtolower($a->plan_snapshot['currency'])
                     && in_array($invoice['billing_reason'] ?? null, ['subscription_create', 'subscription_cycle'], true)
                     && count(data_get($invoice, 'lines.data', [])) === 1 && data_get($invoice, 'lines.has_more') === false
                     && data_get($line, 'period.start') === $start && data_get($line, 'period.end') === $end && $end > $start;
                    if ($proof) {
                        $until = CarbonImmutable::createFromTimestampUTC($end);
                        $receipt = CompanyBillingReceipt::firstOrCreate(['stripe_invoice_id' => $invoiceId], ['company_billing_account_id' => $a->id, 'stripe_subscription_id' => $subId, 'amount_paid_minor' => $invoice['amount_paid'], 'currency' => strtoupper($invoice['currency']), 'paid_until' => $until, 'plan_snapshot' => $a->plan_snapshot, 'created_at' => now('UTC')]);
                        if ((int) $receipt->company_billing_account_id !== (int) $a->id || $receipt->stripe_subscription_id !== $subId || $receipt->paid_until->timestamp !== $end || $receipt->amount_paid_minor !== $invoice['amount_paid']) {
                            throw new \RuntimeException('Immutable receipt conflict.');
                        }
                    }
                }
                if (! in_array($status, ['active', 'past_due'], true)) {
                    $until = null;
                }
                $a->update(['stripe_subscription_id' => $subId, 'status' => $status, 'paid_until' => $until, 'cancel_at_period_end' => (bool) ($sub['cancel_at_period_end'] ?? false), 'last_synced_at' => now('UTC')]);

                return true;
            });
            if ($applied) {
                return;
            }
        }
        throw new \RuntimeException('Billing account changed during synchronization; retry required.');
    }

    /** Retry only SQLite lock contention, after the failed transaction rolls back. */
    private function databaseWrite(callable $callback): mixed
    {
        for ($attempt = 0; ; $attempt++) {
            try {
                return DB::transaction($callback, DB::connection()->getDriverName() === 'sqlite' ? 1 : 3);
            } catch (QueryException $exception) {
                if (DB::connection()->getDriverName() !== 'sqlite'
                    || ! in_array((int) ($exception->errorInfo[1] ?? 0), [5, 6], true)
                    || $attempt >= 5) {
                    throw $exception;
                }
                usleep(50000 * (2 ** $attempt));
            }
        }
    }

    public function cancellation(User $u, bool $cancel): void
    {
        $w = $this->owner($u);
        $a = $this->account($w);
        $this->sync($a);
        $a->refresh();
        if (! $a->stripe_subscription_id || ! in_array($a->status, ['active', 'past_due'], true)) {
            $this->fail('no_subscription');
        }
        $this->gateway->call('POST', 'subscriptions/'.$this->gateway->id($a->stripe_subscription_id, 'sub_'), ['cancel_at_period_end' => $cancel ? 'true' : 'false'], 'hag-cancel-'.Str::uuid());
        $this->sync($a);
    }

    public function portal(User $u): string
    {
        $a = $this->account($this->owner($u));
        $configuration = $this->gateway->id(config('platform_billing.portal_configuration'), 'bpc_');
        if (! $a->stripe_customer_id) {
            $this->fail('no_subscription');
        }
        $portal = $this->gateway->call('POST', 'billing_portal/sessions', ['customer' => $a->stripe_customer_id, 'configuration' => $configuration, 'return_url' => route('company.billing')]);

        return $this->gateway->checkoutUrl($portal['url'] ?? '', 'billing.stripe.com');
    }

    public function webhook(array $event, string $raw): void
    {
        $id = $event['id'];
        if (CompanyBillingEvent::where('stripe_event_id', $id)->exists()) {
            return;
        }
        if (in_array($event['type'], ['checkout.session.completed', 'checkout.session.async_payment_succeeded', 'customer.subscription.created', 'customer.subscription.updated', 'customer.subscription.deleted', 'invoice.paid', 'invoice.payment_failed', 'invoice.payment_action_required'], true)) {
            $customer = data_get($event, 'data.object.customer');
            if (is_string($customer)) {
                $account = CompanyBillingAccount::where('stripe_customer_id', $customer)->first();
                if ($account) {
                    $this->sync($account);
                }
            }
        }
        // A crash here leaves the event retryable. Sync and receipt creation are
        // idempotent, so a retry cannot duplicate a subscription or receipt.
        try {
            $this->databaseWrite(fn () => CompanyBillingEvent::create([
                'stripe_event_id' => $id, 'type' => $event['type'],
                'payload_sha256' => hash('sha256', $raw), 'created_at' => now('UTC'),
            ]));
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23505'], true)
                && CompanyBillingEvent::where('stripe_event_id', $id)->exists()) {
                return;
            }
            throw $exception;
        }
    }
}
