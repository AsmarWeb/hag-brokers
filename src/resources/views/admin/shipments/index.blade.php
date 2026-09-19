<x-admin-layout>
    <div class="mx-auto max-w-7xl">

        {{-- Header --}}
        <div class="mb-8 flex flex-col justify-between gap-4 lg:flex-row lg:items-center">

            <div>
                <div class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-cyan-400">
                    HAG Brokers / Operations
                </div>

                <h1 class="text-3xl font-black text-white">
                    Shipments
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                    Manage customer shipments, routes, carriers, vessels and
                    operational shipment status.
                </p>
            </div>

            <a href="{{ route('admin.shipments.create') }}"
                class="inline-flex items-center justify-center rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/10 transition hover:bg-cyan-300">
                + Create Shipment
            </a>

        </div>


        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-400/20 bg-emerald-400/5 p-4">
                <div class="flex items-center gap-3 text-sm font-bold text-emerald-300">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-400/10">
                        ✓
                    </span>

                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-2xl border border-red-400/20 bg-red-400/5 p-4">
                <div class="flex items-center gap-3 text-sm font-bold text-red-300">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-400/10">
                        !
                    </span>

                    {{ session('error') }}
                </div>
            </div>
        @endif


        {{-- Filters --}}
        <div class="mb-6 rounded-2xl border border-slate-800 bg-slate-950/60 p-5">

            <form action="{{ route('admin.shipments.index') }}" method="GET"
                class="grid grid-cols-1 gap-4 md:grid-cols-3">

                {{-- Search --}}
                <div class="md:col-span-2">

                    <label for="search" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                        Search
                    </label>

                    <input id="search" type="text" name="search" value="{{ request('search') }}"
                        placeholder="Shipment number, booking, customer, carrier or vessel..."
                        class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">

                </div>


                {{-- Status --}}
                <div>

                    <label for="status" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                        Status
                    </label>

                    <select id="status" name="status"
                        class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">

                        <option value="">
                            All Statuses
                        </option>

                        @foreach ([
        'pending' => 'Pending',
        'booked' => 'Booked',
        'confirmed' => 'Confirmed',
        'in_transit' => 'In Transit',
        'arrived' => 'Arrived',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach

                    </select>

                </div>


                {{-- Buttons --}}
                <div class="flex flex-wrap items-end gap-3 md:col-span-3">

                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950 transition hover:bg-cyan-300">
                        Search
                    </button>

                    <a href="{{ route('admin.shipments.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-700 px-5 py-3 text-sm font-bold text-slate-300 transition hover:bg-slate-900 hover:text-white">
                        Reset
                    </a>

                </div>

            </form>

        </div>


        {{-- Desktop Table --}}
        <div class="hidden overflow-hidden rounded-2xl border border-slate-800 bg-slate-950/60 md:block">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[1100px] text-left">

                    <thead class="border-b border-slate-800 bg-slate-900/70">

                        <tr>

                            <th class="px-5 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                                Shipment
                            </th>

                            <th class="px-5 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                                Customer
                            </th>

                            <th class="px-5 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                                Route
                            </th>

                            <th class="px-5 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                                Carrier / Vessel
                            </th>

                            <th class="px-5 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                                Schedule
                            </th>

                            <th class="px-5 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                                Status
                            </th>

                            <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-800">

                        @forelse($shipments as $shipment)

                            <tr class="transition hover:bg-slate-900/50">

                                {{-- Shipment --}}
                                <td class="px-5 py-5 align-top">

                                    <div class="font-black text-white">
                                        {{ $shipment->shipment_no }}
                                    </div>

                                    @if ($shipment->booking_no)
                                        <div class="mt-1 text-xs text-slate-500">
                                            Booking:
                                            <span class="text-slate-400">
                                                {{ $shipment->booking_no }}
                                            </span>
                                        </div>
                                    @endif

                                    <div class="mt-2 text-xs text-slate-600">
                                        #{{ $shipment->id }}
                                    </div>

                                </td>


                                {{-- Customer --}}
                                <td class="px-5 py-5 align-top">

                                    @if ($shipment->customer)
                                        <div class="font-bold text-slate-200">
                                            {{ $shipment->customer->company_name }}
                                        </div>

                                        @if ($shipment->customer->contact_person)
                                            <div class="mt-1 text-xs text-slate-500">
                                                {{ $shipment->customer->contact_person }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-sm text-slate-600">
                                            No customer
                                        </span>
                                    @endif

                                </td>


                                {{-- Route --}}
                                <td class="px-5 py-5 align-top">

                                    <div class="flex items-center gap-2">

                                        <span class="font-bold text-slate-200">
                                            {{ $shipment->originPort?->port_code ?? ($shipment->originPort?->name ?? '—') }}
                                        </span>

                                        <span class="text-cyan-400">
                                            →
                                        </span>

                                        <span class="font-bold text-slate-200">
                                            {{ $shipment->destinationPort?->port_code ?? ($shipment->destinationPort?->name ?? '—') }}
                                        </span>

                                    </div>

                                    <div class="mt-2 text-xs text-slate-500">

                                        {{ $shipment->originPort?->name ?? 'Unknown origin' }}

                                        <span class="mx-1 text-slate-700">
                                            /
                                        </span>

                                        {{ $shipment->destinationPort?->name ?? 'Unknown destination' }}

                                    </div>

                                </td>


                                {{-- Carrier / Vessel --}}
                                <td class="px-5 py-5 align-top">

                                    @if ($shipment->carrier)
                                        <div class="font-bold text-slate-200">
                                            {{ $shipment->carrier->name }}
                                        </div>
                                    @else
                                        <div class="text-sm text-slate-600">
                                            No carrier
                                        </div>
                                    @endif

                                    @if ($shipment->vessel)
                                        <div class="mt-1 flex items-center gap-2 text-xs text-slate-500">

                                            <span class="text-cyan-400">
                                                ⚓
                                            </span>

                                            {{ $shipment->vessel->name }}

                                        </div>
                                    @endif

                                </td>


                                {{-- Schedule --}}
                                <td class="px-5 py-5 align-top">

                                    @if ($shipment->etd)
                                        <div class="text-xs text-slate-500">
                                            ETD
                                        </div>

                                        <div class="font-bold text-slate-300">
                                            {{ $shipment->etd->format('d M Y H:i') }}
                                        </div>
                                    @elseif($shipment->preferred_date)
                                        <div class="text-xs text-slate-500">
                                            Preferred
                                        </div>

                                        <div class="font-bold text-slate-300">
                                            {{ $shipment->preferred_date->format('d M Y') }}
                                        </div>
                                    @else
                                        <span class="text-sm text-slate-600">
                                            No schedule
                                        </span>
                                    @endif

                                    @if ($shipment->eta)
                                        <div class="mt-2 text-xs text-slate-500">
                                            ETA
                                        </div>

                                        <div class="text-xs font-bold text-cyan-300">
                                            {{ $shipment->eta->format('d M Y H:i') }}
                                        </div>
                                    @endif

                                </td>


                                {{-- Status --}}
                                <td class="px-5 py-5 align-top">

                                    @php
                                        $statusClasses = match ($shipment->status) {
                                            'pending' => 'border-amber-400/20 bg-amber-400/10 text-amber-300',
                                            'booked' => 'border-blue-400/20 bg-blue-400/10 text-blue-300',
                                            'confirmed' => 'border-cyan-400/20 bg-cyan-400/10 text-cyan-300',
                                            'in_transit' => 'border-violet-400/20 bg-violet-400/10 text-violet-300',
                                            'arrived' => 'border-indigo-400/20 bg-indigo-400/10 text-indigo-300',
                                            'delivered' => 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300',
                                            'cancelled' => 'border-red-400/20 bg-red-400/10 text-red-300',
                                            default => 'border-slate-700 bg-slate-800 text-slate-300',
                                        };
                                    @endphp

                                    <span
                                        class="inline-flex rounded-full border px-3 py-1 text-xs font-black {{ $statusClasses }}">
                                        {{ ucwords(str_replace('_', ' ', $shipment->status)) }}
                                    </span>

                                </td>


                                {{-- Actions --}}
                                <td class="px-5 py-5 align-top">

                                    <div class="flex items-center justify-end gap-2">

                                        <a href="{{ route('admin.shipments.show', $shipment) }}" title="View"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-700 text-slate-400 transition hover:border-cyan-400/30 hover:bg-cyan-400/10 hover:text-cyan-300">
                                            →
                                        </a>

                                        <a href="{{ route('admin.shipments.edit', $shipment) }}" title="Edit"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-700 text-slate-400 transition hover:border-amber-400/30 hover:bg-amber-400/10 hover:text-amber-300">
                                            ✎
                                        </a>

                                        <form action="{{ route('admin.shipments.destroy', $shipment) }}" method="POST"
                                            onsubmit="return confirm('Delete this shipment?');">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" title="Delete"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-700 text-slate-400 transition hover:border-red-400/30 hover:bg-red-400/10 hover:text-red-300">
                                                ×
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7" class="px-6 py-16 text-center">

                                    <div class="mx-auto flex max-w-md flex-col items-center">

                                        <div
                                            class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-slate-800 bg-slate-900 text-2xl">
                                            ⚓
                                        </div>

                                        <h3 class="text-lg font-black text-white">
                                            No shipments found
                                        </h3>

                                        <p class="mt-2 text-sm leading-6 text-slate-500">
                                            No shipments match your current search
                                            or filter.
                                        </p>

                                        <a href="{{ route('admin.shipments.create') }}"
                                            class="mt-5 inline-flex items-center rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950 transition hover:bg-cyan-300">
                                            + Create Shipment
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- Mobile Cards --}}
        <div class="space-y-4 md:hidden">

            @forelse($shipments as $shipment)
                <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-5">

                    {{-- Header --}}
                    <div class="flex items-start justify-between gap-4">

                        <div>

                            <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                                Shipment
                            </div>

                            <div class="mt-1 font-black text-white">
                                {{ $shipment->shipment_no }}
                            </div>

                            @if ($shipment->booking_no)
                                <div class="mt-1 text-xs text-slate-500">
                                    Booking: {{ $shipment->booking_no }}
                                </div>
                            @endif

                        </div>

                        @php
                            $mobileStatusClasses = match ($shipment->status) {
                                'pending' => 'border-amber-400/20 bg-amber-400/10 text-amber-300',
                                'booked' => 'border-blue-400/20 bg-blue-400/10 text-blue-300',
                                'confirmed' => 'border-cyan-400/20 bg-cyan-400/10 text-cyan-300',
                                'in_transit' => 'border-violet-400/20 bg-violet-400/10 text-violet-300',
                                'arrived' => 'border-indigo-400/20 bg-indigo-400/10 text-indigo-300',
                                'delivered' => 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300',
                                'cancelled' => 'border-red-400/20 bg-red-400/10 text-red-300',
                                default => 'border-slate-700 bg-slate-800 text-slate-300',
                            };
                        @endphp

                        <span
                            class="shrink-0 rounded-full border px-3 py-1 text-[11px] font-black {{ $mobileStatusClasses }}">
                            {{ ucwords(str_replace('_', ' ', $shipment->status)) }}
                        </span>

                    </div>


                    {{-- Customer --}}
                    <div class="mt-5 border-t border-slate-800 pt-4">

                        <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                            Customer
                        </div>

                        <div class="mt-1 font-bold text-slate-200">
                            {{ $shipment->customer?->company_name ?? '—' }}
                        </div>

                    </div>


                    {{-- Route --}}
                    <div class="mt-4">

                        <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                            Route
                        </div>

                        <div class="mt-2 flex items-center gap-2">

                            <span class="font-black text-white">
                                {{ $shipment->originPort?->port_code ?? ($shipment->originPort?->name ?? '—') }}
                            </span>

                            <span class="text-cyan-400">
                                →
                            </span>

                            <span class="font-black text-white">
                                {{ $shipment->destinationPort?->port_code ?? ($shipment->destinationPort?->name ?? '—') }}
                            </span>

                        </div>

                    </div>


                    {{-- Carrier --}}
                    <div class="mt-4 grid grid-cols-2 gap-4">

                        <div>

                            <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                                Carrier
                            </div>

                            <div class="mt-1 text-sm font-bold text-slate-300">
                                {{ $shipment->carrier?->name ?? '—' }}
                            </div>

                        </div>

                        <div>

                            <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                                Vessel
                            </div>

                            <div class="mt-1 text-sm font-bold text-slate-300">
                                {{ $shipment->vessel?->name ?? '—' }}
                            </div>

                        </div>

                    </div>


                    {{-- Schedule --}}
                    <div class="mt-4 grid grid-cols-2 gap-4">

                        <div>

                            <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                                ETD
                            </div>

                            <div class="mt-1 text-sm font-bold text-slate-300">
                                {{ $shipment->etd?->format('d M Y') ?? '—' }}
                            </div>

                        </div>

                        <div>

                            <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                                ETA
                            </div>

                            <div class="mt-1 text-sm font-bold text-cyan-300">
                                {{ $shipment->eta?->format('d M Y') ?? '—' }}
                            </div>

                        </div>

                    </div>


                    {{-- Actions --}}
                    <div class="mt-5 flex gap-2 border-t border-slate-800 pt-4">

                        <a href="{{ route('admin.shipments.show', $shipment) }}"
                            class="flex-1 rounded-xl border border-slate-700 px-4 py-3 text-center text-sm font-bold text-slate-300 transition hover:bg-slate-900 hover:text-white">
                            View
                        </a>

                        <a href="{{ route('admin.shipments.edit', $shipment) }}"
                            class="flex-1 rounded-xl border border-amber-400/20 bg-amber-400/5 px-4 py-3 text-center text-sm font-bold text-amber-300 transition hover:bg-amber-400/10">
                            Edit
                        </a>

                        <form action="{{ route('admin.shipments.destroy', $shipment) }}" method="POST"
                            onsubmit="return confirm('Delete this shipment?');" class="flex-1">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="w-full rounded-xl border border-red-400/20 bg-red-400/5 px-4 py-3 text-sm font-bold text-red-300 transition hover:bg-red-400/10">
                                Delete
                            </button>

                        </form>

                    </div>

                </div>

            @empty

                <div class="rounded-2xl border border-slate-800 bg-slate-950/60 px-6 py-16 text-center">

                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-slate-800 bg-slate-900 text-2xl">
                        ⚓
                    </div>

                    <h3 class="mt-4 text-lg font-black text-white">
                        No shipments found
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        No shipments match your current filters.
                    </p>

                </div>
            @endforelse

        </div>


        {{-- Pagination --}}
        @if ($shipments->hasPages())
            <div class="mt-6 rounded-2xl border border-slate-800 bg-slate-950/60 p-4">

                {{ $shipments->links() }}

            </div>
        @endif

    </div>
</x-admin-layout>
