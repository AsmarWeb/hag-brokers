<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminQuotationController extends Controller
{
    /**
     * Display all quotations.
     */
    public function index(Request $request): View
    {
        $query = Quotation::query();

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')->toString()
            );
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where(
                    'reference_no',
                    'like',
                    "%{$search}%"
                );
            });
        }

        $quotations = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.quotations.index', [
            'quotations' => $quotations,
        ]);
    }

    /**
     * Display a quotation.
     */
    public function show(Quotation $quotation): View
    {
        return view('admin.quotations.show', [
            'quotation' => $quotation,
        ]);
    }

    /**
     * Update quotation status.
     */
    public function updateStatus(
        Request $request,
        Quotation $quotation
    ): RedirectResponse {
        $data = $request->validate([
            'status' => [
                'required',
                'string',
                'max:50',
            ],
        ]);

        $quotation->update([
            'status' => $data['status'],
        ]);

        return back()->with(
            'success',
            'Quotation status updated successfully.'
        );
    }
}
