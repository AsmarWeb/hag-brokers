<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();

        if (
            $user?->customer_id &&
            !in_array($user->role, ['admin', 'operations', 'manager'], true)
        ) {
            $this->merge([
                'customer_id' => $user->customer_id,
            ]);
        }
    }

    public function rules(): array
    {
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
                'required',
                'string',
                'max:100',
                'unique:shipments,shipment_no',
            ],

            'booking_no' => [
                'nullable',
                'string',
                'max:100',
                'unique:shipments,booking_no',
            ],

            'origin_port_id' => [
                'required',
                'integer',
                'exists:ports,id',
                'different:destination_port_id',
            ],

            'destination_port_id' => [
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
