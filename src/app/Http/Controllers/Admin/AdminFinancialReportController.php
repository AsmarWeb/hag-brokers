<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Deal;
use App\Services\Revenue\FinancialReport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class AdminFinancialReportController extends Controller
{
    public function index(Request $request, FinancialReport $report): View
    {
        if (is_string($request->input('currency'))) {
            $request->merge(['currency' => strtoupper(trim($request->input('currency')))]);
        }
        $dateTo = ['nullable', 'date_format:Y-m-d'];
        if ($request->filled('date_from')) $dateTo[] = 'after_or_equal:date_from';
        $filters = $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => $dateTo,
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'currency' => ['nullable', 'string', 'regex:/^[A-Z]{3}$/D'],
            'commission_status' => ['nullable', Rule::in(['pending', 'earned', 'reversed'])],
        ]);
        $query = $report->deals($filters);
        return view('admin.finance.index', [
            'filters' => $filters,
            'summary' => $report->summary($query, $filters),
            'deals' => (clone $query)->with(['customer', 'commissions', 'agreements' => fn ($q) => $q->where('status', 'approved')])
                ->orderByDesc('approved_at')->orderByDesc('id')->paginate(20)->withQueryString(),
            'customers' => Customer::orderBy('company_name')->get(['id', 'company_name']),
            'currencies' => Deal::where('status', 'approved')->whereNotNull('approved_at')
                ->distinct()->orderBy('currency')->pluck('currency'),
        ]);
    }

    public function show(Deal $deal): View
    {
        abort_unless($deal->status === 'approved' && $deal->approved_at !== null, 404);
        $deal->load(['customer', 'quotation', 'opportunity', 'agreements', 'commissions.agreement', 'commissions.invoiceDraft']);
        return view('admin.finance.show', ['deal' => $deal]);
    }
}
