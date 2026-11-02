<?php

namespace Tests\Feature;

use App\Models\CompanyAccessGrant;
use App\Models\CompanyMember;
use App\Models\CompanyPlatformAudit;
use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\PlatformPlan;
use App\Models\Role;
use App\Models\User;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CompanyPlatformFoundationTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $this->artisan('platform:install-plans')->assertSuccessful();
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        $customer = Customer::create(['company_name' => 'Platform Test Company', 'is_active' => true]);
        $owner = User::factory()->create(['customer_id' => $customer->id]);
        $service = new CompanyWorkspaceService;

        return [$admin, $customer, $owner, $service];
    }

    private function rejects(callable $call): void
    {
        try {
            $call();
            $this->fail('Expected rejection');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }
    }

    public function test_catalog_install_is_idempotent_and_preserves_custom_prices(): void
    {
        $this->artisan('platform:install-plans')->assertSuccessful();
        PlatformPlan::where('code', 'business')->update(['monthly_amount_minor' => 25000]);
        $this->artisan('platform:install-plans')->assertSuccessful();
        $this->assertDatabaseCount('platform_plans', 3);
        $this->assertSame(25000, PlatformPlan::where('code', 'business')->first()->monthly_amount_minor);
    }

    public function test_activation_reuses_customer_and_requires_explicit_linked_owner(): void
    {
        [$admin,$c,$owner,$s] = $this->fixture();
        $w = $s->activate($c, $owner, $admin);
        $this->assertSame($c->id, $w->customer_id);
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseCount('company_members', 1);
        $this->assertDatabaseHas('company_members', ['user_id' => $owner->id, 'role' => 'owner']);
        $this->assertSame('free', $s->plan($w)['code']);
        $this->assertFalse($owner->hasRole('admin'));
        $this->assertDatabaseCount('company_platform_audits', 1);
    }

    public function test_wrong_owner_or_platform_admin_or_inactive_company_is_rejected(): void
    {
        [$admin,$c,$owner,$s] = $this->fixture();
        $this->rejects(fn () => $s->activate($c, User::factory()->create(), $admin));
        $admin->update(['customer_id' => $c->id]);
        $this->rejects(fn () => $s->activate($c, $admin, $admin));
        $c->update(['is_active' => false]);
        $this->rejects(fn () => $s->activate($c, $owner, $admin));
        $this->assertDatabaseCount('company_workspaces', 0);
    }

    public function test_duplicate_activation_creates_no_extra_records(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        $s->activate($c, $o, $a);
        $this->rejects(fn () => $s->activate($c, $o, $a));
        $this->assertDatabaseCount('company_workspaces', 1);
        $this->assertDatabaseCount('company_access_grants', 1);
    }

    public function test_free_seat_limit_counts_owner(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        $w = $s->activate($c, $o, $a);
        $u = User::factory()->create(['customer_id' => $c->id]);
        $this->rejects(fn () => $s->addMember($w, $u, 'member', $a));
        $this->assertDatabaseCount('company_members', 1);
    }

    public function test_trial_team_limit_and_company_roles_do_not_grant_global_admin(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        $w = $s->activate($c, $o, $a);
        $s->trial($w, PlatformPlan::where('code', 'business')->first(), 14, 'Evaluate team access', $a);
        foreach (['manager', 'member'] as $role) {
            $u = User::factory()->create(['customer_id' => $c->id]);
            $s->addMember($w, $u, $role, $a);
            $this->assertFalse($u->hasRole('admin'));
        }
        $this->rejects(fn () => $s->addMember($w, User::factory()->create(['customer_id' => $c->id]), 'member', $a));
        $this->assertSame('manual_trial', $s->plan($w)['source']);
        $this->assertDatabaseCount('company_members', 3);
    }

    public function test_member_from_other_company_and_privileged_role_are_rejected(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        $w = $s->activate($c, $o, $a);
        $s->trial($w, PlatformPlan::where('code', 'business')->first(), 7, 'Test', $a);
        $this->rejects(fn () => $s->addMember($w, User::factory()->create(), 'member', $a));
        $this->rejects(fn () => $s->addMember($w, User::factory()->create(['customer_id' => $c->id]), 'admin', $a));
        $this->assertDatabaseCount('company_members', 1);
    }

    public function test_expired_trial_falls_back_to_free_without_deleting_members_or_data(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        $w = $s->activate($c, $o, $a);
        $s->trial($w, PlatformPlan::where('code', 'business')->first(), 7, 'Test', $a);
        $u = User::factory()->create(['customer_id' => $c->id]);
        $s->addMember($w, $u, 'member', $a);
        $this->travel(8)->days();
        $this->assertSame('free', $s->plan($w)['code']);
        $this->assertSame(1, $s->plan($w)['seats']);
        $this->assertSame($w->id, $s->workspaceFor($u)->id);
        $this->assertDatabaseCount('company_members', 2);
        $this->rejects(fn () => $s->addMember($w, User::factory()->create(['customer_id' => $c->id]), 'member', $a));
    }

    public function test_plan_grant_snapshot_does_not_change_with_catalog_edit(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        $w = $s->activate($c, $o, $a);
        $p = PlatformPlan::where('code', 'business')->first();
        $s->trial($w, $p, 7, 'Test', $a);
        $p->update(['seats' => 99, 'monthly_amount_minor' => 1]);
        $this->assertSame(3, $s->plan($w)['seats']);
        $this->assertSame(19900, $s->plan($w)['monthly_amount_minor']);
    }

    public function test_owner_cannot_be_removed_deleted_or_reassigned(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        $w = $s->activate($c, $o, $a);
        $m = CompanyMember::where('user_id', $o->id)->first();
        $this->rejects(fn () => $s->removeMember($w, $m, $a));
        $this->rejects(fn () => $o->delete());
        $other = Customer::create(['company_name' => 'Other', 'is_active' => true]);
        $this->rejects(fn () => $o->update(['customer_id' => $other->id]));
        $this->assertDatabaseHas('users', ['id' => $o->id, 'customer_id' => $c->id]);
    }

    public function test_member_removal_is_scoped_and_audited(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        $w = $s->activate($c, $o, $a);
        $s->trial($w, PlatformPlan::where('code', 'business')->first(), 7, 'Test', $a);
        $u = User::factory()->create(['customer_id' => $c->id]);
        $s->addMember($w, $u, 'member', $a);
        $m = CompanyMember::where('user_id', $u->id)->first();
        $s->removeMember($w, $m, $a);
        $this->assertDatabaseCount('company_members', 1);
        $this->assertDatabaseHas('users', ['id' => $u->id]);
        $this->assertDatabaseHas('company_platform_audits', ['action' => 'member_removed']);
    }

    public function test_member_reassignment_requires_removal_first(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        $w = $s->activate($c, $o, $a);
        $s->trial($w, PlatformPlan::where('code', 'business')->first(), 7, 'Test', $a);
        $u = User::factory()->create(['customer_id' => $c->id]);
        $s->addMember($w, $u, 'member', $a);
        $other = Customer::create(['company_name' => 'Other', 'is_active' => true]);
        $this->rejects(fn () => $u->update(['customer_id' => $other->id]));
        $s->removeMember($w, CompanyMember::where('user_id', $u->id)->first(), $a);
        $u->refresh()->update(['customer_id' => $other->id]);
        $this->assertSame($other->id, $u->fresh()->customer_id);
    }

    public function test_invalid_or_disabled_trial_is_rejected(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        $w = $s->activate($c, $o, $a);
        $p = PlatformPlan::where('code', 'business')->first();
        $this->rejects(fn () => $s->trial($w, $p, 365, 'Test', $a));
        $this->rejects(fn () => $s->trial($w, $p, 7, ' ', $a));
        $p->update(['is_available' => false]);
        $this->rejects(fn () => $s->trial($w, $p, 7, 'Test', $a));
        $this->assertDatabaseCount('company_access_grants', 1);
    }

    public function test_company_portal_is_scoped_to_membership_and_both_languages(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        $s->activate($c, $o, $a);
        foreach (['ar', 'en'] as $locale) {
            $this->actingAs($o)->withSession(['locale' => $locale])->get(route('company.workspace'))->assertOk()->assertSee('Platform Test Company');
        }
        $this->actingAs(User::factory()->create(['customer_id' => $c->id]))->get(route('company.workspace'))->assertForbidden();
        $this->actingAs(User::factory()->create())->get(route('company.workspace'))->assertForbidden();
    }

    public function test_suspension_blocks_portal_and_new_members_and_is_audited(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        $w = $s->activate($c, $o, $a);
        $s->setStatus($w, 'suspended', 'Review required', $a);
        $this->actingAs($o)->get(route('company.workspace'))->assertForbidden();
        $this->rejects(fn () => $s->addMember($w, User::factory()->create(['customer_id' => $c->id]), 'member', $a));
        $this->assertDatabaseHas('company_platform_audits', ['action' => 'status_changed']);
        $s->setStatus($w, 'active', 'Reviewed', $a);
        $this->get(route('company.workspace'))->assertOk();
    }

    public function test_guests_and_regular_users_cannot_manage_companies_or_trials(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        $w = $s->activate($c, $o, $a);
        $this->get(route('admin.platform.index'))->assertRedirect(route('login'));
        $this->post(route('admin.platform.store'), ['customer_id' => $c->id, 'owner_id' => $o->id])->assertRedirect(route('login'));
        $this->actingAs($o)->get(route('admin.platform.index'))->assertForbidden();
        $this->post(route('admin.platform.trial', $w), ['plan_id' => PlatformPlan::where('code', 'business')->first()->id, 'days' => 7, 'reason' => 'Attempt'])->assertForbidden();
    }

    public function test_admin_pages_and_activation_forms_work_in_both_languages(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        $this->actingAs($a)->post(route('admin.platform.store'), ['customer_id' => $c->id, 'owner_id' => $o->id])->assertRedirect();
        $w = CompanyWorkspace::first();
        foreach (['ar', 'en'] as $locale) {
            $this->withSession(['locale' => $locale])->get(route('admin.platform.index'))->assertOk();
            $this->get(route('admin.platform.show', $w))->assertOk()->assertSee('Platform Test Company');
        }
    }

    public function test_grants_and_audit_entries_are_immutable(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        $s->activate($c, $o, $a);
        foreach ([CompanyAccessGrant::first(), CompanyPlatformAudit::first()] as $record) {
            try {
                $record->update(['created_by' => 999]);
                $this->fail('Immutable');
            } catch (\LogicException) {
                $this->assertTrue(true);
            }
            try {
                $record->delete();
                $this->fail('Immutable');
            } catch (\LogicException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_foundation_does_not_create_invoices_payments_or_subscriptions(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        $w = $s->activate($c, $o, $a);
        $s->trial($w, PlatformPlan::where('code', 'professional')->first(), 7, 'Prototype', $a);
        foreach (['issued_invoices', 'invoice_payments', 'deals', 'deal_commissions'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }

    public function test_membership_removal_cannot_target_a_different_workspace(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        $w = $s->activate($c, $o, $a);
        $other = Customer::create(['company_name' => 'Separate company', 'is_active' => true]);
        $user = User::factory()->create(['customer_id' => $other->id]);
        $second = $s->activate($other,$user,$a);
        $member = CompanyMember::where('user_id',$user->id)->first();
        $this->actingAs($a)->delete(route('admin.platform.members.remove',[$w, $member]))->assertNotFound();
        $this->assertDatabaseCount('company_members',2);
    }

    public function test_missing_catalog_blocks_activation_without_partial_records(): void
    {
        [$a,$c,$o,$s] = $this->fixture();
        PlatformPlan::query()->delete();
        $this->rejects(fn () => $s->activate($c,$o,$a));
        $this->assertDatabaseCount('company_workspaces',0);
        $this->assertDatabaseCount('company_members',0);
        $this->assertDatabaseCount('company_platform_audits',0);
    }

    public function test_company_with_workspace_cannot_be_deleted(): void
    {
        [$a, $c, $o, $s] = $this->fixture();
        $s->activate($c, $o, $a);
        $this->rejects(fn () => $c->delete());
        $this->assertDatabaseHas('customers', ['id' => $c->id]);
        $this->actingAs($a)->deleteJson(route('admin.customers.destroy', $c))->assertUnprocessable();
    }
}
