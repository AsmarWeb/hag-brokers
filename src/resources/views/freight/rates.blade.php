<x-market-layout>
<section class="mx-auto max-w-7xl space-y-7 px-4 py-8 sm:px-6 lg:py-12" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
    <header class="relative isolate overflow-hidden rounded-[2rem] bg-[#071522] px-6 py-9 text-white shadow-2xl shadow-slate-900/10 sm:px-10 sm:py-12">
        <div aria-hidden="true" class="absolute -end-24 -top-32 -z-10 h-80 w-80 rounded-full bg-cyan-400/20 blur-3xl"></div>
        <div aria-hidden="true" class="absolute -bottom-40 start-1/3 -z-10 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl"></div>
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_280px] lg:items-center">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1.5 text-[11px] font-black tracking-[.18em] text-cyan-200">
                    <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
                    HAG BROKERS <span class="text-white/40">/</span> OCEAN FREIGHT
                </div>
                <h1 class="mt-5 max-w-3xl text-3xl font-black leading-tight tracking-tight sm:text-5xl">{{ __('freight_compare.title') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">{{ __('freight_compare.intro') }}</p>
                <div class="mt-6 flex flex-wrap gap-2 text-xs font-bold">
                    <span class="rounded-full border border-white/10 bg-white/[.06] px-3 py-2">{{ __('freight_compare.badge_compare') }}</span>
                    <span class="rounded-full border border-white/10 bg-white/[.06] px-3 py-2">FCL · 20ft / 40ft / 40ft HC</span>
                    <span class="rounded-full border border-white/10 bg-white/[.06] px-3 py-2">{{ __('freight_compare.badge_transparent') }}</span>
                </div>
            </div>
            <div class="hidden lg:block">
                <div class="rounded-3xl border border-white/10 bg-white/[.06] p-5 backdrop-blur-sm">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-300">
                        <span>OCEAN FREIGHT</span><span class="text-cyan-300">FCL</span>
                    </div>
                    <div class="mt-7 flex items-center gap-3">
                        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-cyan-300/10 text-xl text-cyan-200">⚓</div>
                        <div class="h-px flex-1 bg-gradient-to-r from-cyan-300 via-white/30 to-cyan-300"></div>
                        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-blue-400/10 text-xl text-blue-200">▤</div>
                    </div>
                    <div class="mt-4 flex justify-between text-[10px] font-bold tracking-widest text-slate-400">
                        <span>ORIGIN</span><span>DESTINATION</span>
                    </div>
                    <p class="mt-6 border-t border-white/10 pt-4 text-xs leading-6 text-slate-300">Compare ocean freight options and review the details before selecting an offer.</p>
                </div>
            </div>
        </div>
    </header>

    @if($errors->any())
        <div role="alert" class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900"><ul class="list-disc space-y-1 ps-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="GET" action="{{ route('freight.rates') }}" class="rounded-[1.75rem] border border-slate-200/80 bg-white p-5 shadow-xl shadow-slate-900/[.04] sm:p-7 lg:p-8">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-3 border-b border-slate-100 pb-5"><div><p class="text-xs font-black tracking-widest text-cyan-700">{{ __('freight_compare.SEARCH_LABEL') }}</p><h2 class="mt-1 text-xl font-black text-slate-950">{{ __('freight_compare.search_title') }}</h2></div><span class="text-xs text-slate-500">{{ __('freight_compare.search_hint') }}</span></div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <label class="grid gap-2 text-sm font-bold text-slate-700">{{ __('freight_compare.origin') }}<select name="origin_port_id" required class="min-h-14 rounded-xl border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10"><option value="">{{ __('freight_compare.choose_port') }}</option>@foreach($ports as $port)<option value="{{ $port->id }}" @selected((string)($filters['origin_port_id'] ?? '') === (string)$port->id)>{{ $port->name }} · {{ $port->unlocode }}</option>@endforeach</select></label>
            <label class="grid gap-2 text-sm font-bold text-slate-700">{{ __('freight_compare.destination') }}<select name="destination_port_id" required class="min-h-14 rounded-xl border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10"><option value="">{{ __('freight_compare.choose_port') }}</option>@foreach($ports as $port)<option value="{{ $port->id }}" @selected((string)($filters['destination_port_id'] ?? '') === (string)$port->id)>{{ $port->name }} · {{ $port->unlocode }}</option>@endforeach</select></label>
            <label class="grid gap-2 text-sm font-bold text-slate-700">{{ __('freight_compare.ready_on') }}<input type="date" name="ready_on" min="{{ now('Asia/Dubai')->toDateString() }}" value="{{ $filters['ready_on'] ?? '' }}" class="min-h-14 rounded-xl border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10"></label>
            <label class="grid gap-2 text-sm font-bold text-slate-700">{{ __('freight_compare.container') }}<select name="container_type" required class="min-h-14 rounded-xl border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10">@foreach(['20ft','40ft','40ft_hc'] as $type)<option value="{{ $type }}" @selected(($filters['container_type'] ?? '40ft_hc') === $type)>{{ __('freight_compare.containers.'.$type) }}</option>@endforeach</select></label>
            <label class="grid gap-2 text-sm font-bold text-slate-700">{{ __('freight_compare.quantity') }}<input type="number" name="quantity" min="1" max="10000" value="{{ $filters['quantity'] ?? 1 }}" required class="min-h-14 rounded-xl border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10"></label>
            <label class="grid gap-2 text-sm font-bold text-slate-700">{{ __('freight_compare.currency') }}<input name="currency" maxlength="3" minlength="3" pattern="[A-Za-z]{3}" value="{{ $filters['currency'] ?? 'USD' }}" required class="min-h-14 rounded-xl border-slate-200 bg-slate-50/70 px-4 text-sm font-bold uppercase text-slate-900 shadow-sm transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10"></label>
            <label class="grid gap-2 text-sm font-bold text-slate-700">{{ __('freight_compare.carrier') }}<select name="carrier_id" class="min-h-14 rounded-xl border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10"><option value="">{{ __('freight_compare.any_carrier') }}</option>@foreach($carriers as $carrier)<option value="{{ $carrier->id }}" @selected((string)($filters['carrier_id'] ?? '') === (string)$carrier->id)>{{ $carrier->name }}</option>@endforeach</select></label>
            <label class="grid gap-2 text-sm font-bold text-slate-700">{{ __('freight_compare.sort') }}<select name="sort" class="min-h-14 rounded-xl border-slate-200 bg-slate-50/70 px-4 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-500/10"><option value="best_value" @selected(($filters['sort'] ?? 'best_value') === 'best_value')>{{ __('freight_compare.sorts.best_value') }}</option><option value="lowest_price" @selected(($filters['sort'] ?? '') === 'lowest_price')>{{ __('freight_compare.sorts.lowest_price') }}</option><option value="fastest" @selected(($filters['sort'] ?? '') === 'fastest')>{{ __('freight_compare.sorts.fastest') }}</option></select></label>
        </div>
        <div class="mt-7 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-6"><button class="inline-flex min-h-12 items-center justify-center rounded-xl bg-cyan-400 px-8 py-3 text-sm font-black text-slate-950 shadow-lg shadow-cyan-500/20 transition hover:-translate-y-0.5 hover:bg-cyan-300 focus:outline-none focus:ring-4 focus:ring-cyan-500/20">{{ __('freight_compare.search_button') }}</button><a href="{{ route('freight.rates') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">{{ __('freight_compare.reset') }}</a></div>
    </form>

    @if($searched)
        <section aria-live="polite" class="space-y-4">
            <div class="flex flex-wrap items-end justify-between gap-3"><div><p class="text-xs font-black tracking-widest text-cyan-700">{{ __('freight_compare.RESULTS_LABEL') }}</p><h2 class="mt-1 text-2xl font-black text-slate-950">{{ __('freight_compare.results_title') }}</h2><p class="mt-1 text-sm text-slate-600">{{ $results->count() }} {{ __('freight_compare.results_count') }} · {{ $filters['quantity'] }} × {{ __('freight_compare.containers.'.$filters['container_type']) }} · {{ $filters['currency'] }}</p></div><span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700">{{ __('freight_compare.sorts.'.$filters['sort']) }}</span></div>
            @forelse($results as $result)
                <article class="group overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white shadow-sm transition duration-300 hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-xl hover:shadow-slate-900/[.06]">
                    <div class="grid gap-6 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_260px] lg:gap-8 lg:p-7">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-black text-cyan-800">{{ $result['carrier']['name'] ?? $result['source']['name'] }}</span><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ __('freight_compare.rate_types.'.$result['rate_type']) }}</span>@if($result['availability'] === 'available')<span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800">{{ __('freight_compare.availability.available') }}</span>@elseif($result['availability'] === 'limited')<span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-800">{{ __('freight_compare.availability.limited') }}</span>@else<span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ __('freight_compare.availability.on_request') }}</span>@endif</div>
                            <div class="mt-5 grid gap-4 sm:grid-cols-[1fr_auto_1fr] sm:items-center">
                                <div><p class="text-xs font-bold text-slate-500">{{ __('freight_compare.origin') }}</p><h3 class="mt-1 text-lg font-black text-slate-950">{{ $result['route']['origin']['name'] }}</h3><p class="text-xs text-slate-500"><bdi>{{ $result['route']['origin']['unlocode'] }}</bdi></p></div>
                                <div class="flex items-center gap-2 text-cyan-600 sm:px-2"><span class="h-px w-6 bg-cyan-200 sm:w-10"></span><span class="grid h-9 w-9 place-items-center rounded-full bg-cyan-50 text-base" aria-hidden="true">⌁</span><span class="h-px w-6 bg-cyan-200 sm:w-10"></span></div>
                                <div><p class="text-xs font-bold text-slate-500">{{ __('freight_compare.destination') }}</p><h3 class="mt-1 text-lg font-black text-slate-950">{{ $result['route']['destination']['name'] }}</h3><p class="text-xs text-slate-500"><bdi>{{ $result['route']['destination']['unlocode'] }}</bdi></p></div>
                            </div>
                            <div class="mt-6 grid grid-cols-2 gap-4 rounded-2xl bg-slate-50/80 p-4 text-sm sm:grid-cols-4">
                                <div><p class="text-xs text-slate-500">{{ __('freight_compare.departure') }}</p><p class="mt-1 font-bold text-slate-800">{{ $result['departure_at'] ? \Illuminate\Support\Carbon::parse($result['departure_at'])->format('d M Y') : '—' }}</p></div>
                                <div><p class="text-xs text-slate-500">{{ __('freight_compare.arrival') }}</p><p class="mt-1 font-bold text-slate-800">{{ $result['arrival_at'] ? \Illuminate\Support\Carbon::parse($result['arrival_at'])->format('d M Y') : '—' }}</p></div>
                                <div><p class="text-xs text-slate-500">{{ __('freight_compare.transit') }}</p><p class="mt-1 font-bold text-slate-800">{{ $result['transit_days'] !== null ? $result['transit_days'].' '. __('freight_compare.days') : '—' }}</p></div>
                                <div><p class="text-xs text-slate-500">{{ __('freight_compare.vessel') }}</p><p class="mt-1 font-bold text-slate-800">{{ $result['vessel_name'] ?: '—' }}</p></div>
                            </div>
                            <details class="mt-5 rounded-2xl border border-slate-100 bg-slate-50/70 p-4 text-sm"><summary class="cursor-pointer font-bold text-slate-800">{{ __('freight_compare.price_details') }}</summary><div class="mt-3 grid gap-3 sm:grid-cols-3"><div><p class="text-xs font-bold text-slate-500">{{ __('freight_compare.surcharges') }}</p><ul class="mt-1 list-disc ps-5 text-slate-700">@forelse($result['surcharges'] as $charge)<li>{{ $charge['name'] ?? $charge['label'] ?? __('freight_compare.extra_charge') }}: {{ number_format((float)($charge['customer_amount'] ?? $charge['amount'] ?? 0), 2) }} {{ $result['currency'] }}</li>@empty<li>—</li>@endforelse</ul></div><div><p class="text-xs font-bold text-slate-500">{{ __('freight_compare.included') }}</p><ul class="mt-1 list-disc ps-5 text-slate-700">@forelse($result['included_charges'] as $charge)<li>{{ $charge }}</li>@empty<li>—</li>@endforelse</ul></div><div><p class="text-xs font-bold text-slate-500">{{ __('freight_compare.excluded') }}</p><ul class="mt-1 list-disc ps-5 text-slate-700">@forelse($result['excluded_charges'] as $charge)<li>{{ $charge }}</li>@empty<li>—</li>@endforelse</ul></div></div>@if($result['terms'])<p class="mt-3 border-t pt-3 text-slate-600">{{ $result['terms'] }}</p>@endif</details>
                        </div>
                        <aside class="flex flex-col justify-between rounded-2xl border border-slate-100 bg-gradient-to-br from-slate-50 to-white p-5 lg:text-end"><div><p class="text-xs font-bold text-slate-500">{{ __('freight_compare.total_for') }} {{ $result['quantity'] }} · {{ $result['container_type'] }}</p><p class="mt-2 text-4xl font-black tracking-tight text-slate-950 sm:text-[2.65rem]"><bdi>{{ number_format((float)$result['total_price'], 2) }}</bdi></p><p class="text-sm font-bold text-slate-600">{{ $result['currency'] }}</p><p class="mt-2 text-xs text-slate-500">{{ number_format((float)$result['unit_price'], 2) }} {{ $result['currency'] }} / {{ __('freight_compare.per_container') }}</p><p class="mt-1 text-xs text-slate-500">{{ __('freight_compare.price_validity') }}: {{ $result['expires_at'] ? \Illuminate\Support\Carbon::parse($result['expires_at'])->format('d M Y H:i') : __('freight_compare.confirm_validity') }}</p></div><div class="mt-5">
                            <form method="POST" action="{{ route('freight.rates.select', $result['id']) }}" class="space-y-2">
                                @csrf
                                <input type="hidden" name="request_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                                <input type="hidden" name="quantity" value="{{ $result['quantity'] }}">
                                <input type="hidden" name="preferred_date" value="{{ $filters['ready_on'] ?? '' }}">
                                <input type="hidden" name="cargo_type" value="General cargo">
                                <button class="block min-h-12 w-full rounded-xl bg-[#071522] px-4 py-3 text-center text-sm font-black text-white shadow-lg shadow-slate-900/10 transition hover:bg-cyan-700 focus:outline-none focus:ring-4 focus:ring-cyan-500/20">{{ __('freight_selection.select_offer') }}</button>
                            </form>
                        <p class="mt-2 text-center text-[11px] leading-5 text-slate-500">{{ __('freight_compare.no_instant_booking') }}</p></div></aside>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center"><p class="text-lg font-black text-slate-900">{{ __('freight_compare.no_results') }}</p><p class="mt-2 text-sm text-slate-600">{{ __('freight_compare.no_results_hint') }}</p><a href="{{ route('quotations.create') }}" class="mt-5 inline-flex rounded-xl bg-cyan-500 px-5 py-3 text-sm font-black text-slate-950">{{ __('freight_compare.request_custom_quote') }}</a></div>
            @endforelse
        </section>
    @else
        <section class="grid gap-4 md:grid-cols-3"><article class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-2xl">⇄</p><h3 class="mt-3 font-black text-slate-950">{{ __('freight_compare.feature_compare') }}</h3><p class="mt-2 text-sm leading-6 text-slate-600">{{ __('freight_compare.feature_compare_text') }}</p></article><article class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-2xl">◷</p><h3 class="mt-3 font-black text-slate-950">{{ __('freight_compare.feature_schedule') }}</h3><p class="mt-2 text-sm leading-6 text-slate-600">{{ __('freight_compare.feature_schedule_text') }}</p></article><article class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-2xl">✓</p><h3 class="mt-3 font-black text-slate-950">{{ __('freight_compare.feature_transparency') }}</h3><p class="mt-2 text-sm leading-6 text-slate-600">{{ __('freight_compare.feature_transparency_text') }}</p></article></section>
    @endif
</section>
</x-market-layout>
