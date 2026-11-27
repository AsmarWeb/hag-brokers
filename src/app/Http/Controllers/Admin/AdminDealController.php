<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealAudit;
use App\Models\DealCommission;
use App\Models\Quotation;
use App\Services\Revenue\DealCreationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class AdminDealController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['draft', 'approved'])],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $search = trim((string) ($filters['search'] ?? ''));

        $query = Deal::query()
            ->with([
                'customer:id,company_name,contact_person',
                'quotation:id,reference_no',
                'opportunity:id,title',
                'agreements:id,deal_id,version,status,fee_kind,commission_bps,fixed_fee,approved_at',
                'commissions.invoiceDraft.issuedInvoice',
            ])
            ->when(
                ! empty($filters['status']),
                fn ($builder) => $builder->where('status', $filters['status'])
            )
            ->when($search !== '', function ($builder) use ($search): void {
                $builder->where(function ($nested) use ($search): void {
                    $nested->where('reference', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customer) use ($search): void {
                            $customer->where('company_name', 'like', "%{$search}%")
                                ->orWhere('contact_person', 'like', "%{$search}%");
                        })
                        ->orWhereHas('quotation', function ($quotation) use ($search): void {
                            $quotation->where('reference_no', 'like', "%{$search}%");
                        });
                });
            });

        $deals = $query
            ->latest('created_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Deal::query()->count(),
            'draft' => Deal::query()->where('status', 'draft')->count(),
            'approved' => Deal::query()->where('status', 'approved')->count(),
            'pending_commissions' => DealCommission::query()
                ->where('status', 'pending')
                ->count(),
        ];

        return view('admin.deals.index', [
            'deals' => $deals,
            'stats' => $stats,
            'filters' => [
                'status' => $filters['status'] ?? '',
                'search' => $search,
            ],
        ]);
    }

    public function show(Deal $deal): View
    {
        $deal->load([
            'customer',
            'quotation.customer',
            'quotation.originPort',
            'quotation.destinationPort',
            'quotation.offers.carrier',
            'opportunity',
            'agreements' => fn ($query) => $query->orderByDesc('version'),
            'commissions.invoiceDraft.issuedInvoice',
        ]);

        $approvedOffer = null;

        if ($deal->quotation && $deal->quotation->approved_offer_id) {
            $approvedOffer = $deal->quotation->offers->firstWhere(
                'id',
                $deal->quotation->approved_offer_id
            );
        }

        $auditTrail = DealAudit::query()
            ->where('deal_id', $deal->getKey())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $hasDraftAgreement = $deal->agreements->contains(
            fn ($agreement) => $agreement->status === 'draft'
        );
        $hasApprovedAgreement = $deal->agreements->contains(
            fn ($agreement) => $agreement->status === 'approved'
        );

        return view('admin.deals.show', [
            'deal' => $deal,
            'approvedOffer' => $approvedOffer,
            'auditTrail' => $auditTrail,
            'canCreateAgreement' => $deal->status === 'draft'
                && ! $hasDraftAgreement
                && ! $hasApprovedAgreement,
            'canApproveAgreement' => auth()->user()?->can('approve-deal-agreement') ?? false,
        ]);
    }

    public function storeFromQuotation(
        Request $request,
        Quotation $quotation,
        DealCreationService $service
    ): RedirectResponse {
        $deal = $service->fromApprovedQuotation(
            $quotation,
            (int) $request->user()->getKey()
        );

        return redirect()
            ->route('admin.quotations.show', $quotation)
            ->with(
                'success',
                'Draft deal created successfully: ' . $deal->reference
            );
    }
}
