<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuotationController extends Controller
{
    public function index(Request $r)
    {
        return response()->json(['success' => true, 'data' => Quotation::where('customer_id', $r->user()->customer_id)->latest()->paginate(20)]);
    }
    public function store(Request $r)
    {
        $d = $r->validate(['origin_port_id' => 'required|exists:ports,id', 'destination_port_id' => 'required|exists:ports,id', 'cargo_type' => 'required|string', 'container_type' => 'required|string', 'quantity' => 'required|integer|min:1', 'weight' => 'nullable|numeric|min:0', 'preferred_date' => 'nullable|date']);
        $d['customer_id'] = $r->user()->customer_id;
        $d['reference_no'] = 'QT-' . now()->format('Y') . '-' . str_pad((string)(Quotation::max('id') + 1), 5, '0', STR_PAD_LEFT);
        $q = Quotation::create($d);
        return response()->json(['success' => true, 'data' => $q], 201);
    }
    public function show(Request $r, Quotation $quotation)
    {
        abort_unless($quotation->customer_id === $r->user()->customer_id, 403);
        return response()->json(['success' => true, 'data' => $quotation]);
    }
}
