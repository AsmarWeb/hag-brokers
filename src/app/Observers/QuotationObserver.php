<?php

namespace App\Observers;

use App\Models\Quotation;
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

            NotificationService::admins(
                type: 'quotation.status_changed',
                title: 'Quotation Status Updated',
                message: sprintf(
                    'Quotation %s status changed to %s.',
                    $reference,
                    $quotation->status
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
    }
}
