<?php

namespace App\Services\Platform;

use App\Mail\CompanyWorkspaceClosureVerificationMail;
use App\Models\CompanyMember;
use App\Models\CompanyPlatformAudit;
use App\Models\CompanyWorkspace;
use App\Models\CompanyWorkspaceClosureRequest;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final class CompanyWorkspaceClosureService
{
    public function start(User $owner): void
    {
        $token = Str::random(64);
        $hash = hash('sha256', $token);
        $request = DB::transaction(function () use ($owner, $hash) {
            $member = CompanyMember::where('user_id', $owner->id)->lockForUpdate()->first();
            abort_unless($member && $member->role === 'owner', 403);
            $workspace = CompanyWorkspace::whereKey($member->company_workspace_id)->lockForUpdate()->firstOrFail();
            $customer = Customer::whereKey($workspace->customer_id)->lockForUpdate()->firstOrFail();
            if ($workspace->status !== 'active' || ! $customer->is_active) {
                throw ValidationException::withMessages(['closure' => 'This company workspace is not active.']);
            }
            if (CompanyWorkspaceClosureRequest::where('company_workspace_id', $workspace->id)
                ->whereIn('status', ['email_pending', 'owner_confirmation', 'pending_review'])->exists()) {
                throw ValidationException::withMessages(['closure' => 'A workspace closure request is already in progress.']);
            }

            $closure = CompanyWorkspaceClosureRequest::create([
                'company_workspace_id' => $workspace->id,
                'customer_id' => $customer->id,
                'owner_id' => $owner->id,
                'email' => $owner->email,
                'token_hash' => $hash,
                'token_expires_at' => now()->addHours(24),
                'status' => 'email_pending',
                'version' => 1,
            ]);
            CompanyPlatformAudit::create([
                'company_workspace_id' => $workspace->id, 'actor_id' => $owner->id,
                'action' => 'closure_requested', 'details' => ['request_id' => $closure->id],
                'created_at' => now('UTC')->format('Y-m-d H:i:s'),
            ]);
            return $closure;
        }, 3);

        try {
            $url = route('company.workspace.closure.verify.show', ['token' => $token]);
            Mail::to($request->email)->send(new CompanyWorkspaceClosureVerificationMail(
                verificationUrl: $url,
                companyName: (string) Customer::findOrFail($request->customer_id)->company_name,
                language: app()->getLocale(),
            ));
            $request->update(['email_sent_at' => now()]);
        } catch (Throwable $exception) {
            $request->update(['status' => 'email_failed', 'token_hash' => null, 'token_expires_at' => null]);
            report($exception);
            throw ValidationException::withMessages(['closure' => 'The verification email could not be sent. Please try again.']);
        }
    }

    public function tokenIsValid(string $token): bool
    {
        return CompanyWorkspaceClosureRequest::where('token_hash', hash('sha256', $token))
            ->where('status', 'email_pending')->whereNotNull('email_sent_at')
            ->where('token_expires_at', '>', now())->exists();
    }

    public function verifyEmail(string $token): ?CompanyWorkspaceClosureRequest
    {
        $hash = hash('sha256', $token);
        return DB::transaction(function () use ($hash) {
            $request = CompanyWorkspaceClosureRequest::where('token_hash', $hash)->lockForUpdate()->first();
            if (! $request || $request->status !== 'email_pending' || ! $request->email_sent_at || ! $request->token_expires_at || now()->greaterThanOrEqualTo($request->token_expires_at)) {
                return null;
            }
            $owner = User::whereKey($request->owner_id)->lockForUpdate()->first();
            if (! $owner || Str::lower(trim($owner->email)) !== Str::lower(trim($request->email))) {
                return null;
            }
            $request->update([
                'status' => 'owner_confirmation', 'email_verified_at' => now(),
                'token_hash' => null, 'token_expires_at' => null, 'version' => $request->version + 1,
            ]);
            CompanyPlatformAudit::create([
                'company_workspace_id' => $request->company_workspace_id, 'actor_id' => $owner->id,
                'action' => 'closure_email_verified', 'details' => ['request_id' => $request->id],
                'created_at' => now('UTC')->format('Y-m-d H:i:s'),
            ]);
            return $request->fresh();
        }, 3);
    }

    public function confirmByOwner(User $owner): CompanyWorkspaceClosureRequest
    {
        return DB::transaction(function () use ($owner) {
            $request = CompanyWorkspaceClosureRequest::where('owner_id', $owner->id)
                ->where('status', 'owner_confirmation')->whereNotNull('email_verified_at')
                ->latest('id')->lockForUpdate()->firstOrFail();
            $workspace = CompanyWorkspace::whereKey($request->company_workspace_id)->lockForUpdate()->firstOrFail();
            $customer = Customer::whereKey($request->customer_id)->lockForUpdate()->firstOrFail();
            abort_unless(CompanyMember::where('company_workspace_id', $workspace->id)->where('user_id', $owner->id)->where('role', 'owner')->exists(), 403);
            if ($workspace->status !== 'active' || ! $customer->is_active) {
                throw ValidationException::withMessages(['closure' => 'The workspace state changed. Contact an administrator.']);
            }
            $request->update([
                'previous_workspace_status' => $workspace->status,
                'previous_customer_active' => (bool) $customer->is_active,
                'owner_confirmed_at' => now(), 'status' => 'pending_review',
                'version' => $request->version + 1,
            ]);
            $workspace->update(['status' => 'suspended']);
            $customer->update(['is_active' => false]);
            CompanyPlatformAudit::create([
                'company_workspace_id' => $workspace->id, 'actor_id' => $owner->id,
                'action' => 'closure_suspended',
                'details' => ['request_id' => $request->id, 'reason' => 'Owner explicitly confirmed workspace closure.'],
                'created_at' => now('UTC')->format('Y-m-d H:i:s'),
            ]);
            return $request->fresh();
        }, 3);
    }

    public function approve(CompanyWorkspaceClosureRequest $closure, User $admin, CompanyBillingService $billing): void
    {
        abort_unless($admin->hasRole(['admin', 'super-admin']), 403);
        $closure = CompanyWorkspaceClosureRequest::findOrFail($closure->id);
        abort_unless($closure->status === 'pending_review', 409);
        $workspace = CompanyWorkspace::findOrFail($closure->company_workspace_id);
        $owner = User::findOrFail($closure->owner_id);

        // Stripe calls happen before the database transaction. The stable key makes retries safe.
        $billing->cancelForClosure($workspace, $owner, (int) $closure->id);

        DB::transaction(function () use ($closure, $admin): void {
            $request = CompanyWorkspaceClosureRequest::whereKey($closure->id)->lockForUpdate()->firstOrFail();
            abort_unless($request->status === 'pending_review', 409);
            $workspace = CompanyWorkspace::whereKey($request->company_workspace_id)->lockForUpdate()->firstOrFail();
            $customer = Customer::whereKey($request->customer_id)->lockForUpdate()->firstOrFail();
            abort_unless($workspace->status === 'suspended' && ! $customer->is_active, 409);

            $members = CompanyMember::where('company_workspace_id', $workspace->id)->lockForUpdate()->get();
            foreach ($members as $member) {
                CompanyPlatformAudit::create([
                    'company_workspace_id' => $workspace->id, 'actor_id' => $admin->id,
                    'action' => 'closure_member_detached',
                    'details' => ['request_id' => $request->id, 'user_id' => $member->user_id, 'role' => $member->role],
                    'created_at' => now('UTC')->format('Y-m-d H:i:s'),
                ]);
                $user = User::whereKey($member->user_id)->lockForUpdate()->first();
                $member->delete();
                if ($user && (int) $user->customer_id === (int) $customer->id) {
                    $user->forceFill(['customer_id' => null])->save();
                }
            }

            // Close only opportunities owned by this workspace. Shared offers, rooms, messages and events remain immutable.
            $opportunitiesClosed = Opportunity::where('company_workspace_id', $workspace->id)
                ->where(function ($query): void {
                    $query->whereNull('marketplace_state')->orWhere('marketplace_state', '!=', 'closed')->orWhere('visibility', '!=', 'private');
                })->update(['marketplace_state' => 'closed', 'visibility' => 'private', 'updated_at' => now()]);

            $workspace->update(['status' => 'closed']);
            $customer->update(['is_active' => false]);
            CompanyPlatformAudit::create([
                'company_workspace_id' => $workspace->id, 'actor_id' => $admin->id,
                'action' => 'closure_approved',
                'details' => ['request_id' => $request->id, 'opportunities_closed' => $opportunitiesClosed, 'members_detached' => $members->count()],
                'created_at' => now('UTC')->format('Y-m-d H:i:s'),
            ]);
            $request->update([
                'status' => 'completed', 'reviewed_by' => $admin->id, 'reviewed_at' => now(),
                'review_reason' => 'Approved by platform administration.', 'completed_at' => now(),
                'version' => $request->version + 1,
            ]);
        }, 3);
    }

    public function reject(CompanyWorkspaceClosureRequest $closure, User $admin, string $reason): void
    {
        abort_unless($admin->hasRole(['admin', 'super-admin']), 403);
        $reason = trim($reason);
        if ($reason === '') {
            throw ValidationException::withMessages(['reason' => 'A rejection reason is required.']);
        }
        DB::transaction(function () use ($closure, $admin, $reason): void {
            $request = CompanyWorkspaceClosureRequest::whereKey($closure->id)->lockForUpdate()->firstOrFail();
            abort_unless($request->status === 'pending_review', 409);
            $workspace = CompanyWorkspace::whereKey($request->company_workspace_id)->lockForUpdate()->firstOrFail();
            $customer = Customer::whereKey($request->customer_id)->lockForUpdate()->firstOrFail();
            $lastAudit = CompanyPlatformAudit::where('company_workspace_id', $workspace->id)->latest('id')->first();
            $canRestore = $workspace->status === 'suspended'
                && ! $customer->is_active
                && $lastAudit?->action === 'closure_suspended'
                && (int) data_get($lastAudit->details, 'request_id') === (int) $request->id;
            $restored = false;
            if ($canRestore && $request->previous_workspace_status === 'active' && $request->previous_customer_active) {
                $workspace->update(['status' => 'active']);
                $customer->update(['is_active' => true]);
                $restored = true;
            }
            CompanyPlatformAudit::create([
                'company_workspace_id' => $workspace->id, 'actor_id' => $admin->id,
                'action' => 'closure_rejected',
                'details' => ['request_id' => $request->id, 'reason' => $reason, 'workspace_reactivated' => $restored],
                'created_at' => now('UTC')->format('Y-m-d H:i:s'),
            ]);
            $request->update([
                'status' => 'rejected', 'reviewed_by' => $admin->id, 'reviewed_at' => now(),
                'review_reason' => $reason, 'version' => $request->version + 1,
            ]);
        }, 3);
    }
}
