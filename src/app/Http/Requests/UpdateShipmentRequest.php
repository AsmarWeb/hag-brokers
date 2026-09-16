<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $shipment = $this->route('shipment');

        $privileged = in_array(
            $this->user()?->role,
            ['admin', 'operations', 'manager'],
            true
        );

        return [
            'customer_id' => [
                Rule::requiredIf($privileged),
                'nullable',
                'integer',
                'exists:customers,id',
            ],

            'shipment_no' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('shipments', 'shipment_no')
                    ->ignore($shipment?->id),
            ],

            'booking_no' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('shipments', 'booking_no')
                    ->ignore($shipment?->id),
            ],

            'origin_port_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:ports,id',
            ],

            'destination_port_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:ports,id',
            ],

            'vessel_id' => [
                'nullable',
                'integer',
                'exists:vessels,id',
            ],

            'cargo_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'container_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'quantity' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:100000',
            ],

            'weight' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'status' => [
                'sometimes',
                'required',
                'string',
                Rule::in([
                    'pending',
                    'booked',
                    'loading',
                    'loaded',
                    'in_transit',
                    'arrived',
                    'customs',
                    'delivered',
                    'cancelled',
                ]),
            ],

            'preferred_date' => [
                'nullable',
                'date',
            ],

            'eta' => [
                'nullable',
                'date',
            ],

            'actual_arrival' => [
                'nullable',
                'date',
            ],
        ];
    }
}
