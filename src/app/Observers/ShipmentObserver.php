<?php

namespace App\Observers;

use App\Models\Shipment;
use App\Notifications\AdminPlatformNotification;
use App\Services\NotificationService;

class ShipmentObserver
{
    /**
     * Handle the Shipment "created" event.
     */
    public function created(Shipment $shipment): void
    {
        $reference = $shipment->shipment_no
            ?: 'SHIP-' . $shipment->id;

        /*
         * Notify administrators.
         */
        NotificationService::admins(
            type: 'shipment.created',
            title: 'New Shipment',
            message: sprintf(
                'A new shipment %s has been created.',
                $reference
            ),
            model: $shipment,
            data: [
                'shipment_id' => $shipment->id,
                'shipment_no' => $reference,
                'status' => $shipment->status,
                'url' => route(
                    'admin.shipments.show',
                    ['shipment' => $shipment->id]
                ),
            ]
        );
    }

    /**
     * Handle the Shipment "updated" event.
     */
    public function updated(Shipment $shipment): void
    {
        /*
         * Notify administrators and customer users
         * only when the shipment status actually changes.
         */
        if ($shipment->wasChanged('status')) {

            $reference = $shipment->shipment_no
                ?: 'SHIP-' . $shipment->id;

            $oldStatus = $shipment->getOriginal('status');
            $newStatus = $shipment->status;

            /*
             * =====================================================
             * ADMIN NOTIFICATION
             * =====================================================
             */
            NotificationService::admins(
                type: 'shipment.status_changed',
                title: 'Shipment Status Updated',
                message: sprintf(
                    'Shipment %s status changed to %s.',
                    $reference,
                    $newStatus
                ),
                model: $shipment,
                data: [
                    'shipment_id' => $shipment->id,
                    'shipment_no' => $reference,
                    'status' => $newStatus,
                    'old_status' => $oldStatus,
                    'url' => route(
                        'admin.shipments.show',
                        ['shipment' => $shipment->id]
                    ),
                ]
            );

            /*
             * =====================================================
             * CUSTOMER USER NOTIFICATION
             * =====================================================
             */
            $this->notifyCustomerUsers(
                shipment: $shipment,
                reference: $reference,
                oldStatus: $oldStatus,
                newStatus: $newStatus
            );
        }
    }

    /**
     * Notify all users belonging to the shipment customer.
     */
    private function notifyCustomerUsers(
        Shipment $shipment,
        string $reference,
        mixed $oldStatus,
        mixed $newStatus
    ): void {
        /*
         * No customer attached to this shipment.
         */
        if (!$shipment->customer) {
            return;
        }

        /*
         * Notify every user belonging to this customer.
         */
        foreach ($shipment->customer->users as $user) {

            $user->notify(
                new AdminPlatformNotification(
                    type: 'shipment.status_changed',

                    title: 'Shipment Status Updated',

                    message: sprintf(
                        'Your shipment %s status has changed to %s.',
                        $reference,
                        $newStatus
                    ),

                    url: route(
                        'shipments.show',
                        ['shipment' => $shipment->id]
                    ),

                    extra: [
                        'shipment_id' => $shipment->id,
                        'shipment_no' => $reference,
                        'old_status' => $oldStatus,
                        'status' => $newStatus,
                    ]
                )
            );
        }
    }
}
