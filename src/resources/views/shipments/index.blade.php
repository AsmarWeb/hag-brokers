<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-400 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/20">
                        HAG
                    </div>

                    <div>
                        <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            {{ __('dashboard.shipments') }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Manage and monitor your sea freight shipments.
                        </p>
                    </div>
                </div>
            </div>

            <a href="{{ route('quotations.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-400 px-5 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-cyan-400/20 transition hover:bg-cyan-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>

                {{ __('dashboard.new_shipment') }}
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8 dark:bg-slate-950">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Page intro --}}
            <div
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="relative overflow-hidden p-6 sm:p-8">
                    <div class="absolute -right-20 -top-20 h-56 w-56 rounded-full bg-cyan-400/10 blur-3xl"></div>
                    <div class="absolute -bottom-24 -left-20 h-56 w-56 rounded-full bg-blue-500/10 blur-3xl"></div>

                    <div class="relative">
                        <div
                            class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-cyan-600 dark:text-cyan-400">
                            <span class="h-2 w-2 rounded-full bg-cyan-400"></span>
                            HAG Brokers
                        </div>

                        <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-950 dark:text-white sm:text-3xl">
                            Your Shipments
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                            View your shipments, monitor their status and access tracking and shipment documents.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div
                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <form method="GET" action="{{ route('shipments.index') }}"
                    class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <label for="status"
                            class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                            Shipment status
                        </label>

                        <select id="status" name="status"
                            class="block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-cyan-400 focus:ring-cyan-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <option value="">All statuses</option>

                            <option value="pending" @selected(request('status') === 'pending')>
                                Pending
                            </option>

                            <option value="in_transit" @selected(request('status') === 'in_transit')>
                                In Transit
                            </option>

                            <option value="delivered" @selected(request('status') === 'delivered')>
                                Delivered
                            </option>
                        </select>
                    </div>

                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-6 py-3 text-sm font-bold text-white transition hover:bg-slate-800 dark:bg-cyan-400 dark:text-slate-950 dark:hover:bg-cyan-300">
                        Filter
                    </button>

                    @if (request('status'))
                        <a href="{{ route('shipments.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-6 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            {{-- Shipments --}}
            <div
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-950 dark:text-white">
                                Shipment List
                            </h2>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ $shipments->total() }} shipment{{ $shipments->total() == 1 ? '' : 's' }} found
                            </p>
                        </div>

                        <div
                            class="hidden rounded-xl bg-cyan-50 px-3 py-2 text-xs font-bold text-cyan-700 dark:bg-cyan-400/10 dark:text-cyan-400 sm:block">
                            SEA FREIGHT
                        </div>
                    </div>
                </div>

                @if ($shipments->count())

                    {{-- Desktop table --}}
                    <div class="hidden overflow-x-auto lg:block">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-950/60">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Reference
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Route
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Vessel
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Status
                                    </th>

                                    <th
                                        class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Action
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @foreach ($shipments as $shipment)
                                    <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-950/60">

                                        <td class="px-6 py-5">
                                            <div class="font-bold text-slate-950 dark:text-white">
                                                {{ $shipment->reference_no ?? '—' }}
                                            </div>

                                            <div class="mt-1 text-xs text-slate-400">
                                                #{{ $shipment->id }}
                                            </div>
                                        </td>

                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-3">
                                                <div>
                                                    <div class="font-semibold text-slate-900 dark:text-white">
                                                        {{ $shipment->originPort->name ?? '—' }}
                                                    </div>

                                                    <div class="my-1 text-xs text-slate-400">
                                                        ↓
                                                    </div>

                                                    <div class="font-semibold text-slate-900 dark:text-white">
                                                        {{ $shipment->destinationPort->name ?? '—' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-5">
                                            @if ($shipment->vessel)
                                                <div class="font-semibold text-slate-900 dark:text-white">
                                                    {{ $shipment->vessel->name ?? '—' }}
                                                </div>

                                                @if (isset($shipment->vessel->imo))
                                                    <div class="mt-1 text-xs text-slate-400">
                                                        IMO {{ $shipment->vessel->imo }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-sm text-slate-400">
                                                    Not assigned
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-6 py-5">
                                            @php
                                                $status = $shipment->status;

                                                $statusClasses = match ($status) {
                                                    'delivered'
                                                        => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400',
                                                    'in_transit'
                                                        => 'bg-cyan-50 text-cyan-700 ring-cyan-600/20 dark:bg-cyan-400/10 dark:text-cyan-400',
                                                    'pending'
                                                        => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400',
                                                    default
                                                        => 'bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-300',
                                                };
                                            @endphp

                                            <span
                                                class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-bold ring-1 ring-inset {{ $statusClasses }}">
                                                {{ str($status)->replace('_', ' ')->title() }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-5 text-right">
                                            <a href="{{ route('shipments.show', $shipment) }}"
                                                class="inline-flex items-center gap-2 rounded-xl bg-slate-950 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-cyan-500 hover:text-slate-950 dark:bg-white dark:text-slate-950 dark:hover:bg-cyan-400">
                                                View
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile cards --}}
                    <div class="space-y-4 p-4 lg:hidden">
                        @foreach ($shipments as $shipment)
                            @php
                                $status = $shipment->status;

                                $statusClasses = match ($status) {
                                    'delivered'
                                        => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-400',
                                    'in_transit' => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-400/10 dark:text-cyan-400',
                                    'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-400/10 dark:text-amber-400',
                                    default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
                                };
                            @endphp

                            <div class="rounded-2xl border border-slate-200 p-5 dark:border-slate-800">

                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                            Shipment
                                        </p>

                                        <h3 class="mt-1 font-extrabold text-slate-950 dark:text-white">
                                            {{ $shipment->reference_no ?? '#' . $shipment->id }}
                                        </h3>
                                    </div>

                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses }}">
                                        {{ str($status)->replace('_', ' ')->title() }}
                                    </span>
                                </div>

                                <div class="mt-5 rounded-xl bg-slate-50 p-4 dark:bg-slate-950">
                                    <div class="text-xs font-semibold text-slate-400">
                                        ROUTE
                                    </div>

                                    <div class="mt-2 flex items-center justify-between gap-3">
                                        <span class="font-bold text-slate-900 dark:text-white">
                                            {{ $shipment->originPort->name ?? '—' }}
                                        </span>

                                        <span class="text-cyan-500">
                                            →
                                        </span>

                                        <span class="text-right font-bold text-slate-900 dark:text-white">
                                            {{ $shipment->destinationPort->name ?? '—' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between gap-4">
                                    <div>
                                        <div class="text-xs text-slate-400">
                                            Vessel
                                        </div>

                                        <div class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                                            {{ $shipment->vessel->name ?? 'Not assigned' }}
                                        </div>
                                    </div>

                                    <a href="{{ route('shipments.show', $shipment) }}"
                                        class="rounded-xl bg-cyan-400 px-4 py-2.5 text-xs font-bold text-slate-950 transition hover:bg-cyan-300">
                                        View
                                    </a>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- Empty state --}}
                    <div class="px-6 py-20 text-center">

                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-400">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M20 13V7a2 2 0 00-1-1.73l-6-3.46a2 2 0 00-2 0L5 5.27A2 2 0 004 7v6a2 2 0 001 1.73l6 3.46a2 2 0 002 0l6-3.46A2 2 0 0020 13z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M4.27 6.96L12 11.5l7.73-4.54M12 11.5V19" />
                            </svg>
                        </div>

                        <h3 class="mt-5 text-lg font-extrabold text-slate-950 dark:text-white">
                            No shipments found
                        </h3>

                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400">
                            Your shipments will appear here once they are created and assigned to your account.
                        </p>

                        <a href="{{ route('quotations.create') }}"
                            class="mt-6 inline-flex items-center rounded-xl bg-cyan-400 px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-cyan-300">
                            Request a Quote
                        </a>
                    </div>

                @endif

                {{-- Pagination --}}
                @if ($shipments->hasPages())
                    <div class="border-t border-slate-200 px-6 py-5 dark:border-slate-800">
                        {{ $shipments->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
