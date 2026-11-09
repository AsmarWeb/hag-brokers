<?php

namespace App\Http\Controllers;

use App\Models\FreightSailing;
use App\Services\Freight\SailingCatalog;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class FreightSailingController extends Controller
{
    public function index(Request $request, SailingCatalog $catalog)
    {
        $ports = $catalog->ports()->orderBy('name')->get(['id', 'name', 'unlocode']);
        $searching = $request->hasAny(['origin_port_id', 'destination_port_id']);
        $filters = $request->validate([
            'origin_port_id' => [Rule::requiredIf($searching), 'integer', Rule::in($ports->modelKeys())],
            'destination_port_id' => [Rule::requiredIf($searching), 'integer', 'different:origin_port_id', Rule::in($ports->modelKeys())],
            'ready_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.now('Asia/Dubai')->toDateString()],
            'container_type' => ['nullable', Rule::in(FreightSailing::CONTAINER_TYPES)],
            'quantity' => ['nullable', 'integer', 'between:1,10000'],
        ]);

        $query = $catalog->available();
        if ($searching) {
            $query->where('origin_port_id', $filters['origin_port_id'])
                ->where('destination_port_id', $filters['destination_port_id']);
        }
        if (isset($filters['ready_on'])) {
            $query->where('cargo_cutoff_at', '>=', CarbonImmutable::parse($filters['ready_on'], 'Asia/Dubai')->utc());
        }
        if (isset($filters['container_type'])) {
            $query->whereJsonContains('container_types', $filters['container_type']);
        }

        $sailings = $query->orderBy('departure_at')->orderBy('id')->paginate(20)->withQueryString();

        if ($request->routeIs('api.freight.sailings')) {
            return response()->json([
                'data' => $sailings->getCollection()->map(fn (FreightSailing $sailing) => ['id' => $sailing->id, 'revision' => $catalog->revision($sailing)] + $catalog->snapshot($sailing)),
                'meta' => ['current_page' => $sailings->currentPage(), 'last_page' => $sailings->lastPage()],
            ])->header('Cache-Control', 'no-store');
        }

        $view = $request->routeIs('dashboard.freight.sailings') ? 'freight.dashboard-sailings' : 'freight.sailings';

        return response()->view($view, compact('ports', 'sailings', 'filters'))
            ->header('Cache-Control', 'no-store');
    }

    public function select(Request $request, int $sailing, SailingCatalog $catalog)
    {
        $voyage = $catalog->available()->findOrFail($sailing);
        $data = $request->validate([
            'container_type' => ['required', Rule::in($voyage->container_types)],
            'quantity' => ['required', 'integer', 'between:1,10000'],
            'ready_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.now('Asia/Dubai')->toDateString()],
        ]);
        if (isset($data['ready_on']) && $data['ready_on'] > $voyage->cargo_cutoff_at->setTimezone('Asia/Dubai')->toDateString()) {
            throw ValidationException::withMessages(['ready_on' => __('sailings.invalid_selection')]);
        }

        return redirect()->route('quotations.create', [
            'sailing' => $voyage->id,
            'container_type' => $data['container_type'],
            'quantity' => $data['quantity'],
            'ready_on' => $data['ready_on'] ?? null,
        ]);
    }
}
