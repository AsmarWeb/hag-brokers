<x-admin-layout>
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900 transition">
                        Dashboard
                    </a>

                    <span>/</span>

                    <span class="text-slate-900">Shipments</span>
                </div>

                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                    Shipments
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Manage and monitor all sea freight shipments.
                </p>
            </div>
        </div>


        {{-- Filters --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <form method="GET" action="{{ route('admin.shipments.index') }}"
                class="grid grid-cols-1 gap-4 md:grid-cols-4">

                {{-- Search --}}
                <div class="md:col-span-2">
                    <label for="search" class="mb-2 block text-sm font-medium text-slate-700">
                        Search
                    </label>

                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg>

                        <input id="search" name="search" type="text" value="{{ request('search') }}"
                            placeholder="Shipment or reference number..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-100">
                    </div>
                </div>


                {{-- Status --}}
                <div>
                    <label for="status" class="mb-2 block text-sm font-medium text-slate-700">
                        Status
                    </label>

                    <select id="status" name="status"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-100">

                        <option value="">All statuses</option>

                        <option value="pending" @selected(request('status') === 'pending')}>
                            Pending
                        </option>

                        <option value="in_transit" @selected(request('status') === 'in_transit')}>
                            In Transit
                        </option>

                        <option value="delivered" @selected(request('status') === 'delivered')}>
                            Delivered
                        </option>

                        <option value="cancelled" @selected(request('status') === 'cancelled')}>
                            Cancelled
                        </option>
                    </select>
                </div>


                {{-- Buttons --}}
                <div class="flex items-end gap-2">

                    <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">

                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg>

                        Filter
                    </button>

                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.shipments.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">

                            Clear
                        </a>
                    @endif

                </div>

            </form>
        </div>


        {{-- Results --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Table header --}}
            <div
                class="flex flex-col gap-2 border-b border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="font-semibold text-slate-900">
                        Shipment List
                    </h2>

                    <p class="text-sm text-slate-500">
                        {{ $shipments->total() }}
                        {{ Str::plural('shipment', $shipments->total()) }}
                    </p>
                </div>

                <div class="text-sm text-slate-500">
                    Page {{ $shipments->currentPage() }}
                    of {{ $shipments->lastPage() }}
                </div>

            </div>


            {{-- Desktop table --}}
            <div class="hidden overflow-x-auto lg:block">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">
                        <tr>

                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Shipment
                            </th>

                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Route
                            </th>

                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Vessel
                            </th>

                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Status
                            </th>

                            <th
                                class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Action
                            </th>

                        </tr>
                    </thead>


                    <tbody class="divide-y divide-slate-100 bg-white">

                        @forelse($shipments as $shipment)
                            <tr class="transition hover:bg-slate-50">

                                {{-- Shipment --}}
                                <td class="whitespace-nowrap px-6 py-4">

                                    <div class="font-semibold text-slate-900">
                                        {{ $shipment->shipment_no ?? '—' }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">
                                        Ref:
                                        {{ $shipment->reference_no ?? '—' }}
                                    </div>

                                </td>


                                {{-- Route --}}
                                <td class="px-6 py-4">

                                    <div class="flex items-center gap-2 text-sm">

                                        <span class="font-medium text-slate-700">
                                            {{ $shipment->originPort->name ?? '—' }}
                                        </span>

                                        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 12h14m-6-6 6 6-6 6" />
                                        </svg>

                                        <span class="font-medium text-slate-700">
                                            {{ $shipment->destinationPort->name ?? '—' }}
                                        </span>

                                    </div>

                                </td>


                                {{-- Vessel --}}
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">

                                    {{ $shipment->vessel->name ?? '—' }}

                                </td>


                                {{-- Status --}}
                                <td class="whitespace-nowrap px-6 py-4">

                                    @php
                                        $status = $shipment->status ?? 'pending';

                                        $statusClasses = match ($status) {
                                            'delivered' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                            'in_transit' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                            'cancelled' => 'bg-red-50 text-red-700 ring-red-600/20',
                                            default => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                        };

                                        $statusLabel = match ($status) {
                                            'in_transit' => 'In Transit',
                                            'delivered' => 'Delivered',
                                            'cancelled' => 'Cancelled',
                                            'pending' => 'Pending',
                                            default => ucwords(str_replace('_', ' ', $status)),
                                        };
                                    @endphp

                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClasses }}">
                                        {{ $statusLabel }}
                                    </span>

                                </td>


                                {{-- Action --}}
                                <td class="whitespace-nowrap px-6 py-4 text-right">

                                    <a href="{{ route('admin.shipments.show', $shipment) }}"
                                        class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 hover:text-slate-900">

                                        View

                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="m9 18 6-6-6-6" />
                                        </svg>

                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">

                                    <div
                                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                                        <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 7h18M5 7v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        </svg>
                                    </div>

                                    <h3 class="mt-4 font-semibold text-slate-900">
                                        No shipments found
                                    </h3>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Try changing your search or filter.
                                    </p>

                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Mobile cards --}}
            <div class="divide-y divide-slate-100 lg:hidden">

                @forelse($shipments as $shipment)
                    @php
                        $status = $shipment->status ?? 'pending';

                        $statusClasses = match ($status) {
                            'delivered' => 'bg-emerald-50 text-emerald-700',
                            'in_transit' => 'bg-blue-50 text-blue-700',
                            'cancelled' => 'bg-red-50 text-red-700',
                            default => 'bg-amber-50 text-amber-700',
                        };

                        $statusLabel = match ($status) {
                            'in_transit' => 'In Transit',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled',
                            'pending' => 'Pending',
                            default => ucwords(str_replace('_', ' ', $status)),
                        };
                    @endphp

                    <a href="{{ route('admin.shipments.show', $shipment) }}"
                        class="block p-5 transition hover:bg-slate-50">

                        <div class="flex items-start justify-between gap-4">

                            <div>
                                <div class="font-semibold text-slate-900">
                                    {{ $shipment->shipment_no ?? '—' }}
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    Ref: {{ $shipment->reference_no ?? '—' }}
                                </div>
                            </div>

                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                                {{ $statusLabel }}
                            </span>

                        </div>


                        <div class="mt-4 flex items-center gap-2 text-sm text-slate-600">

                            <span>
                                {{ $shipment->originPort->name ?? '—' }}
                            </span>

                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 12h14m-6-6 6 6-6 6" />
                            </svg>

                            <span>
                                {{ $shipment->destinationPort->name ?? '—' }}
                            </span>

                        </div>


                        <div class="mt-3 text-xs text-slate-500">
                            Vessel:
                            <span class="font-medium text-slate-700">
                                {{ $shipment->vessel->name ?? '—' }}
                            </span>
                        </div>

                    </a>

                @empty

                    <div class="px-6 py-16 text-center text-sm text-slate-500">
                        No shipments found.
                    </div>
                @endforelse

            </div>


            {{-- Pagination --}}
            @if ($shipments->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $shipments->links() }}
                </div>
            @endif

        </div>

    </div>
</x-admin-layout>
