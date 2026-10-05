<?php

namespace App\Observers;

use App\Models\Shipment;
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
         * Notify administrators only when
         * the shipment status actually changes.
         */
        if ($shipment->wasChanged('status')) {
            $reference = $shipment->shipment_no
                ?: 'SHIP-' . $shipment->id;

            NotificationService::admins(
                type: 'shipment.status_changed',
                title: 'Shipment Status Updated',
                message: sprintf(
                    'Shipment %s status changed to %s.',
                    $reference,
                    $shipment->status
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
    }
}
