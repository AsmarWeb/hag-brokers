<?php

namespace App\Services\Platform;

use App\Models\CompanyMarketplaceRequest;
use App\Models\CompanyMember;
use App\Models\CompanyPlatformAudit;
use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\OpportunityInterest;
use App\Models\User;
use App\Notifications\AdminPlatformNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CompanyMarketplaceService
{
    private function fail(string $key): never
    {
        throw ValidationException::withMessages(['marketplace' => __('market.'.$key)]);
    }

    public function actor(User $actor, bool $publish = false): CompanyWorkspace
    {
        abort_unless($actor->hasVerifiedEmail() && ! $actor->hasRole(['admin', 'super-admin']), 403);
        $w = app(CompanyWorkspaceService::class)->workspaceFor($actor);
        $member = CompanyMember::where('user_id', $actor->id)->where('company_workspace_id', $w->id)->firstOrFail();
        if ($publish) {
            abort_unless(in_array($member->role, ['owner', 'manager'], true), 403);
        }

        return $w;
    }

    public function publicQuery()
    {
        return Opportunity::query()->where('visibility', 'public')->whereIn('verification_status', ['verified', 'auto_eligible'])
            ->whereNotNull('published_at')->where('published_at', '<=', now())
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->where(fn ($q) => $q->whereNull('marketplace_state')->orWhere('marketplace_state', 'published'))
            ->where(function ($q): void {
                $q->whereNull('company_workspace_id')->orWhereIn('company_workspace_id', CompanyWorkspace::where('status', 'active')->whereIn('customer_id', Customer::where('is_active', true)->select('id'))->select('id'));
            });
    }

    public function isPublic(Opportunity $o): bool
    {
        return $this->publicQuery()->whereKey($o->id)->exists();
    }

    public function usage(CompanyWorkspace $w): array
    {
        $plan = app(CompanyWorkspaceService::class)->plan($w);

        return ['plan' => $plan, 'active' => Opportunity::where('company_workspace_id', $w->id)->where('marketplace_state', 'published')->where('expires_at', '>', now())->count(),
            'applications' => CompanyMarketplaceRequest::where('company_workspace_id', $w->id)->where('action', 'apply')->where('usage_month', now('Asia/Dubai')->format('Y-m'))->count(),
            'month' => now('Asia/Dubai')->format('Y-m')];
    }

    private function lockActor(User $actor, bool $publish): CompanyWorkspace
    {
        $initial = $this->actor($actor, $publish);
        $w = CompanyWorkspace::whereKey($initial->id)->lockForUpdate()->firstOrFail();
        $user = User::whereKey($actor->id)->lockForUpdate()->firstOrFail();
        $member = CompanyMember::where('user_id', $user->id)->where('company_workspace_id', $w->id)->lockForUpdate()->first();
        $customer = Customer::whereKey($w->customer_id)->lockForUpdate()->firstOrFail();
        abort_unless($member && $w->status === 'active' && $customer->is_active && (int) $user->customer_id === (int) $w->customer_id && $user->hasVerifiedEmail() && ! $user->hasRole(['admin', 'super-admin']), 403);
        if ($publish) {
            abort_unless(in_array($member->role, ['owner', 'manager'], true), 403);
        }

        return $w;
    }

    private function hash(string $action, array $data, User $actor, array $media = []): string
    {
        // Commercial detail object key order must not invalidate a safe retry.
        if (isset($data['commercial_details']) && is_array($data['commercial_details'])) {
            ksort($data['commercial_details']);
        }
        ksort($data);

        return hash('sha256', json_encode([$action, $actor->id, $data, array_column($media, 'sha256')], JSON_THROW_ON_ERROR));
    }

    private function previous(CompanyWorkspace $w, string $key, string $hash): ?CompanyMarketplaceRequest
    {
        $event = CompanyMarketplaceRequest::where('company_workspace_id', $w->id)->where('request_key', $key)->first();
        if ($event && ! hash_equals($event->request_hash, $hash)) {
            $this->fail('request_conflict');
        }

        return $event;
    }

    private function record(CompanyWorkspace $w, string $key, string $hash, string $action, int $id): void
    {
        CompanyMarketplaceRequest::create(['company_workspace_id' => $w->id, 'request_key' => $key, 'request_hash' => $hash, 'action' => $action, 'resource_id' => $id, 'usage_month' => now('Asia/Dubai')->format('Y-m'), 'created_at' => now('UTC')->format('Y-m-d H:i:s')]);
    }

    private function audit(CompanyWorkspace $w, User $actor, string $action, array $details): void
    {
        CompanyPlatformAudit::create(['company_workspace_id' => $w->id, 'actor_id' => $actor->id, 'action' => $action, 'details' => $details, 'created_at' => now('UTC')->format('Y-m-d H:i:s')]);
    }

    private function notifyCompany(int $workspaceId, string $type, string $title, Opportunity $opportunity, int $interestId): void
    {
        $workspace = CompanyWorkspace::find($workspaceId);
        if (! $workspace) {
            return;
        }
        $query = CompanyMember::where('company_workspace_id', $workspaceId);
        if ($type === 'marketplace.application') {
            $query->whereIn('role', ['owner', 'manager']);
        }
        foreach (User::where('customer_id', $workspace->customer_id)->whereIn('id', $query->select('user_id'))->get() as $recipient) {
            $recipient->notify(new AdminPlatformNotification(type: $type, title: $title, message: $opportunity->title, url: '/company/marketplace', extra: ['opportunity_id' => $opportunity->id, 'interest_id' => $interestId]));
        }
    }

    public function publish(User $actor, array $data, string $key, array $media = []): array
    {
        $hash = $this->hash('publish', $data, $actor, $media);

        return DB::transaction(function () use ($actor, $data, $key, $media, $hash): array {
            $w = $this->lockActor($actor, true);
            if ($event = $this->previous($w, $key, $hash)) {
                return ['opportunity' => Opportunity::findOrFail($event->resource_id), 'created' => false];
            }
            $usage = $this->usage($w);
            if ($usage['active'] >= $usage['plan']['active_opportunities']) {
                $this->fail('opportunity_limit');
            }
            $vessel = $data['vessel'] ?? null;
            unset($data['vessel']);
            $data = array_intersect_key($data, array_flip(['commercial_details', 'title', 'opportunity_type', 'category', 'description', 'cargo_type', 'container_type', 'origin_port_id', 'destination_port_id', 'country', 'city', 'estimated_value', 'required_capital', 'min_investment', 'currency', 'commission_type', 'commission_rate', 'expected_close_date', 'expires_at', 'notes']));
            $o = Opportunity::create($data + ['customer_id' => $w->customer_id, 'company_workspace_id' => $w->id, 'submitted_by' => $actor->id, 'stage' => 'new', 'probability' => 0, 'visibility' => 'public', 'verification_status' => 'auto_eligible', 'published_at' => now('UTC'), 'marketplace_state' => 'published']);
            if ($vessel) {
                $o->vessel()->create($vessel);
            }
            foreach ($media as $index => $file) {
                unset($file['sha256']);
                $o->media()->create($file + ['sort_order' => $index, 'is_primary' => $index === 0]);
            }
            $this->record($w, $key, $hash, 'publish', $o->id);
            $this->audit($w, $actor, 'opportunity_published', ['opportunity_id' => $o->id]);

            return ['opportunity' => $o, 'created' => true];
        }, 3);
    }

    public function update(User $actor, Opportunity $opportunity, array $data): Opportunity
    {
        return DB::transaction(function () use ($actor, $opportunity, $data) {
            $w = $this->lockActor($actor, true);
            $o = Opportunity::whereKey($opportunity->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $o->company_workspace_id === (int) $w->id, 404);
            // Editing never reopens a closed/expired listing or changes ownership/financial agreement fields.
            if ($o->marketplace_state !== 'published' || ! $o->expires_at?->isFuture()) {
                $this->fail('closed');
            }
            $o->update(array_intersect_key($data, array_flip(['title', 'description', 'country', 'city', 'notes', 'category', 'commercial_details'])));
            $this->audit($w, $actor, 'opportunity_updated', ['opportunity_id' => $o->id]);

            return $o;
        }, 3);
    }

    public function close(User $actor, Opportunity $opportunity): void
    {
        DB::transaction(function () use ($actor, $opportunity): void {
            $w = $this->lockActor($actor, true);
            $o = Opportunity::whereKey($opportunity->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $o->company_workspace_id === (int) $w->id, 404);
            if ($o->marketplace_state === 'closed') {
                return;
            }
            $o->update(['marketplace_state' => 'closed', 'visibility' => 'private']);
            $this->audit($w, $actor, 'opportunity_closed', ['opportunity_id' => $o->id]);
        }, 3);
    }

    public function apply(User $actor, Opportunity $opportunity, array $data, string $key): OpportunityInterest
    {
        $hash = $this->hash('apply', $data + ['opportunity_id' => $opportunity->id], $actor);

        return DB::transaction(function () use ($actor, $opportunity, $data, $key, $hash) {
            $w = $this->lockActor($actor, false);
            if ($event = $this->previous($w, $key, $hash)) {
                return OpportunityInterest::findOrFail($event->resource_id);
            }
            // This shared opportunity lock serializes application against closing/expiry changes.
            $o = Opportunity::whereKey($opportunity->id)->lockForUpdate()->firstOrFail();
            abort_unless($this->isPublic($o), 404);
            if (! $o->company_workspace_id) {
                $this->fail('legacy_listing');
            }
            if ((int) $o->customer_id === (int) $w->customer_id) {
                $this->fail('own_opportunity');
            }
            if ($existing = OpportunityInterest::where('opportunity_id', $o->id)->where('company_workspace_id', $w->id)->first()) {
                return $existing;
            }
            if (OpportunityInterest::where('opportunity_id', $o->id)->where('user_id', $actor->id)->exists()) {
                $this->fail('legacy_interest');
            }
            $usage = $this->usage($w);
            if ($usage['applications'] >= $usage['plan']['monthly_applications']) {
                $this->fail('application_limit');
            }
            $data = array_intersect_key($data, array_flip(['role', 'available_capital', 'currency', 'message']));
            $interest = OpportunityInterest::create($data + ['opportunity_id' => $o->id, 'user_id' => $actor->id, 'company_workspace_id' => $w->id, 'status' => 'pending', 'reviewed_at' => null, 'reviewed_by' => null]);
            $this->record($w, $key, $hash, 'apply', $interest->id);
            $this->notifyCompany($o->company_workspace_id, 'marketplace.application', 'تقديم جديد من شركة / New company application', $o, $interest->id);
            $this->audit($w, $actor, 'opportunity_applied', ['opportunity_id' => $o->id, 'interest_id' => $interest->id]);

            return $interest;
        }, 3);
    }

    public function decide(User $actor, OpportunityInterest $interest, string $status): void
    {
        DB::transaction(function () use ($actor, $interest, $status): void {
            $w = $this->lockActor($actor, true);
            $o = Opportunity::whereKey($interest->opportunity_id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $o->company_workspace_id === (int) $w->id, 404);
            abort_unless(in_array($status, ['accepted', 'rejected'], true), 422);
            $i = OpportunityInterest::whereKey($interest->id)->lockForUpdate()->firstOrFail();
            if ($i->status === $status) {
                return;
            }
            $i->update(['status' => $status, 'reviewed_at' => now('UTC'), 'reviewed_by' => $actor->id]);
            if ($i->company_workspace_id) {
                $this->notifyCompany($i->company_workspace_id, 'marketplace.decision', ($status === 'accepted' ? 'تم قبول الاهتمام / Interest accepted' : 'تم رفض الاهتمام / Interest rejected'), $o, $i->id);
            }$this->audit($w, $actor, 'application_decided', ['interest_id' => $i->id, 'status' => $status]);
        }, 3);
    }
}
