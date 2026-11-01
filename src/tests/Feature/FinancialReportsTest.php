<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\Role;
use App\Models\User;
use App\Services\Revenue\FinancialReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class FinancialReportsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $roleName = 'admin'): User
    {
        $user = User::factory()->create();
        $user->addRole(Role::firstOrCreate(['name' => $roleName], ['display_name' => $roleName]));
        return $user;
    }

    private function record(
        string $currency = 'AED', string $amount = '10.00', string $status = 'pending',
        string $approvedAt = '2026-11-01 12:00:00', ?Customer $customer = null
    ): Deal {
        $customer ??= Customer::create(['company_name' => 'Finance customer '.Str::random(6)]);
        $deal = Deal::create([
            'reference' => 'FIN-'.Str::upper(Str::random(12)), 'customer_id' => $customer->id,
            'collection_model' => 'commission_only', 'status' => 'approved',
            'currency' => $currency, 'deal_amount' => '1000.00', 'platform_revenue' => '0.00',
            'approved_at' => $approvedAt,
        ]);
        $agreement = $deal->agreements()->create([
            'version' => 1, 'status' => 'approved', 'fee_kind' => 'fixed',
            'fixed_fee' => $amount, 'terms' => 'Approved test terms', 'approved_at' => $approvedAt,
        ]);
        $deal->commissions()->create([
            'deal_agreement_id' => $agreement->id, 'currency' => $currency,
            'basis_amount' => '1000.00', 'amount' => $amount, 'status' => $status,
        ]);
        return $deal;
    }

    public function test_guest_and_regular_user_cannot_read_financial_data(): void
    {
        $deal = $this->record();
        $this->get(route('admin.finance.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create());
        $this->get(route('admin.finance.index'))->assertForbidden()->assertDontSee($deal->reference);
        $this->get(route('admin.finance.show', $deal))->assertForbidden()->assertDontSee($deal->reference);
    }

    public function test_admin_and_super_admin_can_open_both_languages(): void
    {
        $deal = $this->record();
        foreach (['admin', 'super-admin'] as $role) {
            $this->actingAs($this->admin($role));
            foreach (['ar', 'en'] as $locale) {
                $this->withSession(['locale' => $locale])->get(route('admin.finance.index'))
                    ->assertOk()->assertSee($deal->reference)->assertDontSee('finance.title');
                $this->get(route('admin.finance.show', $deal))->assertOk()->assertSee($deal->reference);
            }
        }
    }

    public function test_currency_summaries_and_statuses_are_separate_and_exact(): void
    {
        $this->record('AED', '0.10');
        $this->record('AED', '0.20');
        $this->record('AED', '5.00', 'earned');
        $this->record('AED', '2.00', 'reversed');
        $this->record('USD', '7.00');
        $service = new FinancialReport();
        $summary = $service->summary($service->deals([]));
        $this->assertSame(['AED', 'USD'], array_keys($summary));
        $this->assertSame('0.30', $summary['AED']['pending']);
        $this->assertSame('5.00', $summary['AED']['earned']);
        $this->assertSame('2.00', $summary['AED']['reversed']);
        $this->assertSame('7.00', $summary['USD']['pending']);
        $this->assertSame('4000.00', $summary['AED']['deal_value']);
    }

    public function test_drafts_do_not_appear_or_open_as_approved_deals(): void
    {
        $draft = Deal::create([
            'reference' => 'DRAFT-PRIVATE', 'customer_id' => Customer::create(['company_name' => 'Draft customer'])->id,
            'collection_model' => 'commission_only', 'currency' => 'AED', 'deal_amount' => '1000.00',
        ]);
        $this->actingAs($this->admin())->get(route('admin.finance.index'))->assertOk()->assertDontSee('DRAFT-PRIVATE');
        $this->get(route('admin.finance.show', $draft))->assertNotFound();
    }

    public function test_filters_use_customer_currency_and_commission_status(): void
    {
        $customer = Customer::create(['company_name' => 'Selected customer']);
        $wanted = $this->record('AED', '10.00', 'pending', customer: $customer);
        $earned = $this->record('AED', '20.00', 'earned', customer: $customer);
        $usd = $this->record('USD', '30.00', customer: $customer);
        $other = $this->record();
        $this->actingAs($this->admin())->get(route('admin.finance.index', [
            'customer_id' => $customer->id, 'currency' => 'aed', 'commission_status' => 'pending',
        ]))->assertOk()->assertSee($wanted->reference)->assertDontSee($earned->reference)
            ->assertDontSee($usd->reference)->assertDontSee($other->reference)
            ->assertViewHas('summary', fn ($summary) => array_keys($summary) === ['AED'] && $summary['AED']['pending'] === '10.00');
    }

    public function test_date_filter_includes_whole_last_day_and_excludes_next_day(): void
    {
        $wanted = $this->record(approvedAt: '2026-11-01 23:59:59');
        $nextDay = $this->record(approvedAt: '2026-11-02 00:00:00');
        $previous = $this->record(approvedAt: '2026-10-31 23:59:59');
        $this->actingAs($this->admin())->get(route('admin.finance.index', [
            'date_from' => '2026-11-01', 'date_to' => '2026-11-01',
        ]))->assertOk()->assertSee($wanted->reference)->assertDontSee($nextDay->reference)->assertDontSee($previous->reference);
    }

    public function test_invalid_filters_return_validation_errors(): void
    {
        $this->actingAs($this->admin())->getJson(route('admin.finance.index', [
            'date_from' => '2026-11-05', 'date_to' => '2026-11-01',
            'customer_id' => 999999, 'currency' => 'BAD-CURRENCY', 'commission_status' => 'paid',
        ]))->assertUnprocessable()->assertJsonValidationErrors(['date_to', 'customer_id', 'currency', 'commission_status']);
    }

    public function test_totals_cover_all_pages_and_pagination_keeps_filters(): void
    {
        $customer = Customer::create(['company_name' => 'Pagination customer']);
        for ($i = 0; $i < 21; $i++) $this->record('AED', '1.00', customer: $customer);
        $this->actingAs($this->admin())->get(route('admin.finance.index', ['currency' => 'AED']))
            ->assertOk()
            ->assertViewHas('deals', fn ($deals) => $deals->total() === 21 && $deals->count() === 20 && str_contains($deals->nextPageUrl(), 'currency=AED'))
            ->assertViewHas('summary', fn ($summary) => $summary['AED']['pending'] === '21.00');
    }

    public function test_report_requests_do_not_change_financial_records(): void
    {
        $deal = $this->record();
        $snapshot = fn () => collect(['deals', 'deal_agreements', 'deal_commissions', 'deal_audits'])
            ->mapWithKeys(fn ($table) => [$table => DB::table($table)->orderBy('id')->get()->toJson()])->all();
        $before = $snapshot();
        $this->actingAs($this->admin())->get(route('admin.finance.index'))->assertOk();
        $this->get(route('admin.finance.show', $deal))->assertOk();
        $this->assertSame($before, $snapshot());
    }

    public function test_cash_is_unavailable_not_reported_as_collected_commission(): void
    {
        $deal = $this->record('AED', '125.00', 'earned');
        $this->actingAs($this->admin())->withSession(['locale' => 'en'])
            ->get(route('admin.finance.show', $deal))->assertOk()
            ->assertSee('Cash received')->assertSee('Unavailable')->assertSee('does not confirm payment');
    }

    public function test_report_has_no_post_put_or_delete_actions(): void
    {
        $deal = $this->record();
        $this->actingAs($this->admin())->post(route('admin.finance.index'))->assertStatus(405);
        $this->put(route('admin.finance.show', $deal))->assertStatus(405);
        $this->delete(route('admin.finance.show', $deal))->assertStatus(405);
        $this->assertDatabaseHas('deals', ['id' => $deal->id, 'status' => 'approved']);
    }
}
