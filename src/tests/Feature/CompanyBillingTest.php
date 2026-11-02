<?php

namespace Tests\Feature;

use App\Models\CompanyBillingAccount;
use App\Models\CompanyBillingEvent;
use App\Models\CompanyBillingReceipt;
use App\Models\CompanyMember;
use App\Models\Customer;
use App\Models\PlatformPlan;
use App\Models\Role;
use App\Models\User;
use App\Services\Platform\CompanyBillingService;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class CompanyBillingTest extends TestCase
{
    use RefreshDatabase;

    private ?\Closure $providerObserver = null;

    private ?array $remote = null;

    private bool $providerFailure = false;

    private bool $duplicates = false;

    private array $invoice = [];

    private array $price = [];

    private array $session = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['platform_billing.enabled' => true, 'platform_billing.secret' => 'sk_test_local_fake', 'platform_billing.webhook_secret' => 'whsec_local_fake', 'platform_billing.prices' => ['business' => 'price_business', 'professional' => 'price_professional'], 'platform_billing.portal_configuration' => 'bpc_test']);
        $this->price = ['id' => 'price_business', 'livemode' => false, 'active' => true, 'currency' => 'aed', 'unit_amount' => 19900, 'tax_behavior' => 'exclusive', 'recurring' => ['interval' => 'month', 'interval_count' => 1]];
        $this->session = ['id' => 'cs_test_hag', 'livemode' => false, 'customer' => 'cus_hag', 'mode' => 'subscription', 'url' => 'https://checkout.stripe.com/c/pay/test', 'status' => 'open', 'subscription' => null];
        Http::preventStrayRequests();
        Http::fake(function ($r) {
            if ($this->providerFailure) {
                return Http::response(['error' => ['message' => 'temporary']], 503);
            }
            $path = parse_url($r->url(), PHP_URL_PATH);
            if ($this->providerObserver) {
                ($this->providerObserver)($path);
            }
            if (str_starts_with($path, '/v1/prices/')) {
                return Http::response($this->price);
            }
            if ($path === '/v1/customers') {
                return Http::response(['id' => 'cus_hag', 'livemode' => false]);
            }
            if ($path === '/v1/subscriptions') {
                return Http::response(['data' => $this->remote ? ($this->duplicates ? [$this->remote, array_replace($this->remote, ['id' => 'sub_second'])] : [$this->remote]) : [], 'has_more' => false]);
            }
            if (str_starts_with($path, '/v1/invoices/')) {
                return Http::response($this->invoice);
            }
            if (str_starts_with($path, '/v1/checkout/sessions')) {
                return Http::response($this->session);
            }
            if ($path === '/v1/billing_portal/sessions') {
                return Http::response(['url' => 'https://billing.stripe.com/p/session/test']);
            }
            if ($path === '/v1/subscriptions/sub_hag') {
                $this->remote['cancel_at_period_end'] = $r['cancel_at_period_end'] === 'true';

                return Http::response($this->remote);
            }
            throw new \RuntimeException('Unexpected Stripe path '.$path);
        });
    }

    private function company(): array
    {
        $this->artisan('platform:install-plans');
        $c = Customer::create(['company_name' => 'Billing Company', 'is_active' => true]);
        $u = User::factory()->create(['customer_id' => $c->id]);
        $w = app(CompanyWorkspaceService::class)->activateNewRegistration($c, $u);

        return [$u, $w];
    }

    private function bound(): array
    {
        [$u,$w] = $this->company();
        $p = PlatformPlan::where('code', 'business')->first();
        $a = CompanyBillingAccount::create(['company_workspace_id' => $w->id, 'customer_key' => (string) Str::uuid(), 'stripe_customer_id' => 'cus_hag', 'plan_code' => 'business', 'price_id' => 'price_business', 'plan_snapshot' => $p->only(['code', 'name_ar', 'name_en', 'monthly_amount_minor', 'currency', 'seats', 'active_opportunities', 'monthly_applications'])]);
        $start = now('UTC')->timestamp;
        $end = now('UTC')->addMonth()->timestamp;
        $this->remote = ['id' => 'sub_hag', 'livemode' => false, 'customer' => 'cus_hag', 'status' => 'active', 'cancel_at_period_end' => false, 'latest_invoice' => 'in_paid', 'items' => ['data' => [['id' => 'si_hag', 'price' => ['id' => 'price_business'], 'quantity' => 1, 'current_period_start' => $start, 'current_period_end' => $end]]]];
        $this->invoice = ['id' => 'in_paid', 'livemode' => false, 'customer' => 'cus_hag', 'status' => 'paid', 'amount_paid' => 20895, 'currency' => 'aed', 'billing_reason' => 'subscription_create', 'parent' => ['subscription_details' => ['subscription' => 'sub_hag']], 'lines' => ['data' => [['period' => ['start' => $start, 'end' => $end]]], 'has_more' => false]];

        return [$u, $w, $a];
    }

    private function webhook(string $id = 'evt_hag', string $type = 'invoice.paid', array $changes = [])
    {
        $event = array_replace_recursive(['id' => $id, 'type' => $type, 'livemode' => false, 'data' => ['object' => ['customer' => 'cus_hag']]], $changes);
        $raw = json_encode($event);
        $t = time();
        $sig = 't='.$t.',v1='.hash_hmac('sha256', $t.'.'.$raw, 'whsec_local_fake');

        return $this->call('POST', route('platform-billing.webhook'), [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $sig], $raw);
    }

    public function test_owner_checkout_uses_server_price_and_returns_hosted_test_page(): void
    {
        [$u,$w] = $this->company();
        $this->actingAs($u)->post(route('company.billing.checkout'), ['plan' => 'business', 'amount' => 1, 'price' => 'price_attacker', 'company_workspace_id' => 999])->assertRedirect('https://checkout.stripe.com/c/pay/test');
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/checkout/sessions') && $r['line_items'][0]['price'] === 'price_business' && $r['line_items'][0]['quantity'] === 1);
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan($w)['code']);
        $this->assertDatabaseCount('company_billing_receipts', 0);
    }

    public function test_repeated_checkout_reuses_session_without_duplicate_creation(): void
    {
        [$u] = $this->company();
        $this->actingAs($u)->post(route('company.billing.checkout'), ['plan' => 'business'])->assertRedirect();
        $this->post(route('company.billing.checkout'), ['plan' => 'business'])->assertRedirect();
        $this->assertCount(1, Http::recorded(fn ($r) => $r->method() === 'POST' && str_ends_with($r->url(), '/checkout/sessions')));
        $this->assertDatabaseCount('company_billing_accounts', 1);
    }

    public function test_success_query_does_not_grant_access(): void
    {
        [$u,$w] = $this->company();
        $this->actingAs($u)->get(route('company.billing').'?checkout=returned&paid=1&plan=professional')->assertOk();
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan($w)['code']);
        Http::assertNothingSent();
    }

    public function test_live_key_and_live_event_are_blocked(): void
    {
        [$u] = $this->company();
        config(['platform_billing.secret' => 'sk_live_fake']);
        $this->actingAs($u)->post(route('company.billing.checkout'), ['plan' => 'business'])->assertStatus(503);
        Http::assertNothingSent();
        config(['platform_billing.secret' => 'sk_test_local_fake']);
        $this->webhook(changes: ['livemode' => true])->assertBadRequest();
        $this->assertDatabaseCount('company_billing_events', 0);
    }

    public function test_disabled_billing_does_not_call_provider(): void
    {
        [$u] = $this->company();
        config(['platform_billing.enabled' => false]);
        $this->actingAs($u)->get(route('company.billing'))->assertOk();
        $this->post(route('company.billing.checkout'), ['plan' => 'business'])->assertStatus(503);
        Http::assertNothingSent();
    }

    public function test_missing_or_invalid_webhook_signature_is_rejected(): void
    {
        $this->postJson(route('platform-billing.webhook'), ['id' => 'evt_fake'])->assertBadRequest();
        $raw = '{}';
        $t = time() - 400;
        $sig = 't='.$t.',v1='.hash_hmac('sha256', $t.'.'.$raw, 'whsec_local_fake');
        $this->call('POST', route('platform-billing.webhook'), [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $sig], $raw)->assertBadRequest();
        Http::assertNothingSent();
    }

    public function test_confirmed_paid_invoice_grants_frozen_company_plan_once(): void
    {
        [$u,$w,$a] = $this->bound();
        $this->webhook()->assertOk();
        $this->webhook()->assertOk();
        $plan = app(CompanyWorkspaceService::class)->plan($w);
        $this->assertSame('business', $plan['code']);
        $this->assertSame('stripe_test', $plan['source']);
        $this->assertDatabaseCount('company_billing_receipts', 1);
        $this->assertDatabaseCount('company_billing_events', 1);
        Http::assertSentCount(2);
    }

    public function test_signed_paid_event_alone_cannot_grant_unpaid_invoice_access(): void
    {
        [$u,$w] = $this->bound();
        $this->invoice['status'] = 'open';
        $this->invoice['amount_paid'] = 0;
        $this->webhook()->assertOk();
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan($w)['code']);
        $this->assertDatabaseCount('company_billing_receipts', 0);
    }

    public function test_wrong_customer_invoice_or_currency_cannot_grant_access(): void
    {
        [$u,$w,$a] = $this->bound();
        $this->invoice['customer'] = 'cus_foreign';
        app(CompanyBillingService::class)->sync($a);
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan($w)['code']);
        $this->invoice['customer'] = 'cus_hag';
        $this->invoice['currency'] = 'usd';
        app(CompanyBillingService::class)->sync($a);
        $this->assertDatabaseCount('company_billing_receipts', 0);
    }

    public function test_wrong_invoice_period_cannot_extend_access(): void
    {
        [$u,$w,$a] = $this->bound();
        $this->invoice['lines']['data'][0]['period']['end'] += 86400;
        app(CompanyBillingService::class)->sync($a);
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan($w)['code']);
    }

    public function test_unrecognized_price_revokes_access_and_blocks_second_checkout(): void
    {
        [$u,$w,$a] = $this->bound();
        $this->remote['items']['data'][0]['price']['id'] = 'price_unknown';
        app(CompanyBillingService::class)->sync($a);
        $this->assertSame('invalid_price', $a->fresh()->status);
        $this->actingAs($u)->postJson(route('company.billing.checkout'), ['plan' => 'business'])->assertUnprocessable();
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan($w)['code']);
    }

    public function test_delayed_event_uses_latest_provider_state_not_old_payload(): void
    {
        [$u,$w] = $this->bound();
        $this->webhook()->assertOk();
        $this->remote['status'] = 'canceled';
        $this->webhook('evt_delayed', 'invoice.paid')->assertOk();
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan($w)['code']);
    }

    public function test_duplicate_open_subscription_blocks_checkout(): void
    {
        [$u] = $this->bound();
        $this->actingAs($u)->postJson(route('company.billing.checkout'), ['plan' => 'business'])->assertUnprocessable();
        $this->assertCount(0, Http::recorded(fn ($r) => str_ends_with($r->url(), '/checkout/sessions')));
    }

    public function test_price_amount_tax_mode_or_live_price_mismatch_blocks_checkout(): void
    {
        [$u] = $this->company();
        foreach ([['unit_amount' => 1], ['tax_behavior' => 'unspecified'], ['currency' => 'usd'], ['livemode' => true]] as $bad) {
            $old = $this->price;
            $this->price = array_replace($old, $bad);
            $this->actingAs($u)->post(route('company.billing.checkout'), ['plan' => 'business'])->assertRedirect();
            $this->price = $old;
        }$this->assertDatabaseCount('company_billing_accounts', 0);
    }

    public function test_failed_renewal_does_not_extend_paid_access(): void
    {
        [$u,$w,$a] = $this->bound();
        app(CompanyBillingService::class)->sync($a);
        $until = $a->fresh()->paid_until;
        $this->remote['status'] = 'past_due';
        $this->invoice['status'] = 'open';
        $this->invoice['amount_paid'] = 0;
        app(CompanyBillingService::class)->sync($a);
        $this->assertSame($until->timestamp, $a->fresh()->paid_until->timestamp);
        $this->travelTo($until->addSecond());
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan($w)['code']);
    }

    public function test_paid_renewal_records_new_invoice_and_extends_period(): void
    {
        [$u,$w,$a] = $this->bound();
        app(CompanyBillingService::class)->sync($a);
        $old = $a->fresh()->paid_until->timestamp;
        $start = $old;
        $end = $old + 2592000;
        $this->remote['items']['data'][0]['current_period_start'] = $start;
        $this->remote['items']['data'][0]['current_period_end'] = $end;
        $this->remote['latest_invoice'] = 'in_renewal';
        $this->invoice['id'] = 'in_renewal';
        $this->invoice['billing_reason'] = 'subscription_cycle';
        $this->invoice['lines']['data'][0]['period'] = ['start' => $start, 'end' => $end];
        app(CompanyBillingService::class)->sync($a);
        $this->assertSame($end, $a->fresh()->paid_until->timestamp);
        $this->assertDatabaseCount('company_billing_receipts', 2);
    }

    public function test_owner_can_cancel_at_period_end_and_resume_without_losing_paid_period(): void
    {
        [$u,$w,$a] = $this->bound();
        $this->actingAs($u)->post(route('company.billing.cancel'), ['cancel' => 1])->assertRedirect();
        $this->assertTrue($a->fresh()->cancel_at_period_end);
        $this->assertSame('business', app(CompanyWorkspaceService::class)->plan($w)['code']);
        $this->post(route('company.billing.cancel'), ['cancel' => 0])->assertRedirect();
        $this->assertFalse($a->fresh()->cancel_at_period_end);
    }

    public function test_members_managers_guests_and_admins_cannot_manage_company_billing(): void
    {
        [$u,$w] = $this->company();
        $this->get(route('company.billing'))->assertRedirect(route('login'));
        foreach (['member', 'manager'] as $role) {
            $m = User::factory()->create(['customer_id' => $u->customer_id]);
            CompanyMember::create(['company_workspace_id' => $w->id, 'user_id' => $m->id, 'role' => $role]);
            $this->actingAs($m)->get(route('company.billing'))->assertForbidden();
            $this->post(route('company.billing.checkout'), ['plan' => 'business'])->assertForbidden();
        }Http::assertNothingSent();
    }

    public function test_billing_page_does_not_show_other_company_receipts(): void
    {
        [$owner,$w,$a] = $this->bound();
        app(CompanyBillingService::class)->sync($a);
        [$other] = $this->company();
        $this->actingAs($other)->get(route('company.billing').'?company_workspace_id='.$w->id)->assertOk()->assertDontSee('in_paid');
    }

    public function test_admin_billing_overview_is_read_only(): void
    {
        [$u] = $this->bound();
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        $this->actingAs($admin)->get(route('admin.platform.billing'))->assertOk();
        $this->post('/admin/platform/billing', [])->assertStatus(405);
        $this->get(route('company.billing'))->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_company_pages_work_in_both_languages(): void
    {
        [$u] = $this->company();
        foreach (['ar', 'en'] as $locale) {
            $this->actingAs($u)->withSession(['locale' => $locale])->get(route('company.billing'))->assertOk();
        }
    }

    public function test_subscription_receipts_are_immutable_and_do_not_change_brokerage_finances(): void
    {
        [$u,$w,$a] = $this->bound();
        app(CompanyBillingService::class)->sync($a);
        $r = CompanyBillingReceipt::firstOrFail();
        try {
            $r->update(['amount_paid_minor' => 1]);
            $this->fail('Immutable');
        } catch (\LogicException) {
            $this->assertTrue(true);
        }try {
            $r->delete();
            $this->fail('Immutable');
        } catch (\LogicException) {
            $this->assertTrue(true);
        }foreach (['issued_invoices', 'invoice_payments', 'deal_commissions', 'deals'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }

    public function test_provider_failure_rolls_back_event_so_delivery_can_retry(): void
    {
        [$u,$w] = $this->bound();
        $this->providerFailure = true;
        $this->webhook()->assertStatus(500);
        $this->assertDatabaseCount('company_billing_events', 0);
        $this->assertDatabaseCount('company_billing_receipts', 0);
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan($w)['code']);
    }

    public function test_background_reconciliation_restores_paid_state_without_webhook(): void
    {
        [$u,$w] = $this->bound();
        $this->artisan('platform:billing-reconcile')->assertSuccessful();
        $this->assertSame('business', app(CompanyWorkspaceService::class)->plan($w)['code']);
    }

    public function test_disabled_reconciliation_has_no_network_calls(): void
    {
        config(['platform_billing.enabled' => false]);
        $this->artisan('platform:billing-reconcile')->assertSuccessful();
        Http::assertNothingSent();
    }

    public function test_portal_uses_company_customer_and_trusted_host(): void
    {
        [$u] = $this->bound();
        $this->actingAs($u)->post(route('company.billing.portal'))->assertRedirect('https://billing.stripe.com/p/session/test');
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/billing_portal/sessions') && $r['customer'] === 'cus_hag');
    }

    public function test_professional_price_is_selected_server_side(): void
    {
        [$u] = $this->company();
        $this->price['id'] = 'price_professional';
        $this->price['unit_amount'] = 49900;
        $this->actingAs($u)->post(route('company.billing.checkout'), ['plan' => 'professional'])->assertRedirect();
        $this->assertDatabaseHas('company_billing_accounts', ['plan_code' => 'professional', 'price_id' => 'price_professional']);
    }

    public function test_conflicting_provider_subscriptions_block_access_and_checkout(): void
    {
        [$u,$w,$a] = $this->bound();
        $this->duplicates = true;
        app(CompanyBillingService::class)->sync($a);
        $this->assertSame('conflict', $a->fresh()->status);
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan($w)['code']);
        $this->actingAs($u)->postJson(route('company.billing.checkout'), ['plan' => 'business'])->assertUnprocessable();
    }

    public function test_old_trial_cannot_reappear_after_provider_cancellation(): void
    {
        [$u,$w,$a] = $this->bound();
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        app(CompanyWorkspaceService::class)->trial($w, PlatformPlan::where('code', 'professional')->first(), 30, 'Pre-billing evaluation', $admin);
        app(CompanyBillingService::class)->sync($a);
        $this->assertSame('business', app(CompanyWorkspaceService::class)->plan($w)['code']);
        $this->remote['status'] = 'canceled';
        app(CompanyBillingService::class)->sync($a);
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan($w)['code']);
        $this->actingAs($admin)->postJson(route('admin.platform.trial', $w), ['plan_id' => PlatformPlan::where('code', 'professional')->first()->id, 'days' => 7, 'reason' => 'Override'])->assertUnprocessable();
    }

    public function test_missing_known_subscription_does_not_clear_paid_state_or_allow_duplicate(): void
    {
        [$u,$w,$a] = $this->bound();
        app(CompanyBillingService::class)->sync($a);
        $until = $a->fresh()->paid_until->timestamp;
        $this->remote = null;
        try {
            app(CompanyBillingService::class)->sync($a);
            $this->fail('Expected failure');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Known subscription', $e->getMessage());
        }
        $this->assertSame($until, $a->fresh()->paid_until->timestamp);
    }

    public function test_suspended_company_cannot_checkout_or_manage_subscription(): void
    {
        [$u,$w] = $this->company();
        $w->update(['status' => 'suspended']);
        $this->actingAs($u)->post(route('company.billing.checkout'), ['plan' => 'business'])->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_changed_hosted_checkout_host_is_rejected(): void
    {
        [$u] = $this->company();
        $this->session['url'] = 'https://attacker.example/checkout';
        $this->actingAs($u)->post(route('company.billing.checkout'), ['plan' => 'business'])->assertRedirect()->assertSessionHasErrors('billing');
        $this->assertDatabaseCount('company_billing_receipts', 0);
    }

    public function test_disabling_test_mode_removes_test_entitlements_without_network(): void
    {
        [$u,$w,$a] = $this->bound();
        app(CompanyBillingService::class)->sync($a);
        config(['platform_billing.enabled' => false]);
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan($w)['code']);
        Http::assertSentCount(2);
    }

    public function test_expired_checkout_does_not_start_another_while_completion_is_unreconciled(): void
    {
        [$u,$w] = $this->company();
        $this->actingAs($u)->post(route('company.billing.checkout'), ['plan' => 'business'])->assertRedirect();
        CompanyBillingAccount::query()->update(['checkout_expires_at' => now('UTC')->subMinute()]);
        $this->session['status'] = 'complete';
        $this->session['subscription'] = 'sub_unreconciled';
        $this->postJson(route('company.billing.checkout'), ['plan' => 'business'])->assertUnprocessable();
        $this->assertCount(1, Http::recorded(fn ($r) => $r->method() === 'POST' && str_ends_with($r->url(), '/checkout/sessions')));
    }

    public function test_confirmed_subscription_changes_real_marketplace_limits_and_cancellation_restores_free_limits(): void
    {
        [$owner, $workspace, $account] = $this->bound();
        $payload = fn () => ['request_key' => (string) Str::uuid(), 'title' => 'Subscription listing', 'description' => 'Commercial opportunity with enough details for publishing.', 'opportunity_type' => 'trade', 'currency' => 'AED', 'commission_type' => 'fixed'];
        $this->actingAs($owner)->post(route('opportunities.store'), $payload())->assertRedirect();
        $this->postJson(route('opportunities.store'), $payload())->assertUnprocessable();
        app(CompanyBillingService::class)->sync($account);
        $this->post(route('opportunities.store'), $payload())->assertRedirect();
        $this->assertDatabaseCount('opportunities', 2);
        $this->remote['status'] = 'canceled';
        app(CompanyBillingService::class)->sync($account);
        $this->postJson(route('opportunities.store'), $payload())->assertUnprocessable();
        $this->assertDatabaseCount('opportunities', 2);
    }

    public function test_webhook_provider_calls_do_not_hold_an_extra_database_transaction(): void
    {
        $this->bound();
        $baseline = DB::transactionLevel();
        $calls = 0;
        $this->providerObserver = function () use ($baseline, &$calls): void {
            $calls++;
            $this->assertSame($baseline, DB::transactionLevel());
        };
        $this->webhook()->assertOk();
        $this->assertSame(2, $calls);
        $this->assertDatabaseCount('company_billing_events', 1);
    }

    public function test_changed_account_during_provider_request_is_refetched_before_save(): void
    {
        [, , $account] = $this->bound();
        $calls = 0;
        $this->providerObserver = function ($path) use ($account, &$calls): void {
            if ($path !== '/v1/subscriptions') {
                return;
            }
            $calls++;
            if ($calls === 1) {
                $account->update(['status' => 'concurrent_change']);
            }
        };
        $this->webhook()->assertOk();
        $this->assertSame(2, $calls);
        $this->assertSame('active', $account->fresh()->status);
        $this->assertDatabaseCount('company_billing_receipts', 1);
    }

    public function test_non_lock_database_error_is_not_retried_or_acknowledged(): void
    {
        $this->bound();
        $attempts = 0;
        CompanyBillingReceipt::creating(function () use (&$attempts): void {
            $attempts++;
            $previous = new \PDOException('Simulated constraint error');
            $previous->errorInfo = ['23000', 19, 'constraint failed'];
            throw new QueryException('sqlite', 'insert receipt', [], $previous);
        });
        $this->webhook()->assertStatus(500);
        $this->assertSame(1, $attempts);
        $this->assertDatabaseCount('company_billing_receipts', 0);
        $this->assertDatabaseCount('company_billing_events', 0);
    }

    public function test_event_record_failure_can_be_retried_after_committed_sync(): void
    {
        $this->bound();
        $attempts = 0;
        CompanyBillingEvent::creating(function () use (&$attempts): void {
            if (++$attempts === 1) {
                throw new \RuntimeException('Simulated event write failure.');
            }
        });
        $this->webhook()->assertStatus(500);
        $this->assertDatabaseCount('company_billing_events', 0);
        $this->assertDatabaseCount('company_billing_receipts', 1);
        $this->webhook()->assertOk();
        $this->assertDatabaseCount('company_billing_events', 1);
        $this->assertDatabaseCount('company_billing_receipts', 1);
    }

    public function test_three_checkout_events_share_one_receipt_and_preserve_paid_access(): void
    {
        [, $workspace, $account] = $this->bound();
        foreach (['checkout.session.completed', 'invoice.paid', 'customer.subscription.created'] as $index => $type) {
            $this->webhook('evt_parallel_'.$index, $type)->assertOk();
        }
        $this->assertDatabaseCount('company_billing_events', 3);
        $this->assertDatabaseCount('company_billing_receipts', 1);
        $this->assertSame('active', $account->fresh()->status);
        $this->assertSame('business', app(CompanyWorkspaceService::class)->plan($workspace)['code']);
    }
}
