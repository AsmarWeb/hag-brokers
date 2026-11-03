<?php

namespace App\Console\Commands;

use App\Models\PlatformPlan;
use App\Services\Platform\StripeTestGateway;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckCompanyBilling extends Command
{
    protected $signature = 'platform:billing-check {--remote : GET-only verification against Stripe}';

    protected $description = 'Inspect current billing mode without creating payments, customers or subscriptions';

    public function handle(StripeTestGateway $gateway): int
    {
        $this->line('Mode: '.config('platform_billing.mode', 'test'));
        $this->line('Gateway configured: '.($gateway->ready() ? 'YES' : 'NO'));
        $this->line('Free launch: '.(config('platform_billing.free_launch', true) ? 'ON' : 'OFF'));
        $this->line('New live checkout: '.(config('platform_billing.checkout_enabled') && ! config('platform_billing.free_launch', true) ? 'ENABLED' : 'DISABLED'));
        $tables = $gateway->live() ? ['company_live_billing_accounts', 'company_live_billing_events', 'company_live_billing_receipts'] : ['company_billing_accounts', 'company_billing_events', 'company_billing_receipts'];
        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                $this->error('Missing table: '.$table);

                return self::FAILURE;
            }
        }
        $this->line('Billing tables: READY');
        if (! $this->option('remote')) {
            $this->line('Local check only. No Stripe requests or record updates.');

            return self::SUCCESS;
        }
        if (! $gateway->ready()) {
            $this->error('Remote check blocked: configuration is incomplete.');

            return self::FAILURE;
        }
        try {
            $account = $gateway->call('GET', 'account');
            if (($account['country'] ?? null) !== 'AE' || ($account['charges_enabled'] ?? null) !== true || ($account['payouts_enabled'] ?? null) !== true) {
                throw new \RuntimeException('Account not ready');
            }
            $this->line('UAE Stripe account charges/payouts: READY');
            foreach (['business', 'professional'] as $code) {
                $plan = PlatformPlan::where('code', $code)->where('is_available', true)->firstOrFail();
                $price = $gateway->call('GET', 'prices/'.$gateway->id(config('platform_billing.prices.'.$code), 'price_'));
                $gateway->testObject($price);
                if (($price['id'] ?? null) !== config('platform_billing.prices.'.$code) || ($price['active'] ?? null) !== true || ($price['currency'] ?? null) !== 'aed' || $plan->currency !== 'AED' || ($price['unit_amount'] ?? null) !== $plan->monthly_amount_minor || ($price['tax_behavior'] ?? null) !== 'exclusive' || data_get($price, 'recurring.interval') !== 'month' || data_get($price, 'recurring.interval_count') !== 1) {
                    throw new \RuntimeException('Price not ready');
                }
                $this->line($code.': price/mode/amount/month/tax READY');
            }
            $tax = $gateway->call('GET', 'tax/settings');
            $gateway->testObject($tax);
            if (($tax['status'] ?? null) !== 'active' || data_get($tax, 'head_office.address.country') !== 'AE') {
                throw new \RuntimeException('Tax settings not ready');
            }
            $registrations = $gateway->call('GET', 'tax/registrations', ['status' => 'active', 'limit' => 100]);
            $found = false;
            foreach ($registrations['data'] ?? [] as $reg) {
                $gateway->testObject($reg);
                if (($reg['country'] ?? null) === 'AE' && ($reg['status'] ?? null) === 'active') {
                    $found = true;
                }
            }
            if (! $found || ($registrations['has_more'] ?? true) !== false) {
                throw new \RuntimeException('Registration not ready');
            }
            $this->line('Stripe Tax settings and UAE registration: READY');
            $portal = $gateway->call('GET', 'billing_portal/configurations/'.$gateway->id(config('platform_billing.portal_configuration'), 'bpc_'));
            $gateway->testObject($portal);
            if (($portal['active'] ?? null) !== true || data_get($portal, 'features.subscription_update.enabled') !== false || data_get($portal, 'features.subscription_cancel.enabled') !== true || data_get($portal, 'features.subscription_cancel.mode') !== 'at_period_end') {
                throw new \RuntimeException('Portal not ready');
            }
            foreach (['terms_of_service_url', 'privacy_policy_url'] as $policy) {
                $policyUrl = data_get($portal, 'business_profile.'.$policy);
                if (! is_string($policyUrl) || ! filter_var($policyUrl, FILTER_VALIDATE_URL) || parse_url($policyUrl, PHP_URL_SCHEME) !== 'https') {
                    throw new \RuntimeException('Policy URL not ready');
                }
            }
            $this->line('Portal cancellation policy and disabled plan switching: READY');
            $endpoints = $gateway->call('GET', 'webhook_endpoints', ['limit' => 100]);
            $expected = rtrim(config('app.url'), '/').($gateway->live() ? '/api/platform-billing/stripe/live/webhook' : '/api/platform-billing/stripe/webhook');
            $events = ['checkout.session.completed', 'checkout.session.async_payment_succeeded', 'customer.subscription.created', 'customer.subscription.updated', 'customer.subscription.deleted', 'invoice.paid', 'invoice.payment_failed', 'invoice.payment_action_required', 'charge.refunded', 'charge.dispute.created', 'charge.dispute.closed'];
            $found = false;
            foreach ($endpoints['data'] ?? [] as $endpoint) {
                if (($endpoint['url'] ?? null) !== $expected || ($endpoint['status'] ?? null) !== 'enabled') {
                    continue;
                }
                $gateway->testObject($endpoint);
                $selected = $endpoint['enabled_events'] ?? [];
                if (($endpoint['api_version'] ?? null) === config('platform_billing.api_version') && (in_array('*', $selected, true) || ! array_diff($events, $selected))) {
                    $found = true;
                }
            }
            if (! $found || ($endpoints['has_more'] ?? true) !== false) {
                throw new \RuntimeException('Webhook not ready');
            }
            $this->line('Webhook URL/events/API version/mode: READY');
            $this->info('GET-only remote checks passed. No charges or record updates. Signed delivery and real payment still require verification.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Remote check failed at the next unconfirmed step. Review the Stripe settings/permissions. No charges were created; no provider response or secret is printed.');

            return self::FAILURE;
        }
    }
}
