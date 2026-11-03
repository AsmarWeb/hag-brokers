<?php

namespace Tests\Feature;

use App\Models\CompanyMember;
use App\Models\CompanyTradeOffer;
use App\Models\CompanyTradeOfferEvent;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Role;
use App\Models\User;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\CompanyTradeOfferService;
use App\Services\Platform\CompanyWorkspaceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CompanyTradeOffersTest extends TestCase
{
    use RefreshDatabase;

    private function company(string $role = 'owner'): array
    {
        $this->artisan('platform:install-plans');
        $customer = Customer::create(['company_name' => 'UAE Company '.Str::random(6), 'is_active' => true]);
        $owner = User::factory()->create(['customer_id' => $customer->id]);
        $workspace = app(CompanyWorkspaceService::class)->activateNewRegistration($customer, $owner);
        if ($role === 'owner') {
            return [$owner, $workspace];
        }
        $member = User::factory()->create(['customer_id' => $customer->id]);
        CompanyMember::create(['company_workspace_id' => $workspace->id, 'user_id' => $member->id, 'role' => $role]);

        return [$member, $workspace];
    }

    private function listingData(array $extra = []): array
    {
        return array_replace(['request_key' => (string) Str::uuid(), 'title' => 'Office chairs for Dubai businesses',
            'description' => 'Supply of office chairs with adjustable armrests for business offices in Dubai.',
            'opportunity_type' => 'sell', 'category' => 'consumer_goods', 'country' => 'United Arab Emirates', 'city' => 'Dubai', 'currency' => 'AED',
            'commercial_details' => ['product_name' => 'Office chair', 'specifications' => 'Adjustable armrests', 'quantity' => '500.125', 'unit' => 'piece', 'unit_price' => '199.99', 'min_order_quantity' => '10', 'lead_time_days' => 14, 'delivery_terms' => 'dap', 'payment_terms' => 'To be agreed', 'target_market' => 'UAE', 'condition' => 'new', 'price_basis' => 'quotation']], $extra);
    }

    private function publish(User $user, array $extra = []): Opportunity
    {
        $this->actingAs($user)->post(route('opportunities.store'), $this->listingData($extra))->assertRedirect(route('company.marketplace'));

        return Opportunity::latest('id')->firstOrFail();
    }

    private function yachtData(): array
    {
        return ['title' => 'Dubai yacht with private cabins', 'category' => 'marine', 'estimated_value' => '950000.00', 'commercial_details' => ['subcategory' => 'yachts', 'features' => ['stabilizers', 'tender']], 'vessel' => ['listing_type' => 'sale', 'vessel_category' => 'yacht', 'builder' => 'Test shipyard', 'length_m' => '25.50', 'beam_m' => '6.20', 'draft_m' => '1.80', 'cabins' => 4, 'guest_capacity' => 8, 'crew_capacity' => 3, 'engine_make' => 'Test engine', 'engine_hours' => 450, 'price_on_request' => false]];
    }

    private function setupOffer(): array
    {
        [$buyer] = $this->company();
        $o = $this->publish($buyer, ['opportunity_type' => 'buy']);
        [$supplier,$w] = $this->company();

        return [$buyer, $supplier, $o, $w];
    }

    private function data(array $extra = []): array
    {
        return array_replace(['request_key' => (string) Str::uuid(), 'quantity' => '2.125', 'unit' => 'piece', 'unit_price' => '19.99', 'currency' => 'AED', 'tax_basis' => 'exclusive', 'delivery_days' => '7', 'valid_until' => now('Asia/Dubai')->addDays(10)->toDateString(), 'payment_terms' => 'Bank transfer after delivery confirmation.', 'notes' => 'Supplier declared specifications.', 'confirmed' => 1], $extra);
    }

    private function send(User $supplier, Opportunity $o, array $data = []): CompanyTradeOffer
    {
        $this->actingAs($supplier)->post(route('company.trade-offers.store', $o), $data ?: $this->data())->assertRedirect();

        return CompanyTradeOffer::latest('id')->firstOrFail();
    }

    private function decision(CompanyTradeOffer $q, string $action = 'selected', int $version = 0, array $extra = [])
    {
        return $this->post(route('company.trade-offers.decide', $q), array_replace(['action' => $action, 'version' => $version, 'reason' => 'Decision after reviewing the commercial terms.', 'confirmed' => 1], $extra));
    }

    private function admin(): User
    {
        $u = User::factory()->create();
        $u->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));

        return $u;
    }

    public function test_submission_is_exact_private_and_creates_no_financial_records(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        $this->assertSame('42.48', $q->base_amount);
        $this->assertSame('2.125', $q->quantity);
        $this->assertTrue(app(CompanyTradeOfferService::class)->verify($q));
        $this->assertDatabaseCount('opportunity_interests', 1);
        $this->assertDatabaseCount('company_marketplace_requests', 2);
        foreach (['deals', 'issued_invoices', 'invoice_payments', 'deal_commissions'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
        $this->get(route('company.trade-offers.show', $q))->assertOk()->assertSee('42.48')->assertDontSee('rfq.integrity');
        $this->actingAs($buyer)->get(route('company.trade-offers.comparison', $o))->assertOk()->assertSee('42.48');
        $this->get(route('opportunities.show', $o))->assertDontSee('42.48')->assertDontSee('Supplier declared specifications.');
    }

    public function test_identical_retry_is_idempotent_changed_terms_and_new_key_duplicate_are_rejected(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $data = $this->data();
        $this->send($supplier, $o, $data);
        $this->post(route('company.trade-offers.store', $o), $data)->assertRedirect();
        $this->postJson(route('company.trade-offers.store', $o), array_replace($data, ['unit_price' => '20']))->assertUnprocessable();
        $this->postJson(route('company.trade-offers.store', $o), array_replace($data, ['request_key' => (string) Str::uuid()]))->assertUnprocessable();
        $this->assertDatabaseCount('company_trade_offers', 1);
        $this->assertDatabaseCount('company_trade_offer_events', 1);
        $this->assertSame(1, $buyer->notifications()->get()->filter(fn ($n) => ($n->data['type'] ?? '') === 'rfq.submitted')->count());
    }

    public function test_validation_rejects_invalid_money_dates_files_and_missing_confirmation(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $this->actingAs($supplier);
        foreach ([['quantity' => '0'], ['unit_price' => '-1'], ['unit_price' => '1e3'], ['unit_price' => '1.234'], ['quantity' => '2.1234'], ['currency' => 'XXX'], ['unit' => 'unknown'], ['tax_basis' => ''], ['delivery_days' => -1], ['valid_until' => now('Asia/Dubai')->subDay()->toDateString()], ['confirmed' => 0]] as $bad) {
            $this->postJson(route('company.trade-offers.store', $o), $this->data($bad))->assertUnprocessable();
        }
        $this->assertDatabaseCount('company_trade_offers', 0);
        $this->assertDatabaseCount('opportunity_interests', 0);
    }

    public function test_non_owner_members_other_companies_admins_and_guests_have_correct_access(): void
    {
        [$buyer,$supplier,$o,$w] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        [$outsider] = $this->company();
        $this->actingAs($outsider)->get(route('company.trade-offers.show', $q))->assertNotFound();
        $this->decision($q)->assertNotFound();
        $member = User::factory()->create(['customer_id' => $w->customer_id]);
        CompanyMember::create(['company_workspace_id' => $w->id, 'user_id' => $member->id, 'role' => 'member']);
        $this->actingAs($member)->get(route('company.trade-offers.show', $q))->assertForbidden();
        $this->post(route('company.trade-offers.store', $o), $this->data())->assertForbidden();
        $this->actingAs($this->admin())->get(route('company.trade-offers.show', $q))->assertForbidden();
        $this->get(route('admin.trade-offers.show', $q))->assertOk();
        $this->get(route('admin.trade-offers.comparison', $o))->assertOk();
        auth()->logout();
        $this->get(route('company.trade-offers.show', $q))->assertRedirect(route('login'));
    }

    public function test_only_buyer_can_select_and_only_supplier_can_withdraw_with_stale_revision_protection(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        $this->decision($q)->assertForbidden();
        $this->actingAs($buyer);
        $this->decision($q)->assertRedirect();
        $this->assertSame('selected', $q->fresh()->status);
        $this->decision($q, 'declined', 0)->assertSessionHasErrors('offer');
        $this->decision($q, 'withdrawn', 1)->assertForbidden();
        $this->actingAs($supplier);
        $this->decision($q, 'withdrawn', 1)->assertRedirect();
        $this->assertSame('withdrawn', $q->fresh()->status);
    }

    public function test_selection_of_second_offer_is_blocked_until_previous_selection_is_declined(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $first = $this->send($supplier, $o);
        [$secondSupplier] = $this->company();
        $second = $this->send($secondSupplier, $o);
        $this->actingAs($buyer);
        $this->decision($first)->assertRedirect();
        $this->decision($second)->assertSessionHasErrors('offer');
        $this->decision($first, 'declined', 1)->assertRedirect();
        $this->decision($second)->assertRedirect();
        $this->assertSame('selected', $second->fresh()->status);
    }

    public function test_hidden_expired_closed_or_suspended_sources_cannot_be_selected(): void
    {
        [$buyer,$supplier,$o,$w] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        $this->actingAs($buyer);
        $o->update(['marketplace_state' => 'closed']);
        $this->decision($q)->assertSessionHasErrors('offer');
        $o->update(['marketplace_state' => 'published', 'expires_at' => now()->subMinute()]);
        $this->decision($q)->assertSessionHasErrors('offer');
        $o->update(['expires_at' => now()->addDay()]);
        $w->update(['status' => 'suspended']);
        $this->decision($q)->assertSessionHasErrors('offer');
    }

    public function test_private_attachments_require_authorization_and_integrity(): void
    {
        Storage::fake('rfq_private');
        [$buyer,$supplier,$o] = $this->setupOffer();
        $q = $this->send($supplier, $o, $this->data(['attachments' => [UploadedFile::fake()->create('spec.pdf', 10, 'application/pdf')]]));
        $attachment = $q->attachments[0];
        Storage::disk('rfq_private')->assertExists($attachment['path']);
        $this->get(route('company.trade-offers.download', [$q, 0]))->assertOk()->assertDownload();
        [$other] = $this->company();
        $this->actingAs($other)->get(route('company.trade-offers.download', [$q, 0]))->assertNotFound();
        Storage::disk('rfq_private')->put($attachment['path'], 'tampered');
        $this->actingAs($buyer)->get(route('company.trade-offers.download', [$q, 0]))->assertStatus(409);
    }

    public function test_tampering_hides_terms_and_blocks_decisions_and_downloads(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        DB::table('company_trade_offers')->where('id', $q->id)->update(['unit_price' => '500.00']);
        $this->actingAs($buyer)->get(route('company.trade-offers.show', $q))->assertOk()->assertDontSee('500.00')->assertDontSee('name="action"', false);
        $this->decision($q)->assertSessionHasErrors('offer');
    }

    public function test_records_are_immutable_and_failed_event_rolls_back_database_and_files(): void
    {
        Storage::fake('rfq_private');
        [$buyer,$supplier,$o] = $this->setupOffer();
        CompanyTradeOfferEvent::creating(function () {
            throw new \RuntimeException('Event insertion failure');
        });
        $this->actingAs($supplier)->post(route('company.trade-offers.store', $o), $this->data(['attachments' => [UploadedFile::fake()->create('spec.pdf', 10, 'application/pdf')]]))->assertServerError();
        $this->assertDatabaseCount('company_trade_offers', 0);
        $this->assertDatabaseCount('opportunity_interests', 0);
        $this->assertCount(0, Storage::disk('rfq_private')->allFiles());
    }

    public function test_selling_opportunity_and_own_buying_request_cannot_receive_rfq(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $this->actingAs($buyer)->post(route('company.trade-offers.store', $o), $this->data())->assertForbidden();
        $o->update(['opportunity_type' => 'sell']);
        $this->actingAs($supplier)->post(route('company.trade-offers.store', $o), $this->data())->assertNotFound();
    }

    public function test_forms_lists_and_details_render_both_languages_with_no_public_cache(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        foreach (['ar', 'en'] as $lang) {
            $this->actingAs($supplier)->get(route('company.trade-offers.create', [$o, 'lang' => $lang]))->assertOk()->assertDontSee('rfq.fields.');
        }
        $q = $this->send($supplier, $o);
        foreach (['ar', 'en'] as $lang) {
            $this->actingAs($buyer)->get(route('company.trade-offers.show', [$q, 'lang' => $lang]))->assertOk()->assertSee('noindex')->assertDontSee('rfq.fields.');
            $this->get(route('company.trade-offers.index', ['lang' => $lang]))->assertOk();
        }
    }

    public function test_existing_interest_does_not_consume_another_application_quota(): void
    {
        [$buyer,$supplier,$o,$w] = $this->setupOffer();
        $this->actingAs($supplier)->post(route('opportunities.interest.store', $o), ['request_key' => (string) Str::uuid(), 'role' => 'supplier', 'message' => 'We are interested in supplying this buying request.', 'currency' => 'AED'])->assertRedirect();
        $before = app(CompanyMarketplaceService::class)->usage($w)['applications'];
        $q = $this->send($supplier, $o);
        $this->assertSame($before, app(CompanyMarketplaceService::class)->usage($w)['applications']);
        $this->assertDatabaseCount('opportunity_interests', 1);
    }

    public function test_company_quota_cannot_be_bypassed_by_structured_offers(): void
    {
        [$buyer,$supplier,$o,$w] = $this->setupOffer();
        $this->send($supplier, $o);
        [$anotherBuyer] = $this->company();
        $second = $this->publish($anotherBuyer, ['opportunity_type' => 'buy']);
        $plan = app(CompanyWorkspaceService::class)->plan($w);
        for ($n = 1; $n < $plan['monthly_applications']; $n++) {
            DB::table('company_marketplace_requests')->insert(['company_workspace_id' => $w->id, 'request_key' => (string) Str::uuid(), 'action' => 'apply', 'request_hash' => str_repeat('a', 64), 'resource_id' => 1000 + $n, 'usage_month' => now('Asia/Dubai')->format('Y-m'), 'created_at' => now()]);
        }
        $this->actingAs($supplier)->postJson(route('company.trade-offers.store', $second), $this->data())->assertUnprocessable();
        $this->assertDatabaseCount('company_trade_offers', 1);
    }

    public function test_offer_validity_includes_whole_dubai_date_and_blocks_next_day(): void
    {
        $this->travelTo(Carbon::parse('2026-11-03 23:50:00', 'Asia/Dubai'));
        [$buyer,$supplier,$o] = $this->setupOffer();
        $q = $this->send($supplier, $o, $this->data(['valid_until' => '2026-11-03']));
        $this->actingAs($buyer);
        $this->decision($q)->assertRedirect();
        $this->decision($q, 'declined', 1)->assertRedirect();
        [$supplier2] = $this->company();
        $q2 = $this->send($supplier2, $o, $this->data(['valid_until' => '2026-11-03']));
        $this->travel(11)->minutes();
        $this->actingAs($buyer);
        $this->decision($q2)->assertSessionHasErrors('offer');
    }

    public function test_financial_terms_and_history_cannot_be_edited_or_deleted(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        try {
            $q->update(['unit_price' => '1.00']);
            $this->fail('Expected immutable terms');
        } catch (\LogicException) {
        }
        try {
            $q->delete();
            $this->fail('Expected no delete');
        } catch (\LogicException) {
        }
        $event = $q->events()->first();
        try {
            $event->update(['reason' => 'altered']);
            $this->fail('Expected immutable event');
        } catch (\LogicException) {
        }
        $this->assertDatabaseCount('company_trade_offers', 1);
    }

    public function test_forbidden_file_types_and_file_limits_are_rejected(): void
    {
        Storage::fake('rfq_private');
        [$buyer,$supplier,$o] = $this->setupOffer();
        $this->actingAs($supplier);
        foreach ([[UploadedFile::fake()->create('script.html', 1, 'text/html')], [UploadedFile::fake()->create('huge.pdf', 5121, 'application/pdf')], array_map(fn ($i) => UploadedFile::fake()->create('file'.$i.'.pdf', 1, 'application/pdf'), range(1, 4)), array_map(fn ($i) => UploadedFile::fake()->create('file'.$i.'.pdf', 4000, 'application/pdf'), range(1, 3))] as $files) {
            $this->postJson(route('company.trade-offers.store', $o), $this->data(['attachments' => $files]))->assertUnprocessable();
        }
        $this->assertDatabaseCount('company_trade_offers', 0);
        $this->assertCount(0, Storage::disk('rfq_private')->allFiles());
    }

    public function test_snapshots_survive_profile_changes_and_private_html_is_escaped(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $q = $this->send($supplier,$o,$this->data(['payment_terms' => '<script>alert(1)</script> payment on delivery']));
        $original = $q->snapshot;
        Customer::whereKey($supplier->customer_id)->update(['company_name' => 'Changed company name']);
        $o->update(['title' => 'Changed opportunity title']);
        $this->assertSame($original,$q->fresh()->snapshot);
        $this->actingAs($buyer)->get(route('company.trade-offers.show',$q))->assertOk()->assertDontSee('<script>alert(1)</script>',false)->assertHeader('Cache-Control','no-store, private');
    }
}
