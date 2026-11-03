<?php

namespace App\Services\Platform;

use App\Models\CompanyMember;
use App\Models\CompanyWorkspace;
use App\Models\Opportunity;
use App\Models\OpportunityModeration;
use App\Models\OpportunityModerationEvent;
use App\Models\User;
use App\Notifications\AdminPlatformNotification;
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
            $event = OpportunityModerationEvent::create(['opportunity_id' => $o->id, 'actor_id' => $actor->id, 'action' => $data['action'], 'reason' => $data['reason'], 'was_hidden' => $hidden, 'is_hidden' => $next, 'version' => $version + 1, 'created_at' => now()]);
            if ($data['action'] !== 'note' && $o->company_workspace_id) {
                $workspace = CompanyWorkspace::find($o->company_workspace_id);
                if ($workspace) {
                    $leaders = CompanyMember::where('company_workspace_id', $workspace->id)->whereIn('role', ['owner', 'manager'])->select('user_id');
                    foreach (User::where('customer_id', $workspace->customer_id)->whereIn('id', $leaders)->get() as $recipient) {
                        if ($recipient->hasRole(['admin', 'super-admin'])) {
                            continue;
                        }
                        $recipient->notify(new AdminPlatformNotification(
                            type: 'marketplace.moderation.'.$data['action'],
                            title: $next ? 'تحديث حالة الفرصة / Listing visibility update' : 'رفع الإخفاء الإداري / Administrative hold removed',
                            message: $o->title,
                            url: '/company/marketplace',
                            extra: ['opportunity_id' => $o->id, 'moderation_event_id' => $event->id, 'hidden' => $next]
                        ));
                    }
                }
            }

        }, 3);
    }
}
