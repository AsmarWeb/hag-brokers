<?php

namespace App\Services\Platform;

use App\Models\CompanyAccessGrant;
use App\Models\CompanyMember;
use App\Models\CompanyPlatformAudit;
use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\PlatformPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CompanyWorkspaceService
{
    private function fail(string $key): never
    {
        throw ValidationException::withMessages(['company' => __('platform.'.$key)]);
    }

    private function admin(User $actor): void
    {
        abort_unless($actor->hasRole(['admin', 'super-admin']), 403);
    }

    private function audit(CompanyWorkspace $w, User $actor, string $action, array $details): void
    {
        CompanyPlatformAudit::create(['company_workspace_id' => $w->id, 'actor_id' => $actor->id, 'action' => $action, 'details' => $details, 'created_at' => now('UTC')->format('Y-m-d H:i:s')]);
    }

    public function plan(CompanyWorkspace $w): array
    {
        $grant = CompanyAccessGrant::where('company_workspace_id', $w->id)->latest('id')->first();
        if ($grant && (! $grant->expires_at || $grant->expires_at->isFuture())) {
            return $grant->plan_snapshot + ['source' => $grant->source, 'expires_at' => $grant->expires_at?->toIso8601String()];
        }
        $free = PlatformPlan::where('code', 'free')->where('is_available', true)->first();
        if (! $free) {
            $this->fail('plans_missing');
        }

        return $this->snapshot($free) + ['source' => 'free', 'expires_at' => null];
    }

    private function snapshot(PlatformPlan $p): array
    {
        return $p->only(['code', 'name_ar', 'name_en', 'monthly_amount_minor', 'currency', 'seats', 'active_opportunities', 'monthly_applications']);
    }

    public function activate(Customer $customer, User $owner, User $actor): CompanyWorkspace
    {
        $this->admin($actor);

        return DB::transaction(function () use ($customer, $owner, $actor) {
            $customer = Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $owner = User::whereKey($owner->id)->lockForUpdate()->firstOrFail();
            if (! $customer->is_active || (int) $owner->customer_id !== (int) $customer->id || $owner->hasRole(['admin', 'super-admin'])) {
                $this->fail('invalid_owner');
            }
            if (CompanyWorkspace::where('customer_id', $customer->id)->exists() || CompanyMember::where('user_id', $owner->id)->exists()) {
                $this->fail('already_exists');
            }
            $free = PlatformPlan::where('code', 'free')->where('is_available', true)->first();
            if (! $free || $free->seats < 1) {
                $this->fail('plans_missing');
            }
            $w = CompanyWorkspace::create(['customer_id' => $customer->id, 'status' => 'active']);
            CompanyMember::create(['company_workspace_id' => $w->id, 'user_id' => $owner->id, 'role' => 'owner']);
            CompanyAccessGrant::create(['company_workspace_id' => $w->id, 'source' => 'free', 'plan_snapshot' => $this->snapshot($free), 'expires_at' => null, 'reason' => 'Initial free access', 'created_by' => $actor->id, 'created_at' => now('UTC')->format('Y-m-d H:i:s')]);
            $this->audit($w, $actor, 'activated', ['customer_id' => $customer->id, 'owner_id' => $owner->id]);

            return $w;
        }, 3);
    }

    public function workspaceFor(User $user): CompanyWorkspace
    {
        $member = CompanyMember::where('user_id', $user->id)->first();
        abort_unless($member, 403);
        $w = CompanyWorkspace::findOrFail($member->company_workspace_id);
        $customer = Customer::findOrFail($w->customer_id);
        abort_unless($w->status === 'active' && $customer->is_active && (int) $user->customer_id === (int) $w->customer_id, 403);

        return $w;
    }

    public function addMember(CompanyWorkspace $workspace, User $user, string $role, User $actor): void
    {
        $this->admin($actor);
        DB::transaction(function () use ($workspace, $user, $role, $actor): void {
            $w = CompanyWorkspace::whereKey($workspace->id)->lockForUpdate()->firstOrFail();
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($w->status !== 'active' || ! Customer::findOrFail($w->customer_id)->is_active) {
                $this->fail('inactive');
            }
            if (! in_array($role, ['manager', 'member'], true) || (int) $user->customer_id !== (int) $w->customer_id || $user->hasRole(['admin', 'super-admin'])) {
                $this->fail('invalid_member');
            }
            if (CompanyMember::where('user_id', $user->id)->exists()) {
                $this->fail('already_member');
            }
            if (CompanyMember::where('company_workspace_id', $w->id)->count() >= $this->plan($w)['seats']) {
                $this->fail('seat_limit');
            }
            CompanyMember::create(['company_workspace_id' => $w->id, 'user_id' => $user->id, 'role' => $role]);
            $this->audit($w, $actor, 'member_added', ['user_id' => $user->id, 'role' => $role]);
        }, 3);
    }

    public function removeMember(CompanyWorkspace $workspace, CompanyMember $member, User $actor): void
    {
        $this->admin($actor);
        DB::transaction(function () use ($workspace, $member, $actor): void {
            $w = CompanyWorkspace::whereKey($workspace->id)->lockForUpdate()->firstOrFail();
            $member = CompanyMember::whereKey($member->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $member->company_workspace_id === (int) $w->id, 404);
            if ($member->role === 'owner') {
                $this->fail('owner_protected');
            }
            $this->audit($w, $actor, 'member_removed', ['user_id' => $member->user_id, 'role' => $member->role]);
            $member->delete();
        }, 3);
    }

    public function trial(CompanyWorkspace $workspace, PlatformPlan $plan, int $days, string $reason, User $actor): void
    {
        $this->admin($actor);
        DB::transaction(function () use ($workspace, $plan, $days, $reason, $actor): void {
            $w = CompanyWorkspace::whereKey($workspace->id)->lockForUpdate()->firstOrFail();
            $plan = PlatformPlan::findOrFail($plan->id);
            if ($w->status !== 'active' || ! Customer::findOrFail($w->customer_id)->is_active || ! $plan->is_available || $plan->code === 'free' || ! in_array($days, [7, 14, 30], true) || trim($reason) === '') {
                $this->fail('invalid_trial');
            }
            $grant = CompanyAccessGrant::create(['company_workspace_id' => $w->id, 'source' => 'manual_trial', 'plan_snapshot' => $this->snapshot($plan), 'expires_at' => now('UTC')->addDays($days)->format('Y-m-d H:i:s'), 'reason' => $reason, 'created_by' => $actor->id, 'created_at' => now('UTC')->format('Y-m-d H:i:s')]);
            $this->audit($w, $actor, 'trial_granted', ['grant_id' => $grant->id, 'plan' => $plan->code, 'days' => $days, 'reason' => $reason]);
        }, 3);
    }

    public function setStatus(CompanyWorkspace $workspace, string $status, string $reason, User $actor): void
    {
        $this->admin($actor);
        DB::transaction(function () use ($workspace, $status, $reason, $actor): void {
            $w = CompanyWorkspace::whereKey($workspace->id)->lockForUpdate()->firstOrFail();
            if (! in_array($status, ['active', 'suspended'], true) || trim($reason) === '') {
                $this->fail('invalid_status');
            }
            $this->audit($w,$actor,'status_changed',['from' => $w->status, 'to' => $status, 'reason' => $reason]);
            $w->update(['status' => $status]);
        }, 3);
    }
}
