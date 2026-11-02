<?php

namespace Tests\Feature;

use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\InvoiceDraft;
use App\Models\IssuedInvoice;
use App\Models\Role;
use App\Models\User;
use App\Services\Revenue\DealApprovalService;
use App\Services\Revenue\InvoiceDraftService;
use App\Services\Revenue\InvoiceIssuanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\Revenue\InvoicePdfService;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = 'admin'): User
    {
        $user = User::factory()->create();
        $user->addRole(Role::firstOrCreate(['name' => $role], ['display_name' => $role]));
        return $user;
    }

    private function company(): CompanySetting
    {
        return CompanySetting::updateOrCreate([], [
            'company_name' => 'HAG Test Supplier', 'company_name_ar' => 'المورد التجريبي',
            'address' => 'Supplier Test Address', 'city' => 'Dubai', 'country' => 'UAE',
            'tax_number' => '100000000000001',
        ]);
    }

    private function draft(User $actor, string $currency = 'AED'): InvoiceDraft
    {
        $customer = Customer::create(['company_name' => 'Invoice Test Customer']);
        $deal = Deal::create([
            'reference' => 'ISSUE-'.Str::upper(Str::random(12)), 'customer_id' => $customer->id,
            'collection_model' => 'full_service', 'currency' => $currency,
            'deal_amount' => '1000.00', 'supplier_cost' => '800.00', 'platform_revenue' => '0.00',
        ]);
        $agreement = $deal->agreements()->create([
            'version' => 1, 'status' => 'draft', 'fee_kind' => 'fixed',
            'fixed_fee' => '125.50', 'terms' => 'Approved invoice fixture terms',
        ]);
        $commission = (new DealApprovalService())->approve($deal, $agreement, $actor->id);
        return (new InvoiceDraftService())->create($commission, $actor->id);
    }

    private function input(): array
    {
        return [
            'supply_date' => now('Asia/Dubai')->toDateString(),
            'due_date' => now('Asia/Dubai')->addDays(30)->toDateString(),
            'tax_treatment' => 'standard', 'amount_basis' => 'exclusive',
            'recipient_registered' => 'yes', 'recipient_trn' => '100000000000002',
            'recipient_address' => 'Customer Test Address',
            'description_ar' => 'خدمة وساطة تجريبية', 'description_en' => 'Test brokerage service',
            'public_notes' => 'Public invoice note',
        ];
    }

    private function review(User $actor, InvoiceDraft $draft, array $changes = []): array
    {
        return (new InvoiceIssuanceService())->preview($draft, array_replace($this->input(), $changes), $actor->id);
    }

    private function issue(User $actor, InvoiceDraft $draft, array $changes = []): IssuedInvoice
    {
        $review = $this->review($actor, $draft, $changes);
        return (new InvoiceIssuanceService())->issue($draft, $review['token'], $actor->id);
    }


    public function test_admin_downloads_pdf_in_both_languages_without_financial_writes(): void
    {
        $this->company(); $admin = $this->admin(); $invoice = $this->issue($admin, $this->draft($admin));
        $before = DB::table('issued_invoices')->first();
        $sequence = DB::table('invoice_sequences')->first();
        foreach (['ar', 'en'] as $lang) {
            $response = $this->actingAs($admin)->get(route('admin.invoices.pdf', [$invoice, $lang]));
            $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent());
            $this->assertStringContainsString($invoice->reference.'-'.$lang.'.pdf', $response->headers->get('Content-Disposition'));
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        }
        $this->assertEquals($before, DB::table('issued_invoices')->first());
        $this->assertEquals($sequence, DB::table('invoice_sequences')->first());
    }

    public function test_pdf_contains_only_frozen_public_invoice_data(): void
    {
        $company = $this->company(); $admin = $this->admin(); $invoice = $this->issue($admin, $this->draft($admin));
        $company->update(['company_name' => 'Changed live supplier', 'tax_number' => '100000000000099']);
        Customer::query()->update(['company_name' => 'Changed live customer']);
        $html = app(InvoicePdfService::class)->html($invoice->fresh(), 'en');
        foreach (['HAG Test Supplier', 'Invoice Test Customer', '100000000000001', 'Test brokerage service', '125.50', '6.28', '131.78'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        foreach (['Changed live supplier', 'Changed live customer', 'supplier_cost', 'platform_revenue', 'Approved invoice fixture terms'] as $text) {
            $this->assertStringNotContainsString($text, $html);
        }
    }

    public function test_requested_language_does_not_depend_on_session_language(): void
    {
        $this->company(); $admin = $this->admin(); $invoice = $this->issue($admin, $this->draft($admin));
        app()->setLocale('en');
        $html = app(InvoicePdfService::class)->html($invoice, 'ar');
        $this->assertStringContainsString('dir="rtl"', $html);
        $this->assertStringContainsString('فاتورة ضريبية', $html);
        $this->assertStringContainsString('خدمة وساطة تجريبية', $html);
        $this->assertSame('en', app()->getLocale());
    }

    public function test_tampered_invoice_is_blocked_before_rendering(): void
    {
        $this->company(); $admin = $this->admin(); $invoice = $this->issue($admin, $this->draft($admin));
        DB::table('issued_invoices')->where('id', $invoice->id)->update(['total' => '999.00']);
        $this->actingAs($admin)->get(route('admin.invoices.pdf', [$invoice, 'en']))->assertStatus(409);
    }

    public function test_guest_and_regular_user_cannot_download_pdf(): void
    {
        $this->company(); $admin = $this->admin(); $invoice = $this->issue($admin, $this->draft($admin));
        $url = route('admin.invoices.pdf', [$invoice, 'en']);
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
    }

    public function test_super_admin_can_download_and_unknown_invoice_or_language_is_404(): void
    {
        $this->company(); $admin = $this->admin('super-admin'); $invoice = $this->issue($admin, $this->draft($admin));
        $this->actingAs($admin)->get(route('admin.invoices.pdf', [$invoice, 'en']))->assertOk();
        $this->get('/admin/invoices/999999/pdf/en')->assertNotFound();
        $this->get('/admin/invoices/'.$invoice->id.'/pdf/fr')->assertNotFound();
    }

    public function test_snapshot_html_is_escaped_and_cannot_load_external_resources(): void
    {
        $this->company(); $admin = $this->admin();
        $invoice = $this->issue($admin, $this->draft($admin), ['public_notes' => '<img src="https://example.test/private">']);
        $html = app(InvoicePdfService::class)->html($invoice, 'en');
        $this->assertStringContainsString('&lt;img', $html);
        $this->assertStringNotContainsString('<img ', $html);
    }

    public function test_foreign_currency_pdf_shows_frozen_exchange_rate_and_aed_totals(): void
    {
        $this->company(); $admin = $this->admin();
        $invoice = $this->issue($admin, $this->draft($admin, 'USD'), [
            'exchange_rate' => '3.6725', 'exchange_rate_date' => now('Asia/Dubai')->toDateString(),
            'exchange_rate_reference' => 'Approved test FX reference',
        ]);
        $html = app(InvoicePdfService::class)->html($invoice, 'en');
        foreach (['3.67250000', 'USD', '483.96', '23.06', 'Approved test FX reference'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
    }

    public function test_download_links_appear_on_valid_invoice_page(): void
    {
        $this->company(); $admin = $this->admin(); $invoice = $this->issue($admin, $this->draft($admin));
        $this->actingAs($admin)->get(route('admin.invoices.show', $invoice))->assertOk()
            ->assertSee(route('admin.invoices.pdf', [$invoice, 'ar']))
            ->assertSee(route('admin.invoices.pdf', [$invoice, 'en']));
    }
    public function test_pdf_preserves_inclusive_and_zero_rated_invoice_amounts(): void
    {
        $this->company(); $admin = $this->admin();
        $inclusive = $this->issue($admin, $this->draft($admin), ['amount_basis' => 'inclusive']);
        $html = app(InvoicePdfService::class)->html($inclusive, 'en');
        $this->assertStringContainsString('119.52', $html);
        $this->assertStringContainsString('5.98', $html);
        $this->assertStringContainsString('125.50', $html);
        $zero = $this->issue($admin, $this->draft($admin), [
            'tax_treatment' => 'zero', 'tax_reason' => 'Reviewed zero rate test basis',
        ]);
        $html = app(InvoicePdfService::class)->html($zero, 'en');
        $this->assertStringContainsString('0.00', $html);
        $this->assertStringContainsString('Reviewed zero rate test basis', $html);
    }

}
