@php
    $locale = app()->getLocale();

    // Only Arabic is RTL.
    $isRtl = $locale === 'ar';
@endphp

<x-app-layout>

    <x-slot name="header">
        <div dir="{{ $isRtl ? 'rtl' : 'ltr' }}" class="flex items-center justify-between gap-4">

            <div class="min-w-0">

                <h2 class="text-xl font-semibold leading-tight text-slate-800">
                    {{ __('dashboard.dashboard') }}
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    HAG Brokers
                    <span class="mx-1">•</span>
                    {{ __('dashboard.sea_freight_solutions') }}
                </p>

            </div>

            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center
                       rounded-xl bg-cyan-400 text-sm font-black text-slate-950
                       shadow-lg shadow-cyan-400/20">
                HAG
            </div>

        </div>
    </x-slot>

    <div dir="{{ $isRtl ? 'rtl' : 'ltr' }}" class="min-h-screen bg-slate-50 py-8 sm:py-10">

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- HERO --}}
            <section class="relative mb-8 overflow-hidden rounded-3xl
                       bg-slate-950 shadow-xl">

                {{-- UAE accent --}}
                <div
                    class="absolute inset-x-0 top-0 h-1
                           bg-gradient-to-r from-red-500
                           via-white/90 to-emerald-500">
                </div>

                <div
                    class="pointer-events-none absolute -end-24 -top-24
                           h-80 w-80 rounded-full bg-cyan-500/10 blur-3xl">
                </div>

                <div
                    class="pointer-events-none absolute -start-24 -bottom-24
                           h-72 w-72 rounded-full bg-emerald-500/10 blur-3xl">
                </div>

                <div class="relative p-7 sm:p-10">

                    {{-- Brand --}}
                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-12 w-12 shrink-0 items-center
                                   justify-center rounded-xl bg-cyan-400
                                   text-sm font-black text-slate-950">
                            HAG
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold uppercase
                                       tracking-[0.2em] text-cyan-400">
                                HAG Brokers
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                {{ __('dashboard.sea_freight_solutions') }}
                            </p>
                        </div>

                    </div>

                    {{-- UAE Badge --}}
                    <div
                        class="mt-7 inline-flex items-center gap-2
                               rounded-full border border-white/10
                               bg-white/5 px-3 py-1.5">
                        <span class="text-base">🇦🇪</span>

                        <span class="text-xs font-medium text-slate-300">
                            {{ __('dashboard.based_in_uae') }}
                        </span>
                    </div>

                    {{-- Heading --}}
                    <h1
                        class="mt-5 max-w-4xl text-3xl font-bold tracking-tight
                               text-white sm:text-4xl lg:text-5xl">
                        {{ __('dashboard.welcome') }}
                    </h1>

                    <p
                        class="mt-4 max-w-3xl text-sm leading-7 text-slate-300
                               sm:text-base">
                        {{ __('dashboard.description') }}
                    </p>

                    {{-- GCC --}}
                    <div class="mt-6 flex flex-wrap gap-2">

                        @foreach ([['🇦🇪', 'dashboard.uae'], ['🇸🇦', 'dashboard.saudi_arabia'], ['🇴🇲', 'dashboard.oman'], ['🇶🇦', 'dashboard.qatar'], ['🇧🇭', 'dashboard.bahrain'], ['🇰🇼', 'dashboard.kuwait']] as [$flag, $key])
                            <span
                                class="rounded-full border border-white/10
                                       bg-white/5 px-3 py-1.5 text-xs
                                       font-medium text-slate-300">
                                {{ $flag }} {{ __($key) }}
                            </span>
                        @endforeach

                    </div>

                    {{-- Buttons --}}
                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">

                        <a href="{{ route('quotations.create') }}"
                            class="inline-flex items-center justify-center
                                   rounded-xl bg-cyan-400 px-5 py-3
                                   text-sm font-bold text-slate-950
                                   transition hover:-translate-y-0.5
                                   hover:bg-cyan-300">
                            <span class="me-2 text-lg">+</span>

                            {{ __('dashboard.request_quote') }}
                        </a>

                        <a href="{{ route('shipments.index') }}"
                            class="inline-flex items-center justify-center
                                   rounded-xl border border-slate-700
                                   px-5 py-3 text-sm font-semibold text-white
                                   transition hover:border-cyan-400
                                   hover:bg-slate-800
                                   hover:text-cyan-400">
                            {{ __('dashboard.shipments') }}

                            <span class="ms-2" dir="ltr">
                                {{ $isRtl ? '←' : '→' }}
                            </span>
                        </a>

                    </div>

                </div>

            </section>


            {{-- STATISTICS --}}
            <section class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">

                @php
                    $statCards = [
                        [
                            'label' => 'dashboard.shipments',
                            'value' => $stats['total_shipments'] ?? 0,
                            'description' => 'dashboard.gulf_shipments',
                            'icon' => '🚢',
                            'bg' => 'bg-cyan-50',
                        ],
                        [
                            'label' => 'dashboard.in_transit',
                            'value' => $stats['in_transit'] ?? 0,
                            'description' => 'dashboard.currently_moving',
                            'icon' => '🌊',
                            'bg' => 'bg-blue-50',
                        ],
                        [
                            'label' => 'dashboard.delivered',
                            'value' => $stats['delivered'] ?? 0,
                            'description' => 'dashboard.completed_shipments',
                            'icon' => '✓',
                            'bg' => 'bg-emerald-50',
                        ],
                        [
                            'label' => 'dashboard.pending',
                            'value' => $stats['pending'] ?? 0,
                            'description' => 'dashboard.pending_requests',
                            'icon' => '⏱',
                            'bg' => 'bg-amber-50',
                        ],
                    ];
                @endphp

                @foreach ($statCards as $card)
                    <div
                        class="rounded-2xl bg-white p-6 shadow-sm
                               ring-1 ring-slate-200 transition
                               hover:-translate-y-0.5 hover:shadow-md">

                        <div class="flex items-center justify-between gap-4">

                            <div class="min-w-0">

                                <p class="text-sm text-slate-500">
                                    {{ __($card['label']) }}
                                </p>

                                <p class="mt-2 text-3xl font-bold text-slate-900">
                                    {{ $card['value'] }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ __($card['description']) }}
                                </p>

                            </div>

                            <div
                                class="flex h-12 w-12 shrink-0 items-center
                                       justify-center rounded-xl
                                       {{ $card['bg'] }} text-xl">
                                {{ $card['icon'] }}
                            </div>

                        </div>

                    </div>
                @endforeach

            </section>


            {{-- MAIN --}}
            <section class="mt-8 grid gap-6 lg:grid-cols-3">

                {{-- QUICK ACTIONS --}}
                <div
                    class="rounded-2xl bg-white p-6 shadow-sm
                           ring-1 ring-slate-200 lg:col-span-2">

                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ __('dashboard.quick_actions') }}
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ __('dashboard.quick_actions_description') }}
                    </p>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">

                        @php
                            $actions = [
                                [
                                    'route' => 'quotations.create',
                                    'icon' => '+',
                                    'title' => 'dashboard.request_sea_freight',
                                    'description' => 'dashboard.request_sea_freight_description',
                                ],
                                [
                                    'route' => 'shipments.index',
                                    'icon' => '#',
                                    'title' => 'dashboard.track_shipment',
                                    'description' => 'dashboard.track_shipment_description',
                                ],
                                [
                                    'route' => 'quotations.create',
                                    'icon' => '$',
                                    'title' => 'dashboard.get_freight_quote',
                                    'description' => 'dashboard.get_freight_quote_description',
                                ],
                                [
                                    'route' => 'notifications.index',
                                    'icon' => '!',
                                    'title' => 'dashboard.notifications_updates',
                                    'description' => 'dashboard.notifications_description',
                                ],
                            ];
                        @endphp

                        @foreach ($actions as $action)
                            <a href="{{ route($action['route']) }}"
                                class="group rounded-xl border border-slate-200
                                       p-5 transition hover:border-cyan-400
                                       hover:bg-cyan-50/50">

                                <div class="flex items-start gap-4">

                                    <div
                                        class="flex h-11 w-11 shrink-0
                                               items-center justify-center
                                               rounded-xl bg-slate-900
                                               text-xl text-white transition
                                               group-hover:bg-cyan-400
                                               group-hover:text-slate-950">
                                        {{ $action['icon'] }}
                                    </div>

                                    <div class="min-w-0">

                                        <h4
                                            class="font-semibold text-slate-900
                                                   transition
                                                   group-hover:text-cyan-700">
                                            {{ __($action['title']) }}
                                        </h4>

                                        <p
                                            class="mt-1 text-xs leading-5
                                                   text-slate-500">
                                            {{ __($action['description']) }}
                                        </p>

                                    </div>

                                </div>

                            </a>
                        @endforeach

                    </div>

                </div>


                {{-- COVERAGE --}}
                <div
                    class="relative overflow-hidden rounded-2xl
                           bg-slate-900 p-6 text-white shadow-sm">

                    <div
                        class="flex h-12 w-12 items-center justify-center
                               rounded-xl bg-cyan-400 text-lg font-black
                               text-slate-950">
                        HAG
                    </div>

                    <h3 class="mt-6 text-xl font-semibold">
                        {{ __('dashboard.gulf_coverage') }}
                    </h3>

                    <p class="mt-1 text-sm text-cyan-400">
                        {{ __('dashboard.sea_freight_brokerage') }}
                    </p>

                    <p class="mt-5 text-sm leading-7 text-slate-300">
                        {{ __('dashboard.gulf_coverage_description') }}
                    </p>

                    <div class="mt-6 rounded-xl border border-red-400/20
                               bg-white/5 p-4">

                        <div class="flex items-center justify-between gap-3">

                            <div class="flex items-center gap-2">
                                <span class="text-xl">🇦🇪</span>

                                <span class="text-sm font-semibold">
                                    {{ __('dashboard.uae') }}
                                </span>
                            </div>

                            <span
                                class="rounded-full bg-emerald-400/10
                                       px-2 py-1 text-[10px]
                                       font-semibold text-emerald-300">
                                {{ __('dashboard.primary_market') }}
                            </span>

                        </div>

                    </div>

                    <div class="mt-4 space-y-3 border-t border-slate-700 pt-5">

                        @foreach ([['🇸🇦', 'dashboard.saudi_arabia'], ['🇴🇲', 'dashboard.oman'], ['🇶🇦', 'dashboard.qatar'], ['🇧🇭', 'dashboard.bahrain'], ['🇰🇼', 'dashboard.kuwait']] as [$flag, $key])
                            <div class="flex items-center justify-between text-sm">

                                <span class="text-slate-400">
                                    {{ $flag }} {{ __($key) }}
                                </span>

                                <span class="font-medium text-slate-300">
                                    GCC
                                </span>

                            </div>
                        @endforeach

                    </div>

                </div>

            </section>


            {{-- PORT NETWORK --}}
            <section class="mt-8 rounded-2xl bg-white shadow-sm
                       ring-1 ring-slate-200">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ __('dashboard.gulf_network') }}
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ __('dashboard.gulf_network_description') }}
                    </p>

                </div>

                @php
                    $countries = [
                        [
                            'flag' => '🇦🇪',
                            'name' => 'dashboard.united_arab_emirates',
                            'short' => 'dashboard.uae',
                            'ports' => ['dashboard.jebel_ali', 'dashboard.khalifa_port', 'dashboard.port_rashid'],
                            'uae' => true,
                        ],
                        [
                            'flag' => '🇸🇦',
                            'name' => 'dashboard.saudi_arabia',
                            'short' => 'dashboard.kingdom_of_saudi_arabia',
                            'ports' => ['dashboard.jeddah', 'dashboard.dammam', 'dashboard.king_abdullah_port'],
                        ],
                        [
                            'flag' => '🇴🇲',
                            'name' => 'dashboard.oman',
                            'short' => 'dashboard.sultanate_of_oman',
                            'ports' => ['dashboard.sohar', 'dashboard.salalah', 'dashboard.duqm'],
                        ],
                        [
                            'flag' => '🇶🇦',
                            'name' => 'dashboard.qatar',
                            'short' => 'dashboard.state_of_qatar',
                            'ports' => ['dashboard.hamad_port'],
                        ],
                        [
                            'flag' => '🇧🇭',
                            'name' => 'dashboard.bahrain',
                            'short' => 'dashboard.kingdom_of_bahrain',
                            'ports' => ['dashboard.khalifa_bin_salman'],
                        ],
                        [
                            'flag' => '🇰🇼',
                            'name' => 'dashboard.kuwait',
                            'short' => 'dashboard.state_of_kuwait',
                            'ports' => ['dashboard.shuwaikh', 'dashboard.shuaiba'],
                        ],
                    ];
                @endphp

                <div class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-3">

                    @foreach ($countries as $country)
                        <div
                            class="rounded-xl border p-5 transition
                                   hover:border-cyan-400 hover:shadow-sm
                                   {{ $country['uae'] ?? false
                                       ? 'border-red-100 bg-gradient-to-br from-red-50 via-white to-emerald-50'
                                       : 'border-slate-200 bg-white' }}">

                            <div class="flex items-center gap-3">

                                <span class="text-2xl">
                                    {{ $country['flag'] }}
                                </span>

                                <div>

                                    <h4 class="font-semibold text-slate-900">
                                        {{ __($country['name']) }}
                                    </h4>

                                    <p class="text-xs text-slate-500">
                                        {{ __($country['short']) }}
                                    </p>

                                </div>

                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">

                                @foreach ($country['ports'] as $port)
                                    <span
                                        class="rounded-lg bg-white px-2.5 py-1
                                               text-xs text-slate-600 shadow-sm">
                                        {{ __($port) }}
                                    </span>
                                @endforeach

                            </div>

                        </div>
                    @endforeach

                </div>

            </section>


            {{-- JOURNEY --}}
            <section class="mt-8 rounded-2xl bg-slate-900 p-6
                       shadow-sm sm:p-8">

                <div class="text-center">

                    <p
                        class="text-xs font-semibold uppercase
                               tracking-[0.2em] text-cyan-400">
                        HAG BROKERS
                    </p>

                    <h3 class="mt-2 text-2xl font-bold text-white">
                        {{ __('dashboard.gulf_journey') }}
                    </h3>

                    <p class="mx-auto mt-2 max-w-2xl text-sm
                               leading-6 text-slate-400">
                        {{ __('dashboard.gulf_journey_description') }}
                    </p>

                </div>

                <div class="mt-8 grid gap-4 md:grid-cols-5">

                    <div
                        class="rounded-xl border border-slate-700
                               bg-slate-800/40 p-5 text-center">
                        <div class="text-2xl">📦</div>

                        <p class="mt-3 text-sm font-semibold text-white">
                            {{ __('dashboard.cargo_request') }}
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            {{ __('dashboard.cargo_request_description') }}
                        </p>
                    </div>

                    <div
                        class="hidden items-center justify-center
                               text-cyan-400 md:flex">
                        <span dir="ltr">
                            {{ $isRtl ? '←' : '→' }}
                        </span>
                    </div>

                    <div
                        class="rounded-xl border border-slate-700
                               bg-slate-800/40 p-5 text-center">
                        <div class="text-2xl">🔎</div>

                        <p class="mt-3 text-sm font-semibold text-white">
                            {{ __('dashboard.carrier_options') }}
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            {{ __('dashboard.carrier_options_description') }}
                        </p>
                    </div>

                    <div
                        class="hidden items-center justify-center
                               text-cyan-400 md:flex">
                        <span dir="ltr">
                            {{ $isRtl ? '←' : '→' }}
                        </span>
                    </div>

                    <div
                        class="rounded-xl border border-slate-700
                               bg-slate-800/40 p-5 text-center">
                        <div class="text-2xl">🚢</div>

                        <p class="mt-3 text-sm font-semibold text-white">
                            {{ __('dashboard.sailing') }}
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            {{ __('dashboard.sailing_description') }}
                        </p>
                    </div>

                </div>

            </section>


            {{-- RECENT ACTIVITY --}}
            <section
                class="mt-8 overflow-hidden rounded-2xl
                       bg-white shadow-sm ring-1 ring-slate-200">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ __('dashboard.recent_activity') }}
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ __('dashboard.recent_activity_description') }}
                    </p>

                </div>

                <div class="flex min-h-[220px] items-center
                           justify-center px-6 py-10">

                    <div class="text-center">

                        <div
                            class="mx-auto flex h-14 w-14 items-center
                                   justify-center rounded-full
                                   bg-slate-100 text-xl">
                            🚢
                        </div>

                        <p class="mt-4 font-medium text-slate-700">
                            {{ __('dashboard.no_activity_yet') }}
                        </p>

                        <p
                            class="mx-auto mt-1 max-w-md
                                   text-sm leading-6 text-slate-500">
                            {{ __('dashboard.no_activity_description') }}
                        </p>

                        <a href="{{ route('shipments.index') }}"
                            class="mt-5 inline-flex items-center gap-2
                                   text-sm font-semibold text-cyan-600
                                   transition hover:text-cyan-500">
                            {{ __('dashboard.shipments') }}

                            <span dir="ltr">
                                {{ $isRtl ? '←' : '→' }}
                            </span>
                        </a>

                    </div>

                </div>

            </section>


            {{-- FOOTER --}}
            <div class="mt-8 text-center">

                <p class="text-xs text-slate-400">

                    HAG Brokers

                    <span class="mx-1">•</span>

                    {{ __('dashboard.sea_freight_brokerage') }}

                    <span class="mx-1">•</span>

                    {{ __('dashboard.uae_and_gcc') }}

                </p>

            </div>

        </div>

    </div>

</x-app-layout>
