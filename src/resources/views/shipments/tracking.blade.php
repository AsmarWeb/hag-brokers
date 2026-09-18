<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center gap-3">

            <a href="{{ route('shipments.show', $shipment) }}"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-cyan-400 hover:text-cyan-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400"
                aria-label="Back to shipment">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <div>
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">
                    {{ __('Shipment Tracking') }}
                </h2>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $shipment->reference_no ?? '#' . $shipment->id }}
                </p>
            </div>

        </div>

    </x-slot>


    <div class="min-h-screen bg-slate-50 py-8 dark:bg-slate-950">

        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">


            {{-- ========================================================= --}}
            {{-- Shipment Header --}}
            {{-- ========================================================= --}}

            <div class="overflow-hidden rounded-3xl bg-slate-950 p-6 shadow-xl sm:p-8">

                <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">

                    <div>

                        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-cyan-400">

                            <span class="h-2 w-2 rounded-full bg-cyan-400"></span>

                            HAG Brokers

                        </div>

                        <h1 class="mt-4 text-3xl font-black text-white">
                            {{ __('Track Shipment') }}
                        </h1>

                        <p class="mt-2 text-sm text-slate-400">
                            {{ $shipment->reference_no ?? '#' . $shipment->id }}
                        </p>

                    </div>


                    {{-- Status --}}

                    @php

                        $status = $shipment->status ?? 'pending';

                        $statusClasses = match ($status) {
                            'delivered' => 'bg-emerald-400/15 text-emerald-300',

                            'in_transit' => 'bg-cyan-400/15 text-cyan-300',

                            'pending' => 'bg-amber-400/15 text-amber-300',

                            'cancelled' => 'bg-red-400/15 text-red-300',

                            default => 'bg-white/10 text-slate-300',
                        };

                        $statusLabel = match ($status) {
                            'delivered' => 'Delivered',

                            'in_transit' => 'In Transit',

                            'pending' => 'Pending',

                            'cancelled' => 'Cancelled',

                            default => str($status)->replace('_', ' ')->title(),
                        };

                    @endphp


                    <span
                        class="inline-flex w-fit items-center gap-2 rounded-full px-4 py-2 text-sm font-bold {{ $statusClasses }}">

                        <span class="h-2 w-2 rounded-full bg-current"></span>

                        {{ __($statusLabel) }}

                    </span>

                </div>


                {{-- ===================================================== --}}
                {{-- Route --}}
                {{-- ===================================================== --}}

                <div class="mt-8 grid gap-5 sm:grid-cols-[1fr_auto_1fr] sm:items-center">

                    {{-- Origin --}}

                    <div>

                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            {{ __('Origin') }}
                        </div>

                        <div class="mt-2 text-lg font-extrabold text-white">

                            {{ $shipment->originPort?->name ?? '—' }}

                        </div>

                        @if ($shipment->originPort?->unlocode)
                            <div class="mt-1 text-xs text-slate-500">
                                {{ $shipment->originPort->unlocode }}
                            </div>
                        @endif

                    </div>


                    {{-- Arrow --}}

                    <div class="hidden text-cyan-400 sm:block">

                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-width="2" d="M5 12h14m-6-6l6 6-6 6" />
                        </svg>

                    </div>


                    {{-- Destination --}}

                    <div>

                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            {{ __('Destination') }}
                        </div>

                        <div class="mt-2 text-lg font-extrabold text-white">

                            {{ $shipment->destinationPort?->name ?? '—' }}

                        </div>

                        @if ($shipment->destinationPort?->unlocode)
                            <div class="mt-1 text-xs text-slate-500">
                                {{ $shipment->destinationPort->unlocode }}
                            </div>
                        @endif

                    </div>

                </div>


                {{-- ===================================================== --}}
                {{-- Shipment Meta --}}
                {{-- ===================================================== --}}

                <div class="mt-8 grid gap-3 sm:grid-cols-3">

                    <div class="rounded-2xl bg-white/5 p-4">

                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Shipment
                        </div>

                        <div class="mt-1 text-sm font-bold text-white">
                            {{ $shipment->reference_no ?? '#' . $shipment->id }}
                        </div>

                    </div>


                    <div class="rounded-2xl bg-white/5 p-4">

                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Cargo
                        </div>

                        <div class="mt-1 text-sm font-bold text-white">
                            {{ $shipment->cargo_type ?? '—' }}
                        </div>

                    </div>


                    <div class="rounded-2xl bg-white/5 p-4">

                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Container
                        </div>

                        <div class="mt-1 text-sm font-bold text-white">
                            {{ $shipment->container_type ?? '—' }}
                        </div>

                    </div>

                </div>

            </div>


            {{-- ========================================================= --}}
            {{-- Tracking Timeline --}}
            {{-- ========================================================= --}}

            <div
                class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">

                    <h2 class="font-extrabold text-slate-950 dark:text-white">
                        {{ __('Tracking Timeline') }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ __('Latest shipment events and status updates.') }}
                    </p>

                </div>


                @if ($events->isNotEmpty())

                    <div class="p-6 sm:p-8">

                        <div class="space-y-8">

                            @foreach ($events as $event)
                                @php

                                    $eventTitle = $event->event_type ?? ($event->status ?? 'Shipment Update');

                                @endphp


                                <div class="relative flex gap-5">

                                    {{-- Timeline indicator --}}

                                    <div class="relative flex w-7 shrink-0 justify-center">

                                        <span
                                            class="relative z-10 flex h-7 w-7 items-center justify-center rounded-full bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/20">

                                            <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>

                                        </span>


                                        @unless ($loop->last)
                                            <span
                                                class="absolute top-7 bottom-[-32px] w-px bg-slate-200 dark:bg-slate-800"></span>
                                        @endunless

                                    </div>


                                    {{-- Event card --}}

                                    <div
                                        class="min-w-0 flex-1 rounded-2xl border border-slate-200 p-5 dark:border-slate-800">

                                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                                            <div class="min-w-0">

                                                <h3 class="font-extrabold text-slate-950 dark:text-white">
                                                    {{ str($eventTitle)->replace('_', ' ')->title() }}
                                                </h3>


                                                {{-- Event description --}}

                                                @if ($event->description)
                                                    <p
                                                        class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">

                                                        {{ $event->description }}

                                                    </p>
                                                @endif


                                                {{-- Event Port --}}

                                                @if ($event->port)
                                                    <div
                                                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">

                                                        <svg class="h-4 w-4 text-cyan-500" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="1.8"
                                                                d="M3 20h18M5 20v-8l7-4 7 4v8M8 20v-5h8v5" />
                                                        </svg>

                                                        <span>
                                                            {{ $event->port->name }}
                                                        </span>

                                                        @if ($event->port->unlocode)
                                                            <span class="text-slate-400">
                                                                {{ $event->port->unlocode }}
                                                            </span>
                                                        @endif

                                                    </div>
                                                @endif

                                            </div>


                                            {{-- Event Date --}}

                                            @if ($event->event_date)
                                                <time datetime="{{ $event->event_date }}"
                                                    class="shrink-0 text-xs font-bold text-cyan-600 dark:text-cyan-400">

                                                    {{ \Carbon\Carbon::parse($event->event_date)->format('d M Y, H:i') }}

                                                </time>
                                            @endif

                                        </div>

                                    </div>

                                </div>
                            @endforeach

                        </div>

                    </div>
                @else
                    {{-- Empty state --}}

                    <div class="px-6 py-20 text-center">

                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-400">

                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>

                        </div>


                        <h3 class="mt-5 text-lg font-extrabold text-slate-950 dark:text-white">
                            {{ __('No tracking events yet') }}
                        </h3>


                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400">
                            {{ __('Tracking updates for this shipment will appear here when available.') }}
                        </p>

                    </div>

                @endif

            </div>


            {{-- ========================================================= --}}
            {{-- Vessel --}}
            {{-- ========================================================= --}}

            @if ($shipment->vessel)

                <div
                    class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                        <div class="flex items-center gap-4">

                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-400">

                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M3 17h18M5 17l1.5-7h11L19 17M8 10V6h8v4" />
                                </svg>

                            </div>


                            <div>

                                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                    {{ __('Current Vessel') }}
                                </div>

                                <div class="mt-1 font-extrabold text-slate-950 dark:text-white">
                                    {{ $shipment->vessel->name }}
                                </div>


                                @if ($shipment->vessel->imo)
                                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        IMO {{ $shipment->vessel->imo }}
                                    </div>
                                @endif

                            </div>

                        </div>


                        {{-- Vessel details --}}

                        <div class="flex flex-wrap gap-2">

                            @if ($shipment->vessel->mmsi)
                                <span
                                    class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    MMSI {{ $shipment->vessel->mmsi }}
                                </span>
                            @endif


                            @if ($shipment->vessel->flag)
                                <span
                                    class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $shipment->vessel->flag }}
                                </span>
                            @endif

                        </div>

                    </div>

                </div>

            @endif


            {{-- ========================================================= --}}
            {{-- Back --}}
            {{-- ========================================================= --}}

            <div class="mt-6">

                <a href="{{ route('shipments.show', $shipment) }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-cyan-400 hover:text-cyan-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-cyan-400 dark:hover:text-cyan-400">

                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>

                    {{ __('Back to Shipment') }}

                </a>

            </div>

        </div>

    </div>

</x-app-layout>
