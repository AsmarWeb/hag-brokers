<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuotationController extends Controller
{
    private function customerId(Request $request): int
    {
        $customerId = $request->user()?->customer_id;

        abort_if($customerId === null, 403, 'A company account is required.');

        return (int) $customerId;
    }

    public function index(Request $request): View
    {
        $customerId = $this->customerId($request);

        $quotations = Quotation::query()
            ->where('customer_id', $customerId)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('quotations.index', compact('quotations'));
    }

    public function create(Request $request): View
    {
        $this->customerId($request);

        return view('quotations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $customerId = $this->customerId($request);

        $data = $request->validate([
            'origin_port_id' => ['required', 'exists:ports,id'],
            'destination_port_id' => ['required', 'exists:ports,id', 'different:origin_port_id'],
            'cargo_type' => ['required', 'string', 'max:255'],
            'container_type' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'preferred_date' => ['nullable', 'date'],
        ]);

        $data['customer_id'] = $customerId;

        // UUID-based reference: no MAX(id) race; the database UNIQUE constraint
        // remains the final safeguard against any accidental collision.
        $data['reference_no'] = 'QT-' . now()->format('Y') . '-'
            . Str::upper(Str::replace('-', '', (string) Str::uuid()));

        $quotation = Quotation::create($data);

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('success', 'Quotation request submitted successfully.');
    }

    public function show(Request $request, Quotation $quotation): View
    {
        $customerId = $this->customerId($request);

        abort_unless((int) $quotation->customer_id === $customerId, 403);

        return view('quotations.show', compact('quotation'));
    }
}
