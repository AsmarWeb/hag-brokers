<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <div class="flex items-center gap-3">

                    <a href="{{ route('shipments.index') }}"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-cyan-400 hover:text-cyan-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>

                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">
                            Shipment Details
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ $shipment->reference_no ?? '#' . $shipment->id }}
                        </p>
                    </div>

                </div>
            </div>

            <div class="flex flex-wrap gap-2">

                <a href="{{ route('shipments.tracking', $shipment) }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-cyan-400 hover:text-cyan-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 20l-5-5 5-5M4 15h11a4 4 0 004-4V4" />
                    </svg>

                    Track Shipment
                </a>

                <a href="{{ route('shipments.documents.index', $shipment) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-cyan-400 px-4 py-2.5 text-sm font-bold text-slate-950 shadow-lg shadow-cyan-400/20 transition hover:bg-cyan-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h10M7 11h10M7 15h6M5 3h10l4 4v14H5V3z" />
                    </svg>

                    Documents
                </a>

            </div>
        </div>
    </x-slot>


    <div class="min-h-screen bg-slate-50 py-8 dark:bg-slate-950">

        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Shipment Hero --}}
            <div class="relative overflow-hidden rounded-3xl bg-slate-950 p-6 shadow-xl sm:p-8">

                <div class="absolute -right-20 -top-24 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl"></div>
                <div class="absolute -bottom-32 -left-20 h-72 w-72 rounded-full bg-blue-500/10 blur-3xl"></div>

                <div class="relative">

                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

                        <div>
                            <div
                                class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-cyan-400">
                                <span class="h-2 w-2 rounded-full bg-cyan-400"></span>
                                HAG Brokers
                            </div>

                            <h1 class="mt-4 text-3xl font-black tracking-tight text-white sm:text-4xl">
                                {{ $shipment->reference_no ?? 'Shipment #' . $shipment->id }}
                            </h1>

                            <p class="mt-2 text-sm text-slate-400">
                                Sea Freight Shipment
                            </p>
                        </div>

                        @php
                            $status = $shipment->status;

                            $statusClasses = match ($status) {
                                'delivered' => 'bg-emerald-400/15 text-emerald-300 ring-emerald-400/20',
                                'in_transit' => 'bg-cyan-400/15 text-cyan-300 ring-cyan-400/20',
                                'pending' => 'bg-amber-400/15 text-amber-300 ring-amber-400/20',
                                default => 'bg-white/10 text-slate-300 ring-white/10',
                            };
                        @endphp

                        <div>
                            <span
                                class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-bold ring-1 ring-inset {{ $statusClasses }}">
                                <span class="h-2 w-2 rounded-full bg-current"></span>
                                {{ str($status)->replace('_', ' ')->title() }}
                            </span>
                        </div>

                    </div>

                    {{-- Route --}}
                    <div class="mt-8 grid gap-4 md:grid-cols-[1fr_auto_1fr] md:items-center">

                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                Origin
                            </div>

                            <div class="mt-2 text-xl font-black text-white">
                                {{ $shipment->originPort->name ?? 'Not available' }}
                            </div>
                        </div>

                        <div class="hidden text-cyan-400 md:block">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-width="2" d="M5 12h14m-6-6l6 6-6 6" />
                            </svg>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                Destination
                            </div>

                            <div class="mt-2 text-xl font-black text-white">
                                {{ $shipment->destinationPort->name ?? 'Not available' }}
                            </div>
                        </div>

                    </div>

                </div>
            </div>


            {{-- Information cards --}}
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Vessel
                    </div>

                    <div class="mt-2 font-extrabold text-slate-950 dark:text-white">
                        {{ $shipment->vessel->name ?? 'Not assigned' }}
                    </div>

                    @if ($shipment->vessel && isset($shipment->vessel->imo))
                        <div class="mt-1 text-xs text-slate-400">
                            IMO {{ $shipment->vessel->imo }}
                        </div>
                    @endif
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Shipment Type
                    </div>

                    <div class="mt-2 font-extrabold text-slate-950 dark:text-white">
                        {{ $shipment->type ?? 'Sea Freight' }}
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Created
                    </div>

                    <div class="mt-2 font-extrabold text-slate-950 dark:text-white">
                        {{ $shipment->created_at?->format('d M Y') ?? '—' }}
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Last Updated
                    </div>

                    <div class="mt-2 font-extrabold text-slate-950 dark:text-white">
                        {{ $shipment->updated_at?->diffForHumans() ?? '—' }}
                    </div>
                </div>

            </div>


            {{-- Main content --}}
            <div class="grid gap-6 lg:grid-cols-3">

                {{-- Shipment details --}}
                <div
                    class="lg:col-span-2 rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                        <h2 class="font-extrabold text-slate-950 dark:text-white">
                            Shipment Information
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Detailed information about this shipment.
                        </p>
                    </div>

                    <div class="grid gap-px bg-slate-200 dark:bg-slate-800 sm:grid-cols-2">

                        <div class="bg-white p-6 dark:bg-slate-900">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Reference Number
                            </div>

                            <div class="mt-2 font-bold text-slate-950 dark:text-white">
                                {{ $shipment->reference_no ?? '—' }}
                            </div>
                        </div>

                        <div class="bg-white p-6 dark:bg-slate-900">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Status
                            </div>

                            <div class="mt-2 font-bold text-slate-950 dark:text-white">
                                {{ str($shipment->status)->replace('_', ' ')->title() }}
                            </div>
                        </div>

                        <div class="bg-white p-6 dark:bg-slate-900">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Origin Port
                            </div>

                            <div class="mt-2 font-bold text-slate-950 dark:text-white">
                                {{ $shipment->originPort->name ?? '—' }}
                            </div>
                        </div>

                        <div class="bg-white p-6 dark:bg-slate-900">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Destination Port
                            </div>

                            <div class="mt-2 font-bold text-slate-950 dark:text-white">
                                {{ $shipment->destinationPort->name ?? '—' }}
                            </div>
                        </div>

                        <div class="bg-white p-6 dark:bg-slate-900">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Vessel
                            </div>

                            <div class="mt-2 font-bold text-slate-950 dark:text-white">
                                {{ $shipment->vessel->name ?? '—' }}
                            </div>
                        </div>

                        <div class="bg-white p-6 dark:bg-slate-900">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Shipment ID
                            </div>

                            <div class="mt-2 font-bold text-slate-950 dark:text-white">
                                #{{ $shipment->id }}
                            </div>
                        </div>

                    </div>

                </div>


                {{-- Actions --}}
                <div
                    class="rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                        <h2 class="font-extrabold text-slate-950 dark:text-white">
                            Shipment Actions
                        </h2>
                    </div>

                    <div class="space-y-3 p-6">

                        <a href="{{ route('shipments.tracking', $shipment) }}"
                            class="flex items-center gap-4 rounded-2xl border border-slate-200 p-4 transition hover:border-cyan-400 hover:bg-cyan-50/50 dark:border-slate-800 dark:hover:bg-cyan-400/5">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 20l-5-5 5-5M4 15h11a4 4 0 004-4V4" />
                                </svg>
                            </div>

                            <div>
                                <div class="font-bold text-slate-950 dark:text-white">
                                    Track Shipment
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    View shipment movement
                                </div>
                            </div>
                        </a>


                        <a href="{{ route('shipments.documents.index', $shipment) }}"
                            class="flex items-center gap-4 rounded-2xl border border-slate-200 p-4 transition hover:border-cyan-400 hover:bg-cyan-50/50 dark:border-slate-800 dark:hover:bg-cyan-400/5">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h10M7 11h10M7 15h6M5 3h10l4 4v14H5V3z" />
                                </svg>
                            </div>

                            <div>
                                <div class="font-bold text-slate-950 dark:text-white">
                                    Documents
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    View shipment documents
                                </div>
                            </div>
                        </a>

                    </div>
                </div>

            </div>


            {{-- Containers --}}
            @if ($shipment->containers && $shipment->containers->count())

                <div
                    class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                        <h2 class="font-extrabold text-slate-950 dark:text-white">
                            Containers
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Containers associated with this shipment.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">

                            <thead class="bg-slate-50 dark:bg-slate-950/50">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Container
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Type
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Status
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">

                                @foreach ($shipment->containers as $container)
                                    <tr>
                                        <td class="px-6 py-4 font-semibold text-slate-900 dark:text-white">
                                            {{ $container->container_no ?? ($container->number ?? '—') }}
                                        </td>

                                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">
                                            {{ $container->type ?? '—' }}
                                        </td>

                                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">
                                            {{ isset($container->status) ? str($container->status)->replace('_', ' ')->title() : '—' }}
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>

                        </table>
                    </div>

                </div>

            @endif


            {{-- Events --}}
            @if ($shipment->events && $shipment->events->count())

                <div
                    class="rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                        <h2 class="font-extrabold text-slate-950 dark:text-white">
                            Shipment Activity
                        </h2>
                    </div>

                    <div class="p-6">

                        <div class="space-y-6">

                            @foreach ($shipment->events->sortByDesc('event_date') as $event)
                                <div class="relative flex gap-4">

                                    <div class="relative flex w-6 justify-center">
                                        <span
                                            class="mt-1.5 h-3 w-3 rounded-full bg-cyan-400 ring-4 ring-cyan-400/10"></span>

                                        @unless ($loop->last)
                                            <span class="absolute top-5 h-full w-px bg-slate-200 dark:bg-slate-800"></span>
                                        @endunless
                                    </div>

                                    <div class="pb-2">
                                        <div class="font-bold text-slate-950 dark:text-white">
                                            {{ $event->event_type ?? ($event->status ?? 'Shipment update') }}
                                        </div>

                                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                            {{ $event->description ?? '' }}
                                        </div>

                                        @if ($event->event_date)
                                            <div class="mt-2 text-xs font-semibold text-cyan-600 dark:text-cyan-400">
                                                {{ \Carbon\Carbon::parse($event->event_date)->format('d M Y, H:i') }}
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            @endforeach

                        </div>

                    </div>

                </div>

            @endif

        </div>

    </div>

</x-app-layout>
