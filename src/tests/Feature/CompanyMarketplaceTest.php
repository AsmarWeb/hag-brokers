<?php

namespace Tests\Feature;

use App\Models\CompanyAccessGrant;
use App\Models\CompanyMember;
use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\OpportunityInterest;
use App\Models\Role;
use App\Models\User;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\CompanyWorkspaceService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CompanyMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    private function company(string $role = 'owner', int $limit = 1): array
    {
        $this->artisan('platform:install-plans');
        $c = Customer::create(['company_name' => 'Company '.Str::random(8), 'is_active' => true]);
        $u = User::factory()->create(['customer_id' => $c->id]);
        $w = app(CompanyWorkspaceService::class)->activateNewRegistration($c, $u);
        if ($limit > 1) {
            $p = app(CompanyWorkspaceService::class)->plan($w);
            unset($p['source'],$p['expires_at']);
            $p['active_opportunities'] = $limit;
            CompanyAccessGrant::create(['company_workspace_id' => $w->id, 'source' => 'free', 'plan_snapshot' => $p, 'expires_at' => null, 'reason' => 'Test capacity', 'created_by' => $u->id, 'created_at' => now()]);
        }
        if ($role !== 'owner') {
            $u = User::factory()->create(['customer_id' => $c->id]);
            CompanyMember::create(['company_workspace_id' => $w->id, 'user_id' => $u->id, 'role' => $role]);
        }

return [$u, $w];
    }

    private function payload(array $x = []): array
    {
        return array_replace(['request_key' => (string) Str::uuid(), 'title' => 'Commercial equipment opportunity', 'opportunity_type' => 'trade', 'description' => 'Commercial supply opportunity with sufficient public information.', 'currency' => 'AED', 'commission_type' => 'fixed', 'commission_rate' => '100.00', 'expires_at' => now('Asia/Dubai')->addMonths(3)->toDateString()], $x);
    }

    private function listing(User $u, array $x = []): Opportunity
    {
        $this->actingAs($u)->post(route('opportunities.store'), $this->payload($x))->assertRedirect(route('company.marketplace'));

        return Opportunity::latest('id')->firstOrFail();
    }

    private function application(array $x = []): array
    {
        return array_replace(['request_key' => (string) Str::uuid(), 'role' => 'supplier', 'currency' => 'AED', 'available_capital' => '2500.00', 'message' => 'We can fulfil the requirements of this opportunity.'], $x);
    }

    private function admin(): User
    {
        $a = User::factory()->create();
        $a->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));

        return $a;
    }

    public function test_registration_is_personal_and_company_workspace_is_created_after_verification(): void
    {
        Notification::fake();
        $this->artisan('platform:install-plans');

        $this->post('/register', [
            'name' => 'Owner',
            'email' => 'owner@example.test',
            'password' => 'Strong-password-123!',
            'password_confirmation' => 'Strong-password-123!',
        ])->assertRedirect(route('login'));

        $this->assertGuest();
        $u = User::where('email', 'owner@example.test')->firstOrFail();
        $this->assertNull($u->customer_id);
        $this->assertNull($u->phone);
        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('company_workspaces', 0);
        Notification::assertSentTo($u, VerifyEmail::class);

        $u->forceFill([
            'email_verified_at' => now(),
            'phone' => '+971500000001',
            'phone_verified_at' => null,
            'phone_verification_required' => false,
        ])->save();

        $this->actingAs($u)->post(route('company.onboarding.store'), [
            'company_name' => 'Automatic Company',
            'country_code' => 'AE',
            'city' => 'Dubai',
        ])->assertRedirect(route('company.workspace'));

        $w = CompanyWorkspace::firstOrFail();
        $this->assertSame($u->fresh()->customer_id, $w->customer_id);
        $this->assertDatabaseHas('company_members', ['user_id' => $u->id, 'role' => 'owner']);
        $this->assertSame('free', app(CompanyWorkspaceService::class)->plan($w)['code']);
    }

    public function test_company_publishes_automatically_with_trusted_identity_and_private_notes(): void
    {
        [$u,$w] = $this->company();
        $o = $this->listing($u, ['customer_id' => 999999, 'company_workspace_id' => 99999, 'verification_status' => 'verified', 'submitted_by' => 9999, 'notes' => 'PRIVATE INTERNAL NOTE']);
        $this->assertSame($w->id, $o->company_workspace_id);
        $this->assertSame($w->customer_id, $o->customer_id);
        $this->assertSame($u->id, $o->submitted_by);
        $this->assertSame('auto_eligible', $o->verification_status);
        $this->assertSame('public', $o->visibility);
        $this->get(route('opportunities.show', $o))->assertOk()->assertDontSee('PRIVATE INTERNAL NOTE');
        $this->assertDatabaseCount('company_marketplace_requests', 1);
    }

    public function test_active_quota_blocks_second_listing_and_closing_releases_slot(): void
    {
        [$u] = $this->company();
        $o = $this->listing($u);
        $this->postJson(route('opportunities.store'), $this->payload())->assertUnprocessable();
        $this->post(route('company.opportunities.close', $o))->assertRedirect();
        $this->listing($u);
        $this->assertDatabaseCount('opportunities', 2);
        $this->get(route('opportunities.show', $o))->assertNotFound();
    }

    public function test_repeated_publish_is_idempotent_and_changed_payload_is_rejected(): void
    {
        [$u] = $this->company();
        $p = $this->payload();
        $this->actingAs($u)->post(route('opportunities.store'), $p)->assertRedirect();
        $this->post(route('opportunities.store'), $p)->assertRedirect();
        $this->postJson(route('opportunities.store'), array_replace($p, ['title' => 'Changed']))->assertUnprocessable();
        $this->assertDatabaseCount('opportunities', 1);
        $this->assertDatabaseCount('company_marketplace_requests', 1);
    }

    public function test_member_can_apply_but_cannot_publish(): void
    {
        [$a] = $this->company();
        $o = $this->listing($a);
        [$b] = $this->company('member');
        $this->actingAs($b)->get(route('opportunities.create'))->assertForbidden();
        $this->postJson(route('opportunities.store'), $this->payload())->assertForbidden();
        $this->post(route('opportunities.interest.store', $o), $this->application())->assertRedirect(route('company.marketplace'));
    }

    public function test_guest_can_complete_company_application_before_login_and_resume_without_reentering_it(): void
    {
        Notification::fake();
        [$owner] = $this->company();
        $opportunity = $this->listing($owner);
        auth()->guard()->logout();
        $application = $this->application();

        $this->get(route('opportunities.interest', $opportunity))->assertOk();
        $this->post(route('opportunities.interest.store', $opportunity), $application)
            ->assertRedirect(route('login'));
        $this->assertDatabaseCount('opportunity_interests', 0);

        $resumeUrl = session('url.intended');
        $this->assertNotEmpty($resumeUrl);
        $draftToken = basename(parse_url($resumeUrl, PHP_URL_PATH));
        $this->assertSame(route('opportunities.interest.resume', ['draft' => $draftToken]), $resumeUrl);

        $this->post('/register', [
            'name' => 'New Applicant',
            'email' => 'new-applicant@example.test',
            'password' => 'Strong-password-123!',
            'password_confirmation' => 'Strong-password-123!',
        ])->assertRedirect(route('login'));

        $applicant = User::where('email', 'new-applicant@example.test')->firstOrFail();
        $this->assertNull($applicant->customer_id);
        $this->assertDatabaseCount('opportunity_interests', 0);

        // Simulate completion of the signed email-verification link. Phone setup
        // remains required until the applicant completes the phone step.
        $applicant->forceFill(['email_verified_at' => now()])->save();
        $this->post('/login', [
            'email' => 'new-applicant@example.test',
            'password' => 'Strong-password-123!',
        ])->assertRedirect($resumeUrl);

        $this->get($resumeUrl)->assertRedirect(route('phone.verification.notice'));

        // Simulate successful phone-code verification before opening company setup.
        $applicant->forceFill([
            'phone' => '+971500001111',
            'phone_verified_at' => now(),
            'phone_verification_required' => false,
        ])->save();

        // Refresh the authenticated applicant so the next request sees the persisted phone state.
        $this->actingAs($applicant->fresh());

        $this->get($resumeUrl)->assertRedirect(route('company.onboarding.show'));
        $this->post(route('company.onboarding.store'), [
            'company_name' => 'New Applicant Trading',
            'country_code' => 'AE',
            'city' => 'Dubai',
        ])->assertRedirect($resumeUrl);

        // A new HTTP request reloads this user from the database; refresh the test guard too.
        $this->actingAs($applicant->fresh());

        $this->get($resumeUrl)
            ->assertOk()
            ->assertSee($opportunity->title)
            ->assertSee($application['message']);

        $this->post(route('opportunities.interest.resume.submit', ['draft' => $draftToken]))
            ->assertRedirect(route('company.marketplace'));

        $workspaceId = CompanyMember::where('user_id', $applicant->id)->value('company_workspace_id');
        $this->assertDatabaseCount('opportunity_interests', 1);
        $this->assertDatabaseHas('opportunity_interests', [
            'opportunity_id' => $opportunity->id,
            'user_id' => $applicant->id,
            'company_workspace_id' => $workspaceId,
        ]);
        $workspace = CompanyWorkspace::findOrFail($workspaceId);
        $this->assertSame(1, app(CompanyMarketplaceService::class)->usage($workspace)['applications']);
        $this->assertNull(session('pending_opportunity_applications.'.$draftToken));
    }

    public function test_manager_can_publish(): void
    {
        [$u,$w] = $this->company('manager');
        $o = $this->listing($u);
        $this->assertSame($w->id, $o->company_workspace_id);
    }

    public function test_company_cannot_apply_to_own_listing(): void
    {
        [$u] = $this->company();
        $o = $this->listing($u);
        $this->postJson(route('opportunities.interest.store', $o), $this->application())->assertUnprocessable();
        $this->assertDatabaseCount('opportunity_interests', 0);
    }

    public function test_application_is_unique_across_company_members_and_retries(): void
    {
        [$a] = $this->company();
        $o = $this->listing($a);
        [$b,$w] = $this->company();
        $p = $this->application();
        $this->actingAs($b)->post(route('opportunities.interest.store', $o), $p)->assertRedirect();
        $this->post(route('opportunities.interest.store', $o), $p)->assertRedirect();
        $m = User::factory()->create(['customer_id' => $b->customer_id]);
        CompanyMember::create(['company_workspace_id' => $w->id, 'user_id' => $m->id, 'role' => 'member']);
        $this->actingAs($m)->post(route('opportunities.interest.store', $o), $this->application())->assertRedirect();
        $this->assertDatabaseCount('opportunity_interests', 1);
        $this->assertSame(1, app(CompanyMarketplaceService::class)->usage($w)['applications']);
    }

    public function test_monthly_quota_resets_at_dubai_month_boundary(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-15 12:00:00', 'Asia/Dubai'));
        [$a] = $this->company('owner', 5);
        $list = [];
        for ($i = 0; $i < 4; $i++) {
            $list[] = $this->listing($a, ['title' => 'Listing '.$i]);
        }[$b,$w] = $this->company();
        foreach (array_slice($list, 0, 3) as $o) {
            $this->actingAs($b)->post(route('opportunities.interest.store', $o), $this->application())->assertRedirect();
        }$this->postJson(route('opportunities.interest.store', $list[3]), $this->application())->assertUnprocessable();
        $this->travelTo(CarbonImmutable::parse('2026-12-01 00:00:01', 'Asia/Dubai'));
        $this->post(route('opportunities.interest.store', $list[3]), $this->application())->assertRedirect();
        $this->assertSame(1, app(CompanyMarketplaceService::class)->usage($w)['applications']);
    }

    public function test_expiry_releases_slot_without_scheduler(): void
    {
        [$u] = $this->company();
        $o = $this->listing($u, ['expires_at' => now('Asia/Dubai')->toDateString()]);
        $this->travel(2)->days();
        $this->get(route('opportunities.show', $o))->assertNotFound();
        $this->listing($u);
        $this->assertDatabaseCount('opportunities', 2);
    }

    public function test_unverified_user_cannot_publish_even_without_email_interface(): void
    {
        [$u] = $this->company();
        $u->forceFill(['email_verified_at' => null])->save();
        $this->actingAs($u)->get(route('opportunities.create'))->assertRedirect(route('verification.notice'));
        $this->postJson(route('opportunities.store'), $this->payload())->assertRedirect(route('verification.notice'));
        $this->assertDatabaseCount('opportunities', 0);
    }

    public function test_nonmembers_and_platform_admins_cannot_publish(): void
    {
        $personal = User::factory()->create(['email_verified_at' => now(), 'phone_verification_required' => false]);
        $this->actingAs($personal)->post(route('opportunities.store'), $this->payload())
            ->assertRedirect(route('company.onboarding.show'));
        $legacyCustomer = \App\Models\Customer::create(['company_name' => 'Legacy publisher without workspace', 'is_active' => true]);
        $legacy = User::factory()->create([
            'customer_id' => $legacyCustomer->id,
            'email_verified_at' => now(),
            'phone_verification_required' => false,
        ]);
        $this->actingAs($legacy)->postJson(route('opportunities.store'), $this->payload())->assertForbidden();
        $this->actingAs($this->admin())->postJson(route('opportunities.store'), $this->payload())->assertForbidden();
        $this->assertDatabaseCount('opportunities', 0);
    }

    public function test_suspension_hides_listings_and_blocks_company_actions(): void
    {
        [$u,$w] = $this->company();
        $o = $this->listing($u);
        $w->update(['status' => 'suspended']);
        $this->actingAs($u)->get(route('company.marketplace'))->assertForbidden();
        $this->postJson(route('opportunities.store'), $this->payload())->assertForbidden();
        $this->get(route('opportunities.show', $o))->assertNotFound();
    }

    public function test_cross_company_edit_close_and_decision_are_rejected(): void
    {
        [$a] = $this->company();
        $o = $this->listing($a);
        [$b] = $this->company();
        $this->actingAs($b)->post(route('opportunities.interest.store', $o), $this->application())->assertRedirect();
        $i = OpportunityInterest::first();
        $this->get(route('company.opportunities.edit', $o))->assertNotFound();
        $this->post(route('company.opportunities.close', $o))->assertNotFound();
        $this->post(route('company.applications.decide', $i), ['status' => 'accepted'])->assertNotFound();
    }

    public function test_owner_decision_is_delivered_without_creating_financial_records(): void
    {
        [$a] = $this->company();
        $o = $this->listing($a);
        [$b] = $this->company();
        $this->actingAs($b)->post(route('opportunities.interest.store', $o), $this->application())->assertRedirect();
        $i = OpportunityInterest::firstOrFail();
        $this->actingAs($a)->get(route('company.marketplace'))->assertOk()->assertSee($b->email);
        $this->post(route('company.applications.decide', $i), ['status' => 'accepted'])->assertRedirect();
        $this->assertSame('accepted', $i->fresh()->status);
        foreach (['deals', 'deal_commissions', 'issued_invoices', 'invoice_payments'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }

    public function test_application_details_are_private_to_relevant_company_roles(): void
    {
        [$a] = $this->company();
        $o = $this->listing($a);
        [$b] = $this->company();
        $this->actingAs($b)->post(route('opportunities.interest.store', $o), $this->application(['message' => 'CONFIDENTIAL APPLICATION CONTENT']))->assertRedirect();
        [$c] = $this->company();
        $this->actingAs($c)->get(route('company.marketplace'))->assertOk()->assertDontSee('CONFIDENTIAL APPLICATION CONTENT');
        $m = User::factory()->create(['customer_id' => $a->customer_id]);
        $w = CompanyWorkspace::where('customer_id', $a->customer_id)->first();
        CompanyMember::create(['company_workspace_id' => $w->id, 'user_id' => $m->id, 'role' => 'member']);
        $this->actingAs($m)->get(route('company.marketplace'))->assertOk()->assertDontSee('CONFIDENTIAL APPLICATION CONTENT');
    }

    public function test_closed_listing_cannot_receive_applications(): void
    {
        [$a] = $this->company();
        $o = $this->listing($a);
        $this->post(route('company.opportunities.close', $o));
        [$b] = $this->company();
        $this->actingAs($b)->postJson(route('opportunities.interest.store', $o), $this->application())->assertNotFound();
        $this->assertDatabaseCount('opportunity_interests', 0);
    }

    public function test_admin_opportunities_are_read_only(): void
    {
        [$u] = $this->company();
        $o = $this->listing($u);
        $this->actingAs($this->admin())->get(route('admin.opportunities.index'))->assertOk();
        $this->get(route('admin.opportunities.show', $o))->assertOk();
        $this->get('/admin/opportunities/create')->assertNotFound();
        $this->get('/admin/opportunities/'.$o->id.'/edit')->assertNotFound();
        $this->post('/admin/opportunities', $this->payload())->assertStatus(405);
        $this->patch('/admin/opportunities/'.$o->id, ['title' => 'Override'])->assertStatus(405);
        $this->delete('/admin/opportunities/'.$o->id)->assertStatus(405);
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_company_edits_public_text_without_changing_identity_or_verification(): void
    {
        [$u,$w] = $this->company();
        $o = $this->listing($u);
        $this->patch(route('company.opportunities.update', $o), ['title' => 'Updated company title', 'description' => 'Updated description for the commercial opportunity.', 'customer_id' => 99999, 'verification_status' => 'verified'])->assertRedirect();
        $this->assertSame('Updated company title', $o->fresh()->title);
        $this->assertSame('auto_eligible', $o->fresh()->verification_status);
        $this->assertSame($w->customer_id, $o->fresh()->customer_id);
    }

    public function test_invalid_fields_leave_no_listing_or_usage(): void
    {
        [$u] = $this->company();
        foreach ([['title' => ''], ['description' => 'short'], ['request_key' => 'invalid'], ['expires_at' => '2000-01-01'], ['opportunity_type' => 'unknown'], ['opportunity_type' => 'vessel_brokerage', 'vessel' => ['listing_type' => 'sale']]] as $x) {
            $this->actingAs($u)->postJson(route('opportunities.store'), $this->payload($x))->assertUnprocessable();
        }$this->assertDatabaseCount('opportunities', 0);
        $this->assertDatabaseCount('company_marketplace_requests', 0);
    }

    public function test_replayed_application_cannot_override_message(): void
    {
        [$a] = $this->company();
        $o = $this->listing($a);
        [$b] = $this->company();
        $p = $this->application();
        $this->actingAs($b)->post(route('opportunities.interest.store', $o), $p)->assertRedirect();
        $this->postJson(route('opportunities.interest.store', $o), array_replace($p, ['message' => 'Changed application message']))->assertUnprocessable();
        $this->assertDatabaseCount('opportunity_interests', 1);
    }

    public function test_both_languages_render_company_forms_and_public_listing(): void
    {
        [$u] = $this->company();
        $o = $this->listing($u);
        foreach (['ar', 'en'] as $locale) {
            $this->actingAs($u)->withSession(['locale' => $locale])->get(route('company.marketplace'))->assertOk();
            $this->get(route('opportunities.create'))->assertOk();
            $this->get(route('opportunities.show', $o))->assertOk();
        }
    }

    public function test_media_retry_does_not_leak_files(): void
    {
        Storage::fake('public');
        [$u] = $this->company();
        $file = UploadedFile::fake()->image('cargo.jpg');
        $p = $this->payload(['media' => [$file]]);
        $this->actingAs($u)->post(route('opportunities.store'), $p)->assertRedirect();
        $this->post(route('opportunities.store'), $p)->assertRedirect();
        $this->assertDatabaseCount('opportunities', 1);
        $this->assertDatabaseCount('opportunity_media', 1);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_failed_usage_write_rolls_back_and_removes_uploads(): void
    {
        Storage::fake('public');
        [$u] = $this->company();
        DB::listen(function ($q): void {
            if (str_starts_with(strtolower($q->sql), 'insert into "company_marketplace_requests"')) {
                throw new \RuntimeException('Usage failure');
            }
        });
        $this->withoutExceptionHandling();
        try {
            $this->actingAs($u)->post(route('opportunities.store'),$this->payload(['media' => [UploadedFile::fake()->image('cargo.jpg')]]));
            $this->fail('Expected failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Usage failure',$e->getMessage());
        }$this->assertDatabaseCount('opportunities',0);
        $this->assertDatabaseCount('company_marketplace_requests',0);
        $this->assertCount(0,Storage::disk('public')->allFiles());
    }

    public function test_application_and_decision_notifications_are_idempotent_and_scoped(): void
    {
        [$owner] = $this->company(); $o = $this->listing($owner);
        [$applicant] = $this->company(); [$outsider] = $this->company(); $payload = $this->application();
        $this->actingAs($applicant)->post(route('opportunities.interest.store', $o), $payload)->assertRedirect();
        $this->post(route('opportunities.interest.store', $o), $payload)->assertRedirect();
        $this->assertSame(1, $owner->notifications()->where('data->type', 'marketplace.application')->count());
        $this->assertSame(0, $outsider->notifications()->count());
        $interest = OpportunityInterest::firstOrFail();
        $this->actingAs($owner)->post(route('company.applications.decide', $interest), ['status' => 'accepted'])->assertRedirect();
        $this->post(route('company.applications.decide', $interest), ['status' => 'accepted'])->assertRedirect();
        $this->assertSame(1, $applicant->notifications()->where('data->type', 'marketplace.decision')->count());
    }
    public function test_legacy_listing_is_not_silently_assigned_to_a_company(): void
    {
        [$owner] = $this->company(); $o = $this->listing($owner);
        $o->update(['company_workspace_id' => null, 'verification_status' => 'verified']); [$applicant] = $this->company();
        $this->actingAs($applicant)->get(route('opportunities.show', $o))->assertOk()->assertSee(__('market.legacy_listing'));
        $this->postJson(route('opportunities.interest.store', $o), $this->application())->assertUnprocessable();
        $this->assertDatabaseCount('opportunity_interests', 0);
    }

    public function test_public_marketplace_category_filter_shows_only_visible_opportunities_in_selected_category(): void
    {
        [$u] = $this->company('owner', 4);

        $food = $this->listing($u, [
            'title' => 'Fresh Food Supply Opportunity',
            'category' => 'food',
        ]);

        $marine = $this->listing($u, [
            'title' => 'Marine Equipment Opportunity',
            'category' => 'marine',
        ]);

        $hiddenFood = $this->listing($u, [
            'title' => 'Hidden Food Opportunity',
            'category' => 'food',
        ]);

        $hiddenFood->moderation()->create(['hidden' => true]);

        $this->get(route('opportunities.index', [
            'category' => 'food',
            'lang' => 'en',
        ]))
            ->assertOk()
            ->assertSee('Explore Business Sectors')
            ->assertSee('Fresh Food Supply Opportunity')
            ->assertDontSee('Marine Equipment Opportunity')
            ->assertDontSee('Hidden Food Opportunity');

        $this->get(route('opportunities.index', [
            'category' => 'marine',
            'lang' => 'en',
        ]))
            ->assertOk()
            ->assertSee('Marine Equipment Opportunity')
            ->assertDontSee('Fresh Food Supply Opportunity');
    }

    public function test_guest_can_publish_opportunity_after_personal_signup_phone_setup_and_company_onboarding(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        \Illuminate\Support\Facades\Storage::fake('local');
        \Illuminate\Support\Facades\Storage::fake('public');
        $this->artisan('platform:install-plans');

        $this->get(route('opportunities.create'))->assertOk();
        $payload = $this->payload([
            'title' => 'Guest deferred commercial vessel opportunity',
            'opportunity_type' => 'vessel_brokerage',
            'category' => 'marine',
            'commercial_details' => ['subcategory' => 'commercial_vessels'],
            'vessel' => [
                'listing_type' => 'sale',
                'vessel_category' => 'commercial_vessel',
                'vessel_name' => 'HAG Guest Test Vessel',
                'imo_number' => '9876543',
            ],
            'media' => [\Illuminate\Http\UploadedFile::fake()->image('commercial-vessel.jpg')],
        ]);
        $this->post(route('opportunities.store'), $payload)->assertRedirect(route('login'));
        $this->assertDatabaseCount('opportunities', 0);
        $drafts = session('pending_opportunity_publications', []);
        $token = array_key_first($drafts);
        $this->assertNotNull($token);
        $this->assertCount(1, $drafts[$token]['media']);

        $this->post('/register', [
            'name' => 'Deferred Publisher',
            'email' => 'deferred-publisher@example.test',
            'password' => 'Strong-password-123!',
            'password_confirmation' => 'Strong-password-123!',
        ])->assertRedirect(route('login'));
        $owner = \App\Models\User::where('email', 'deferred-publisher@example.test')->firstOrFail();
        $owner->forceFill([
            'email_verified_at' => now(),
            'phone' => '+971500000071',
            'phone_verified_at' => null,
            'phone_verification_required' => true,
        ])->save();

        $resumeUrl = route('opportunities.create.resume', ['draft' => $token]);
        $this->actingAs($owner)->get($resumeUrl)->assertRedirect(route('phone.verification.notice'));
        $other = \App\Models\User::factory()->create(['email_verified_at' => now(), 'phone_verification_required' => false]);
        $this->actingAs($other)->get($resumeUrl)->assertForbidden();
        $owner = \App\Models\User::findOrFail($owner->id);
        $owner->forceFill(['phone_verified_at' => now(), 'phone_verification_required' => false])->save();
        $owner = \App\Models\User::findOrFail($owner->id);
        $this->actingAs($owner)->get($resumeUrl)->assertRedirect(route('company.onboarding.show'));
        $this->post(route('company.onboarding.store'), [
            'company_name' => 'Deferred Opportunity Publisher LLC',
            'country_code' => 'AE',
            'city' => 'Dubai',
        ])->assertRedirect($resumeUrl);

        $owner = \App\Models\User::findOrFail($owner->id);
        $this->actingAs($owner)->get($resumeUrl)->assertOk()
            ->assertSee('Guest deferred commercial vessel opportunity')
            ->assertSee('HAG Guest Test Vessel')
            ->assertSee('commercial-vessel.jpg');
        $this->post(route('opportunities.create.resume.submit', ['draft' => $token]))
            ->assertRedirect(route('company.marketplace'));

        $opportunity = \App\Models\Opportunity::where('title', 'Guest deferred commercial vessel opportunity')->firstOrFail();
        $workspace = \App\Models\CompanyWorkspace::where('customer_id', $owner->fresh()->customer_id)->firstOrFail();
        $this->assertSame($workspace->id, $opportunity->company_workspace_id);
        $this->assertSame('HAG Guest Test Vessel', $opportunity->vessel()->firstOrFail()->vessel_name);
        $this->assertSame('9876543', (string) $opportunity->vessel()->firstOrFail()->imo_number);
        $this->assertDatabaseCount('opportunity_media', 1);
        $this->assertCount(1, \Illuminate\Support\Facades\Storage::disk('public')->allFiles('opportunities/company'));
        $this->assertSame([], session('pending_opportunity_publications', []));
        $this->assertSame([], \Illuminate\Support\Facades\Storage::disk('local')->allFiles('pending-opportunity-publications'));
    }
}
