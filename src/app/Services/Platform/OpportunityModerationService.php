<?php

namespace App\Services\Platform;

use App\Models\Opportunity;
use App\Models\OpportunityModeration;
use App\Models\OpportunityModerationEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class OpportunityModerationService
{
    public function apply(User $actor, Opportunity $opportunity, array $data): void
    {
        abort_unless($actor->hasRole(['super-admin', 'admin']), 403);
        DB::transaction(function () use ($actor, $opportunity, $data) {
            $o = Opportunity::whereKey($opportunity->id)->lockForUpdate()->firstOrFail();
            $state = OpportunityModeration::where('opportunity_id', $o->id)->lockForUpdate()->first();
            $version = $state?->version ?? 0;
            $hidden = $state?->hidden ?? false;
            if ($version !== (int) $data['version']) {
                throw ValidationException::withMessages(['version' => __('admin_opportunities.conflict')]);
            }
            $next = match ($data['action']) {
                'hide' => true,'restore' => false,'note' => $hidden
            };
            if ($data['action'] !== 'note' && $next === $hidden) {
                throw ValidationException::withMessages(['action' => __('admin_opportunities.no_change')]);
            }
            $state ??= new OpportunityModeration(['opportunity_id' => $o->id]);
            $state->fill(['hidden' => $next, 'version' => $version + 1])->save();
            OpportunityModerationEvent::create(['opportunity_id' => $o->id, 'actor_id' => $actor->id, 'action' => $data['action'], 'reason' => $data['reason'], 'was_hidden' => $hidden, 'is_hidden' => $next, 'version' => $version + 1, 'created_at' => now()]);
        }, 3);
    }
}
