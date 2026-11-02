<?php

namespace Tests\Feature;

use App\Models\CompanyMember;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Role;
use App\Models\User;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommercialOpportunitiesTest extends TestCase
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

    private function assistantData(array $extra = []): array
    {
        return array_replace($this->listingData(), ['mode' => 'guide', 'language' => 'en'], $extra);
    }

    private function aiResponse(array $suggestion = []): array
    {
        return ['status' => 'completed', 'output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => json_encode($suggestion ?: ['title' => 'Office chairs for Dubai companies', 'description' => 'Office chairs with adjustable armrests for companies in Dubai. Please request the specifications and delivery terms.', 'missing_details' => ['Confirm delivery schedule.']])]]]]];
    }

    private function enableAi(): void
    {
        config(['opportunity_assistant.enabled' => true, 'opportunity_assistant.key' => 'fake-test-key', 'opportunity_assistant.company_daily_limit' => 10, 'opportunity_assistant.platform_daily_limit' => 100]);
        Http::preventStrayRequests();
    }

    public function test_sell_buy_and_service_types_publish_with_exact_commercial_strings(): void
    {
        foreach (['sell', 'buy', 'service_offer', 'service_request'] as $type) {
            [$user, $workspace] = $this->company();
            $listing = $this->publish($user, ['opportunity_type' => $type]);
            $this->assertSame($workspace->id, $listing->company_workspace_id);
            $this->assertSame('500.125', $listing->commercial_details['quantity']);
            $this->assertSame('199.99', $listing->commercial_details['unit_price']);
            $this->assertNull($listing->commission_rate);
            $this->assertSame('auto_eligible', $listing->verification_status);
        }
    }

    public function test_invalid_details_are_rejected_without_listing_or_quota_usage(): void
    {
        [$user] = $this->company();
        foreach ([['quantity' => '-1'], ['unit_price' => '1.234'], ['unit_price' => '1e3'], ['quantity' => '0'], ['lead_time_days' => 0], ['unit' => 'unknown'], ['condition' => 'verified'], ['company_workspace_id' => 999], ['specifications' => str_repeat('x', 5001)]] as $detail) {
            $payload = $this->listingData();
            $payload['commercial_details'] = array_replace($payload['commercial_details'], $detail);
            $this->actingAs($user)->postJson(route('opportunities.store'), $payload)->assertUnprocessable();
        }
        $this->assertDatabaseCount('opportunities', 0);
        $this->assertDatabaseCount('company_marketplace_requests', 0);
    }

    public function test_company_can_edit_details_but_not_identity_status_or_financial_source(): void
    {
        [$user] = $this->company();
        $listing = $this->publish($user);
        $this->patch(route('company.opportunities.update', $listing), ['title' => $listing->title, 'description' => $listing->description, 'category' => 'machinery', 'commercial_details' => ['specifications' => 'Updated public specifications'], 'customer_id' => 999, 'verification_status' => 'verified', 'marketplace_state' => 'closed', 'currency' => 'USD'])->assertRedirect();
        $fresh = $listing->fresh();
        $this->assertSame('machinery', $fresh->category);
        $this->assertSame('Updated public specifications', $fresh->commercial_details['specifications']);
        $this->assertSame($listing->customer_id, $fresh->customer_id);
        $this->assertSame('AED', $fresh->currency);
        $this->assertSame('auto_eligible', $fresh->verification_status);
        $this->assertSame('published', $fresh->marketplace_state);
    }

    public function test_other_company_and_member_cannot_edit_details(): void
    {
        [$owner] = $this->company();
        $listing = $this->publish($owner);
        [$other] = $this->company();
        $this->actingAs($other)->patchJson(route('company.opportunities.update', $listing), ['title' => $listing->title, 'description' => $listing->description])->assertNotFound();
        $member = User::factory()->create(['customer_id' => $owner->customer_id]);
        CompanyMember::create(['company_workspace_id' => $listing->company_workspace_id, 'user_id' => $member->id, 'role' => 'member']);
        $this->actingAs($member)->get(route('company.opportunities.edit', $listing))->assertForbidden();
    }

    public function test_search_and_industry_type_and_uae_alias_filters_work(): void
    {
        [$owner] = $this->company();
        $listing = $this->publish($owner, ['country' => 'AE', 'commercial_details' => ['product_name' => 'UniqueProduct123']]);
        $this->get(route('opportunities.index', ['search' => 'UniqueProduct123', 'type' => 'sell', 'category' => 'consumer_goods', 'country' => 'United Arab Emirates']))->assertOk()->assertSee($listing->title);
        $this->get(route('opportunities.index', ['category' => 'machinery']))->assertOk()->assertDontSee($listing->title);
        $this->getJson(route('opportunities.index', ['type' => 'unknown']))->assertUnprocessable();
        $this->getJson(route('opportunities.index', ['search' => ['x']]))->assertUnprocessable();
        $this->getJson(route('opportunities.index', ['search' => str_repeat('a', 151)]))->assertUnprocessable();
    }

    public function test_html_is_escaped_in_public_content_and_head_metadata(): void
    {
        [$owner] = $this->company();
        $listing = $this->publish($owner, ['title' => '</title><script>alert(1)</script>', 'description' => '\"><script>alert(2)</script> Public listing description text.', 'notes' => 'PRIVATE-NOTES-SECRET', 'commercial_details' => ['specifications' => '<img src=x onerror=alert(3)>']]);
        $this->get(route('opportunities.show', $listing))->assertOk()->assertDontSee('<script>alert(', false)->assertDontSee('<img src=x', false)->assertDontSee('PRIVATE-NOTES-SECRET')->assertSee('&lt;script&gt;', false);
    }

    public function test_both_languages_render_create_edit_browse_and_details(): void
    {
        [$owner] = $this->company();
        $listing = $this->publish($owner);
        foreach (['ar', 'en'] as $lang) {
            $this->withSession(['locale' => $lang]);
            foreach (['opportunities.index', 'opportunities.create', 'opportunities.show', 'company.opportunities.edit'] as $route) {
                $this->get(route($route, str_contains($route, 'show') || str_contains($route, 'edit') ? $listing : []))->assertOk()->assertDontSee('trade.fields.')->assertDontSee('trade.ai_');
            }
        }
    }

    public function test_vessel_and_legacy_listing_data_are_preserved(): void
    {
        [$owner] = $this->company();
        $listing = $this->publish($owner, ['opportunity_type' => 'vessel_brokerage', 'vessel' => ['listing_type' => 'sale', 'vessel_category' => 'commercial_vessel', 'vessel_name' => 'Existing Barge', 'length_m' => '91.20'], 'required_capital' => '1000.00']);
        $this->get(route('opportunities.show', $listing))->assertOk()->assertSee('Existing Barge')->assertSee('91.20')->assertSee('1000.00');
        $listing->update(['commercial_details' => null, 'category' => 'Legacy specialty sector']);
        $this->get(route('opportunities.index'))->assertOk()->assertSee('Legacy specialty sector');
        $this->get(route('company.opportunities.edit', $listing))->assertOk()->assertSee('Legacy specialty sector');
    }

    public function test_guide_is_honestly_labelled_and_never_calls_provider_or_publishes(): void
    {
        [$owner] = $this->company();
        Http::fake();
        $this->actingAs($owner)->postJson(route('company.opportunity-assistant'), $this->assistantData())->assertOk()->assertJsonPath('mode', 'guide')->assertJsonStructure(['missing_details', 'message']);
        Http::assertNothingSent();
        $this->assertDatabaseCount('opportunities', 0);
        $this->assertDatabaseCount('company_marketplace_requests', 0);
    }

    public function test_ai_disabled_or_missing_consent_makes_no_provider_request(): void
    {
        [$owner] = $this->company();
        Http::fake();
        config(['opportunity_assistant.enabled' => false]);
        $this->actingAs($owner)->postJson(route('company.opportunity-assistant'), $this->assistantData(['mode' => 'ai', 'consent' => true]))->assertStatus(503);
        $this->enableAi();
        $this->postJson(route('company.opportunity-assistant'), $this->assistantData(['mode' => 'ai']))->assertUnprocessable();
        $this->postJson(route('company.opportunity-assistant'), $this->assistantData(['mode' => 'ai', 'consent' => false]))->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_ai_sends_only_public_allowlisted_text_and_does_not_write_listing(): void
    {
        [$owner] = $this->company();
        $this->enableAi();
        $baseline = DB::transactionLevel();
        Http::fake(function ($request) use ($baseline) {
            $this->assertSame($baseline, DB::transactionLevel());
            $encoded = json_encode($request->data());
            foreach (['PRIVATE-SECRET', 'BankAccountSecret', 'owner@example.com', 'unit_price', 'request_key', 'commission_rate', 'customer_id', 'workspace_id'] as $excluded) {
                $this->assertStringNotContainsString($excluded, $encoded);
            }
            $this->assertFalse($request['store']);
            $this->assertSame('json_schema', $request['text']['format']['type']);

            return Http::response($this->aiResponse());
        });
        $this->actingAs($owner)->postJson(route('company.opportunity-assistant'), $this->assistantData(['mode' => 'ai', 'consent' => true, 'notes' => 'PRIVATE-SECRET', 'bank' => 'BankAccountSecret', 'email' => 'owner@example.com']))->assertOk()->assertJsonPath('mode', 'ai');
        Http::assertSentCount(1);
        $this->assertDatabaseCount('opportunities', 0);
        $this->assertDatabaseCount('company_platform_audits', 1); // Workspace activation only.
    }

    public function test_ai_provider_failure_is_generic_without_leaking_provider_body_or_key(): void
    {
        [$owner] = $this->company();
        $this->enableAi();
        Http::fake(['*' => Http::response(['error' => ['message' => 'PRIVATE-PROVIDER-ERROR fake-test-key']], 500)]);
        $this->actingAs($owner)->postJson(route('company.opportunity-assistant'), $this->assistantData(['mode' => 'ai', 'consent' => true]))->assertStatus(503)->assertDontSee('PRIVATE-PROVIDER-ERROR')->assertDontSee('fake-test-key');
        $this->assertDatabaseCount('opportunities', 0);
    }

    public function test_ai_rejects_incomplete_refusal_malformed_and_oversized_output(): void
    {
        [$owner] = $this->company();
        $this->enableAi();
        foreach ([['status' => 'incomplete'], ['status' => 'completed', 'output' => [['type' => 'message', 'content' => [['type' => 'refusal', 'refusal' => 'No']]]]], $this->aiResponse(['title' => str_repeat('x', 256), 'description' => str_repeat('x', 40), 'missing_details' => []])] as $response) {
            Http::fake(['*' => Http::response($response)]);
            $this->actingAs($owner)->postJson(route('company.opportunity-assistant'), $this->assistantData(['mode' => 'ai', 'consent' => true]))->assertStatus(503);
        }
        $this->assertDatabaseCount('opportunities', 0);
    }

    public function test_complete_ai_suggestion_may_have_no_missing_details(): void
    {
        [$owner] = $this->company();
        $this->enableAi();
        Http::fake(['*' => Http::response($this->aiResponse(['title' => 'Office chairs for Dubai', 'description' => str_repeat('Clear commercial facts. ', 3), 'missing_details' => []]))]);
        $this->actingAs($owner)->postJson(route('company.opportunity-assistant'), $this->assistantData(['mode' => 'ai', 'consent' => true]))->assertOk()->assertJsonPath('suggestion.missing_details', []);
    }

    public function test_company_daily_ai_limit_blocks_provider_call(): void
    {
        [$owner, $workspace] = $this->company();
        $this->enableAi();
        config(['opportunity_assistant.company_daily_limit' => 1]);
        RateLimiter::hit('opportunity-ai:company:'.$workspace->id.':'.now('Asia/Dubai')->format('Y-m-d'), 86400);
        Http::fake();
        $this->actingAs($owner)->postJson(route('company.opportunity-assistant'), $this->assistantData(['mode' => 'ai', 'consent' => true]))->assertStatus(429);
        Http::assertNothingSent();
    }

    public function test_platform_daily_ai_limit_blocks_provider_call(): void
    {
        [$owner] = $this->company();
        $this->enableAi();
        config(['opportunity_assistant.platform_daily_limit' => 0]);
        Http::fake();
        $this->actingAs($owner)->postJson(route('company.opportunity-assistant'), $this->assistantData(['mode' => 'ai', 'consent' => true]))->assertStatus(429);
        Http::assertNothingSent();
    }

    public function test_guest_regular_member_unverified_admin_and_suspended_company_cannot_use_assistant(): void
    {
        Http::fake();
        $this->postJson(route('company.opportunity-assistant'), $this->assistantData())->assertUnauthorized();
        $regular = User::factory()->create();
        $this->actingAs($regular)->postJson(route('company.opportunity-assistant'), $this->assistantData())->assertForbidden();
        [$member] = $this->company('member');
        $this->actingAs($member)->postJson(route('company.opportunity-assistant'), $this->assistantData())->assertForbidden();
        [$unverified] = $this->company();
        $unverified->forceFill(['email_verified_at' => null])->save();
        $this->actingAs($unverified)->postJson(route('company.opportunity-assistant'), $this->assistantData())->assertForbidden();
        [$owner, $workspace] = $this->company();
        $workspace->update(['status' => 'suspended']);
        $this->actingAs($owner)->postJson(route('company.opportunity-assistant'), $this->assistantData())->assertForbidden();
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        $this->actingAs($admin)->postJson(route('company.opportunity-assistant'), $this->assistantData())->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_manager_can_request_guide_and_language_validation_is_enforced(): void
    {
        [$manager] = $this->company('manager');
        $this->actingAs($manager)->postJson(route('company.opportunity-assistant'), $this->assistantData(['language' => 'ar']))->assertOk()->assertJsonPath('mode', 'guide');
        $this->postJson(route('company.opportunity-assistant'), $this->assistantData(['language' => 'xx']))->assertUnprocessable();
    }

    public function test_quantity_and_price_require_units_and_fixed_price_requires_amount(): void
    {
        [$owner] = $this->company();
        foreach ([['quantity' => '100'], ['unit_price' => '10.50'], ['price_basis' => 'fixed'], ['min_order_quantity' => '5', 'unit' => 'piece']] as $details) {
            $this->actingAs($owner)->postJson(route('opportunities.store'), $this->listingData(['commercial_details' => $details]))->assertUnprocessable();
        }
        $this->assertDatabaseCount('opportunities', 0);
    }

    public function test_additive_migration_preserves_existing_listing_and_company_data(): void
    {
        [$owner] = $this->company();
        $listing = $this->publish($owner);
        $migration = require database_path('migrations/2026_11_02_200000_add_commercial_details_to_opportunities.php');
        $migration->down();
        $migration->up();
        $fresh = Opportunity::findOrFail($listing->id);
        $this->assertSame($listing->title, $fresh->title);
        $this->assertSame($listing->customer_id, $fresh->customer_id);
        $this->assertNull($fresh->commercial_details);
        $this->assertDatabaseHas('customers', ['id' => $listing->customer_id]);
    }

    public function test_admin_can_read_commercial_details_in_both_languages_without_write_actions(): void
    {
        [$owner] = $this->company();
        $listing = $this->publish($owner);
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        foreach (['ar', 'en'] as $language) {
            $this->actingAs($admin)->withSession(['locale' => $language])->get(route('admin.opportunities.show', $listing))->assertOk()->assertSee('500.125')->assertSee('199.99')->assertDontSee('trade.fields.');
        }
        $this->postJson('/admin/opportunities', $this->listingData())->assertStatus(405);
        $this->assertSame('199.99', $listing->fresh()->commercial_details['unit_price']);
    }

    public function test_detail_key_reordering_keeps_publication_idempotent(): void
    {
        [$owner] = $this->company();
        $payload = $this->listingData();
        $this->actingAs($owner)->post(route('opportunities.store'), $payload)->assertRedirect();
        $payload['commercial_details'] = array_reverse($payload['commercial_details'], true);
        $this->post(route('opportunities.store'), $payload)->assertRedirect();
        $this->assertDatabaseCount('opportunities', 1);
        $this->assertDatabaseCount('company_marketplace_requests', 1);
    }

    public function test_total_media_limit_is_rejected_before_storage_or_quota_write(): void
    {
        [$owner] = $this->company();
        Storage::fake('public');
        $payload = $this->listingData();
        $payload['media'] = [
            UploadedFile::fake()->create('first.mp4', 12288, 'video/mp4'),
            UploadedFile::fake()->create('second.mp4', 12288, 'video/mp4'),
        ];
        $this->actingAs($owner)->postJson(route('opportunities.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('media');
        $this->assertDatabaseCount('opportunities', 0);
        $this->assertDatabaseCount('company_marketplace_requests', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_omitted_commission_type_does_not_bypass_percentage_limit(): void
    {
        [$owner] = $this->company();
        $this->actingAs($owner)->postJson(route('opportunities.store'), $this->listingData(['commission_type' => null, 'commission_rate' => '101']))->assertUnprocessable()->assertJsonValidationErrors('commission_rate');
        $this->assertDatabaseCount('opportunities', 0);
    }

    public function test_long_description_can_use_guide_but_cannot_exceed_ai_input_budget(): void
    {
        [$owner] = $this->company();
        $this->enableAi();
        Http::fake();
        $data = $this->assistantData(['description' => str_repeat('x', 6001)]);
        $this->actingAs($owner)->postJson(route('company.opportunity-assistant'), $data)->assertOk();
        $this->postJson(route('company.opportunity-assistant'), array_replace($data, ['mode' => 'ai', 'consent' => true]))->assertUnprocessable();
        Http::assertNothingSent();
    }
}
