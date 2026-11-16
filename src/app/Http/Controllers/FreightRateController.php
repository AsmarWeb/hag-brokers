<?php

namespace App\Http\Controllers;

use App\Models\FreightRate;
use App\Services\Freight\FreightRateSearch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class FreightRateController extends Controller
{
    public function page(Request $request, FreightRateSearch $search)
    {
        $filters = [];
        $results = collect();
        $searched = $request->hasAny(['origin_port_id', 'destination_port_id', 'container_type', 'ready_on', 'quantity', 'currency', 'carrier_id', 'sort']);

        if ($searched) {
            $filters = $request->validate([
                'origin_port_id' => ['required', 'integer', 'different:destination_port_id', 'exists:ports,id'],
                'destination_port_id' => ['required', 'integer', 'exists:ports,id'],
                'container_type' => ['required', Rule::in(FreightRate::CONTAINER_TYPES)],
                'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
                'quantity' => ['nullable', 'integer', 'between:1,10000'],
                'ready_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.now('Asia/Dubai')->toDateString()],
                'carrier_id' => ['nullable', 'integer', 'exists:carriers,id'],
                'sort' => ['nullable', Rule::in(['lowest_price', 'fastest', 'best_value'])],
            ]);
            $filters['currency'] = strtoupper($filters['currency']);
            $filters['quantity'] = (int) ($filters['quantity'] ?? 1);
            $filters['sort'] = $filters['sort'] ?? 'best_value';
            $results = $search->results($filters);
        }

        return view('freight.rates', [
            'ports' => \App\Models\Port::orderBy('name')->get(),
            'carriers' => \App\Models\Carrier::where('is_active', true)->orderBy('name')->get(),
            'filters' => $filters,
            'results' => $results,
            'searched' => $searched,
        ]);
    }

    public function index(Request $request, FreightRateSearch $search)
    {
        $data = $request->validate([
            'origin_port_id' => ['required', 'integer', 'different:destination_port_id', 'exists:ports,id'],
            'destination_port_id' => ['required', 'integer', 'exists:ports,id'],
            'container_type' => ['required', Rule::in(FreightRate::CONTAINER_TYPES)],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'quantity' => ['nullable', 'integer', 'between:1,10000'],
            'ready_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.now('Asia/Dubai')->toDateString()],
            'carrier_id' => ['nullable', 'integer', 'exists:carriers,id'],
            'sort' => ['nullable', Rule::in(['lowest_price', 'fastest', 'best_value'])],
            'per_page' => ['nullable', 'integer', 'between:1,50'],
        ]);

        $data['currency'] = strtoupper($data['currency']);
        $data['quantity'] = (int) ($data['quantity'] ?? 1);
        $items = $search->results($data);
        $page = max(1, (int) $request->query('page', 1));
        $perPage = (int) ($data['per_page'] ?? 20);
        $slice = $items->forPage($page, $perPage)->values();

        return response()->json([
            'data' => $slice,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $items->count(),
                'last_page' => max(1, (int) ceil($items->count() / $perPage)),
                'sort' => $data['sort'] ?? 'best_value',
                'currency_note' => 'Only offers in the same currency should be compared directly.',
            ],
        ])->header('Cache-Control', 'no-store');
    }
}
