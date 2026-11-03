<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BringDealWizardTest extends TestCase
{
    use RefreshDatabase;

    private function data(array $extra = []): array
    {
        return array_replace(['name' => 'Test submitter', 'company_name' => 'Test trading company', 'phone' => '+971500000001', 'email' => 'submitter@example.test', 'deal_type' => 'buy', 'category' => 'consumer_goods', 'title' => 'Office chairs deal enquiry', 'description' => 'We need 50 adjustable office chairs delivered in Dubai for a business project.', 'estimated_value' => '10000.25', 'currency' => 'AED', 'country' => 'United Arab Emirates', 'city' => 'Dubai', 'preferred_contact' => 'whatsapp'], $extra);
    }

    public function test_guest_can_open_bilingual_card_wizard_with_shared_theme_and_no_extra_toggle(): void
    {
        foreach (['ar', 'en'] as $lang) {
            $this->get(route('leads.create', ['lang' => $lang]))->assertOk()->assertSee('bringDeal(')->assertSee('data-deal-step="1"', false)->assertSee('data-deal-step="5"', false)->assertSee('deal-type-sell')->assertSee('deal-category-marine')->assertSee('deal-category-electronics')->assertSee('hag-theme')->assertDontSee('bring_deal.')->assertDontSee('data-theme-toggle');
        }
    }

    public function test_submission_creates_private_crm_lead_without_publishing_or_financial_records(): void
    {
        $this->post(route('leads.store'), $this->data(['status' => 'won', 'source' => 'approved', 'assigned_to' => 999, 'visibility' => 'public']))->assertRedirect(route('leads.create'))->assertSessionHas('success');
        $lead = Lead::firstOrFail();
        $this->assertSame('new', $lead->status);
        $this->assertSame('website', $lead->source);
        $this->assertNull($lead->assigned_to);
        $this->assertSame('Test submitter', $lead->contact_person);
        $this->assertSame('+971500000001', $lead->phone);
        $this->assertStringContainsString('Country: United Arab Emirates', $lead->notes);
        $this->assertStringContainsString('10,000.25 AED', $lead->notes);
        foreach (['opportunities', 'customers', 'deals', 'issued_invoices', 'invoice_payments'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }

    public function test_original_type_options_and_new_card_types_are_supported(): void
    {
        foreach (['trade', 'import', 'export', 'investment', 'partnership', 'procurement', 'distribution', 'logistics', 'other', 'sell', 'service_request', 'vessel_brokerage'] as $type) {
            $this->post(route('leads.store'), $this->data(['deal_type' => $type]))->assertRedirect(route('leads.create'));
            $this->assertStringContainsString('Deal Type: '.$type, Lead::latest('id')->first()->notes);
        }
    }

    public function test_optional_company_category_email_and_value_remain_optional(): void
    {
        $this->post(route('leads.store'), $this->data(['company_name' => null, 'category' => null, 'email' => null, 'estimated_value' => null]))->assertRedirect();
        $lead = Lead::firstOrFail();
        $this->assertSame('Test submitter', $lead->company_name);
        $this->assertNull($lead->email);
    }

    public function test_email_preference_requires_valid_email_and_unknown_options_are_rejected(): void
    {
        foreach ([['email' => null, 'preferred_contact' => 'email'], ['preferred_contact' => 'fax'], ['deal_type' => 'unknown'], ['category' => 'invented'], ['currency' => 'ZZZ'], ['phone' => ''], ['description' => 'short']] as $bad) {
            $this->postJson(route('leads.store'), $this->data($bad))->assertUnprocessable();
        }$this->assertDatabaseCount('leads', 0);
    }

    public function test_values_are_exact_including_zero_and_reject_invalid_precision(): void
    {
        foreach (['0' => '0.00 AED', '9999999999999.99' => '9,999,999,999,999.99 AED'] as $value => $formatted) {
            $this->post(route('leads.store'), $this->data(['estimated_value' => (string) $value]))->assertRedirect();
            $this->assertStringContainsString($formatted, Lead::latest('id')->first()->notes);
        }
        foreach (['-1', '1e3', '1.234', '10000000000000.00'] as $invalid) {
            $this->postJson(route('leads.store'), $this->data(['estimated_value' => $invalid]))->assertUnprocessable();
        }
    }

    public function test_validation_returns_to_correct_step_and_preserves_escaped_old_input(): void
    {
        $this->from(route('leads.create'))->post(route('leads.store'), $this->data(['phone' => '', 'title' => '<script>alert(1)</script>']))->assertSessionHasErrors('phone');
        $this->get(route('leads.create'))->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false)->assertSee('deal-title');
    }

    public function test_logged_in_user_prefill_and_success_state_render_without_public_contact_data(): void
    {
        $user = User::factory()->create(['name' => 'Known user', 'email' => 'known@example.test', 'phone' => '+971500000002']);
        $this->actingAs($user)->get(route('leads.create'))->assertOk()->assertSee('known@example.test')->assertSee('Known user');
        $this->post(route('leads.store'), $this->data())->assertRedirect();
        $this->get(route('leads.create'))->assertOk()->assertSee('deal-success')->assertDontSee('name="phone"',false);
        $this->assertDatabaseCount('leads',1);
    }
}
