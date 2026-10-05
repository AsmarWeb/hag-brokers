<?php

namespace App\Observers;

use App\Models\Opportunity;
use App\Notifications\AdminPlatformNotification;
use App\Services\NotificationService;

class OpportunityObserver
{
    /**
     * Handle the Opportunity "created" event.
     */
    public function created(Opportunity $opportunity): void
    {
        /*
         * Notify administrators.
         */
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

            /*
             * Notify administrators.
             */
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
                    'old_stage' => $opportunity->getOriginal('stage'),
                    'url' => route(
                        'admin.opportunities.show',
                        ['opportunity' => $opportunity->id]
                    ),
                ]
            );

            /*
             * Notify customer users.
             */
            $this->notifyCustomerUsers(
                opportunity: $opportunity,
                type: 'opportunity.stage_changed',
                title: 'Opportunity Stage Updated',
                message: sprintf(
                    'Your opportunity "%s" stage has been changed to %s.',
                    $opportunity->title,
                    $opportunity->stage
                ),
                data: [
                    'stage' => $opportunity->stage,
                    'old_stage' => $opportunity->getOriginal('stage'),
                ]
            );
        }

        /*
         * Verification status changed.
         */
        if ($opportunity->wasChanged('verification_status')) {

            /*
             * Notify administrators.
             */
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
                    'old_verification_status' =>
                    $opportunity->getOriginal('verification_status'),
                    'url' => route(
                        'admin.opportunities.show',
                        ['opportunity' => $opportunity->id]
                    ),
                ]
            );

            /*
             * Notify customer users.
             */
            $this->notifyCustomerUsers(
                opportunity: $opportunity,
                type: 'opportunity.verification_changed',
                title: 'Opportunity Verification Updated',
                message: sprintf(
                    'Your opportunity "%s" verification status is now %s.',
                    $opportunity->title,
                    $opportunity->verification_status
                ),
                data: [
                    'verification_status' =>
                    $opportunity->verification_status,
                    'old_verification_status' =>
                    $opportunity->getOriginal('verification_status'),
                ]
            );
        }

        /*
         * Marketplace visibility changed.
         */
        if ($opportunity->wasChanged('visibility')) {

            /*
             * Notify administrators.
             */
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
                    'old_visibility' =>
                    $opportunity->getOriginal('visibility'),
                    'url' => route(
                        'admin.opportunities.show',
                        ['opportunity' => $opportunity->id]
                    ),
                ]
            );

            /*
             * Notify customer users.
             */
            $this->notifyCustomerUsers(
                opportunity: $opportunity,
                type: 'opportunity.visibility_changed',
                title: 'Opportunity Visibility Updated',
                message: sprintf(
                    'Your opportunity "%s" visibility has been changed to %s.',
                    $opportunity->title,
                    $opportunity->visibility
                ),
                data: [
                    'visibility' => $opportunity->visibility,
                    'old_visibility' =>
                    $opportunity->getOriginal('visibility'),
                ]
            );
        }
    }

    /**
     * Notify all users belonging to the opportunity customer.
     */
    private function notifyCustomerUsers(
        Opportunity $opportunity,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): void {
        /*
         * No customer attached to this opportunity.
         */
        if (!$opportunity->customer) {
            return;
        }

        /*
         * Notify every user belonging to this customer.
         */
        foreach ($opportunity->customer->users as $user) {

            $user->notify(
                new AdminPlatformNotification(
                    type: $type,
                    title: $title,
                    message: $message,
                    url: route(
                        'opportunities.show',
                        ['opportunity' => $opportunity->id]
                    ),
                    extra: array_merge(
                        [
                            'opportunity_id' => $opportunity->id,
                            'title' => $opportunity->title,
                        ],
                        $data
                    )
                )
            );
        }
    }
}
