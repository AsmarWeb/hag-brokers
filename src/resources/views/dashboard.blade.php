<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-800">
                    {{ __('dashboard.dashboard') }}
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    HAG Brokers {{ __('dashboard.sea_freight_solutions') }}
                </p>
            </div>

            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-sm font-bold text-white">
                HAG
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Welcome --}}
            <div class="mb-8 overflow-hidden rounded-2xl bg-slate-950 shadow-lg">
                <div class="relative p-8 sm:p-10">

                    <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cyan-500/10 blur-3xl">
                    </div>

                    <div class="absolute -bottom-20 left-1/3 h-56 w-56 rounded-full bg-blue-500/10 blur-3xl">
                    </div>

                    <div class="relative">

                        <p class="text-sm font-medium uppercase tracking-[0.2em] text-cyan-400">
                            HAG Brokers
                        </p>

                        <h1 class="mt-3 text-3xl font-bold text-white sm:text-4xl">
                            {{ __('dashboard.welcome') }}
                        </h1>

                        <p class="mt-4 max-w-2xl text-sm leading-6 text-slate-300">
                            {{ __('dashboard.description') }}
                        </p>

                    </div>
                </div>
            </div>


            {{-- Quick Stats --}}
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">

                {{-- Shipments --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <div class="flex items-center justify-between">

                        <div>
                            <p class="text-sm text-slate-500">
                                {{ __('dashboard.shipments') }}
                            </p>

                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                0
                            </p>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                            🚢
                        </div>

                    </div>
                </div>


                {{-- In Transit --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <div class="flex items-center justify-between">

                        <div>
                            <p class="text-sm text-slate-500">
                                {{ __('dashboard.in_transit') }}
                            </p>

                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                0
                            </p>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            🌊
                        </div>

                    </div>
                </div>


                {{-- Delivered --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <div class="flex items-center justify-between">

                        <div>
                            <p class="text-sm text-slate-500">
                                {{ __('dashboard.delivered') }}
                            </p>

                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                0
                            </p>
                        </div>

                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            ✓
                        </div>

                    </div>
                </div>


                {{-- Pending --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <div class="flex items-center justify-between">

                        <div>
                            <p class="text-sm text-slate-500">
                                {{ __('dashboard.pending') }}
                            </p>

                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                0
                            </p>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                            ⏱
                        </div>

                    </div>
                </div>

            </div>


            {{-- Main Sections --}}
            <div class="mt-8 grid gap-6 lg:grid-cols-3">


                {{-- Quick Actions --}}
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
                        <a href="#"
                            class="group rounded-xl border border-slate-200 p-5 transition hover:border-cyan-400 hover:bg-cyan-50/50">

                            <div class="flex items-center gap-4">

                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-900 text-white">
                                    +
                                </div>

                                <div>

                                    <h4 class="font-semibold text-slate-900 group-hover:text-cyan-700">
                                        {{ __('dashboard.new_shipment') }}
                                    </h4>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ __('dashboard.new_shipment_description') }}
                                    </p>

                                </div>

                            </div>
                        </a>


                        {{-- Track Shipment --}}
                        <a href="#"
                            class="group rounded-xl border border-slate-200 p-5 transition hover:border-cyan-400 hover:bg-cyan-50/50">

                            <div class="flex items-center gap-4">

                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-900 text-white">
                                    #
                                </div>

                                <div>

                                    <h4 class="font-semibold text-slate-900 group-hover:text-cyan-700">
                                        {{ __('dashboard.track_shipment') }}
                                    </h4>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ __('dashboard.track_shipment_description') }}
                                    </p>

                                </div>

                            </div>
                        </a>


                        {{-- Request Quote --}}
                        <a href="#"
                            class="group rounded-xl border border-slate-200 p-5 transition hover:border-cyan-400 hover:bg-cyan-50/50">

                            <div class="flex items-center gap-4">

                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-900 text-white">
                                    $
                                </div>

                                <div>

                                    <h4 class="font-semibold text-slate-900 group-hover:text-cyan-700">
                                        {{ __('dashboard.request_quote') }}
                                    </h4>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ __('dashboard.request_quote_description') }}
                                    </p>

                                </div>

                            </div>
                        </a>


                        {{-- Support --}}
                        <a href="#"
                            class="group rounded-xl border border-slate-200 p-5 transition hover:border-cyan-400 hover:bg-cyan-50/50">

                            <div class="flex items-center gap-4">

                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-900 text-white">
                                    ?
                                </div>

                                <div>

                                    <h4 class="font-semibold text-slate-900 group-hover:text-cyan-700">
                                        {{ __('dashboard.support') }}
                                    </h4>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ __('dashboard.support_description') }}
                                    </p>

                                </div>

                            </div>
                        </a>

                    </div>
                </div>


                {{-- Company Info --}}
                <div class="rounded-2xl bg-slate-900 p-6 text-white shadow-sm">

                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-500 font-bold text-lg text-slate-950">
                        HAG
                    </div>

                    <h3 class="mt-6 text-xl font-semibold">
                        HAG Brokers
                    </h3>

                    <p class="mt-1 text-sm text-cyan-400">
                        {{ __('dashboard.sea_freight_solutions') }}
                    </p>

                    <p class="mt-5 text-sm leading-6 text-slate-300">
                        {{ __('dashboard.reliable_sea_freight') }}
                    </p>


                    <div class="mt-8 space-y-3 border-t border-slate-700 pt-6 text-sm">

                        <div class="flex justify-between">
                            <span class="text-slate-400">
                                {{ __('dashboard.coverage') }}
                            </span>

                            <span class="font-medium">
                                {{ __('dashboard.global') }}
                            </span>
                        </div>


                        <div class="flex justify-between">
                            <span class="text-slate-400">
                                {{ __('dashboard.support') }}
                            </span>

                            <span class="font-medium">
                                24/7
                            </span>
                        </div>


                        <div class="flex justify-between">
                            <span class="text-slate-400">
                                {{ __('dashboard.platform') }}
                            </span>

                            <span class="font-medium text-cyan-400">
                                {{ __('dashboard.secure') }}
                            </span>
                        </div>

                    </div>
                </div>

            </div>


            {{-- Recent Activity --}}
            <div class="mt-8 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ __('dashboard.recent_activity') }}
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ __('dashboard.recent_activity_description') }}
                    </p>

                </div>


                <div class="flex min-h-[180px] items-center justify-center px-6">

                    <div class="text-center">

                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-xl">
                            🚢
                        </div>

                        <p class="mt-4 font-medium text-slate-700">
                            {{ __('dashboard.no_activity_yet') }}
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ __('dashboard.no_activity_description') }}
                        </p>

                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
