<?php

namespace App\Services\Platform;

use App\Models\CompanyAccessGrant;
use App\Models\CompanyBillingAccount;
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

    private function freeLaunchPlan(): array
    {
        $free = PlatformPlan::where('code', 'free')->where('is_available', true)->first();
        if (! $free) {
            $this->fail('plans_missing');
        }

        return $this->snapshot($free) + ['source' => 'free', 'expires_at' => null];
    }

    public function plan(CompanyWorkspace $w): array
    {
        $launch = (bool) config('platform_billing.free_launch', true);
        if ($launch && ! app(StripeTestGateway::class)->live()) {
            return $this->freeLaunchPlan();
        }
        $billing = CompanyBillingAccount::where('company_workspace_id', $w->id)->first();
        if ($billing && ($billing->stripe_subscription_id || in_array($billing->status, ['conflict', 'invalid_price'], true))) {
            if (app(StripeTestGateway::class)->ready() && in_array($billing->status, ['active', 'past_due'], true) && $billing->paid_until?->isFuture() && is_array($billing->plan_snapshot)) {
                return $billing->plan_snapshot + ['source' => (app(StripeTestGateway::class)->live() ? 'stripe_live' : 'stripe_test'), 'expires_at' => $billing->paid_until->toIso8601String()];
            }
            $free = PlatformPlan::where('code', 'free')->where('is_available', true)->first();
            if (! $free) {
                $this->fail('plans_missing');
            }

            return $this->snapshot($free) + ['source' => 'free', 'expires_at' => null];
        }
        if ($launch) {
            return $this->freeLaunchPlan();
        }
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

    public function activateNewRegistration(Customer $customer, User $owner): CompanyWorkspace
    {
        // Only the registration controller calls this for the customer it just created.
        abort_unless((int) $owner->customer_id === (int) $customer->id && $customer->users()->count() === 1, 403);
        $free = PlatformPlan::firstOrCreate(['code' => 'free'], [
            'name_ar' => 'مجانية', 'name_en' => 'Free', 'monthly_amount_minor' => 0, 'currency' => 'AED',
            'seats' => 1, 'active_opportunities' => 1, 'monthly_applications' => 3, 'is_available' => true,
        ]);
        if (! $free->is_available || $free->seats < 1) {
            $this->fail('plans_missing');
        }
        $workspace = CompanyWorkspace::create(['customer_id' => $customer->id, 'status' => 'active']);
        CompanyMember::create(['company_workspace_id' => $workspace->id, 'user_id' => $owner->id, 'role' => 'owner']);
        CompanyAccessGrant::create(['company_workspace_id' => $workspace->id, 'source' => 'free', 'plan_snapshot' => $this->snapshot($free), 'expires_at' => null, 'reason' => 'Company self registration', 'created_by' => $owner->id, 'created_at' => now('UTC')->format('Y-m-d H:i:s')]);
        $this->audit($workspace, $owner, 'activated', ['customer_id' => $customer->id, 'owner_id' => $owner->id, 'source' => 'registration']);

        return $workspace;
    }

    public function createForOwner(User $owner, array $data): CompanyWorkspace
    {
        return DB::transaction(function () use ($owner, $data): CompanyWorkspace {
            $user = User::whereKey($owner->id)->lockForUpdate()->firstOrFail();

            abort_unless($user->hasVerifiedEmail() && $user->hasCompletedPhoneSetup(), 403, 'Complete your email and phone setup before creating a company workspace.');
            abort_if($user->isPlatformAdministrator() || $user->carrierPartnership()->exists(), 403);
            abort_unless($user->customer_id === null && ! CompanyMember::where('user_id', $user->id)->exists(), 409, 'This account already belongs to a company.');

            $free = PlatformPlan::where('code', 'free')->where('is_available', true)->lockForUpdate()->first();
            if (! $free || $free->seats < 1) {
                $this->fail('plans_missing');
            }

            $customer = Customer::create([
                'company_name' => trim($data['company_name']),
                'contact_person' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'country_code' => strtoupper($data['country_code']),
                'city' => isset($data['city']) ? trim($data['city']) : null,
                'is_active' => true,
            ]);

            $user->forceFill(['customer_id' => $customer->id])->save();

            $workspace = CompanyWorkspace::create(['customer_id' => $customer->id, 'status' => 'active']);
            CompanyMember::create(['company_workspace_id' => $workspace->id, 'user_id' => $user->id, 'role' => 'owner']);
            CompanyAccessGrant::create([
                'company_workspace_id' => $workspace->id,
                'source' => 'free',
                'plan_snapshot' => $this->snapshot($free),
                'expires_at' => null,
                'reason' => 'Initial self-service company onboarding',
                'created_by' => $user->id,
                'created_at' => now('UTC')->format('Y-m-d H:i:s'),
            ]);
            $this->audit($workspace, $user, 'activated', [
                'customer_id' => $customer->id,
                'owner_id' => $user->id,
                'source' => 'self_service_onboarding',
            ]);

            return $workspace;
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
        if (config('platform_billing.free_launch', true)) {
            throw ValidationException::withMessages(['trial' => __('free_launch.trial_blocked')]);
        }
        DB::transaction(function () use ($workspace, $plan, $days, $reason, $actor): void {
            $w = CompanyWorkspace::whereKey($workspace->id)->lockForUpdate()->firstOrFail();
            if (CompanyBillingAccount::where('company_workspace_id', $w->id)->whereNotNull('stripe_subscription_id')->exists()) {
                $this->fail('billing_managed');
            }
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
            $this->audit($w, $actor, 'status_changed', ['from' => $w->status, 'to' => $status, 'reason' => $reason]);
            $w->update(['status' => $status]);
        }, 3);
    }
}
