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
use App\Models\InvoicePayment;
use App\Services\Revenue\InvoicePaymentService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InvoicePaymentsTest extends TestCase
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


    private function receipt(array $changes = []): array
    {
        return array_replace([
            'request_key' => (string) Str::uuid(), 'amount' => '50.00',
            'paid_on' => now('Asia/Dubai')->toDateString(), 'method' => 'bank_transfer',
            'reference' => 'BANK-'.Str::uuid(), 'notes' => 'Internal receipt note',
        ], $changes);
    }

    private function setupInvoice(string $currency = 'AED'): array
    {
        $this->company(); $admin = $this->admin();
        $changes = $currency === 'AED' ? [] : [
            'exchange_rate' => '3.6725', 'exchange_rate_date' => now('Asia/Dubai')->toDateString(),
            'exchange_rate_reference' => 'Reviewed test rate',
        ];
        return [$admin, $this->issue($admin, $this->draft($admin, $currency), $changes), new InvoicePaymentService()];
    }

    private function invalid(callable $callback, string $key): void
    {
        try { $callback(); $this->fail('Expected validation rejection.'); }
        catch (ValidationException $exception) { $this->assertStringContainsString(__('payments.'.$key), implode(' ', array_merge(...array_values($exception->errors())))); }
    }

    public function test_partial_and_full_payments_use_exact_remaining_gross_amount(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        $this->assertSame('unpaid', $service->summary($invoice)['status']);
        $service->record($invoice, $this->receipt(['amount' => '0.10']), $admin->id);
        $service->record($invoice, $this->receipt(['amount' => '0.20']), $admin->id);
        $summary = $service->summary($invoice);
        $this->assertSame('0.30', $summary['paid']);
        $this->assertSame('131.48', $summary['remaining']);
        $this->assertSame('partial', $summary['status']);
        $service->record($invoice, $this->receipt(['amount' => '131.48']), $admin->id);
        $this->assertSame('0.00', $service->summary($invoice)['remaining']);
        $this->assertSame('paid', $service->summary($invoice)['status']);
    }

    public function test_payment_above_remaining_is_rejected_without_new_event(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        $service->record($invoice, $this->receipt(['amount' => '100.00']), $admin->id);
        $this->invalid(fn () => $service->record($invoice, $this->receipt(['amount' => '31.79']), $admin->id), 'overpayment');
        $this->assertDatabaseCount('invoice_payments', 1);
    }

    public function test_same_request_is_idempotent_and_changed_request_is_rejected(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice(); $input = $this->receipt();
        $a = $service->record($invoice, $input, $admin->id);
        $b = $service->record($invoice, $input, $admin->id);
        $this->assertSame($a->id, $b->id);
        $this->invalid(fn () => $service->record($invoice, array_replace($input, ['amount' => '10.00']), $admin->id), 'request_conflict');
        $other = $this->admin();
        $this->invalid(fn () => $service->record($invoice, $input, $other->id), 'request_conflict');
        $this->assertDatabaseCount('invoice_payments', 1);
    }

    public function test_bank_reference_cannot_be_reused_with_a_new_request_key(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        $service->record($invoice, $this->receipt(['reference' => 'BANK-ABC']), $admin->id);
        $this->invalid(fn () => $service->record($invoice, $this->receipt(['reference' => ' bank-abc ']), $admin->id), 'duplicate_reference');
        $this->assertDatabaseCount('invoice_payments', 1);
    }

    public function test_zero_negative_excess_precision_and_non_decimal_amounts_are_rejected(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        foreach (['0', '-1.00', '1.001', '1e2', '1,00', '99999999999999999.00'] as $amount) {
            $this->actingAs($admin)->postJson(route('admin.invoices.payments.store', $invoice), $this->receipt(['amount' => $amount]))->assertUnprocessable();
        }
        $this->assertDatabaseCount('invoice_payments', 0);
    }

    public function test_future_or_pre_issue_receipt_date_and_currency_override_are_rejected(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        $url = route('admin.invoices.payments.store', $invoice);
        foreach ([['paid_on' => now('Asia/Dubai')->addDay()->toDateString()], ['paid_on' => '2000-01-01'], ['currency' => 'USD'], ['issued_invoice_id' => 9999], ['reference' => '   '], ['method' => 'crypto'], ['request_key' => 'wrong']] as $changes) {
            $this->actingAs($admin)->postJson($url, $this->receipt($changes))->assertUnprocessable();
        }
        $this->assertDatabaseCount('invoice_payments', 0);
    }

    public function test_receipts_do_not_change_invoice_commission_agreement_or_number_sequence(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        $tables = ['issued_invoices', 'invoice_drafts', 'deal_commissions', 'deal_agreements', 'deals', 'invoice_sequences'];
        $before = [];
        foreach ($tables as $table) $before[$table] = DB::table($table)->get()->toJson();
        $payment = $service->record($invoice, $this->receipt(), $admin->id);
        $service->reverse($invoice, $payment, ['request_key' => (string) Str::uuid(), 'reason' => 'Wrong test entry'], $admin->id);
        foreach ($tables as $table) $this->assertSame($before[$table], DB::table($table)->get()->toJson(), $table);
    }

    public function test_reversal_restores_balance_without_deleting_original(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        $payment = $service->record($invoice, $this->receipt(), $admin->id);
        $before = DB::table('invoice_payments')->where('id', $payment->id)->first();
        $input = ['request_key' => (string) Str::uuid(), 'reason' => 'Incorrect receipt'];
        $reverse = $service->reverse($invoice, $payment, $input, $admin->id);
        $this->assertSame($reverse->id, $service->reverse($invoice, $payment, $input, $admin->id)->id);
        $this->assertEquals($before, DB::table('invoice_payments')->where('id', $payment->id)->first());
        $this->assertSame('unpaid', $service->summary($invoice)['status']);
        $this->assertSame('131.78', $service->summary($invoice)['remaining']);
        $this->assertDatabaseCount('invoice_payments', 2);
    }

    public function test_second_reversal_or_reversing_a_reversal_is_rejected(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        $payment = $service->record($invoice, $this->receipt(), $admin->id);
        $input = ['request_key' => (string) Str::uuid(), 'reason' => 'Correction'];
        $reverse = $service->reverse($invoice, $payment, $input, $admin->id);
        $this->invalid(fn () => $service->reverse($invoice, $payment, array_replace($input, ['request_key' => (string) Str::uuid()]), $admin->id), 'already_reversed');
        $this->invalid(fn () => $service->reverse($invoice, $reverse, array_replace($input, ['request_key' => (string) Str::uuid()]), $admin->id), 'wrong_payment');
    }

    public function test_full_payment_can_be_reversed_and_replaced_with_a_new_reference(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        $payment = $service->record($invoice, $this->receipt(['amount' => '131.78']), $admin->id);
        $service->reverse($invoice, $payment, ['request_key' => (string) Str::uuid(), 'reason' => 'Wrong transaction'], $admin->id);
        $this->invalid(fn () => $service->record($invoice, $this->receipt(['reference' => $payment->reference]), $admin->id), 'duplicate_reference');
        $service->record($invoice, $this->receipt(['amount' => '131.78']), $admin->id);
        $this->assertSame('paid', $service->summary($invoice)['status']);
    }

    public function test_foreign_invoice_receipts_stay_in_original_currency(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice('USD');
        $payment = $service->record($invoice, $this->receipt(['amount' => '100.00']), $admin->id);
        $this->assertSame('USD', $payment->currency);
        $this->assertSame('31.78', $service->summary($invoice)['remaining']);
        $this->assertSame('USD', $service->summary($invoice)['currency']);
    }

    public function test_receipt_model_blocks_changes_and_deletion(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        $payment = $service->record($invoice, $this->receipt(), $admin->id);
        try { $payment->update(['amount' => '1.00']); $this->fail('Update should fail'); } catch (\LogicException $e) { $this->assertStringContainsString('cannot', $e->getMessage()); }
        try { $payment->delete(); $this->fail('Delete should fail'); } catch (\LogicException $e) { $this->assertStringContainsString('cannot', $e->getMessage()); }
    }

    public function test_invoice_tampering_blocks_receipts(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        DB::table('issued_invoices')->where('id', $invoice->id)->update(['total' => '999.00']);
        $this->invalid(fn () => $service->record($invoice->fresh(), $this->receipt(), $admin->id), 'invoice_invalid');
        $this->assertDatabaseCount('invoice_payments', 0);
    }

    public function test_receipt_tampering_hides_balance_and_blocks_new_receipts(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        $payment = $service->record($invoice, $this->receipt(), $admin->id);
        DB::table('invoice_payments')->where('id', $payment->id)->update(['amount' => '1.00']);
        $this->invalid(fn () => $service->summary($invoice), 'ledger_invalid');
        $this->invalid(fn () => $service->record($invoice, $this->receipt(), $admin->id), 'ledger_invalid');
        $this->actingAs($admin)->get(route('admin.invoices.payments', $invoice))->assertOk()->assertSee(__('payments.ledger_invalid'))->assertDontSee('name="amount"', false);
    }

    public function test_admin_can_open_both_languages_and_post_receipt_and_reversal(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        foreach (['ar', 'en'] as $locale) $this->actingAs($admin)->withSession(['locale' => $locale])->get(route('admin.invoices.payments', $invoice))->assertOk();
        $this->post(route('admin.invoices.payments.store', $invoice), $this->receipt())->assertRedirect(route('admin.invoices.payments', $invoice));
        $payment = InvoicePayment::firstOrFail();
        $this->post(route('admin.invoices.payments.reverse', [$invoice, $payment]), ['request_key' => (string) Str::uuid(), 'reason' => 'Correction'])->assertRedirect(route('admin.invoices.payments', $invoice));
        $this->assertDatabaseCount('invoice_payments', 2);
    }

    public function test_guest_and_regular_user_cannot_read_record_or_reverse(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        $payment = $service->record($invoice, $this->receipt(), $admin->id);
        $show = route('admin.invoices.payments', $invoice);
        $store = route('admin.invoices.payments.store', $invoice);
        $reverse = route('admin.invoices.payments.reverse', [$invoice, $payment]);
        $this->get($show)->assertRedirect(route('login'));
        $this->post($store, $this->receipt())->assertRedirect(route('login'));
        $this->post($reverse, ['request_key' => (string) Str::uuid(), 'reason' => 'Forbidden'])->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create());
        $this->get($show)->assertForbidden(); $this->post($store, $this->receipt())->assertForbidden();
        $this->post($reverse, ['request_key' => (string) Str::uuid(), 'reason' => 'Forbidden'])->assertForbidden();
        $this->assertDatabaseCount('invoice_payments', 1);
    }

    public function test_cross_invoice_reversal_is_rejected(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        $other = $this->issue($admin, $this->draft($admin));
        $payment = $service->record($invoice, $this->receipt(), $admin->id);
        $this->actingAs($admin)->post(route('admin.invoices.payments.reverse', [$other, $payment]), ['request_key' => (string) Str::uuid(), 'reason' => 'Wrong invoice'])->assertNotFound();
        $this->assertDatabaseCount('invoice_payments', 1);
    }

    public function test_blank_reversal_reason_is_rejected(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        $payment = $service->record($invoice, $this->receipt(), $admin->id);
        $this->actingAs($admin)->postJson(route('admin.invoices.payments.reverse', [$invoice, $payment]), ['request_key' => (string) Str::uuid(), 'reason' => '   '])->assertUnprocessable();
        $this->assertDatabaseCount('invoice_payments', 1);
    }

    public function test_super_admin_access_and_missing_invoice(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        $super = $this->admin('super-admin');
        $this->actingAs($super)->get(route('admin.invoices.payments', $invoice))->assertOk();
        $this->post(route('admin.invoices.payments.store', $invoice), $this->receipt())->assertRedirect();
        $this->get('/admin/invoices/999999/payments')->assertNotFound();
    }

    public function test_failed_insert_leaves_no_payment_and_balance_unchanged(): void
    {
        [$admin, $invoice, $service] = $this->setupInvoice();
        DB::listen(function ($query): void {
            if (str_starts_with(strtolower($query->sql), 'insert into "invoice_payments"')) throw new \RuntimeException('Simulated payment failure');
        });
        try { $service->record($invoice, $this->receipt(), $admin->id); $this->fail('Insert must fail'); }
        catch (\RuntimeException $e) { $this->assertSame('Simulated payment failure', $e->getMessage()); }
        $this->assertDatabaseCount('invoice_payments', 0);
        $this->assertSame('131.78', $service->summary($invoice)['remaining']);
    }
}
