<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';
        $commission = $deal->commissions->first();
        $invoiceDraft = $commission?->invoiceDraft;
        $issuedInvoice = $invoiceDraft?->issuedInvoice;
        $approvedStatusClass = 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300';
        $draftStatusClass = 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-300';
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('admin.deals.index') }}" class="text-sm font-bold text-cyan-700 hover:text-cyan-600 dark:text-cyan-300">← {{ __('deals.back_to_deals') }}</a>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white sm:text-3xl">{{ $deal->reference }}</h1>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $deal->status === 'approved' ? $approvedStatusClass : $draftStatusClass }}">{{ __('deals.statuses.'.$deal->status) }}</span>
                </div>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('deals.deal_number') }} #{{ $deal->id }} · {{ __('deals.created') }} {{ $deal->created_at?->format('d M Y, H:i') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-end dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('deals.deal_value') }}</p>
                <p class="mt-1 text-xl font-black tabular-nums text-slate-900 dark:text-white">{{ $deal->currency }} {{ number_format((float) $deal->deal_amount, 2) }}</p>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">
                <p class="font-bold">{{ __('deals.validation_title') }}</p>
                <ul class="mt-2 list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="grid gap-5 xl:grid-cols-[1.2fr_.8fr]">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ __('deals.parties_and_source') }}</h2>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ __('deals.collection_models.'.$deal->collection_model) }}</span>
                </div>
                <dl class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('deals.customer') }}</dt>
                        <dd class="mt-1 font-bold text-slate-900 dark:text-white">{{ $deal->customer?->company_name ?? __('deals.unknown_customer') }}</dd>
                        @if ($deal->customer?->contact_person)<dd class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $deal->customer->contact_person }}</dd>@endif
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('deals.source') }}</dt>
                        <dd class="mt-1 font-bold text-slate-900 dark:text-white">
                            @if ($deal->quotation)
                                {{ __('deals.quotation') }} · {{ $deal->quotation->reference_no }}
                                <a class="ms-1 text-xs font-bold text-cyan-700 hover:underline dark:text-cyan-300" href="{{ route('admin.quotations.show', $deal->quotation) }}">{{ __('deals.view_source') }}</a>
                            @elseif ($deal->opportunity)
                                {{ __('deals.opportunity') }} · {{ $deal->opportunity->title ?? ('#'.$deal->opportunity_id) }}
                            @else
                                {{ __('deals.manual_source') }}
                            @endif
                        </dd>
                    </div>
                    @if ($deal->quotation)
                        <div>
                            <dt class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('deals.route') }}</dt>
                            <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $deal->quotation->originPort?->name ?? '—' }} <span class="px-1 text-cyan-600">→</span> {{ $deal->quotation->destinationPort?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('deals.cargo') }}</dt>
                            <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $deal->quotation->cargo_type ?? '—' }} <span class="text-sm font-normal text-slate-500">· {{ __('deals.quantity') }}: {{ $deal->quotation->quantity ?? '—' }}</span></dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('deals.approved_carrier') }}</dt>
                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $approvedOffer?->carrier?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('deals.approved_at') }}</dt>
                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $deal->approved_at?->format('d M Y, H:i') ?? '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ __('deals.financial_snapshot') }}</h2>
                <div class="mt-5 space-y-4">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3 dark:border-slate-800">
                        <span class="text-sm text-slate-500 dark:text-slate-400">{{ __('deals.deal_amount') }}</span>
                        <strong class="tabular-nums text-slate-900 dark:text-white">{{ $deal->currency }} {{ number_format((float) $deal->deal_amount, 2) }}</strong>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3 dark:border-slate-800">
                        <span class="text-sm text-slate-500 dark:text-slate-400">{{ __('deals.supplier_cost_internal') }}</span>
                        <strong class="tabular-nums text-slate-900 dark:text-white">{{ $deal->supplier_cost !== null ? $deal->currency.' '.number_format((float) $deal->supplier_cost, 2) : '—' }}</strong>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-slate-500 dark:text-slate-400">{{ __('deals.expected_platform_revenue') }}</span>
                        <strong class="tabular-nums text-cyan-700 dark:text-cyan-300">{{ $deal->currency }} {{ number_format((float) $deal->platform_revenue, 2) }}</strong>
                    </div>
                </div>
                <p class="mt-5 rounded-xl bg-amber-50 px-3 py-2.5 text-xs leading-5 text-amber-800 dark:bg-amber-400/10 dark:text-amber-200">{{ __('deals.financial_snapshot_note') }}</p>
            </section>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ __('deals.agreement_center') }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('deals.agreement_center_help') }}</p>
                </div>
                @if ($canCreateAgreement)
                    <span class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700 dark:bg-amber-400/10 dark:text-amber-300">{{ __('deals.awaiting_financial_terms') }}</span>
                @endif
            </div>

            @if ($deal->agreements->isNotEmpty())
                <div class="mt-5 space-y-4">
                    @foreach ($deal->agreements as $agreement)
                        <article class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-black text-slate-900 dark:text-white">{{ __('deals.agreement_version', ['version' => $agreement->version]) }}</h3>
                                        <span class="rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 ring-inset {{ $agreement->status === 'approved' ? $approvedStatusClass : $draftStatusClass }}">{{ __('deals.statuses.'.$agreement->status) }}</span>
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ __('deals.fee_kinds.'.$agreement->fee_kind) }}</span>
                                    </div>
                                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ __('deals.approved_at') }}: {{ $agreement->approved_at?->format('d M Y, H:i') ?? '—' }}</p>
                                </div>
                                @if ($agreement->status === 'draft' && $canApproveAgreement)
                                    <form method="POST" action="{{ route('admin.deals.agreements.approve', [$deal, $agreement]) }}" onsubmit="return confirm(@js(__('deals.confirm_approve_agreement')))" class="shrink-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-black text-white transition hover:bg-emerald-500">{{ __('deals.approve_agreement') }}</button>
                                    </form>
                                @elseif ($agreement->status === 'draft')
                                    <span class="text-xs font-semibold text-slate-500">{{ __('deals.approval_privileged_only') }}</span>
                                @endif
                            </div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950"><p class="text-[11px] font-bold text-slate-500">{{ __('deals.commission_rate') }}</p><p class="mt-1 font-bold text-slate-900 dark:text-white">{{ $agreement->commission_bps !== null ? number_format($agreement->commission_bps / 100, 2).'%' : '—' }}</p></div>
                                <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950"><p class="text-[11px] font-bold text-slate-500">{{ __('deals.fixed_fee') }}</p><p class="mt-1 font-bold text-slate-900 dark:text-white">{{ $agreement->fixed_fee !== null ? $deal->currency.' '.number_format((float) $agreement->fixed_fee, 2) : '—' }}</p></div>
                                <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950"><p class="text-[11px] font-bold text-slate-500">{{ __('deals.snapshot_integrity') }}</p><p class="mt-1 break-all font-mono text-[11px] text-slate-600 dark:text-slate-300">{{ $agreement->content_sha256 ?: '—' }}</p></div>
                            </div>
                            <div class="mt-4 rounded-lg bg-slate-50 p-3 dark:bg-slate-950">
                                <p class="mb-2 text-[11px] font-bold text-slate-500">{{ __('deals.agreed_terms') }}</p>
                                <p class="whitespace-pre-line text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $agreement->terms }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="mt-5 rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">{{ __('deals.no_agreements') }}</p>
            @endif

            @if ($canCreateAgreement)
                <div class="mt-6 border-t border-slate-200 pt-6 dark:border-slate-800">
                    <h3 class="font-black text-slate-900 dark:text-white">{{ __('deals.create_agreement') }}</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('deals.create_agreement_help') }}</p>
                    <form method="POST" action="{{ route('admin.deals.agreements.store', $deal) }}" class="mt-4 grid gap-4 md:grid-cols-2">
                        @csrf
                        <label class="block"><span class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">{{ __('deals.fee_kind') }}</span>
                            <select name="fee_kind" required class="w-full rounded-xl border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950">
                                <option value="percent" @selected(old('fee_kind', 'percent') === 'percent')>{{ __('deals.fee_kinds.percent') }}</option>
                                <option value="fixed" @selected(old('fee_kind') === 'fixed')>{{ __('deals.fee_kinds.fixed') }}</option>
                                @if ($deal->collection_model === 'full_service' && $deal->supplier_cost !== null)
                                    <option value="spread" @selected(old('fee_kind') === 'spread')>{{ __('deals.fee_kinds.spread') }}</option>
                                @endif
                            </select>
                        </label>
                        <label class="block"><span class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">{{ __('deals.commission_rate_bps') }}</span>
                            <input type="number" name="commission_bps" min="0" max="10000" step="1" value="{{ old('commission_bps', 250) }}" class="w-full rounded-xl border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950">
                            <span class="mt-1 block text-xs text-slate-500">{{ __('deals.bps_help') }}</span>
                        </label>
                        <label class="block"><span class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">{{ __('deals.fixed_fee') }}</span>
                            <input type="number" name="fixed_fee" min="0" step="0.01" value="{{ old('fixed_fee') }}" class="w-full rounded-xl border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950">
                        </label>
                        <label class="block md:col-span-2"><span class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">{{ __('deals.agreed_terms') }}</span>
                            <textarea name="terms" rows="5" required minlength="10" maxlength="20000" class="w-full rounded-xl border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950" placeholder="{{ __('deals.terms_placeholder') }}">{{ old('terms') }}</textarea>
                        </label>
                        <div class="md:col-span-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="max-w-2xl text-xs leading-5 text-slate-500 dark:text-slate-400">{{ __('deals.terms_warning') }}</p>
                            <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-slate-700 dark:bg-cyan-500 dark:text-slate-950 dark:hover:bg-cyan-400">{{ __('deals.save_agreement_draft') }}</button>
                        </div>
                    </form>
                </div>
            @endif
        </section>

        <div class="grid gap-5 xl:grid-cols-2">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ __('deals.commission_snapshot') }}</h2>
                @if ($commission)
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div><p class="text-xs font-bold text-slate-500">{{ __('deals.commission_basis') }}</p><p class="mt-1 font-bold text-slate-900 dark:text-white">{{ $commission->currency }} {{ number_format((float) $commission->basis_amount, 2) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-500">{{ __('deals.commission_amount') }}</p><p class="mt-1 text-xl font-black text-cyan-700 dark:text-cyan-300">{{ $commission->currency }} {{ number_format((float) $commission->amount, 2) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-500">{{ __('deals.commission_status') }}</p><p class="mt-1 font-bold text-slate-900 dark:text-white">{{ __('deals.statuses.'.$commission->status) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-500">{{ __('deals.commission_earned_at') }}</p><p class="mt-1 font-bold text-slate-900 dark:text-white">{{ $commission->earned_at?->format('d M Y, H:i') ?? '—' }}</p></div>
                    </div>
                @else
                    <p class="mt-5 rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">{{ __('deals.no_commission_yet') }}</p>
                @endif
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ __('deals.invoice_linkage') }}</h2>
                @if ($invoiceDraft)
                    <div class="mt-5 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-3"><span class="text-sm text-slate-500 dark:text-slate-400">{{ __('deals.invoice_draft_reference') }}</span><strong class="text-slate-900 dark:text-white">{{ $invoiceDraft->reference }}</strong></div>
                        <div class="flex flex-wrap items-center justify-between gap-3"><span class="text-sm text-slate-500 dark:text-slate-400">{{ __('deals.invoice_status') }}</span><strong class="text-slate-900 dark:text-white">{{ $issuedInvoice ? __('deals.invoice_issued') : __('deals.statuses.'.$invoiceDraft->status) }}</strong></div>
                        @if ($issuedInvoice)
                            <div class="flex flex-wrap items-center justify-between gap-3"><span class="text-sm text-slate-500 dark:text-slate-400">{{ __('deals.issued_invoice_reference') }}</span><strong class="text-slate-900 dark:text-white">{{ $issuedInvoice->reference }}</strong></div>
                        @endif
                        <p class="text-xs leading-5 text-slate-500 dark:text-slate-400">{{ __('deals.invoice_linkage_note') }}</p>
                    </div>
                @else
                    <p class="mt-5 rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">{{ __('deals.no_invoice_yet') }}</p>
                @endif
            </section>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ __('deals.audit_trail') }}</h2>
            <div class="mt-5 space-y-3">
                @forelse ($auditTrail as $event)
                    <article class="flex gap-3 rounded-xl border border-slate-100 p-3 dark:border-slate-800">
                        <span class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700 dark:bg-cyan-400/10 dark:text-cyan-300">◷</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2"><p class="font-bold text-slate-900 dark:text-white">{{ str($event->event)->replace('.', ' · ')->replace('_', ' ')->title() }}</p><time class="text-xs text-slate-500">{{ $event->created_at?->format('d M Y, H:i') }}</time></div>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('deals.actor_id') }}: {{ $event->actor_id ?? '—' }}</p>
                            @if (is_array($event->payload) && count($event->payload))
                                <p class="mt-2 break-words font-mono text-[11px] leading-5 text-slate-500 dark:text-slate-400">{{ json_encode($event->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</p>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">{{ __('deals.no_audit_events') }}</p>
                @endforelse
            </div>
        </section>
    </div>
</x-admin-layout>
