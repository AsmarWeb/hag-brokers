<?php

namespace Tests\Feature;

use App\Mail\CompanyWorkspaceClosureVerificationMail;
use App\Models\CompanyMember;
use App\Models\CompanyWorkspace;
use App\Models\CompanyWorkspaceClosureRequest;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Role;
use App\Models\User;
use App\Services\Platform\CompanyBillingService;
use App\Services\Platform\CompanyWorkspaceClosureService;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CompanyWorkspaceClosureTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $this->artisan('platform:install-plans')->assertSuccessful();
        $customer = Customer::create(['company_name' => 'Closure Test Company', 'is_active' => true]);
        $owner = User::factory()->create(['customer_id' => $customer->id, 'email_verified_at' => now()]);
        $workspace = app(CompanyWorkspaceService::class)->activateNewRegistration($customer, $owner);
        $member = User::factory()->create(['customer_id' => $customer->id, 'email_verified_at' => now()]);
        CompanyMember::create(['company_workspace_id' => $workspace->id, 'user_id' => $member->id, 'role' => 'member']);
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        return [$owner, $member, $admin, $customer, $workspace];
    }

    public function test_owner_must_verify_email_then_explicitly_confirm_before_workspace_is_suspended(): void
    {
        Mail::fake();
        [$owner, $member, $admin, $customer, $workspace] = $this->fixture();
        $this->actingAs($owner)->post(route('company.workspace.closure.start'))->assertRedirect(route('company.workspace.closure.status'));
        $closure = CompanyWorkspaceClosureRequest::firstOrFail();
        $this->assertSame('email_pending', $closure->status);
        $this->assertSame('active', $workspace->fresh()->status);
        $this->assertTrue($customer->fresh()->is_active);

        $token = null;
        Mail::assertSent(CompanyWorkspaceClosureVerificationMail::class, function ($mail) use (&$token): bool {
            $token = basename(parse_url($mail->verificationUrl, PHP_URL_PATH));
            return true;
        });
        $this->assertNotNull($token);
        $this->get(route('company.workspace.closure.verify.show', $token))->assertOk();
        $this->actingAs($owner)->post(route('company.workspace.closure.verify', $token))
            ->assertRedirect(route('company.workspace.closure.status'));
        $this->assertSame('owner_confirmation', $closure->fresh()->status);
        $this->assertSame('active', $workspace->fresh()->status);

        $this->post(route('company.workspace.closure.confirm'), ['confirm_understood' => '1'])
            ->assertRedirect(route('company.workspace.closure.status'));
        $this->assertSame('pending_review', $closure->fresh()->status);
        $this->assertSame('suspended', $workspace->fresh()->status);
        $this->assertFalse($customer->fresh()->is_active);
        $this->assertDatabaseHas('company_members', ['user_id' => $member->id, 'company_workspace_id' => $workspace->id]);
    }

    public function test_email_verification_does_not_send_a_different_signed_in_user_to_their_own_closure_status(): void
    {
        Mail::fake();
        [$owner, $member, $admin, $customer, $workspace] = $this->fixture();

        $this->actingAs($owner)->post(route('company.workspace.closure.start'))
            ->assertRedirect(route('company.workspace.closure.status'));

        $token = null;
        Mail::assertSent(CompanyWorkspaceClosureVerificationMail::class, function ($mail) use (&$token): bool {
            $token = basename(parse_url($mail->verificationUrl, PHP_URL_PATH));
            return true;
        });
        $this->assertNotNull($token);

        $otherUser = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($otherUser)
            ->post(route('company.workspace.closure.verify', $token))
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseHas('company_workspace_closure_requests', [
            'owner_id' => $owner->id,
            'status' => 'owner_confirmation',
        ]);

        $this->actingAs($owner)
            ->get(route('company.workspace.closure.status'))
            ->assertOk();
    }

    public function test_admin_approval_detaches_members_and_preserves_personal_accounts_and_workspace_history(): void
    {
        [$owner, $member, $admin, $customer, $workspace] = $this->fixture();
        $opportunity = Opportunity::create([
            'company_workspace_id' => $workspace->id, 'customer_id' => $customer->id,
            'submitted_by' => $owner->id, 'title' => 'Closure test opportunity',
            'description' => 'An opportunity used to verify safe closure behavior.',
            'stage' => 'new', 'opportunity_type' => 'sell', 'visibility' => 'public',
            'verification_status' => 'auto_eligible', 'marketplace_state' => 'published',
            'published_at' => now(), 'expires_at' => now()->addDay(),
        ]);
        $closure = CompanyWorkspaceClosureRequest::create([
            'company_workspace_id' => $workspace->id, 'customer_id' => $customer->id,
            'owner_id' => $owner->id, 'email' => $owner->email,
            'status' => 'pending_review', 'email_verified_at' => now(), 'owner_confirmed_at' => now(),
            'previous_workspace_status' => 'active', 'previous_customer_active' => true,
        ]);
        $workspace->update(['status' => 'suspended']);
        $customer->update(['is_active' => false]);
        app(\App\Models\CompanyPlatformAudit::class)->newQuery()->create([
            'company_workspace_id' => $workspace->id, 'actor_id' => $owner->id,
            'action' => 'closure_suspended', 'details' => ['request_id' => $closure->id], 'created_at' => now(),
        ]);

        app(CompanyWorkspaceClosureService::class)->approve($closure, $admin, app(CompanyBillingService::class));

        $this->assertSame('completed', $closure->fresh()->status);
        $this->assertSame('closed', $workspace->fresh()->status);
        $this->assertFalse($customer->fresh()->is_active);
        $this->assertDatabaseCount('company_workspaces', 1);
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseMissing('company_members', ['company_workspace_id' => $workspace->id]);
        $this->assertNull($owner->fresh()->customer_id);
        $this->assertNull($member->fresh()->customer_id);
        $this->assertNotNull($owner->fresh());
        $this->assertNotNull($member->fresh());
        $this->assertSame('closed', $opportunity->fresh()->marketplace_state);
        $this->assertSame('private', $opportunity->fresh()->visibility);
    }

    public function test_admin_rejection_restores_only_the_closure_suspension(): void
    {
        [$owner, $member, $admin, $customer, $workspace] = $this->fixture();
        $closure = CompanyWorkspaceClosureRequest::create([
            'company_workspace_id' => $workspace->id, 'customer_id' => $customer->id,
            'owner_id' => $owner->id, 'email' => $owner->email,
            'status' => 'pending_review', 'email_verified_at' => now(), 'owner_confirmed_at' => now(),
            'previous_workspace_status' => 'active', 'previous_customer_active' => true,
        ]);
        $workspace->update(['status' => 'suspended']);
        $customer->update(['is_active' => false]);
        \App\Models\CompanyPlatformAudit::create([
            'company_workspace_id' => $workspace->id, 'actor_id' => $owner->id,
            'action' => 'closure_suspended', 'details' => ['request_id' => $closure->id], 'created_at' => now(),
        ]);
        app(CompanyWorkspaceClosureService::class)->reject($closure, $admin, 'Please resolve the pending company obligations first.');
        $this->assertSame('rejected', $closure->fresh()->status);
        $this->assertSame('active', $workspace->fresh()->status);
        $this->assertTrue($customer->fresh()->is_active);
        $this->assertDatabaseHas('company_members', ['user_id' => $owner->id, 'company_workspace_id' => $workspace->id]);
    }
}
