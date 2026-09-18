<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">

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
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-400 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/20">
                HAG
            </div>

        </div>
    </x-slot>


    {{-- =========================================================
         Dashboard
    ========================================================== --}}

    <div class="min-h-screen bg-slate-50 py-8 sm:py-10">

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">


            {{-- =====================================================
                 Welcome / Hero
            ====================================================== --}}

            <section class="mb-8 overflow-hidden rounded-3xl bg-slate-950 shadow-xl">

                <div class="relative overflow-hidden p-7 sm:p-10">

                    {{-- Decorative Background --}}
                    <div
                        class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cyan-500/10 blur-3xl">
                    </div>

                    <div
                        class="pointer-events-none absolute -bottom-20 left-1/3 h-56 w-56 rounded-full bg-blue-500/10 blur-3xl">
                    </div>

                    <div class="relative">


                        {{-- Brand --}}
                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-400 text-sm font-black text-slate-950">
                                HAG
                            </div>

                            <div class="min-w-0">

                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-400">
                                    HAG Brokers
                                </p>

                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ __('dashboard.sea_freight_solutions') }}
                                </p>

                            </div>

                        </div>


                        {{-- Heading --}}
                        <h1 class="mt-7 text-3xl font-bold tracking-tight text-white sm:text-4xl">
                            {{ __('dashboard.welcome') }}
                        </h1>


                        {{-- Description --}}
                        <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">
                            {{ __('dashboard.description') }}
                        </p>


                        {{-- Buttons --}}
                        <div class="mt-7 flex flex-col gap-3 sm:flex-row">

                            <a href="{{ route('quotations.create') }}"
                                class="inline-flex items-center justify-center rounded-xl bg-cyan-400 px-5 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-cyan-400/10 transition duration-200 hover:-translate-y-0.5 hover:bg-cyan-300">

                                <span class="me-2 text-lg leading-none">+</span>

                                {{ __('dashboard.request_quote') }}

                            </a>


                            <a href="{{ route('shipments.index') }}"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-700 px-5 py-3 text-sm font-semibold text-white transition duration-200 hover:border-cyan-400 hover:bg-slate-800 hover:text-cyan-400">

                                {{ __('dashboard.shipments') }}

                                <span class="ms-2">
                                    →
                                </span>

                            </a>

                        </div>

                    </div>

                </div>

            </section>


            {{-- =====================================================
                 Statistics
            ====================================================== --}}

            <section class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">


                {{-- Total Shipments --}}
                <div
                    class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition duration-200 hover:-translate-y-0.5 hover:shadow-md">

                    <div class="flex items-center justify-between gap-4">

                        <div class="min-w-0">

                            <p class="text-sm text-slate-500">
                                {{ __('dashboard.shipments') }}
                            </p>

                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                {{ $stats['total_shipments'] ?? 0 }}
                            </p>

                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-xl">
                            🚢
                        </div>

                    </div>

                </div>


                {{-- In Transit --}}
                <div
                    class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition duration-200 hover:-translate-y-0.5 hover:shadow-md">

                    <div class="flex items-center justify-between gap-4">

                        <div class="min-w-0">

                            <p class="text-sm text-slate-500">
                                {{ __('dashboard.in_transit') }}
                            </p>

                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                {{ $stats['in_transit'] ?? 0 }}
                            </p>

                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-xl">
                            🌊
                        </div>

                    </div>

                </div>


                {{-- Delivered --}}
                <div
                    class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition duration-200 hover:-translate-y-0.5 hover:shadow-md">

                    <div class="flex items-center justify-between gap-4">

                        <div class="min-w-0">

                            <p class="text-sm text-slate-500">
                                {{ __('dashboard.delivered') }}
                            </p>

                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                {{ $stats['delivered'] ?? 0 }}
                            </p>

                        </div>

                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-xl">
                            ✓
                        </div>

                    </div>

                </div>


                {{-- Pending --}}
                <div
                    class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition duration-200 hover:-translate-y-0.5 hover:shadow-md">

                    <div class="flex items-center justify-between gap-4">

                        <div class="min-w-0">

                            <p class="text-sm text-slate-500">
                                {{ __('dashboard.pending') }}
                            </p>

                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                {{ $stats['pending'] ?? 0 }}
                            </p>

                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-xl">
                            ⏱
                        </div>

                    </div>

                </div>

            </section>


            {{-- =====================================================
                 Main Sections
            ====================================================== --}}

            <section class="mt-8 grid gap-6 lg:grid-cols-3">


                {{-- =================================================
                     Quick Actions
                ================================================== --}}

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 lg:col-span-2">

                    <div class="mb-6">

                        <h3 class="text-lg font-semibold text-slate-900">
                            {{ __('dashboard.quick_actions') }}
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ __('dashboard.quick_actions_description') }}
                        </p>

                    </div>


                    <div class="grid gap-4 sm:grid-cols-2">


                        {{-- New Shipment --}}
                        <a href="{{ route('quotations.create') }}"
                            class="group rounded-xl border border-slate-200 p-5 transition duration-200 hover:border-cyan-400 hover:bg-cyan-50/50">

                            <div class="flex items-start gap-4">

                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-xl text-white transition group-hover:bg-cyan-400 group-hover:text-slate-950">
                                    +
                                </div>

                                <div class="min-w-0">

                                    <h4 class="font-semibold text-slate-900 transition group-hover:text-cyan-700">
                                        {{ __('dashboard.new_shipment') }}
                                    </h4>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        {{ __('dashboard.new_shipment_description') }}
                                    </p>

                                </div>

                            </div>

                        </a>


                        {{-- Track Shipment --}}
                        <a href="{{ route('shipments.index') }}"
                            class="group rounded-xl border border-slate-200 p-5 transition duration-200 hover:border-cyan-400 hover:bg-cyan-50/50">

                            <div class="flex items-start gap-4">

                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-white transition group-hover:bg-cyan-400 group-hover:text-slate-950">
                                    #
                                </div>

                                <div class="min-w-0">

                                    <h4 class="font-semibold text-slate-900 transition group-hover:text-cyan-700">
                                        {{ __('dashboard.track_shipment') }}
                                    </h4>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        {{ __('dashboard.track_shipment_description') }}
                                    </p>

                                </div>

                            </div>

                        </a>


                        {{-- Request Quote --}}
                        <a href="{{ route('quotations.create') }}"
                            class="group rounded-xl border border-slate-200 p-5 transition duration-200 hover:border-cyan-400 hover:bg-cyan-50/50">

                            <div class="flex items-start gap-4">

                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-white transition group-hover:bg-cyan-400 group-hover:text-slate-950">
                                    $
                                </div>

                                <div class="min-w-0">

                                    <h4 class="font-semibold text-slate-900 transition group-hover:text-cyan-700">
                                        {{ __('dashboard.request_quote') }}
                                    </h4>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        {{ __('dashboard.request_quote_description') }}
                                    </p>

                                </div>

                            </div>

                        </a>


                        {{-- Support --}}
                        <a href="{{ route('notifications.index') }}"
                            class="group rounded-xl border border-slate-200 p-5 transition duration-200 hover:border-cyan-400 hover:bg-cyan-50/50">

                            <div class="flex items-start gap-4">

                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-white transition group-hover:bg-cyan-400 group-hover:text-slate-950">
                                    ?
                                </div>

                                <div class="min-w-0">

                                    <h4 class="font-semibold text-slate-900 transition group-hover:text-cyan-700">
                                        {{ __('dashboard.support') }}
                                    </h4>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        {{ __('dashboard.support_description') }}
                                    </p>

                                </div>

                            </div>

                        </a>

                    </div>

                </div>


                {{-- =================================================
                     Company Info
                ================================================== --}}

                <div class="rounded-2xl bg-slate-900 p-6 text-white shadow-sm">

                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-400 text-lg font-black text-slate-950">
                        HAG
                    </div>

                    <h3 class="mt-6 text-xl font-semibold">
                        HAG Brokers
                    </h3>

                    <p class="mt-1 text-sm text-cyan-400">
                        {{ __('dashboard.sea_freight_solutions') }}
                    </p>

                    <p class="mt-5 text-sm leading-7 text-slate-300">
                        {{ __('dashboard.reliable_sea_freight') }}
                    </p>


                    <div class="mt-8 space-y-4 border-t border-slate-700 pt-6 text-sm">

                        <div class="flex items-center justify-between gap-4">

                            <span class="text-slate-400">
                                {{ __('dashboard.coverage') }}
                            </span>

                            <span class="font-medium">
                                {{ __('dashboard.global') }}
                            </span>

                        </div>


                        <div class="flex items-center justify-between gap-4">

                            <span class="text-slate-400">
                                {{ __('dashboard.platform') }}
                            </span>

                            <span class="font-medium text-cyan-400">
                                {{ __('dashboard.secure') }}
                            </span>

                        </div>

                    </div>

                </div>

            </section>


            {{-- =====================================================
                 Recent Activity
            ====================================================== --}}

            <section class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ __('dashboard.recent_activity') }}
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ __('dashboard.recent_activity_description') }}
                    </p>

                </div>


                <div class="flex min-h-[220px] items-center justify-center px-6 py-10">

                    <div class="text-center">

                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-xl">
                            🚢
                        </div>

                        <p class="mt-4 font-medium text-slate-700">
                            {{ __('dashboard.no_activity_yet') }}
                        </p>

                        <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">
                            {{ __('dashboard.no_activity_description') }}
                        </p>

                        <a href="{{ route('shipments.index') }}"
                            class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-cyan-600 transition hover:text-cyan-500">

                            {{ __('dashboard.shipments') }}

                            <span dir="ltr">
                                →
                            </span>

                        </a>

                    </div>

                </div>

            </section>

        </div>

    </div>

</x-app-layout>
