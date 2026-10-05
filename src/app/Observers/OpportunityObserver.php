<?php

namespace App\Observers;

use App\Models\Opportunity;
use App\Services\NotificationService;

class OpportunityObserver
{
    /**
     * Handle the Opportunity "created" event.
     */
    public function created(Opportunity $opportunity): void
    {
        NotificationService::admins(
            type: 'opportunity.created',
            title: 'New Opportunity Submitted',
            message: sprintf(
                'A new opportunity "%s" has been submitted.',
                $opportunity->title
            ),
            model: $opportunity,
            data: [
                'opportunity_id' => $opportunity->id,
                'title' => $opportunity->title,
                'stage' => $opportunity->stage,
                'verification_status' => $opportunity->verification_status,
                'visibility' => $opportunity->visibility,
                'url' => route(
                    'admin.opportunities.show',
                    ['opportunity' => $opportunity->id]
                ),
            ]
        );
    }

    /**
     * Handle the Opportunity "updated" event.
     */
    public function updated(Opportunity $opportunity): void
    {
        /*
         * CRM stage changed.
         */
        if ($opportunity->wasChanged('stage')) {
            NotificationService::admins(
                type: 'opportunity.stage_changed',
                title: 'Opportunity Stage Updated',
                message: sprintf(
                    'Opportunity "%s" stage changed to %s.',
                    $opportunity->title,
                    $opportunity->stage
                ),
                model: $opportunity,
                data: [
                    'opportunity_id' => $opportunity->id,
                    'title' => $opportunity->title,
                    'stage' => $opportunity->stage,
                    'url' => route(
                        'admin.opportunities.show',
                        ['opportunity' => $opportunity->id]
                    ),
                ]
            );
        }

        /*
         * Verification status changed.
         */
        if ($opportunity->wasChanged('verification_status')) {
            NotificationService::admins(
                type: 'opportunity.verification_changed',
                title: 'Opportunity Verification Updated',
                message: sprintf(
                    'Opportunity "%s" verification status changed to %s.',
                    $opportunity->title,
                    $opportunity->verification_status
                ),
                model: $opportunity,
                data: [
                    'opportunity_id' => $opportunity->id,
                    'title' => $opportunity->title,
                    'verification_status' => $opportunity->verification_status,
                    'url' => route(
                        'admin.opportunities.show',
                        ['opportunity' => $opportunity->id]
                    ),
                ]
            );
        }

        /*
         * Marketplace visibility changed.
         */
        if ($opportunity->wasChanged('visibility')) {
            NotificationService::admins(
                type: 'opportunity.visibility_changed',
                title: 'Opportunity Visibility Updated',
                message: sprintf(
                    'Opportunity "%s" visibility changed to %s.',
                    $opportunity->title,
                    $opportunity->visibility
                ),
                model: $opportunity,
                data: [
                    'opportunity_id' => $opportunity->id,
                    'title' => $opportunity->title,
                    'visibility' => $opportunity->visibility,
                    'url' => route(
                        'admin.opportunities.show',
                        ['opportunity' => $opportunity->id]
                    ),
                ]
            );
        }
    }
}
