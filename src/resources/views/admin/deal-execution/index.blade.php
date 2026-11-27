<x-admin-layout>
    @php($isAr = app()->getLocale() === 'ar')
    <div class="min-h-screen bg-slate-950 text-white" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <header class="border-b border-white/10 bg-gradient-to-br from-slate-900 via-slate-950 to-cyan-950/30 px-4 py-7 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <p class="text-xs font-black uppercase tracking-[.22em] text-cyan-300">HAG BROKERS · OPERATIONS</p>
                <h1 class="mt-2 text-2xl font-black sm:text-3xl">{{ __('deal_execution.title') }}</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">{{ __('deal_execution.subtitle') }}</p>
            </div>
        </header>

        <main class="mx-auto max-w-7xl space-y-8 px-4 py-7 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-emerald-300/20 bg-emerald-300/10 px-4 py-3 text-sm text-emerald-100">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-xl border border-rose-300/20 bg-rose-300/10 px-4 py-3 text-sm text-rose-100">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
            @endif

            <section class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-cyan-300">01 · {{ __('deal_execution.ready_queue') }}</p>
                        <h2 class="mt-1 text-xl font-black">{{ __('deal_execution.signed_recaps') }}</h2>
                    </div>
                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-slate-300">{{ $eligibleDocuments->total() }} {{ __('deal_execution.awaiting_handoff') }}</span>
                </div>

                <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/[.035]">
                    @forelse ($eligibleDocuments as $document)
                        <article class="grid gap-4 border-b border-white/10 p-5 last:border-b-0 lg:grid-cols-[1.3fr_1fr_auto] lg:items-center">
                            <div>
                                <p class="font-mono text-xs text-cyan-300" dir="ltr">{{ $document->reference }}</p>
                                <h3 class="mt-1 font-black">{{ $document->title }}</h3>
                                <p class="mt-1 text-sm text-slate-400">{{ $document->deal?->reference }} · {{ $document->deal?->customer?->company_name }}</p>
                            </div>
                            <div class="text-sm text-slate-300">
                                <p>{{ $document->deal?->quotation?->originPort?->name }} → {{ $document->deal?->quotation?->destinationPort?->name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ __('deal_execution.signed_on') }}: {{ $document->signed_at?->format('Y-m-d') }}</p>
                            </div>
                            <a href="{{ route('admin.deal-execution.create', $document) }}" class="inline-flex justify-center rounded-xl bg-cyan-400 px-4 py-3 text-sm font-black text-slate-950 hover:bg-cyan-300">{{ __('deal_execution.start_handoff') }} <span class="ms-2">→</span></a>
                        </article>
                    @empty
                        <div class="p-8 text-center text-sm text-slate-400">{{ __('deal_execution.no_signed_recaps') }}</div>
                    @endforelse
                </div>
                <div>{{ $eligibleDocuments->links() }}</div>
            </section>

            <section class="space-y-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-cyan-300">02 · {{ __('deal_execution.active_records') }}</p>
                    <h2 class="mt-1 text-xl font-black">{{ __('deal_execution.shipment_executions') }}</h2>
                </div>
                <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/[.035]">
                    @forelse ($executions as $shipment)
                        <a href="{{ route('admin.deal-execution.show', $shipment) }}" class="grid gap-3 border-b border-white/10 p-5 transition hover:bg-white/[.035] last:border-b-0 lg:grid-cols-[1fr_1fr_auto] lg:items-center">
                            <div>
                                <p class="font-mono text-xs text-cyan-300" dir="ltr">{{ $shipment->shipment_no }}</p>
                                <p class="mt-1 font-bold">{{ $shipment->customer?->company_name }}</p>
                            </div>
                            <div class="text-sm text-slate-300">{{ $shipment->quotation?->originPort?->name }} → {{ $shipment->quotation?->destinationPort?->name }}</div>
                            <div class="flex items-center gap-3">
                                <span class="rounded-full border border-white/10 px-3 py-1 text-xs text-slate-300">{{ __('deal_execution.shipment_status_'.$shipment->status) }}</span>
                                <span class="text-cyan-300">→</span>
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center text-sm text-slate-400">{{ __('deal_execution.no_executions') }}</div>
                    @endforelse
                </div>
                <div>{{ $executions->links() }}</div>
            </section>
        </main>
    </div>
</x-admin-layout>
