<x-admin-layout>

    <div class="mx-auto max-w-7xl">

        {{-- Header --}}
        <div class="mb-8 flex flex-col justify-between gap-4 lg:flex-row lg:items-start">

            <div>

                <div class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-cyan-400">
                    HAG Brokers / Shipment Details
                </div>

                <div class="flex flex-wrap items-center gap-3">

                    <h1 class="text-3xl font-black text-white">
                        {{ $shipment->shipment_no }}
                    </h1>

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

                    <span class="rounded-full border px-3 py-1 text-xs font-black {{ $statusClasses }}">
                        {{ ucwords(str_replace('_', ' ', $shipment->status)) }}
                    </span>

                </div>

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Operational shipment information and tracking overview.
                </p>

            </div>


            {{-- Actions --}}
            <div class="flex flex-wrap gap-3">

                <a href="{{ route('admin.shipments.edit', $shipment) }}"
                    class="inline-flex items-center justify-center rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950 transition hover:bg-cyan-300">
                    {{ __('admin_ui.label_049') }}
                </a>

                <a href="{{ route('admin.shipments.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-700 px-5 py-3 text-sm font-bold text-slate-300 transition hover:bg-slate-900 hover:text-white">
                    ← Back
                </a>

            </div>

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


        {{-- Main Shipment Summary --}}
        <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Customer --}}
            <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-6">

                <div class="mb-4 flex items-center justify-between">

                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-600">
                            {{ __('admin_ui.label_005') }}
                        </p>

                        <h2 class="mt-1 text-lg font-black text-white">
                            {{ __('admin_ui.label_005') }}
                        </h2>
                    </div>

                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-300">
                        ◉
                    </span>

                </div>

                @if ($shipment->customer)

                    <div class="font-bold text-slate-200">
                        {{ $shipment->customer->company_name }}
                    </div>

                    @if ($shipment->customer->contact_person)
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ $shipment->customer->contact_person }}
                        </div>
                    @endif

                    @if ($shipment->customer->email)
                        <div class="mt-3 text-sm text-slate-400">
                            {{ $shipment->customer->email }}
                        </div>
                    @endif

                    @if ($shipment->customer->phone)
                        <div class="mt-1 text-sm text-slate-400">
                            {{ $shipment->customer->phone }}
                        </div>
                    @endif
                @else
                    <span class="text-sm text-slate-600">
                        No customer assigned.
                    </span>

                @endif

            </div>


            {{-- Carrier --}}
            <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-6">

                <div class="mb-4 flex items-center justify-between">

                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-600">
                            {{ __('admin_ui.label_002') }}
                        </p>

                        <h2 class="mt-1 text-lg font-black text-white">
                            Shipping Partner
                        </h2>
                    </div>

                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-400/10 text-violet-300">
                        ⚓
                    </span>

                </div>

                @if ($shipment->carrier)

                    <div class="font-bold text-slate-200">
                        {{ $shipment->carrier->name }}
                    </div>

                    @if ($shipment->carrier->code)
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Code: {{ $shipment->carrier->code }}
                        </div>
                    @endif

                    @if ($shipment->carrier->country)
                        <div class="mt-3 text-sm text-slate-400">
                            {{ $shipment->carrier->country }}
                        </div>
                    @endif
                @else
                    <span class="text-sm text-slate-600">
                        No carrier assigned.
                    </span>

                @endif

            </div>


            {{-- Vessel --}}
            <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-6">

                <div class="mb-4 flex items-center justify-between">

                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-slate-600">
                            {{ __('admin_ui.label_024') }}
                        </p>

                        <h2 class="mt-1 text-lg font-black text-white">
                            Vessel Assignment
                        </h2>
                    </div>

                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-300">
                        🚢
                    </span>

                </div>

                @if ($shipment->vessel)

                    <div class="font-bold text-slate-200">
                        {{ $shipment->vessel->name }}
                    </div>

                    @if ($shipment->vessel->imo)
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            IMO: {{ $shipment->vessel->imo }}
                        </div>
                    @endif

                    @if ($shipment->vessel->mmsi)
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            MMSI: {{ $shipment->vessel->mmsi }}
                        </div>
                    @endif
                @else
                    <span class="text-sm text-slate-600">
                        No vessel assigned.
                    </span>

                @endif

            </div>

        </div>


        {{-- Route --}}
        <div class="mb-6 rounded-2xl border border-slate-800 bg-slate-950/60 p-6">

            <div class="mb-6">

                <p class="text-xs font-black uppercase tracking-wider text-cyan-400">
                    Shipping Route
                </p>

                <h2 class="mt-1 text-xl font-black text-white">
                    Origin → Destination
                </h2>

            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-[1fr_auto_1fr] md:items-center">

                {{-- Origin --}}
                <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-5">

                    <div class="text-xs font-black uppercase tracking-wider text-slate-600">
                        Origin Port
                    </div>

                    <div class="mt-2 text-xl font-black text-white">
                        {{ $shipment->originPort?->port_code ?? ($shipment->originPort?->name ?? '—') }}
                    </div>

                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $shipment->originPort?->name ?? 'Unknown origin' }}
                    </div>

                    @if ($shipment->originPort?->country)
                        <div class="mt-2 text-xs text-slate-600">
                            {{ $shipment->originPort->country }}
                        </div>
                    @endif

                </div>


                {{-- Arrow --}}
                <div class="flex items-center justify-center">

                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-full border border-cyan-400/20 bg-cyan-400/10 text-xl text-cyan-300">
                        →
                    </div>

                </div>


                {{-- Destination --}}
                <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-5">

                    <div class="text-xs font-black uppercase tracking-wider text-slate-600">
                        Destination Port
                    </div>

                    <div class="mt-2 text-xl font-black text-white">
                        {{ $shipment->destinationPort?->port_code ?? ($shipment->destinationPort?->name ?? '—') }}
                    </div>

                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $shipment->destinationPort?->name ?? 'Unknown destination' }}
                    </div>

                    @if ($shipment->destinationPort?->country)
                        <div class="mt-2 text-xs text-slate-600">
                            {{ $shipment->destinationPort->country }}
                        </div>
                    @endif

                </div>

            </div>

        </div>


        {{-- Shipment Information --}}
        <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- Booking --}}
            <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-6">

                <div class="mb-5">

                    <p class="text-xs font-black uppercase tracking-wider text-slate-600">
                        Booking Information
                    </p>

                    <h2 class="mt-1 text-xl font-black text-white">
                        Shipment Reference
                    </h2>

                </div>

                <div class="grid grid-cols-2 gap-x-6 gap-y-5">

                    <div>
                        <div class="text-xs text-slate-600">Shipment No.</div>
                        <div class="mt-1 font-bold text-slate-200">
                            {{ $shipment->shipment_no }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-slate-600">Booking No.</div>
                        <div class="mt-1 font-bold text-slate-200">
                            {{ $shipment->booking_no ?? '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-slate-600">{{ __('admin_ui.label_054') }}</div>
                        <div class="mt-1 font-bold text-slate-200">
                            {{ $shipment->quotation?->reference_no ?? '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-slate-600">{{ __('admin_ui.label_055') }}</div>
                        <div class="mt-1 font-bold text-slate-200">
                            {{ $shipment->quantity }}
                        </div>
                    </div>

                </div>

            </div>


            {{-- Cargo --}}
            <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-6">

                <div class="mb-5">

                    <p class="text-xs font-black uppercase tracking-wider text-slate-600">
                        {{ __('admin_ui.label_056') }}
                    </p>

                    <h2 class="mt-1 text-xl font-black text-white">
                        Cargo Information
                    </h2>

                </div>

                <div class="grid grid-cols-2 gap-x-6 gap-y-5">

                    <div>
                        <div class="text-xs text-slate-600">Cargo Type</div>
                        <div class="mt-1 font-bold text-slate-200">
                            {{ $shipment->cargo_type ?? '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-slate-600">Container Type</div>
                        <div class="mt-1 font-bold text-slate-200">
                            {{ $shipment->container_type ?? '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-slate-600">{{ __('admin_ui.label_055') }}</div>
                        <div class="mt-1 font-bold text-slate-200">
                            {{ $shipment->quantity }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-slate-600">Weight</div>
                        <div class="mt-1 font-bold text-slate-200">
                            {{ $shipment->weight !== null ? number_format((float) $shipment->weight, 2) : '—' }}
                        </div>
                    </div>

                </div>

            </div>

        </div>


        {{-- Schedule --}}
        <div class="mb-6 rounded-2xl border border-slate-800 bg-slate-950/60 p-6">

            <div class="mb-6">

                <p class="text-xs font-black uppercase tracking-wider text-cyan-400">
                    {{ __('admin_ui.label_051') }}
                </p>

                <h2 class="mt-1 text-xl font-black text-white">
                    Shipment Timeline
                </h2>

            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">

                {{-- Preferred --}}
                <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">

                    <div class="text-xs text-slate-600">
                        Preferred Date
                    </div>

                    <div class="mt-2 font-bold text-slate-300">
                        {{ $shipment->preferred_date?->format('d M Y') ?? '—' }}
                    </div>

                </div>


                {{-- ETD --}}
                <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">

                    <div class="text-xs text-slate-600">
                        {{ __('admin_ui.label_020') }}
                    </div>

                    <div class="mt-2 font-bold text-slate-300">
                        {{ $shipment->etd?->format('d M Y H:i') ?? '—' }}
                    </div>

                </div>


                {{-- ETA --}}
                <div class="rounded-xl border border-cyan-400/10 bg-cyan-400/5 p-4">

                    <div class="text-xs text-cyan-400/70">
                        {{ __('admin_ui.label_019') }}
                    </div>

                    <div class="mt-2 font-bold text-cyan-300">
                        {{ $shipment->eta?->format('d M Y H:i') ?? '—' }}
                    </div>

                </div>


                {{-- Actual Departure --}}
                <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">

                    <div class="text-xs text-slate-600">
                        Actual Departure
                    </div>

                    <div class="mt-2 font-bold text-slate-300">
                        {{ $shipment->actual_departure?->format('d M Y H:i') ?? '—' }}
                    </div>

                </div>


                {{-- Actual Arrival --}}
                <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">

                    <div class="text-xs text-slate-600">
                        Actual Arrival
                    </div>

                    <div class="mt-2 font-bold text-emerald-300">
                        {{ $shipment->actual_arrival?->format('d M Y H:i') ?? '—' }}
                    </div>

                </div>

            </div>

        </div>


        {{-- Containers --}}
        <div class="mb-6 rounded-2xl border border-slate-800 bg-slate-950/60">

            <div class="flex flex-col justify-between gap-4 border-b border-slate-800 p-6 sm:flex-row sm:items-center">

                <div>

                    <p class="text-xs font-black uppercase tracking-wider text-slate-600">
                        Equipment
                    </p>

                    <h2 class="mt-1 text-xl font-black text-white">
                        Containers
                    </h2>

                </div>

                <div
                    class="rounded-full border border-slate-700 bg-slate-900 px-3 py-1 text-xs font-bold text-slate-400">
                    {{ $shipment->containers->count() }} containers
                </div>

            </div>

            @if ($shipment->containers->count())

                <div class="overflow-x-auto">

                    <table class="w-full min-w-[700px]">

                        <thead class="border-b border-slate-800">

                            <tr>

                                <th
                                    class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                    Container
                                </th>

                                <th
                                    class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                    {{ __('admin_ui.label_035') }}
                                </th>

                                <th
                                    class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                    Size
                                </th>

                                <th
                                    class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                    Seal
                                </th>

                                <th
                                    class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                    {{ __('admin_ui.label_001') }}
                                </th>

                            </tr>

                        </thead>

                        <tbody class="divide-y divide-slate-800">

                            @foreach ($shipment->containers as $container)
                                <tr class="hover:bg-slate-900/40">

                                    <td class="px-6 py-4 font-bold text-white">
                                        {{ $container->container_no }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-400">
                                        {{ $container->type ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-400">
                                        {{ $container->size ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-400">
                                        {{ $container->seal_no ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4">

                                        <span
                                            class="rounded-full border border-slate-700 bg-slate-900 px-3 py-1 text-xs font-bold text-slate-300">
                                            {{ ucwords(str_replace('_', ' ', $container->status ?? 'pending')) }}
                                        </span>

                                    </td>

                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                </div>
            @else
                <div class="p-10 text-center">

                    <div
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-900 text-slate-600">
                        □
                    </div>

                    <p class="mt-3 text-sm font-bold text-slate-500 dark:text-slate-400">
                        No containers assigned to this shipment.
                    </p>

                </div>

            @endif

        </div>


        {{-- Tracking Events --}}
        <div class="mb-6 rounded-2xl border border-slate-800 bg-slate-950/60 p-6">

            <div class="mb-6">

                <p class="text-xs font-black uppercase tracking-wider text-cyan-400">
                    Tracking
                </p>

                <h2 class="mt-1 text-xl font-black text-white">
                    Shipment Events
                </h2>

            </div>

            @if ($shipment->events->count())

                <div class="relative">

                    <div class="absolute bottom-0 start-[15px] top-0 w-px bg-slate-800"></div>

                    <div class="space-y-6">

                        @foreach ($shipment->events->sortByDesc('event_date') as $event)
                            <div class="relative flex gap-5">

                                <div
                                    class="relative z-10 mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-cyan-400/20 bg-slate-950 text-cyan-300">
                                    ●
                                </div>

                                <div class="min-w-0 flex-1 rounded-xl border border-slate-800 bg-slate-900/50 p-4">

                                    <div class="flex flex-col justify-between gap-2 sm:flex-row">

                                        <div>

                                            <h3 class="font-black text-white">
                                                {{ ucwords(str_replace('_', ' ', $event->event_type)) }}
                                            </h3>

                                            @if ($event->location)
                                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                                    {{ $event->location }}
                                                </p>
                                            @endif

                                        </div>

                                        <div class="shrink-0 text-xs font-bold text-slate-600">
                                            {{ $event->event_date?->format('d M Y H:i') ?? '—' }}
                                        </div>

                                    </div>

                                    @if ($event->description)
                                        <p class="mt-3 text-sm leading-6 text-slate-400">
                                            {{ $event->description }}
                                        </p>
                                    @endif

                                    @if ($event->port)
                                        <div class="mt-3 text-xs text-slate-600">
                                            Port:
                                            <span class="font-bold text-slate-500 dark:text-slate-400">
                                                {{ $event->port->name }}
                                            </span>
                                        </div>
                                    @endif

                                </div>

                            </div>
                        @endforeach

                    </div>

                </div>
            @else
                <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-8 text-center">

                    <p class="text-sm font-bold text-slate-500 dark:text-slate-400">
                        No tracking events recorded yet.
                    </p>

                </div>

            @endif

        </div>


        {{-- Documents --}}
        <div class="mb-6 rounded-2xl border border-slate-800 bg-slate-950/60">

            <div class="border-b border-slate-800 p-6">

                <p class="text-xs font-black uppercase tracking-wider text-slate-600">
                    Files
                </p>

                <h2 class="mt-1 text-xl font-black text-white">
                    Shipment Documents
                </h2>

            </div>

            @if ($shipment->documents->count())

                <div class="divide-y divide-slate-800">

                    @foreach ($shipment->documents as $document)
                        <div class="flex flex-col justify-between gap-4 p-5 sm:flex-row sm:items-center">

                            <div class="flex items-center gap-4">

                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-cyan-300">
                                    📄
                                </div>

                                <div>

                                    <div class="font-bold text-slate-200">
                                        {{ $document->name }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-600">
                                        {{ $document->type }}
                                        @if ($document->original_name)
                                            · {{ $document->original_name }}
                                        @endif
                                    </div>

                                </div>

                            </div>

                            @if ($document->file_path)
                                <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank"
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-700 px-4 py-2 text-sm font-bold text-slate-300 transition hover:bg-slate-900 hover:text-white">
                                    Open
                                </a>
                            @endif

                        </div>
                    @endforeach

                </div>
            @else
                <div class="p-8 text-center">

                    <p class="text-sm font-bold text-slate-500 dark:text-slate-400">
                        No documents uploaded for this shipment.
                    </p>

                </div>

            @endif

        </div>


        {{-- Record Metadata --}}
        <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-6">

            <div class="mb-5">

                <p class="text-xs font-black uppercase tracking-wider text-slate-600">
                    Record
                </p>

                <h2 class="mt-1 text-xl font-black text-white">
                    System Information
                </h2>

            </div>

            <div class="grid grid-cols-2 gap-5 md:grid-cols-4">

                <div>
                    <div class="text-xs text-slate-600">
                        ID
                    </div>

                    <div class="mt-1 font-bold text-slate-300">
                        #{{ $shipment->id }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-slate-600">
                        {{ __('admin_ui.label_018') }}
                    </div>

                    <div class="mt-1 font-bold text-slate-300">
                        {{ $shipment->created_at?->format('d M Y H:i') ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-slate-600">
                        Updated
                    </div>

                    <div class="mt-1 font-bold text-slate-300">
                        {{ $shipment->updated_at?->format('d M Y H:i') ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-slate-600">
                        {{ __('admin_ui.label_001') }}
                    </div>

                    <div class="mt-1 font-bold text-slate-300">
                        {{ ucwords(str_replace('_', ' ', $shipment->status)) }}
                    </div>
                </div>

            </div>

        </div>

    </div>

</x-admin-layout>
