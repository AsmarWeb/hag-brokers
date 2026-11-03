<?php

namespace Tests\Feature;

use App\Models\CompanyBillingAccount;
use App\Models\Customer;
use App\Models\PlatformPlan;
use App\Models\User;
use App\Services\Platform\CompanyBillingService;
use App\Services\Platform\CompanyWorkspaceService;
use App\Services\Platform\StripeTestGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class LiveCompanyBillingTest extends TestCase
{
    use RefreshDatabase;

    private bool $mutateOnInvoice = false;

    private bool $objectMode = true;

    private ?array $subscription = null;

    private array $invoice = [];

    private array $charge = [];

    private bool $paymentListIncomplete = false;

    private bool $manualPayment = false;

    private bool $providerFailure = false;

    private bool $duplicateSubscription = false;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'https://market.example.test', 'app.debug' => false, 'platform_billing.mode' => 'live', 'platform_billing.enabled' => true, 'platform_billing.checkout_enabled' => true, 'platform_billing.secret' => 'sk_live_fixture_fake', 'platform_billing.webhook_secret' => 'whsec_live_fixture', 'platform_billing.prices' => ['business' => 'price_business_live', 'professional' => 'price_professional_live'], 'platform_billing.portal_configuration' => 'bpc_live_fixture']);
        Http::preventStrayRequests();
        Http::fake(function ($r) {
            if ($this->providerFailure) {
                return Http::response([], 503);
            }
            $path = parse_url($r->url(), PHP_URL_PATH);
            if ($this->mutateOnInvoice && $path === '/v1/invoices/in_live_fixture') {
                DB::table('company_live_billing_accounts')->increment('sync_revision');
                $this->mutateOnInvoice = false;
            }
            if (str_starts_with($path, '/v1/prices/')) {
                return Http::response($this->price(basename($path)));
            }

            return Http::response(match ($path) {
                '/v1/customers' => ['id' => 'cus_live_fixture', 'livemode' => $this->objectMode],
                '/v1/subscriptions' => ['data' => $this->subscription ? ($this->duplicateSubscription ? [$this->subscription, array_replace($this->subscription, ['id' => 'sub_live_duplicate'])] : [$this->subscription]) : [], 'has_more' => false],
                '/v1/checkout/sessions' => ['id' => 'cs_live_fixture', 'livemode' => $this->objectMode, 'customer' => 'cus_live_fixture', 'mode' => 'subscription', 'url' => 'https://checkout.stripe.com/c/pay/live_fixture'],
                '/v1/invoices/in_live_fixture' => $this->invoice,
                '/v1/invoice_payments' => ['has_more' => $this->paymentListIncomplete, 'data' => [['id' => 'inpay_live_fixture', 'livemode' => $this->objectMode, 'invoice' => 'in_live_fixture', 'status' => 'paid', 'amount_paid' => 20895, 'currency' => 'aed', 'payment' => ['type' => $this->manualPayment ? 'payment_record' : 'payment_intent', 'payment_intent' => 'pi_live_fixture']]]],
                '/v1/payment_intents/pi_live_fixture' => ['id' => 'pi_live_fixture', 'livemode' => $this->objectMode, 'customer' => 'cus_live_fixture', 'currency' => 'aed', 'status' => 'succeeded', 'amount_received' => 20895, 'latest_charge' => 'ch_live_fixture'],
                '/v1/charges/ch_live_fixture' => $this->charge,
                '/v1/billing_portal/sessions' => ['url' => 'https://billing.stripe.com/p/session/live_fixture'],
                '/v1/subscriptions/sub_live_fixture' => $this->subscription = array_replace($this->subscription, ['cancel_at_period_end' => $r['cancel_at_period_end'] === 'true']),
                '/v1/account' => ['country' => 'AE', 'charges_enabled' => true, 'payouts_enabled' => true],
                '/v1/tax/settings' => ['livemode' => $this->objectMode, 'status' => 'active', 'head_office' => ['address' => ['country' => 'AE']]],
                '/v1/tax/registrations' => ['has_more' => false, 'data' => [['livemode' => $this->objectMode, 'country' => 'AE', 'status' => 'active']]],
                '/v1/billing_portal/configurations/bpc_live_fixture' => ['business_profile' => ['terms_of_service_url' => 'https://market.example.test/terms', 'privacy_policy_url' => 'https://market.example.test/privacy'], 'livemode' => $this->objectMode, 'active' => true, 'features' => ['subscription_update' => ['enabled' => false], 'subscription_cancel' => ['enabled' => true, 'mode' => 'at_period_end']]],
                '/v1/webhook_endpoints' => ['has_more' => false, 'data' => [['livemode' => $this->objectMode, 'url' => 'https://market.example.test/api/platform-billing/stripe/live/webhook', 'status' => 'enabled', 'api_version' => config('platform_billing.api_version'), 'enabled_events' => ['*']]]],
                default => throw new \RuntimeException('Unexpected fake provider path '.$path),
            });
        });
    }

    private function price(string $id): array
    {
        return ['id' => $id, 'livemode' => $this->objectMode, 'active' => true, 'currency' => 'aed', 'unit_amount' => str_contains($id, 'professional') ? 49900 : 19900, 'tax_behavior' => 'exclusive', 'recurring' => ['interval' => 'month', 'interval_count' => 1]];
    }

    private function company(): array
    {
        $this->artisan('platform:install-plans');
        $c = Customer::create(['company_name' => 'Live Fixture Company', 'is_active' => true]);
        $u = User::factory()->create(['customer_id' => $c->id]);
        $w = app(CompanyWorkspaceService::class)->activateNewRegistration($c, $u);

        return [$u, $w];
    }

    private function bound(): array
    {
        [$u,$w] = $this->company();
        $plan = PlatformPlan::where('code', 'business')->first();
        $a = CompanyBillingAccount::create(['company_workspace_id' => $w->id, 'customer_key' => (string) Str::uuid(), 'stripe_customer_id' => 'cus_live_fixture', 'plan_code' => 'business', 'price_id' => 'price_business_live', 'plan_snapshot' => $plan->only(['code', 'name_ar', 'name_en', 'monthly_amount_minor', 'currency', 'seats', 'active_opportunities', 'monthly_applications'])]);
        $start = now()->timestamp;
        $end = now()->addMonth()->timestamp;
        $this->subscription = ['id' => 'sub_live_fixture', 'livemode' => $this->objectMode, 'customer' => 'cus_live_fixture', 'status' => 'active', 'latest_invoice' => 'in_live_fixture', 'cancel_at_period_end' => false, 'items' => ['data' => [['id' => 'si_live_fixture', 'quantity' => 1, 'price' => ['id' => 'price_business_live'], 'current_period_start' => $start, 'current_period_end' => $end]]]];
        $this->invoice = ['id' => 'in_live_fixture', 'livemode' => $this->objectMode, 'customer' => 'cus_live_fixture', 'status' => 'paid', 'amount_paid' => 20895, 'amount_due' => 20895, 'amount_remaining' => 0, 'subtotal' => 19900, 'total' => 20895, 'currency' => 'aed', 'automatic_tax' => ['enabled' => true, 'status' => 'complete'], 'amount_shipping' => 0, 'amount_overpaid' => 0, 'collection_method' => 'charge_automatically', 'starting_balance' => 0, 'pre_payment_credit_notes_amount' => 0, 'post_payment_credit_notes_amount' => 0, 'discounts' => [], 'total_discount_amounts' => [], 'billing_reason' => 'subscription_create', 'parent' => ['subscription_details' => ['subscription' => 'sub_live_fixture']], 'lines' => ['has_more' => false, 'data' => [['quantity' => 1, 'amount' => 19900, 'pricing' => ['price_details' => ['price' => 'price_business_live']], 'period' => ['start' => $start, 'end' => $end]]]]];
        $this->charge = ['id' => 'ch_live_fixture', 'livemode' => $this->objectMode, 'customer' => 'cus_live_fixture', 'payment_intent' => 'pi_live_fixture', 'currency' => 'aed', 'paid' => true, 'captured' => true, 'amount_captured' => 20895, 'refunded' => false, 'amount_refunded' => 0, 'disputed' => false];

        return [$u, $w, $a];
    }

    private function event(bool $live = true, string $path = 'platform-billing.live-webhook', string $id = 'evt_live_fixture', string $type = 'invoice.paid')
    {
        $raw = json_encode(['id' => $id, 'type' => $type, 'livemode' => $live, 'data' => ['object' => ['customer' => 'cus_live_fixture', 'charge' => 'ch_live_fixture']]]);
        $t = time();
        $sig = 't='.$t.',v1='.hash_hmac('sha256', $t.'.'.$raw, 'whsec_live_fixture');

        return $this->call('POST', route($path), [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $sig], $raw);
    }

    public function test_live_checkout_requires_two_explicit_flags_and_owner_confirmation(): void
    {
        [$u] = $this->company();
        config(['platform_billing.checkout_enabled' => false]);
        $this->actingAs($u)->post(route('company.billing.checkout'), ['plan' => 'business', 'confirmed' => 1])->assertSessionHasErrors('billing');
        Http::assertNothingSent();
        config(['platform_billing.checkout_enabled' => true]);
        $this->post(route('company.billing.checkout'), ['plan' => 'business'])->assertSessionHasErrors('billing');
        Http::assertNothingSent();
        $this->post(route('company.billing.checkout'), ['plan' => 'business', 'confirmed' => 1])->assertRedirect('https://checkout.stripe.com/c/pay/live_fixture');
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/checkout/sessions') && $r['payment_method_types'] === ['card'] && $r['line_items'][0]['price'] === 'price_business_live');
        $this->assertDatabaseCount('company_live_billing_accounts', 1);
        $this->assertDatabaseCount('company_billing_accounts', 0);
    }

    public function test_mismatched_keys_debug_http_and_unknown_mode_disable_gateway(): void
    {
        $g = app(StripeTestGateway::class);
        $this->assertTrue($g->ready());
        foreach ([['platform_billing.secret' => 'sk_test_wrong'], ['app.debug' => true], ['app.url' => 'http://example.test'], ['platform_billing.mode' => 'invalid']] as $changes) {
            $before = [];
            foreach ($changes as $k => $v) {
                $before[$k] = config($k);
            }config($changes);
            $this->assertFalse($g->ready());
            config($before);
        }
        config(['platform_billing.secret' => 'rk_live_fake']);
        $this->assertTrue($g->ready());
        Http::assertNothingSent();
    }

    public function test_signed_test_events_and_live_events_on_test_endpoint_are_rejected(): void
    {
        $this->bound();
        $this->event(false)->assertStatus(400);
        $this->event(true, 'platform-billing.webhook')->assertStatus(400);
        $this->assertDatabaseCount('company_live_billing_events', 0);
        Http::assertNothingSent();
    }

    public function test_paid_live_invoice_requires_real_provider_payment_proof_and_grants_once(): void
    {
        [$u,$w,$a] = $this->bound();
        $this->event()->assertOk();
        $this->event()->assertOk();
        $this->assertDatabaseCount('company_live_billing_receipts', 1);
        $this->assertDatabaseCount('company_billing_receipts', 0);
        $this->assertSame('stripe_live', app(CompanyWorkspaceService::class)->plan($w)['source']);
        $this->assertTrue($a->fresh()->paid_until->isFuture());
    }

    public function test_manual_payment_missing_proof_and_truncated_payments_do_not_grant(): void
    {
        [$u,$w,$a] = $this->bound();
        $s = app(CompanyBillingService::class);
        $this->manualPayment = true;
        $s->sync($a);
        $this->assertNull($a->fresh()->paid_until);
        $this->manualPayment = false;
        $this->paymentListIncomplete = true;
        $s->sync($a);
        $this->assertNull($a->fresh()->paid_until);
        $this->assertDatabaseCount('company_live_billing_receipts', 0);
    }

    public function test_wrong_amount_price_period_and_customer_do_not_grant(): void
    {
        [$u,$w,$a] = $this->bound();
        $original = $this->invoice;
        foreach ([['amount_paid' => 1], ['customer' => 'cus_foreign'], ['subtotal' => 1], ['lines' => ['has_more' => false, 'data' => [['quantity' => 1, 'amount' => 19900, 'pricing' => ['price_details' => ['price' => 'price_foreign']], 'period' => $original['lines']['data'][0]['period']]]]]] as $change) {
            $this->invoice = array_replace($original, $change);
            app(CompanyBillingService::class)->sync($a);
            $this->assertNull($a->fresh()->paid_until);
        }
        $this->invoice = $original;
        $this->invoice['lines']['data'][0]['period']['end']++;
        app(CompanyBillingService::class)->sync($a);
        $this->assertNull($a->fresh()->paid_until);
        $this->assertDatabaseCount('company_live_billing_receipts', 0);
    }

    public function test_refund_and_dispute_remove_access_without_deleting_receipt(): void
    {
        [$u,$w,$a] = $this->bound();
        $s = app(CompanyBillingService::class);
        $s->sync($a);
        $this->assertTrue($a->fresh()->paid_until->isFuture());
        $this->charge['amount_refunded'] = 100;
        $this->charge['refunded'] = true;
        $this->event(true, 'platform-billing.live-webhook', 'evt_refund', 'charge.refunded')->assertOk();
        $this->assertNull($a->fresh()->paid_until);
        $this->assertDatabaseCount('company_live_billing_receipts', 1);
        $this->charge['amount_refunded'] = 0;
        $this->charge['refunded'] = false;
        $this->charge['disputed'] = true;
        $this->event(true, 'platform-billing.live-webhook', 'evt_dispute', 'charge.dispute.created')->assertOk();
        $this->assertNull($a->fresh()->paid_until);
    }

    public function test_provider_failure_never_creates_receipt_or_records_event_as_success(): void
    {
        [$u,$w,$a] = $this->bound();
        $this->providerFailure = true;
        $this->event()->assertStatus(500);
        $this->assertDatabaseCount('company_live_billing_events', 0);
        $this->assertDatabaseCount('company_live_billing_receipts', 0);
    }

    public function test_test_history_survives_mode_switch_and_never_grants_live_access(): void
    {
        [$u,$w] = $this->company();
        config(['platform_billing.mode' => 'test', 'platform_billing.secret' => 'sk_test_fake']);
        $test = CompanyBillingAccount::create(['company_workspace_id' => $w->id, 'customer_key' => (string) Str::uuid(), 'stripe_customer_id' => 'cus_test_old', 'stripe_subscription_id' => 'sub_test_old', 'status' => 'active', 'paid_until' => now()->addMonth(), 'plan_snapshot' => ['code' => 'professional']]);
        config(['platform_billing.mode' => 'live', 'platform_billing.secret' => 'sk_live_fixture_fake']);
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan($w)['source']);
        $this->assertSame('company_billing_accounts', $test->getTable());
        $this->actingAs($u)->get(route('company.billing'))->assertOk()->assertDontSee('cus_test_old');
        $this->assertDatabaseCount('company_billing_accounts', 1);
    }

    public function test_cross_mode_model_cannot_be_synced_with_live_key(): void
    {
        [$u,$w] = $this->company();
        config(['platform_billing.mode' => 'test']);
        $old = app(CompanyBillingService::class)->account($w);
        config(['platform_billing.mode' => 'live']);
        try {
            app(CompanyBillingService::class)->sync($old);
            $this->fail('Expected cross-mode block');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }Http::assertNothingSent();
    }

    public function test_live_cancellation_and_checkout_kill_switch_keep_confirmed_access(): void
    {
        [$u,$w,$a] = $this->bound();
        $s = app(CompanyBillingService::class);
        $s->sync($a);
        config(['platform_billing.checkout_enabled' => false]);
        $s->cancellation($u, true);
        $this->assertTrue($a->fresh()->cancel_at_period_end);
        $this->assertSame('stripe_live', app(CompanyWorkspaceService::class)->plan($w)['source']);
        $this->assertTrue($a->fresh()->paid_until->isFuture());
    }

    public function test_duplicate_open_live_subscriptions_block_access(): void
    {
        [$u,$w,$a] = $this->bound();
        $this->duplicateSubscription = true;
        app(CompanyBillingService::class)->sync($a);
        $this->assertSame('conflict', $a->fresh()->status);
        $this->assertNull($a->fresh()->paid_until);
    }

    public function test_both_languages_display_real_billing_and_confirmation(): void
    {
        [$u] = $this->company();
        foreach (['ar', 'en'] as $lang) {
            $this->actingAs($u)->get(route('company.billing', ['lang' => $lang]))->assertOk()->assertSee('name="confirmed"', false)->assertDontSee('test_only');
        }
    }

    public function test_incomplete_or_unpaid_live_subscription_never_grants_access(): void
    {
        [$u,$w,$a] = $this->bound();
        $this->invoice['status'] = 'open';
        $this->invoice['amount_paid'] = 0;
        app(CompanyBillingService::class)->sync($a);
        $this->assertNull($a->fresh()->paid_until);
        $this->subscription['status'] = 'incomplete';
        app(CompanyBillingService::class)->sync($a);
        $this->assertNull($a->fresh()->paid_until);
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan($w)['source']);
    }

    public function test_test_mode_cannot_accept_live_object_or_live_endpoint(): void
    {
        config(['platform_billing.mode' => 'test', 'platform_billing.secret' => 'sk_test_fake']);
        $this->event(true)->assertStatus(400);
        try {
            app(StripeTestGateway::class)->testObject(['livemode' => $this->objectMode]);
            $this->fail('Expected wrong mode rejection');
        } catch (\RuntimeException $e) {
            $this->assertSame('Stripe object mode mismatch.', $e->getMessage());
        }
        Http::assertNothingSent();
    }

    public function test_live_invoice_with_failed_tax_calculation_is_not_granted(): void
    {
        [$u,$w,$a] = $this->bound();
        $this->invoice['automatic_tax']['status'] = 'failed';
        app(CompanyBillingService::class)->sync($a);
        $this->assertNull($a->fresh()->paid_until);
        $this->assertDatabaseCount('company_live_billing_receipts', 0);
    }

    public function test_wrong_charge_customer_currency_and_capture_cannot_grant(): void
    {
        [$u,$w,$a] = $this->bound();
        $original = $this->charge;
        foreach ([['customer' => 'cus_foreign'], ['currency' => 'usd'], ['captured' => false], ['amount_captured' => 1]] as $change) {
            $this->charge = array_replace($original, $change);
            app(CompanyBillingService::class)->sync($a);
            $this->assertNull($a->fresh()->paid_until);
        }
        $this->assertDatabaseCount('company_live_billing_receipts', 0);
    }

    public function test_repeated_live_checkout_reuses_same_session(): void
    {
        [$u] = $this->company();
        $service = app(CompanyBillingService::class);
        $first = $service->checkout($u, 'business', true);
        $second = $service->checkout($u, 'business', true);
        $this->assertSame($first, $second);
        $this->assertSame(1, count(Http::recorded(fn ($r) => $r->method() === 'POST' && str_ends_with($r->url(), '/checkout/sessions'))));
        $this->assertDatabaseCount('company_live_billing_accounts', 1);
    }

    public function test_strict_payment_proof_can_be_rehearsed_in_test_mode(): void
    {
        config(['platform_billing.mode' => 'test', 'platform_billing.secret' => 'sk_test_fixture', 'platform_billing.strict_proof_test' => true]);
        $this->objectMode = false;
        [$u,$w,$a] = $this->bound();
        $this->charge['captured'] = false;
        app(CompanyBillingService::class)->sync($a);
        $this->assertNull($a->fresh()->paid_until);
        $this->charge['captured'] = true;
        app(CompanyBillingService::class)->sync($a);
        $this->assertTrue($a->fresh()->paid_until->isFuture());
        $this->assertSame('stripe_test', app(CompanyWorkspaceService::class)->plan($w)['source']);
        $this->assertDatabaseCount('company_live_billing_receipts', 0);
    }

    public function test_live_sync_revision_detects_concurrent_same_second_writes(): void
    {
        [$u,$w,$a] = $this->bound();
        $this->mutateOnInvoice = true;
        app(CompanyBillingService::class)->sync($a);
        $this->assertSame(2, count(Http::recorded(fn ($r) => str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/subscriptions'))));
        $this->assertSame(2, (int) $a->fresh()->sync_revision);
        $this->assertTrue($a->fresh()->paid_until->isFuture());
    }

    public function test_preflight_is_get_only_and_never_opens_checkout(): void
    {
        $this->company();
        $this->artisan('platform:billing-check --remote')->expectsOutputToContain('GET-only remote checks passed')->assertSuccessful();
        Http::assertNotSent(fn ($r) => $r->method() !== 'GET');
        $this->assertDatabaseCount('company_live_billing_accounts', 0);
    }
}
