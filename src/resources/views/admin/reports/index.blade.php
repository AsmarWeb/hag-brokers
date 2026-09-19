<x-admin-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                Reports & Analytics
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                HAG Brokers operational overview.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Main statistics --}}
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

                <div class="rounded-2xl bg-slate-900 p-6 text-white shadow">
                    <p class="text-sm text-slate-400">Customers</p>
                    <p class="mt-2 text-3xl font-bold">
                        {{ $stats['customers'] }}
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                    <p class="text-sm text-slate-500">Users</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                        {{ $stats['users'] }}
                    </p>
                </div>

                <div
                    class="rounded-2xl bg-white p-6 shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                    <p class="text-sm text-slate-500">Shipments</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                        {{ $stats['shipments'] }}
                    </p>
                </div>

                <div
                    class="rounded-2xl bg-white p-6 shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                    <p class="text-sm text-slate-500">Quotations</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                        {{ $stats['quotations'] }}
                    </p>
                </div>

                <div
                    class="rounded-2xl bg-white p-6 shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                    <p class="text-sm text-slate-500">Carriers</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                        {{ $stats['carriers'] }}
                    </p>
                </div>

                <div
                    class="rounded-2xl bg-white p-6 shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                    <p class="text-sm text-slate-500">Vessels</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                        {{ $stats['vessels'] }}
                    </p>
                </div>
            </div>

            {{-- Shipment status --}}
            <div class="mt-8">
                <h3 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">
                    Shipment Status
                </h3>

                <div class="grid gap-5 sm:grid-cols-3">

                    <div
                        class="rounded-2xl bg-amber-50 p-6 ring-1 ring-amber-200 dark:bg-amber-950/30 dark:ring-amber-900">
                        <p class="text-sm font-medium text-amber-700 dark:text-amber-300">
                            Pending
                        </p>
                        <p class="mt-2 text-3xl font-bold text-amber-900 dark:text-amber-200">
                            {{ $stats['pending_shipments'] }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-cyan-50 p-6 ring-1 ring-cyan-200 dark:bg-cyan-950/30 dark:ring-cyan-900">
                        <p class="text-sm font-medium text-cyan-700 dark:text-cyan-300">
                            In Transit
                        </p>
                        <p class="mt-2 text-3xl font-bold text-cyan-900 dark:text-cyan-200">
                            {{ $stats['in_transit_shipments'] }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl bg-emerald-50 p-6 ring-1 ring-emerald-200 dark:bg-emerald-950/30 dark:ring-emerald-900">
                        <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                            Delivered
                        </p>
                        <p class="mt-2 text-3xl font-bold text-emerald-900 dark:text-emerald-200">
                            {{ $stats['delivered_shipments'] }}
                        </p>
                    </div>

                </div>
            </div>

            {{-- Recent shipments --}}
            <div
                class="mt-8 overflow-hidden rounded-2xl bg-white shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">

                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white">
                        Recent Shipments
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th class="px-6 py-4 text-left">Reference</th>
                                <th class="px-6 py-4 text-left">Customer</th>
                                <th class="px-6 py-4 text-left">Route</th>
                                <th class="px-6 py-4 text-left">Carrier</th>
                                <th class="px-6 py-4 text-left">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($recentShipments as $shipment)
                                <tr>
                                    <td class="px-6 py-4 font-semibold text-slate-900 dark:text-white">
                                        {{ $shipment->reference_no ?? 'SH-' . $shipment->id }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $shipment->customer?->company_name ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $shipment->originPort?->name ?? '—' }}
                                        →
                                        {{ $shipment->destinationPort?->name ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $shipment->carrier?->name ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-700">
                                            {{ ucfirst(str_replace('_', ' ', $shipment->status ?? 'pending')) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                        No shipments available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent quotations --}}
            <div
                class="mt-8 overflow-hidden rounded-2xl bg-white shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">

                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white">
                        Recent Quotations
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th class="px-6 py-4 text-left">Reference</th>
                                <th class="px-6 py-4 text-left">Customer</th>
                                <th class="px-6 py-4 text-left">Origin</th>
                                <th class="px-6 py-4 text-left">Destination</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($recentQuotations as $quotation)
                                <tr>
                                    <td class="px-6 py-4 font-semibold text-slate-900 dark:text-white">
                                        {{ $quotation->reference_no }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $quotation->customer?->company_name ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $quotation->originPort?->name ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $quotation->destinationPort?->name ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                        No quotations available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-admin-layout>
