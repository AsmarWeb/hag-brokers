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

    public const FIELDS = ['subcategory', 'product_name', 'specifications', 'quantity', 'unit', 'unit_price', 'price_basis', 'min_order_quantity', 'condition', 'delivery_terms', 'payment_terms', 'lead_time_days', 'target_market', 'certifications'];

    public const SUBCATEGORIES = [
        'marine' => ['yachts', 'boats', 'sailboats', 'commercial_vessels', 'marine_equipment', 'marine_services'],
        'food' => ['fresh_food', 'packaged_food', 'beverages'], 'agriculture' => ['crops', 'agri_equipment'],
        'construction' => ['building_materials', 'construction_services'], 'machinery' => ['heavy_equipment', 'industrial_machines', 'generators'],
        'electronics' => ['computers', 'mobile_devices', 'electronic_components'], 'consumer_goods' => ['furniture', 'household', 'apparel'],
        'automotive' => ['vehicles', 'auto_parts', 'automotive_services'], 'energy' => ['solar', 'energy_equipment'],
        'healthcare' => ['medical_equipment', 'medical_supplies'], 'hospitality' => ['hotel_supplies', 'hospitality_services'],
        'professional_services' => ['consulting', 'business_support'], 'technology' => ['software', 'it_services'],
        'manufacturing' => ['contract_manufacturing', 'raw_materials'], 'import' => ['sourcing', 'import_services'],
        'export' => ['export_goods', 'export_services'], 'distribution' => ['wholesale', 'agency'],
        'services' => ['consulting', 'business_support'], 'real_estate' => ['commercial_property', 'residential_property', 'land'],
        'sea_freight' => ['freight_services', 'marine_equipment'], 'other' => ['general'],
    ];

    public const ATTRIBUTES = [
        'automotive' => ['brand', 'model', 'mileage_km', 'warranty_months'],
        'electronics' => ['brand', 'model', 'warranty_months', 'origin_country'],
        'machinery' => ['brand', 'model', 'origin_country', 'warranty_months'],
        'consumer_goods' => ['brand', 'material', 'origin_country'],
        'construction' => ['material', 'origin_country'],
        'real_estate' => ['area_sqm', 'bedrooms'],
    ];

    public const FEATURES = ['air_conditioning', 'stabilizers', 'flybridge', 'jacuzzi', 'tender', 'crew_cabin', 'navigation_system', 'generator'];

    public const VESSEL_FIELDS = ['listing_type', 'vessel_category', 'vessel_type', 'vessel_name', 'builder', 'year_built', 'length_m', 'beam_m', 'draft_m', 'gross_tonnage', 'deadweight_tonnage', 'imo_number', 'flag_state', 'classification_society', 'cabins', 'guest_capacity', 'crew_capacity', 'engine_make', 'engine_hours', 'condition', 'price_on_request'];

    public static function isMarine(string $type, ?string $category, array $details = []): bool
    {
        return $type === 'vessel_brokerage' || (in_array($type, ['sell', 'buy'], true) && $category === 'marine' && in_array($details['subcategory'] ?? '', ['yachts', 'boats', 'sailboats', 'commercial_vessels'], true));
    }

    public static function vesselRules(bool $required, ?string $subcategory = null): array
    {
        $requiredRule = $required ? 'required' : 'nullable';
        $rules = [
            'vessel' => [$requiredRule, 'array:'.implode(',', self::VESSEL_FIELDS)],
            'vessel.listing_type' => [$requiredRule, 'in:sale,wanted,charter'],
            'vessel.vessel_category' => [$requiredRule, 'in:yacht,boat,commercial_vessel'],
            'vessel.vessel_type' => ['nullable', 'string', 'max:80'],
            'vessel.vessel_name' => ['nullable', 'string', 'max:255'], 'vessel.builder' => ['nullable', 'string', 'max:255'],
            'vessel.year_built' => ['nullable', 'integer', 'between:1800,'.now()->addYears(3)->year],
            'vessel.length_m' => ['nullable', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'vessel.beam_m' => ['nullable', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'vessel.draft_m' => ['nullable', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'vessel.gross_tonnage' => ['nullable', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'vessel.deadweight_tonnage' => ['nullable', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'vessel.imo_number' => ['nullable', 'digits:7'],
            'vessel.flag_state' => ['nullable', 'string', 'max:100'],
            'vessel.classification_society' => ['nullable', 'string', 'max:255'],
            'vessel.cabins' => ['nullable', 'integer', 'between:0,65535'],
            'vessel.guest_capacity' => ['nullable', 'integer', 'between:0,65535'],
            'vessel.crew_capacity' => ['nullable', 'integer', 'between:0,65535'],
            'vessel.engine_make' => ['nullable', 'string', 'max:255'],
            'vessel.engine_hours' => ['nullable', 'integer', 'between:0,4294967295'],
            'vessel.condition' => ['nullable', 'in:new,used,refurbished'],
            'vessel.price_on_request' => ['nullable', 'boolean'],
        ];
        if ($required && $subcategory) {
            $expected = match ($subcategory) {
                'yachts' => 'yacht','boats','sailboats' => 'boat','commercial_vessels' => 'commercial_vessel',default => null
            };
            if ($expected) {
                $rules['vessel.vessel_category'][] = Rule::in([$expected]);
            }
        }

        return $rules;
    }

    public static function display(string $field, mixed $value): string
    {
        if ($field === 'subcategory') {
            return __('marketplace.subcategories.'.$value);
        }
        if (is_array($value)) {
            if ($field === 'features') {
                return implode(', ', array_map(fn ($v) => __('marketplace.features.'.$v), $value));
            }

            return implode(' · ', array_map(fn ($key) => __('marketplace.attributes.'.$key).': '.$value[$key], array_keys($value)));
        }

        return (string) $value;
    }

    public static function detailRules(?string $category = null): array
    {
        return [
            'commercial_details' => ['nullable', 'array:'.implode(',', array_merge(self::FIELDS, ['features', 'attributes']))],
            'commercial_details.subcategory' => ['nullable', Rule::in(self::SUBCATEGORIES[$category] ?? [])],
            'commercial_details.features' => ['nullable', 'array', 'max:8'],
            'commercial_details.features.*' => [Rule::in(self::FEATURES), 'distinct'],
            'commercial_details.attributes' => ['nullable', 'array:'.implode(',', self::ATTRIBUTES[$category] ?? [])],
            'commercial_details.attributes.brand' => ['nullable', 'string', 'max:100'],
            'commercial_details.attributes.model' => ['nullable', 'string', 'max:100'],
            'commercial_details.attributes.material' => ['nullable', 'string', 'max:150'],
            'commercial_details.attributes.origin_country' => ['nullable', 'string', 'max:100'],
            'commercial_details.attributes.warranty_months' => ['nullable', 'integer', 'between:0,120'],
            'commercial_details.attributes.area_sqm' => ['nullable', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'commercial_details.attributes.bedrooms' => ['nullable', 'integer', 'between:0,100'],
            'commercial_details.attributes.mileage_km' => ['nullable', 'integer', 'between:0,9999999'],
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
