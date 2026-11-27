<x-admin-layout>
    @php($isAr = app()->getLocale() === 'ar')
    <div class="min-h-screen bg-slate-950 text-white" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <header class="border-b border-white/10 bg-gradient-to-br from-slate-900 via-slate-950 to-cyan-950/30 px-4 py-7 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-5xl">
                <a href="{{ route('admin.deal-documents.show', $document) }}" class="text-sm font-bold text-cyan-300">← {{ __('deal_execution.back_to_recap') }}</a>
                <p class="mt-4 font-mono text-xs text-cyan-300" dir="ltr">{{ $document->reference }}</p>
                <h1 class="mt-1 text-2xl font-black sm:text-3xl">{{ __('deal_execution.create_title') }}</h1>
                <p class="mt-2 text-sm text-slate-400">{{ __('deal_execution.create_subtitle') }}</p>
            </div>
        </header>

        <main class="mx-auto max-w-5xl space-y-6 px-4 py-7 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="rounded-xl border border-rose-300/20 bg-rose-300/10 px-4 py-3 text-sm text-rose-100">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
            @endif

            <section class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-white/10 bg-white/[.035] p-5">
                    <p class="text-xs text-slate-500">{{ __('deal_execution.deal_customer') }}</p>
                    <p class="mt-1 font-black">{{ $deal->customer?->company_name }}</p>
                    <p class="mt-2 font-mono text-xs text-cyan-300" dir="ltr">{{ $deal->reference }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/[.035] p-5">
                    <p class="text-xs text-slate-500">{{ __('deal_execution.quotation_route') }}</p>
                    <p class="mt-1 font-black">{{ $quotation->originPort?->name }} → {{ $quotation->destinationPort?->name }}</p>
                    <p class="mt-2 text-xs text-slate-400">{{ __('deal_execution.route_note') }}</p>
                </div>
            </section>

            <section class="rounded-2xl border border-emerald-300/20 bg-emerald-300/5 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-black text-emerald-200">{{ __('deal_execution.signed_source') }}</h2>
                    <span class="rounded-full bg-emerald-300/10 px-3 py-1 text-xs font-black text-emerald-200">{{ __('deal_execution.integrity_verified') }}</span>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div><p class="text-xs text-slate-500">{{ __('deal_execution.commodity') }}</p><p class="mt-1 font-bold">{{ $content['commodity'] }}</p></div>
                    <div><p class="text-xs text-slate-500">{{ __('deal_execution.cargo_quantity') }}</p><p class="mt-1 font-bold">{{ $content['quantity'] }} {{ $content['quantity_unit'] }}</p></div>
                    <div><p class="text-xs text-slate-500">{{ __('deal_execution.fixture_route') }}</p><p class="mt-1 font-bold">{{ $content['loading_port'] }} → {{ $content['discharge_port'] }}</p></div>
                    <div><p class="text-xs text-slate-500">{{ __('deal_execution.laycan') }}</p><p class="mt-1 font-bold" dir="ltr">{{ $content['laycan_from'] }} — {{ $content['laycan_to'] }}</p></div>
                </div>
                <p class="mt-4 break-all border-t border-emerald-200/10 pt-3 font-mono text-[11px] text-emerald-100/60">SHA-256: {{ $document->document_sha256 }}</p>
            </section>

            <form method="POST" action="{{ route('admin.deal-execution.store', $document) }}" class="space-y-6">
                @csrf
                @if ($legacy_booking_offer)
                    <section class="rounded-2xl border border-emerald-300/20 bg-emerald-300/5 p-5 sm:p-6">
                        <h2 class="font-black text-emerald-200">{{ __('deal_execution.legacy_confirmed_booking') }}</h2>
                        <p class="mt-2 text-sm text-emerald-100">{{ $legacy_booking_carrier?->name }} · <span dir="ltr">{{ $legacy_booking_offer->carrier_reference }}</span></p>
                        <p class="mt-1 text-xs text-emerald-100/70">{{ __('deal_execution.legacy_confirmed_booking_help') }}</p>
                    </section>
                @endif

                <section class="rounded-2xl border border-white/10 bg-white/[.035] p-5 sm:p-6">
                    <h2 class="text-lg font-black">{{ __('deal_execution.booking_selection') }}</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-400">{{ __('deal_execution.booking_selection_help') }}</p>
                    <label class="mt-5 block text-sm font-bold text-slate-300" for="freight_rate_booking_id">{{ __('deal_execution.confirmed_booking') }}</label>
                    <select id="freight_rate_booking_id" name="freight_rate_booking_id" class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-sm text-white">
                        <option value="">{{ __('deal_execution.no_booking_selected') }}</option>
                        @foreach ($confirmedBookings as $booking)
                            <option value="{{ $booking->id }}" @selected((string) old('freight_rate_booking_id') === (string) $booking->id)>
                                {{ $booking->carrier?->name ?? __('deal_execution.unknown_carrier') }} · {{ $booking->carrier_reference }} · {{ $booking->confirmed_at?->format('Y-m-d') }}
                            </option>
                        @endforeach
                    </select>
                    @if ($confirmedBookings->isEmpty() && ! $legacy_booking_offer)
                        <p class="mt-2 text-xs text-amber-200">{{ __('deal_execution.no_confirmed_booking_note') }}</p>
                    @endif
                </section>

                <section class="rounded-2xl border border-white/10 bg-white/[.035] p-5 sm:p-6">
                    <h2 class="text-lg font-black">{{ __('deal_execution.sailing_selection') }}</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-400">{{ __('deal_execution.sailing_selection_help') }}</p>
                    <label class="mt-5 block text-sm font-bold text-slate-300" for="freight_sailing_id">{{ __('deal_execution.published_sailing') }}</label>
                    <select id="freight_sailing_id" name="freight_sailing_id" class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-sm text-white">
                        <option value="">{{ __('deal_execution.no_sailing_selected') }}</option>
                        @foreach ($sailings as $sailing)
                            <option value="{{ $sailing->id }}" @selected((string) old('freight_sailing_id', $selectedSailingId) === (string) $sailing->id)>
                                {{ $sailing->carrier?->name }} · {{ $sailing->vessel_name }} · {{ $sailing->voyage_number }} · {{ $sailing->departure_at?->format('Y-m-d H:i') }}
                            </option>
                        @endforeach
                    </select>
                    @if ($sailings->isEmpty())
                        <p class="mt-2 text-xs text-amber-200">{{ $quotation->freight_sailing_id ? __('deal_execution.quotation_sailing_unavailable_note') : __('deal_execution.no_sailing_note') }}</p>
                    @endif
                    @if ($quotation->freight_sailing_id)
                        <p class="mt-3 text-xs text-cyan-200">{{ __('deal_execution.quotation_sailing_locked') }}</p>
                    @endif
                </section>

                <section class="rounded-2xl border border-amber-300/20 bg-amber-300/5 p-5">
                    <p class="font-black text-amber-100">{{ __('deal_execution.status_explanation_title') }}</p>
                    <p class="mt-1 text-sm leading-6 text-amber-100/70">{{ __('deal_execution.status_explanation') }}</p>
                </section>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ route('admin.deal-documents.show', $document) }}" class="rounded-xl border border-white/10 px-5 py-3 text-sm font-bold text-slate-300 hover:bg-white/5">{{ __('deal_execution.cancel') }}</a>
                    <button type="submit" class="rounded-xl bg-cyan-400 px-6 py-3 text-sm font-black text-slate-950 hover:bg-cyan-300">{{ __('deal_execution.confirm_handoff') }} →</button>
                </div>
            </form>
        </main>
    </div>
</x-admin-layout>
