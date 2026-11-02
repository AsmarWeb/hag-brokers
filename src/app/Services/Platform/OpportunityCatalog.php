<?php

namespace App\Services\Platform;

use Illuminate\Validation\Rule;

final class OpportunityCatalog
{
    public const TYPES = ['sell', 'buy', 'service_offer', 'service_request', 'partnership', 'distribution', 'procurement', 'trade', 'business', 'investment', 'logistics', 'vessel_brokerage', 'other'];

    public const CATEGORIES = ['food', 'agriculture', 'construction', 'machinery', 'electronics', 'consumer_goods', 'automotive', 'energy', 'healthcare', 'hospitality', 'professional_services', 'technology', 'manufacturing', 'import', 'export', 'distribution', 'services', 'real_estate', 'sea_freight', 'marine', 'other'];

    public const CURRENCIES = ['AED', 'USD', 'EUR', 'GBP', 'SAR', 'QAR', 'KWD', 'OMR', 'BHD', 'INR', 'CNY'];

    public const UNITS = ['piece', 'kg', 'tonne', 'litre', 'metre', 'set', 'service'];

    public const TERMS = ['negotiable', 'exw', 'fca', 'fob', 'cfr', 'cif', 'dap', 'ddp', 'not_applicable'];

    public const FIELDS = ['product_name', 'specifications', 'quantity', 'unit', 'unit_price', 'price_basis', 'min_order_quantity', 'condition', 'delivery_terms', 'payment_terms', 'lead_time_days', 'target_market', 'certifications'];

    public static function detailRules(): array
    {
        return [
            'commercial_details' => ['nullable', 'array:'.implode(',', self::FIELDS)],
            'commercial_details.product_name' => ['nullable', 'string', 'max:180'],
            'commercial_details.specifications' => ['nullable', 'string', 'max:5000'],
            'commercial_details.quantity' => ['required_with:commercial_details.min_order_quantity', 'nullable', 'decimal:0,3', 'gt:0', 'max:999999999.999'],
            'commercial_details.unit' => ['required_with:commercial_details.quantity,commercial_details.min_order_quantity,commercial_details.unit_price', 'nullable', Rule::in(self::UNITS)],
            'commercial_details.min_order_quantity' => ['nullable', 'decimal:0,3', 'gt:0', 'max:999999999.999'],
            'commercial_details.unit_price' => ['required_if:commercial_details.price_basis,fixed', 'nullable', 'decimal:0,2', 'min:0', 'max:999999999.99'],
            'commercial_details.price_basis' => ['nullable', 'in:negotiable,fixed,quotation'],
            'commercial_details.condition' => ['nullable', 'in:new,used,refurbished,not_applicable'],
            'commercial_details.delivery_terms' => ['nullable', Rule::in(self::TERMS)],
            'commercial_details.payment_terms' => ['nullable', 'string', 'max:500'],
            'commercial_details.lead_time_days' => ['nullable', 'integer', 'between:1,3650'],
            'commercial_details.target_market' => ['nullable', 'string', 'max:150'],
            'commercial_details.certifications' => ['nullable', 'string', 'max:500'],
        ];
    }
}
