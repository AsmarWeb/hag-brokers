<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
        $staff = User::whereHas('roles', function ($query) {
            $query->whereIn('name', [
                'admin', 'super-admin', 'operations', 'sales'
            ]);
        })->orderBy('name')->get(['id', 'name', 'email']);

        $history = DB::table('quotation_history')
            ->where('quotation_id', $quotation->id)
            ->orderByDesc('id')
            ->get();

        $historyUsers = User::whereIn(
            'id',
            $history->pluck('user_id')->filter()->unique()->all()
        )->pluck('name', 'id');

        $quotation->load(['offers.carrier']);

        $carriers = \App\Models\Carrier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.quotations.show', [
            'finalQuotationDocument' => \App\Models\Document::query()
                ->where('documentable_type', 'quotation')
                ->where('documentable_id', $quotation->id)
                ->where('type', 'final_quotation')
                ->first(),
            'carriers' => $carriers,
            'quotation' => $quotation,
            'staff' => $staff,
            'history' => $history,
            'historyUsers' => $historyUsers,
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

        $previousStatus = $quotation->status;

        if ($previousStatus === $data['status']) {
            return back()->with('success', 'Status unchanged.');
        }

        DB::transaction(function () use ($quotation, $data, $request, $previousStatus) {
            $quotation->update(['status' => $data['status']]);

            DB::table('quotation_history')->insert([
                'quotation_id' => $quotation->id,
                'user_id' => $request->user()->id,
                'event' => 'status_changed',
                'old_value' => $previousStatus,
                'new_value' => $data['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

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

        return back()->with(
            'success',
            'Quotation status updated successfully.'
        );
    }

    public function assign(Request $request, Quotation $quotation): RedirectResponse
    {
        $data = $request->validate([
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
        ]);

        $staff = User::query()
            ->whereKey($data['assigned_to'])
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', [
                    'admin', 'super-admin', 'operations', 'sales'
                ]);
            })
            ->first();

        if (! $staff) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'assigned_to' => 'Select an authorized staff member.',
            ]);
        }

        $oldId = $quotation->assigned_to;

        if ((int) $oldId === (int) $staff->id) {
            return back()->with('success', 'Already assigned to this staff member.');
        }

        DB::transaction(function () use ($quotation, $staff, $oldId, $request) {
            $quotation->update(['assigned_to' => $staff->id]);

            DB::table('quotation_history')->insert([
                'quotation_id' => $quotation->id,
                'user_id' => $request->user()->id,
                'event' => 'assigned',
                'old_value' => $oldId === null ? null : (string) $oldId,
                'new_value' => (string) $staff->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        try {
            NotificationService::user(
                user: $staff,
                type: 'quotation.assigned',
                title: 'Quotation assigned to you',
                message: 'You have been assigned quotation ' . $quotation->reference_no,
                model: $quotation,
                data: ['url' => route('admin.quotations.show', $quotation)]
            );
        } catch (\Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'Quotation assigned successfully.');
    }

}
