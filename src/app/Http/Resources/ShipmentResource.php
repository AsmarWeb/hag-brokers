<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'shipment_no' => $this->shipment_no,
            'booking_no' => $this->booking_no,

            'status' => $this->status,

            'cargo' => [
                'type' => $this->cargo_type,
                'container_type' => $this->container_type,
                'quantity' => $this->quantity,
                'weight' => $this->weight,
            ],

            'origin' => $this->whenLoaded('originPort', function () {
                return [
                    'id' => $this->originPort->id,
                    'name' => $this->originPort->name,
                    'country' => $this->originPort->country,
                ];
            }),

            'destination' => $this->whenLoaded('destinationPort', function () {
                return [
                    'id' => $this->destinationPort->id,
                    'name' => $this->destinationPort->name,
                    'country' => $this->destinationPort->country,
                ];
            }),

            'vessel' => $this->whenLoaded('vessel', function () {
                return [
                    'id' => $this->vessel->id,
                    'name' => $this->vessel->name,
                    'imo' => $this->vessel->imo,
                    'flag' => $this->vessel->flag,
                ];
            }),

            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'company_name' => $this->customer->company_name,
                ];
            }),

            'preferred_date' => $this->preferred_date?->toDateString(),
            'eta' => $this->eta?->toDateString(),
            'actual_arrival' => $this->actual_arrival?->toISOString(),

            'containers_count' => $this->when(
                isset($this->containers_count),
                $this->containers_count
            ),

            'events_count' => $this->when(
                isset($this->events_count),
                $this->events_count
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
