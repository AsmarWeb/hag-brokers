<?php

namespace App\Services\Platform;

use App\Models\Opportunity;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class OpportunityInput
{
    public function publicationData(Request $request): array
    {
        $marine = OpportunityCatalog::isMarine((string) $request->input('opportunity_type'), $request->input('category'), (array) $request->input('commercial_details', []));
        $data = $request->validate([
            'request_key' => ['required', 'uuid'], 'title' => ['required', 'string', 'max:255'],
            'opportunity_type' => ['required', Rule::in(OpportunityCatalog::TYPES)],
            'description' => ['required', 'string', 'min:20', 'max:20000'], 'category' => ['nullable', Rule::in(OpportunityCatalog::CATEGORIES)],
            'cargo_type' => ['nullable', 'string', 'max:255'], 'container_type' => ['nullable', 'string', 'max:255'],
            'origin_port_id' => ['nullable', 'integer', 'exists:ports,id'], 'destination_port_id' => ['nullable', 'integer', 'exists:ports,id'],
            'country' => ['nullable', 'string', 'max:100'], 'city' => ['nullable', 'string', 'max:100'],
            'estimated_value' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'required_capital' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'min_investment' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'currency' => ['required', 'regex:/^[A-Za-z]{3}$/D'], 'commission_type' => ['nullable', 'in:percentage,fixed'],
            'commission_rate' => ['nullable', 'decimal:0,2', 'min:0', (($request->input('commission_type') ?? 'percentage') === 'percentage' ? 'max:100' : 'max:999999.99')],
            'expected_close_date' => ['nullable', 'date_format:Y-m-d'],
            'expires_at' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.now('Asia/Dubai')->toDateString()],
            'notes' => ['nullable', 'string', 'max:5000'], 'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['file', 'mimes:jpg,jpeg,png,webp,mp4,mov,webm', 'max:20480'],
        ] + OpportunityCatalog::detailRules($request->input('category')) + OpportunityCatalog::vesselRules($marine, $request->input('commercial_details.subcategory')));
        if (collect($request->file('media', []))->sum(fn ($file) => $file->getSize()) > 20 * 1024 * 1024) {
            throw ValidationException::withMessages(['media' => __('trade.photo_hint')]);
        }
        $data['commission_type'] = $data['commission_type'] ?? 'percentage';
        unset($data['request_key'],$data['media']);
        if (! $marine) {
            unset($data['vessel']);
        } elseif (isset($data['vessel'])) {
            $data['vessel'] = array_intersect_key($data['vessel'], array_flip(OpportunityCatalog::VESSEL_FIELDS));
        }
        $data['currency'] = strtoupper($data['currency']);
        // The submitted calendar date means the whole day in Dubai.
        $data['expires_at'] = CarbonImmutable::parse($data['expires_at'] ?? now('Asia/Dubai')->addDays(30)->toDateString(), 'Asia/Dubai')->endOfDay()->utc()->format('Y-m-d H:i:s');

        return $data;
    }

    public function updateData(Request $request, Opportunity $opportunity): array
    {
        $marine = OpportunityCatalog::isMarine($opportunity->opportunity_type, $request->input('category'), (array) $request->input('commercial_details', []));
        $data = $request->validate(['title' => ['required', 'string', 'max:255'], 'description' => ['required', 'string', 'min:20', 'max:20000'],
            'country' => ['nullable', 'string', 'max:100'], 'city' => ['nullable', 'string', 'max:100'], 'notes' => ['nullable', 'string', 'max:5000'],
            'category' => ['nullable', Rule::in(array_merge(OpportunityCatalog::CATEGORIES, [$opportunity->category]))]] + OpportunityCatalog::detailRules($request->input('category')) + OpportunityCatalog::vesselRules($marine, $request->input('commercial_details.subcategory')));
        if (! $marine) {
            unset($data['vessel']);
        }

        return $data;
    }
}
