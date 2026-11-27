<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealDocument;
use App\Models\DealDocumentParty;
use App\Services\Revenue\DealDocumentWorkflowService;
use App\Services\Revenue\DealDocumentIntegrityVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AdminDealDocumentController extends Controller
{
    private const TYPES = ['ncnda', 'fixture_recap', 'commission_addendum'];

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $type = $request->string('type')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $documents = DealDocument::query()
            ->with(['deal.customer', 'parties'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('reference', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhereHas('deal', function ($deal) use ($search): void {
                            $deal->where('reference', 'like', "%{$search}%")
                                ->orWhereHas('customer', function ($customer) use ($search): void {
                                    $customer->where('company_name', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->when(in_array($type, self::TYPES, true), fn ($query) => $query->where('type', $type))
            ->when(in_array($status, ['draft', 'ready_for_signature', 'partially_signed', 'signed'], true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.deal-documents.index', [
            'documents' => $documents,
            'filters' => compact('search', 'type', 'status'),
            'stats' => [
                'total' => DealDocument::query()->count(),
                'draft' => DealDocument::query()->where('status', 'draft')->count(),
                'awaiting' => DealDocument::query()->whereIn('status', ['ready_for_signature', 'partially_signed'])->count(),
                'signed' => DealDocument::query()->where('status', 'signed')->count(),
            ],
        ]);
    }

    public function chooseDeal(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        return view('admin.deal-documents.choose', [
            'deals' => Deal::query()
                ->with('customer')
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($nested) use ($search): void {
                        $nested->where('reference', 'like', '%' . $search . '%')
                            ->orWhereHas('customer', function ($customer) use ($search): void {
                                $customer->where('company_name', 'like', '%' . $search . '%');
                            });
                    });
                })
                ->latest()
                ->paginate(25)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function start(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'deal_id' => ['required', 'integer', 'exists:deals,id'],
            'type' => ['required', Rule::in(self::TYPES)],
        ]);

        $deal = Deal::query()->findOrFail($validated['deal_id']);
        if ($validated['type'] === 'commission_addendum' && $deal->status !== 'approved') {
            throw ValidationException::withMessages([
                'type' => __('deal_documents.no_commission_source'),
            ]);
        }

        return redirect()->route('admin.deals.documents.create', [
            'deal' => $deal,
            'type' => $validated['type'],
        ]);
    }

    public function create(Request $request, Deal $deal): View
    {
        $type = $request->query('type', 'ncnda');
        abort_unless(in_array($type, self::TYPES, true), 404);
        if ($type === 'commission_addendum' && $deal->status !== 'approved') {
            throw ValidationException::withMessages([
                'type' => __('deal_documents.no_commission_source'),
            ]);
        }

        $deal->load('customer');

        return view('admin.deal-documents.create', [
            'deal' => $deal,
            'type' => $type,
            'document' => null,
            'parties' => [
                ['role_key' => 'party_a', 'company_name' => $deal->customer?->company_name ?? '', 'contact_name' => '', 'email' => ''],
                ['role_key' => 'party_b', 'company_name' => '', 'contact_name' => '', 'email' => ''],
            ],
        ]);
    }

    public function store(
        Request $request,
        Deal $deal,
        DealDocumentWorkflowService $service
    ): RedirectResponse {
        $validated = $this->validatedDraft($request);
        $document = $service->createDraft(
            $deal,
            $validated,
            (int) $request->user()->getAuthIdentifier()
        );

        return redirect()
            ->route('admin.deal-documents.show', $document)
            ->with('success', __('deal_documents.created'));
    }

    public function edit(DealDocument $dealDocument): View
    {
        abort_unless($dealDocument->status === 'draft' && $dealDocument->locked_at === null, 403);

        $dealDocument->load(['deal.customer', 'parties']);

        return view('admin.deal-documents.edit', [
            'deal' => $dealDocument->deal,
            'document' => $dealDocument,
            'type' => $dealDocument->type,
            'parties' => $dealDocument->parties->values()->all(),
        ]);
    }

    public function update(
        Request $request,
        DealDocument $dealDocument,
        DealDocumentWorkflowService $service
    ): RedirectResponse {
        abort_unless($dealDocument->status === 'draft' && $dealDocument->locked_at === null, 403);

        $validated = $this->validatedDraft($request, $dealDocument->type);
        $document = $service->updateDraft(
            $dealDocument,
            $validated,
            (int) $request->user()->getAuthIdentifier()
        );

        return redirect()
            ->route('admin.deal-documents.show', $document)
            ->with('success', __('deal_documents.updated'));
    }

    public function show(DealDocument $dealDocument, DealDocumentIntegrityVerifier $verifier): View
    {
        $dealDocument->load([
            'deal.customer',
            'parties.signatureReceivedBy',
            'versions.actor',
            'events.actor',
        ]);

        return view('admin.deal-documents.show', [
            'document' => $dealDocument,
            'integrity' => $verifier->verify($dealDocument),
        ]);
    }

    public function prepare(
        Request $request,
        DealDocument $dealDocument,
        DealDocumentWorkflowService $service
    ): RedirectResponse {
        $document = $service->prepareForSignature(
            $dealDocument,
            (int) $request->user()->getAuthIdentifier()
        );

        return redirect()
            ->route('admin.deal-documents.show', $document)
            ->with('success', __('deal_documents.prepared'));
    }

    public function recordSignature(
        Request $request,
        DealDocument $dealDocument,
        DealDocumentParty $party,
        DealDocumentWorkflowService $service
    ): RedirectResponse {
        abort_unless((int) $party->deal_document_id === (int) $dealDocument->getKey(), 404);

        $signedAtRules = ['required', 'date', 'before_or_equal:today'];
        if ($dealDocument->prepared_at !== null) {
            $signedAtRules[] = 'after_or_equal:' . $dealDocument->prepared_at->toDateString();
        }

        $validated = $request->validate([
            'signed_by_name' => ['required', 'string', 'max:255'],
            'signed_by_title' => ['required', 'string', 'max:160'],
            'signed_at' => $signedAtRules,
            'evidence' => ['required', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $service->recordSignature(
            $dealDocument,
            $party,
            $validated['evidence'],
            $validated,
            (int) $request->user()->getAuthIdentifier()
        );

        return redirect()
            ->route('admin.deal-documents.show', $dealDocument)
            ->with('success', __('deal_documents.signature_recorded'));
    }

    public function downloadEvidence(
        DealDocument $dealDocument,
        DealDocumentParty $party
    ): StreamedResponse {
        abort_unless((int) $party->deal_document_id === (int) $dealDocument->getKey(), 404);

        $path = (string) $party->signature_evidence_path;
        abort_unless(
            $party->status === 'signed'
                && preg_match('~\Adeal-documents/' . preg_quote((string) $dealDocument->getKey(), '~') . '/signatures/[A-Za-z0-9._-]+\.(pdf|jpg|jpeg|png)\z~iD', $path),
            404
        );

        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404);

        $actualHash = hash_file('sha256', $disk->path($path));
        abort_unless(
            is_string($actualHash)
                && hash_equals((string) $party->signature_evidence_sha256, $actualHash),
            404
        );

        return $disk->download(
            $path,
            $dealDocument->reference . '-' . $party->role_key . '-signed-copy.' . pathinfo($path, PATHINFO_EXTENSION),
            [
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    private function validatedDraft(Request $request, ?string $fixedType = null): array
    {
        $type = $fixedType ?? (string) $request->input('type');
        abort_unless(in_array($type, self::TYPES, true), 422);

        $rules = [
            'type' => [$fixedType === null ? 'required' : 'sometimes', Rule::in([$type])],
            'title' => ['required', 'string', 'max:180'],
            'language' => ['required', Rule::in(['en', 'ar'])],
            'parties' => ['required', 'array', 'size:2'],
            'parties.0.role_key' => ['required', Rule::in(['party_a'])],
            'parties.1.role_key' => ['required', Rule::in(['party_b'])],
            'parties.*.company_name' => ['required', 'string', 'max:255'],
            'parties.*.contact_name' => ['required', 'string', 'max:255'],
            'parties.*.email' => ['required', 'email', 'max:255'],
            'content' => ['nullable', 'array'],
        ];

        if ($type === 'ncnda') {
            $rules += [
                'content.protected_contacts' => ['required', 'string', 'max:5000'],
                'content.term_months' => ['required', 'integer', 'min:1', 'max:120'],
                'content.territory' => ['nullable', 'string', 'max:255'],
                'content.effective_date' => ['nullable', 'date'],
                'content.legal_reviewed_by' => ['required', 'string', 'max:255'],
                'content.legal_reviewed_at' => ['required', 'date', 'before_or_equal:today'],
                'content.agreement_text' => ['required', 'string', 'min:20', 'max:15000'],
            ];
        } elseif ($type === 'fixture_recap') {
            $rules += [
                'content.commodity' => ['required', 'string', 'max:180'],
                'content.quantity' => ['required', 'numeric', 'gt:0'],
                'content.quantity_unit' => ['required', 'string', 'max:32'],
                'content.loading_port' => ['required', 'string', 'max:180'],
                'content.discharge_port' => ['required', 'string', 'max:180'],
                'content.laycan_from' => ['required', 'date'],
                'content.laycan_to' => ['required', 'date', 'after_or_equal:content.laycan_from'],
                'content.vessel_name' => ['nullable', 'string', 'max:180'],
                'content.freight_rate' => ['nullable', 'numeric', 'min:0'],
                'content.freight_currency' => ['required', 'string', 'regex:/^[A-Z]{3}$/'],
                'content.freight_rate_basis' => ['nullable', 'string', 'max:80'],
                'content.payment_terms' => ['required', 'string', 'max:3000'],
                'content.demurrage_rate' => ['nullable', 'numeric', 'min:0'],
                'content.despatch_rate' => ['nullable', 'numeric', 'min:0'],
                'content.additional_terms' => ['nullable', 'string', 'max:6000'],
            ];
        }

        $validated = $request->validate($rules);
        $validated['type'] = $type;

        return $validated;
    }
}
