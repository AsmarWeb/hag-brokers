<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\User;
use App\Services\NotificationService;
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

        $quotationStats = [
            'total' => Quotation::count(),
            'pending' => Quotation::where('status', 'pending')->count(),
            'processing' => Quotation::where('status', 'processing')->count(),
            'approved' => Quotation::where('status', 'approved')->count(),
            'completed' => Quotation::where('status', 'completed')->count(),
        ];

        return view('admin.quotations.index', [
            'quotations' => $quotations,
            'quotationStats' => $quotationStats,
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
                'in:pending,processing,approved,rejected,completed',
            ],
        ]);

        $previousStatus = $quotation->status;

        $quotation->update([
            'status' => $data['status'],
        ]);

        if ($previousStatus !== $data['status']) {
            $customerUsers = User::query()
                ->where('customer_id', $quotation->customer_id)
                ->get();

            foreach ($customerUsers as $customerUser) {
                try {
                    NotificationService::user(
                        user: $customerUser,
                        type: 'quotation.status_changed',
                        title: 'Quotation status updated',
                        message: 'Quotation ' . $quotation->reference_no
                            . ' is now ' . $data['status'] . '.',
                        model: $quotation,
                        data: [
                            'url' => route('quotations.show', $quotation),
                            'previous_status' => $previousStatus,
                            'new_status' => $data['status'],
                        ]
                    );
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }
        }

        return back()->with(
            'success',
            'Quotation status updated successfully.'
        );
    }
}
