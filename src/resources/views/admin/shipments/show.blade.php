<x-admin-layout>

    <div class="space-y-6">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm text-slate-500">

            <a href="{{ route('admin.dashboard') }}" class="transition hover:text-slate-900">
                Dashboard
            </a>

            <span>/</span>

            <a href="{{ route('admin.shipments.index') }}" class="transition hover:text-slate-900">
                Shipments
            </a>

            <span>/</span>

            <span class="text-slate-900">
                {{ $shipment->shipment_no ?? 'Shipment' }}
            </span>

        </div>


        {{-- Header --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">

                <div>

                    <div class="flex flex-wrap items-center gap-3">

                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                            {{ $shipment->shipment_no ?? 'Shipment' }}
                        </h1>

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
                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClasses }}">
                            {{ $statusLabel }}
                        </span>

                    </div>

                    <p class="mt-2 text-sm text-slate-500">
                        Reference:
                        <span class="font-medium text-slate-700">
                            {{ $shipment->reference_no ?? '—' }}
                        </span>
                    </p>

                </div>


                <a href="{{ route('admin.shipments.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">

                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0 7-7m-7 7h18" />
                    </svg>

                    Back to Shipments
                </a>

            </div>

        </div>


        {{-- Route --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="mb-6">
                <h2 class="text-lg font-semibold text-slate-900">
                    Shipping Route
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Origin and destination information.
                </p>
            </div>


            <div class="grid grid-cols-1 gap-6 md:grid-cols-3 md:items-center">

                {{-- Origin --}}
                <div class="rounded-xl bg-slate-50 p-5">

                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        Origin Port
                    </p>

                    <p class="mt-2 text-lg font-semibold text-slate-900">
                        {{ $shipment->originPort->name ?? '—' }}
                    </p>

                    @if ($shipment->originPort)
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $shipment->originPort->code ?? '' }}
                        </p>
                    @endif

                </div>


                {{-- Arrow --}}
                <div class="flex justify-center">

                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-900 text-white">

                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 12h14m-6-6 6 6-6 6" />
                        </svg>

                    </div>

                </div>


                {{-- Destination --}}
                <div class="rounded-xl bg-slate-50 p-5">

                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        Destination Port
                    </p>

                    <p class="mt-2 text-lg font-semibold text-slate-900">
                        {{ $shipment->destinationPort->name ?? '—' }}
                    </p>

                    @if ($shipment->destinationPort)
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $shipment->destinationPort->code ?? '' }}
                        </p>
                    @endif

                </div>

            </div>

        </div>


        {{-- Shipment Information --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- General --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h2 class="text-lg font-semibold text-slate-900">
                    Shipment Information
                </h2>

                <div class="mt-5 divide-y divide-slate-100">

                    <div class="flex items-center justify-between gap-4 py-3">
                        <span class="text-sm text-slate-500">
                            Shipment Number
                        </span>

                        <span class="text-sm font-semibold text-slate-900">
                            {{ $shipment->shipment_no ?? '—' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4 py-3">
                        <span class="text-sm text-slate-500">
                            Reference Number
                        </span>

                        <span class="text-sm font-semibold text-slate-900">
                            {{ $shipment->reference_no ?? '—' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4 py-3">
                        <span class="text-sm text-slate-500">
                            Vessel
                        </span>

                        <span class="text-sm font-semibold text-slate-900">
                            {{ $shipment->vessel->name ?? '—' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4 py-3">
                        <span class="text-sm text-slate-500">
                            Status
                        </span>

                        <span class="text-sm font-semibold text-slate-900">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4 py-3">
                        <span class="text-sm text-slate-500">
                            Created
                        </span>

                        <span class="text-sm font-semibold text-slate-900">
                            {{ $shipment->created_at?->format('d M Y, H:i') ?? '—' }}
                        </span>
                    </div>

                </div>

            </div>


            {{-- Vessel --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h2 class="text-lg font-semibold text-slate-900">
                    Vessel Information
                </h2>

                @if ($shipment->vessel)

                    <div class="mt-5 space-y-4">

                        <div class="rounded-xl bg-slate-50 p-4">

                            <p class="text-xs uppercase tracking-wider text-slate-400">
                                Vessel Name
                            </p>

                            <p class="mt-1 font-semibold text-slate-900">
                                {{ $shipment->vessel->name }}
                            </p>

                        </div>

                        @if (isset($shipment->vessel->imo_number))
                            <div class="rounded-xl bg-slate-50 p-4">

                                <p class="text-xs uppercase tracking-wider text-slate-400">
                                    IMO Number
                                </p>

                                <p class="mt-1 font-semibold text-slate-900">
                                    {{ $shipment->vessel->imo_number }}
                                </p>

                            </div>
                        @endif

                    </div>
                @else
                    <div class="mt-5 rounded-xl bg-slate-50 p-5 text-sm text-slate-500">
                        No vessel information available.
                    </div>

                @endif

            </div>

        </div>


        {{-- Containers --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 px-6 py-5">

                <h2 class="text-lg font-semibold text-slate-900">
                    Containers
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Containers assigned to this shipment.
                </p>

            </div>


            @if ($shipment->containers->count())

                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-slate-200">

                        <thead class="bg-slate-50">

                            <tr>

                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Container
                                </th>

                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Type
                                </th>

                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Status
                                </th>

                            </tr>

                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            @foreach ($shipment->containers as $container)
                                <tr>

                                    <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                        {{ $container->container_no ?? ($container->number ?? '—') }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $container->type ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $container->status ?? '—' }}
                                    </td>

                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                </div>
            @else
                <div class="px-6 py-10 text-center text-sm text-slate-500">
                    No containers assigned to this shipment.
                </div>

            @endif

        </div>


        {{-- Documents --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 px-6 py-5">

                <h2 class="text-lg font-semibold text-slate-900">
                    Documents
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Documents related to this shipment.
                </p>

            </div>


            @if ($shipment->documents->count())

                <div class="divide-y divide-slate-100">

                    @foreach ($shipment->documents as $document)
                        <div class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">

                            <div class="flex items-center gap-3">

                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100">

                                    <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 3h8l4 4v14H7V3Zm8 0v5h5M9 13h6m-6 4h6" />
                                    </svg>

                                </div>

                                <div>

                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $document->name ?? ($document->title ?? 'Document') }}
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        {{ $document->type ?? 'Shipment document' }}
                                    </p>

                                </div>

                            </div>

                            @if (isset($document->file_path) && $document->file_path)
                                <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">

                                    View Document

                                </a>
                            @endif

                        </div>
                    @endforeach

                </div>
            @else
                <div class="px-6 py-10 text-center text-sm text-slate-500">
                    No documents available.
                </div>

            @endif

        </div>


        {{-- Shipment Events --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="mb-6">

                <h2 class="text-lg font-semibold text-slate-900">
                    Shipment Timeline
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Latest events and shipment updates.
                </p>

            </div>


            @if ($shipment->events->count())

                <div class="relative">

                    <div class="absolute left-4 top-2 bottom-2 w-px bg-slate-200"></div>

                    <div class="space-y-6">

                        @foreach ($shipment->events->sortByDesc('created_at') as $event)
                            <div class="relative flex gap-4">

                                <div
                                    class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-4 border-white bg-slate-900">

                                    <div class="h-2 w-2 rounded-full bg-white"></div>

                                </div>


                                <div class="min-w-0 flex-1 rounded-xl bg-slate-50 p-4">

                                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">

                                        <h3 class="text-sm font-semibold text-slate-900">
                                            {{ $event->status ?? ($event->event_type ?? 'Shipment Update') }}
                                        </h3>

                                        <time class="text-xs text-slate-500">
                                            {{ $event->created_at?->format('d M Y, H:i') ?? '—' }}
                                        </time>

                                    </div>

                                    @if (isset($event->description) && $event->description)
                                        <p class="mt-2 text-sm leading-6 text-slate-600">
                                            {{ $event->description }}
                                        </p>
                                    @endif

                                </div>

                            </div>
                        @endforeach

                    </div>

                </div>
            @else
                <div class="rounded-xl bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
                    No shipment events recorded yet.
                </div>

            @endif

        </div>

    </div>

</x-admin-layout>
