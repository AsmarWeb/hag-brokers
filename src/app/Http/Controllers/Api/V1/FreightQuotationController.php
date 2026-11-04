<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Port;
use App\Models\Quotation;
use App\Services\FreightQuotationService;
use Illuminate\Http\Request;

final class FreightQuotationController extends Controller
{
    public function ports(Request $r, FreightQuotationService $s)
    {
        $s->customer($r->user());
        $d = $r->validate(['search' => ['nullable', 'string', 'max:100'], 'page' => ['nullable', 'integer', 'min:1']]);
        $ports = Port::where('is_active', true)->when($d['search'] ?? null, function ($q, $term) {
            // Literal contains search; SQL wildcards supplied by a client are escaped.
            $term = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
            $q->whereRaw("name LIKE ? ESCAPE '!'", ['%'.$term.'%']);
        })->orderBy('name')->orderBy('id')->paginate(30, ['id', 'name', 'country_code', 'port_code']);

        return response()->json(['data' => $ports->items(), 'meta' => ['current_page' => $ports->currentPage(), 'last_page' => $ports->lastPage()]]);
    }

    private function item(Quotation $q): array
    {
        $q->loadMissing(['originPort', 'destinationPort']);

        return ['id' => $q->id, 'reference' => $q->reference_no, 'status' => $q->status,
            'origin' => ['id' => $q->origin_port_id, 'name' => $q->originPort?->name],
            'destination' => ['id' => $q->destination_port_id, 'name' => $q->destinationPort?->name],
            'cargo_type' => $q->cargo_type, 'container_type' => $q->container_type, 'quantity' => (int) $q->quantity,
            'weight' => $q->weight, 'preferred_date' => $q->preferred_date, 'created_at' => $q->created_at?->toIso8601String()];
    }

    public function index(Request $r, FreightQuotationService $s)
    {
        $customer = $s->customer($r->user());
        $d = $r->validate(['per_page' => ['nullable', 'integer', 'min:1', 'max:50'], 'page' => ['nullable', 'integer', 'min:1']]);
        $page = Quotation::where('customer_id', $customer->id)->with(['originPort', 'destinationPort'])->latest('id')->paginate($d['per_page'] ?? 20);

        return response()->json(['data' => array_map(fn ($q) => $this->item($q), $page->items()),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total(), 'per_page' => $page->perPage()]]);
    }

    public function show(Request $r, int $quotationId, FreightQuotationService $s)
    {
        $customer = $s->customer($r->user());

        return response()->json(['data' => $this->item(Quotation::where('customer_id', $customer->id)->findOrFail($quotationId))]);
    }

    public function store(Request $r, FreightQuotationService $s)
    {
        $s->customer($r->user());
        $rules = FreightQuotationService::rules() + ['request_key' => ['required', 'uuid']];
        foreach (['customer_id', 'status', 'total_charge', 'customer_total', 'broker_margin', 'reference_no', 'assigned_to', 'currency'] as $field) {
            $rules[$field] = ['prohibited'];
        }
        $d = $r->validate($rules);
        [$q, $created] = $s->submit($r->user(), $d, $d['request_key']);

        return response()->json(['data' => $this->item($q->refresh()), 'meta' => ['created' => $created]], $created ? 201 : 200);
    }
}
