<x-admin-layout>
    @php($isAr = app()->getLocale() === 'ar')
    <div class="min-h-screen bg-slate-950 text-white" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <header class="border-b border-white/10 bg-gradient-to-br from-slate-900 via-slate-950 to-cyan-950/30 px-4 py-7 sm:px-6 lg:px-8">
            <div class="mx-auto flex max-w-7xl flex-col justify-between gap-4 md:flex-row md:items-end">
                <div>
                    <a href="{{ route('admin.deal-execution.index') }}" class="text-sm font-bold text-cyan-300">← {{ __('deal_execution.back_to_execution') }}</a>
                    <p class="mt-4 font-mono text-xs text-cyan-300" dir="ltr">{{ $shipment->shipment_no }}</p>
                    <h1 class="mt-1 text-2xl font-black sm:text-3xl">{{ __('deal_execution.execution_title') }}</h1>
                    <p class="mt-2 text-sm text-slate-400">{{ $deal->reference }} · {{ $shipment->customer?->company_name }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-black">{{ __('deal_execution.shipment_status_'.$shipment->status) }}</span>
                    <a href="{{ route('admin.shipments.show', $shipment) }}" class="rounded-xl bg-cyan-400 px-4 py-2.5 text-sm font-black text-slate-950">{{ __('deal_execution.open_shipment_record') }} →</a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl space-y-6 px-4 py-7 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-emerald-300/20 bg-emerald-300/10 px-4 py-3 text-sm text-emerald-100">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-xl border border-rose-300/20 bg-rose-300/10 px-4 py-3 text-sm text-rose-100">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
            @endif

            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-white/[.035] p-5"><p class="text-xs text-slate-500">{{ __('deal_execution.route') }}</p><p class="mt-2 font-black">{{ $shipment->originPort?->name }} → {{ $shipment->destinationPort?->name }}</p></div>
                <div class="rounded-2xl border border-white/10 bg-white/[.035] p-5"><p class="text-xs text-slate-500">{{ __('deal_execution.carrier') }}</p><p class="mt-2 font-black">{{ $shipment->carrier?->name ?? __('deal_execution.not_assigned') }}</p></div>
                <div class="rounded-2xl border border-white/10 bg-white/[.035] p-5"><p class="text-xs text-slate-500">{{ __('deal_execution.vessel') }}</p><p class="mt-2 font-black">{{ $sailing?->vessel_name ?? $sailing?->vessel?->name ?? __('deal_execution.not_assigned') }}</p></div>
            </section>

            <section class="rounded-2xl border p-5 {{ $sourceIntegrity['valid'] ? 'border-emerald-300/20 bg-emerald-300/5' : 'border-rose-300/20 bg-rose-300/5' }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-black {{ $sourceIntegrity['valid'] ? 'text-emerald-200' : 'text-rose-200' }}">{{ __('deal_execution.source_integrity') }}</h2>
                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $sourceIntegrity['valid'] ? 'bg-emerald-300/10 text-emerald-200' : 'bg-rose-300/10 text-rose-200' }}">{{ $sourceIntegrity['valid'] ? __('deal_execution.source_integrity_ok') : __('deal_execution.source_integrity_failed') }}</span>
                </div>
                @if (! $sourceIntegrity['valid'])
                    <ul class="mt-3 list-inside list-disc space-y-1 text-xs text-rose-100/80">
                        @foreach ($sourceIntegrity['issues'] as $issue)
                            <li class="font-mono">{{ $issue }}</li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="grid gap-4 sm:grid-cols-2">
                <article class="rounded-2xl border border-white/10 bg-white/[.035] p-5">
                    <p class="text-xs text-slate-500">{{ __('deal_execution.commodity') }}</p>
                    <p class="mt-2 font-black">{{ $shipment->fixture_recap_snapshot['content']['commodity'] ?? $shipment->cargo_type }}</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-white/[.035] p-5">
                    <p class="text-xs text-slate-500">{{ __('deal_execution.cargo_quantity') }}</p>
                    <p class="mt-2 font-black" dir="ltr">{{ $shipment->fixture_recap_snapshot['content']['quantity'] ?? '—' }} {{ $shipment->fixture_recap_snapshot['content']['quantity_unit'] ?? '' }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('deal_execution.signed_quantity_note') }}</p>
                </article>
            </section>

            <section class="grid gap-5 lg:grid-cols-2">
                <article class="rounded-2xl border border-white/10 bg-white/[.035] p-5 sm:p-6">
                    <h2 class="text-lg font-black">{{ __('deal_execution.schedule') }}</h2>
                    <dl class="mt-4 grid grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-xs text-slate-500">{{ __('deal_execution.etd') }}</dt><dd class="mt-1 font-bold" dir="ltr">{{ $shipment->etd?->format('Y-m-d H:i') ?? '—' }}</dd></div>
                        <div><dt class="text-xs text-slate-500">{{ __('deal_execution.eta') }}</dt><dd class="mt-1 font-bold" dir="ltr">{{ $shipment->eta?->format('Y-m-d H:i') ?? '—' }}</dd></div>
                        <div><dt class="text-xs text-slate-500">{{ __('deal_execution.booking_reference') }}</dt><dd class="mt-1 font-bold" dir="ltr">{{ $shipment->booking_no ?? '—' }}</dd></div>
                        <div>
                            <dt class="text-xs text-slate-500">{{ __('deal_execution.booking_source') }}</dt>
                            <dd class="mt-1 font-bold">
                                @if ($booking)
                                    {{ __('deal_execution.rate_booking') }} #{{ $booking->id }}
                                @endif
                                @if ($legacyBooking)
                                    {{ $booking ? ' · ' : '' }}{{ __('deal_execution.legacy_booking') }} #{{ $legacyBooking->id }}
                                @endif
                                @if (! $booking && ! $legacyBooking)
                                    {{ __('deal_execution.no_confirmed_booking') }}
                                @endif
                            </dd>
                        </div>
                        <div><dt class="text-xs text-slate-500">{{ __('deal_execution.voyage') }}</dt><dd class="mt-1 font-bold" dir="ltr">{{ $sailing?->voyage_number ?? '—' }}</dd></div>
                    </dl>
                    @if ($sailing)
                        <p class="mt-4 text-xs text-slate-500">{{ __('deal_execution.cargo_cutoff') }}: <span dir="ltr">{{ $sailing->cargo_cutoff_at?->format('Y-m-d H:i') }}</span></p>
                    @endif
                    <a href="{{ route('admin.shipments.edit', $shipment) }}" class="mt-5 inline-flex rounded-xl border border-white/10 px-4 py-2.5 text-sm font-bold text-slate-200 hover:bg-white/5">{{ __('deal_execution.update_schedule') }}</a>
                </article>

                <article class="rounded-2xl border border-white/10 bg-white/[.035] p-5 sm:p-6">
                    <h2 class="text-lg font-black">{{ __('deal_execution.source_document') }}</h2>
                    <p class="mt-2 font-mono text-xs text-cyan-300" dir="ltr">{{ $document->reference }}</p>
                    <p class="mt-1 text-sm font-bold">{{ $document->title }}</p>
                    <p class="mt-3 text-xs text-slate-500">{{ __('deal_execution.signed_on') }}: {{ $document->signed_at?->format('Y-m-d') }}</p>
                    <p class="mt-4 break-all border-t border-white/10 pt-4 font-mono text-[11px] text-slate-400">{{ $shipment->fixture_recap_sha256 }}</p>
                    <a href="{{ route('admin.deal-documents.show', $document) }}" class="mt-4 inline-flex font-bold text-cyan-300">{{ __('deal_execution.view_signed_recap') }} →</a>
                </article>
            </section>

            <section class="rounded-2xl border border-white/10 bg-white/[.035] p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-black">{{ __('deal_execution.commission_invoice') }}</h2>
                        <p class="mt-1 text-sm text-slate-400">{{ __('deal_execution.commission_invoice_help') }}</p>
                    </div>
                    @if ($invoiceDraft)
                        <a href="{{ route('admin.invoice-drafts.show', $invoiceDraft) }}" class="rounded-xl border border-emerald-300/20 bg-emerald-300/10 px-4 py-2.5 text-sm font-black text-emerald-200">{{ __('deal_execution.open_invoice_draft') }} · {{ $invoiceDraft->reference }}</a>
                    @elseif ($commission && $commission->status === 'pending')
                        <form method="POST" action="{{ route('admin.invoice-drafts.store', $commission) }}">
                            @csrf
                            <button class="rounded-xl bg-emerald-300 px-4 py-2.5 text-sm font-black text-slate-950 hover:bg-emerald-200">{{ __('deal_execution.create_invoice_draft') }}</button>
                        </form>
                    @else
                        <span class="rounded-full border border-white/10 px-3 py-2 text-xs text-slate-400">{{ __('deal_execution.invoice_not_available') }}</span>
                    @endif
                </div>
            </section>

            <section class="rounded-2xl border border-white/10 bg-white/[.035] p-5 sm:p-6">
                <h2 class="text-lg font-black">{{ __('deal_execution.tracking_history') }}</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($shipment->events->sortByDesc('event_date') as $event)
                        <div class="flex gap-4 border-s-2 border-cyan-300/30 ps-4">
                            <div class="min-w-0 flex-1">
                                <p class="font-bold">{{ str_replace('_', ' ', $event->event_type) }}</p>
                                <p class="mt-1 text-sm text-slate-400">{{ $event->description }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $event->location }} · <span dir="ltr">{{ $event->event_date?->format('Y-m-d H:i') }}</span></p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">{{ __('deal_execution.no_tracking_events') }}</p>
                    @endforelse
                </div>
            </section>
        </main>
    </div>
</x-admin-layout>
