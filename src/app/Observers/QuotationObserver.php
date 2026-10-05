<?php

namespace App\Observers;

use App\Models\Quotation;
use App\Notifications\AdminPlatformNotification;
use App\Services\NotificationService;

class QuotationObserver
{
    /**
     * Handle the Quotation "created" event.
     */
    public function created(Quotation $quotation): void
    {
        $reference = $quotation->reference_no
            ?: 'QUO-' . $quotation->id;

        /*
         * Notify administrators.
         */
        NotificationService::admins(
            type: 'quotation.created',
            title: 'New Quotation',
            message: sprintf(
                'A new quotation %s has been created.',
                $reference
            ),
            model: $quotation,
            data: [
                'quotation_id' => $quotation->id,
                'reference_no' => $reference,
                'status' => $quotation->status,
                'url' => route(
                    'admin.quotations.show',
                    ['quotation' => $quotation->id]
                ),
            ]
        );
    }

    /**
     * Handle the Quotation "updated" event.
     */
    public function updated(Quotation $quotation): void
    {
        /*
         * Quotation status changed.
         */
        if ($quotation->wasChanged('status')) {

            $reference = $quotation->reference_no
                ?: 'QUO-' . $quotation->id;

            $oldStatus = $quotation->getOriginal('status');
            $newStatus = $quotation->status;

            /*
             * Notify administrators.
             */
            NotificationService::admins(
                type: 'quotation.status_changed',
                title: 'Quotation Status Updated',
                message: sprintf(
                    'Quotation %s status changed to %s.',
                    $reference,
                    $newStatus
                ),
                model: $quotation,
                data: [
                    'quotation_id' => $quotation->id,
                    'reference_no' => $reference,
                    'status' => $newStatus,
                    'old_status' => $oldStatus,
                    'url' => route(
                        'admin.quotations.show',
                        ['quotation' => $quotation->id]
                    ),
                ]
            );

            /*
             * Notify the quotation's customer users.
             */
            $this->notifyCustomerUsers(
                quotation: $quotation,
                reference: $reference,
                oldStatus: $oldStatus,
                newStatus: $newStatus
            );
        }
    }

    /**
     * Notify all users belonging to the quotation customer.
     */
    private function notifyCustomerUsers(
        Quotation $quotation,
        string $reference,
        mixed $oldStatus,
        mixed $newStatus
    ): void {
        /*
         * No customer attached to this quotation.
         */
        if (!$quotation->customer) {
            return;
        }

        /*
         * Notify every user belonging to this customer.
         */
        foreach ($quotation->customer->users as $user) {

            $user->notify(
                new AdminPlatformNotification(
                    type: 'quotation.status_changed',

                    title: 'Quotation Status Updated',

                    message: sprintf(
                        'Your quotation %s status has changed to %s.',
                        $reference,
                        $newStatus
                    ),

                    url: route(
                        'quotations.show',
                        ['quotation' => $quotation->id]
                    ),

                    extra: [
                        'quotation_id' => $quotation->id,
                        'reference_no' => $reference,
                        'old_status' => $oldStatus,
                        'status' => $newStatus,
                    ]
                )
            );
        }
    }
}
