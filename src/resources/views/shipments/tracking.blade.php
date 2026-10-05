@php
    $isAr = app()->getLocale() === 'ar';
    $dir = $isAr ? 'rtl' : 'ltr';

    $t = [
        'shipment_tracking' => $isAr ? 'تتبع الشحنة' : 'Shipment Tracking',
        'track_shipment' => $isAr ? 'تتبع الشحنة' : 'Track Shipment',
        'back_to_shipment' => $isAr ? 'العودة إلى الشحنة' : 'Back to Shipment',

        'origin' => $isAr ? 'ميناء الانطلاق' : 'Origin',
        'destination' => $isAr ? 'ميناء الوصول' : 'Destination',
        'shipment' => $isAr ? 'الشحنة' : 'Shipment',
        'cargo' => $isAr ? 'البضائع' : 'Cargo',
        'container' => $isAr ? 'الحاوية' : 'Container',

        'tracking_timeline' => $isAr ? 'الخط الزمني للتتبع' : 'Tracking Timeline',
        'latest_events' => $isAr ? 'أحدث أحداث الشحنة وتحديثات حالتها.' : 'Latest shipment events and status updates.',

        'no_tracking_events' => $isAr ? 'لا توجد أحداث تتبع حتى الآن' : 'No tracking events yet',
        'tracking_updates' => $isAr
            ? 'ستظهر تحديثات تتبع هذه الشحنة هنا عند توفرها.'
            : 'Tracking updates for this shipment will appear here when available.',

        'current_vessel' => $isAr ? 'السفينة الحالية' : 'Current Vessel',
        'shipment_update' => $isAr ? 'تحديث الشحنة' : 'Shipment Update',
        'port' => $isAr ? 'الميناء' : 'Port',

        'delivered' => $isAr ? 'تم التسليم' : 'Delivered',
        'in_transit' => $isAr ? 'قيد النقل' : 'In Transit',
        'pending' => $isAr ? 'قيد الانتظار' : 'Pending',
        'cancelled' => $isAr ? 'ملغاة' : 'Cancelled',
        'unknown' => $isAr ? 'غير معروف' : 'Unknown',

        'not_available' => $isAr ? 'غير متوفر' : 'Not available',
        'sea_freight' => $isAr ? 'شحن بحري' : 'Sea Freight',
        'mmsi' => 'MMSI',
        'imo' => 'IMO',
    ];

    $status = $shipment->status ?? 'pending';

    $statusClasses = match ($status) {
        'delivered' => 'bg-emerald-400/15 text-emerald-300 ring-emerald-400/20',
        'in_transit' => 'bg-cyan-400/15 text-cyan-300 ring-cyan-400/20',
        'pending' => 'bg-amber-400/15 text-amber-300 ring-amber-400/20',
        'cancelled' => 'bg-red-400/15 text-red-300 ring-red-400/20',
        default => 'bg-white/10 text-slate-300 ring-white/10',
    };

    $statusLabel = match ($status) {
        'delivered' => $t['delivered'],
        'in_transit' => $t['in_transit'],
        'pending' => $t['pending'],
        'cancelled' => $t['cancelled'],
        default => $status ? str($status)->replace('_', ' ')->title() : $t['unknown'],
    };
@endphp

<x-app-layout>

    <x-slot name="header">

        <div dir="{{ $dir }}" class="flex items-center gap-3">

            <a href="{{ route('shipments.show', $shipment) }}"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-cyan-400 hover:text-cyan-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400"
                aria-label="{{ $t['back_to_shipment'] }}">
                <svg class="h-5 w-5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <div class="{{ $isAr ? 'text-right' : 'text-left' }}">
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">
                    {{ $t['shipment_tracking'] }}
                </h2>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $shipment->reference_no ?? '#' . $shipment->id }}
                </p>
            </div>

        </div>

    </x-slot>


    <div dir="{{ $dir }}" class="min-h-screen bg-slate-50 py-8 dark:bg-slate-950">

        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            {{-- ========================================================= --}}
            {{-- Shipment Header --}}
            {{-- ========================================================= --}}

            <div class="overflow-hidden rounded-3xl bg-slate-950 p-6 shadow-xl sm:p-8">

                <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">

                    <div class="{{ $isAr ? 'text-right' : 'text-left' }}">

                        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-cyan-400">
                            <span class="h-2 w-2 rounded-full bg-cyan-400"></span>

                            HAG Brokers
                        </div>

                        <h1 class="mt-4 text-3xl font-black text-white">
                            {{ $t['track_shipment'] }}
                        </h1>

                        <p class="mt-2 text-sm text-slate-400">
                            {{ $shipment->reference_no ?? '#' . $shipment->id }}
                        </p>

                    </div>


                    {{-- Status --}}

                    <span
                        class="inline-flex w-fit items-center gap-2 rounded-full px-4 py-2 text-sm font-bold ring-1 ring-inset {{ $statusClasses }}">
                        <span class="h-2 w-2 rounded-full bg-current"></span>

                        {{ $statusLabel }}
                    </span>

                </div>


                {{-- ===================================================== --}}
                {{-- Route --}}
                {{-- ===================================================== --}}

                <div class="mt-8 grid gap-5 sm:grid-cols-[1fr_auto_1fr] sm:items-center">

                    {{-- Origin --}}

                    <div class="{{ $isAr ? 'text-right' : 'text-left' }}">

                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            {{ $t['origin'] }}
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

                        <svg class="h-6 w-6 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-width="2" d="M5 12h14m-6-6l6 6-6 6" />
                        </svg>

                    </div>


                    {{-- Destination --}}

                    <div class="{{ $isAr ? 'text-right' : 'text-left' }}">

                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            {{ $t['destination'] }}
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

                    <div class="rounded-2xl bg-white/5 p-4 {{ $isAr ? 'text-right' : 'text-left' }}">

                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            {{ $t['shipment'] }}
                        </div>

                        <div class="mt-1 text-sm font-bold text-white">
                            {{ $shipment->reference_no ?? '#' . $shipment->id }}
                        </div>

                    </div>


                    <div class="rounded-2xl bg-white/5 p-4 {{ $isAr ? 'text-right' : 'text-left' }}">

                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            {{ $t['cargo'] }}
                        </div>

                        <div class="mt-1 text-sm font-bold text-white">
                            {{ $shipment->cargo_type ?? $t['not_available'] }}
                        </div>

                    </div>


                    <div class="rounded-2xl bg-white/5 p-4 {{ $isAr ? 'text-right' : 'text-left' }}">

                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            {{ $t['container'] }}
                        </div>

                        <div class="mt-1 text-sm font-bold text-white">
                            {{ $shipment->container_type ?? $t['not_available'] }}
                        </div>

                    </div>

                </div>

            </div>


            {{-- ========================================================= --}}
            {{-- Tracking Timeline --}}
            {{-- ========================================================= --}}

            <div
                class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                <div
                    class="border-b border-slate-200 px-6 py-5 dark:border-slate-800 {{ $isAr ? 'text-right' : 'text-left' }}">

                    <h2 class="font-extrabold text-slate-950 dark:text-white">
                        {{ $t['tracking_timeline'] }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $t['latest_events'] }}
                    </p>

                </div>


                @if ($events->isNotEmpty())

                    <div class="p-6 sm:p-8">

                        <div class="space-y-8">

                            @foreach ($events as $event)
                                @php
                                    $eventTitle = $event->event_type ?? ($event->status ?? $t['shipment_update']);

                                    $eventTitle = str($eventTitle)->replace('_', ' ')->title();
                                @endphp


                                <div class="relative flex gap-5 {{ $isAr ? 'flex-row-reverse' : '' }}">

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

                                            <div class="min-w-0 {{ $isAr ? 'text-right' : 'text-left' }}">

                                                <h3 class="font-extrabold text-slate-950 dark:text-white">
                                                    {{ $eventTitle }}
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

                                                        <svg class="h-4 w-4 shrink-0 text-cyan-500" fill="none"
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
                                                    class="shrink-0 text-xs font-bold text-cyan-600 dark:text-cyan-400 {{ $isAr ? 'text-right' : 'text-left' }}">
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
                            {{ $t['no_tracking_events'] }}
                        </h3>


                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400">
                            {{ $t['tracking_updates'] }}
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

                        <div class="flex items-center gap-4 {{ $isAr ? 'flex-row-reverse' : '' }}">

                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-400">

                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M3 17h18M5 17l1.5-7h11L19 17M8 10V6h8v4" />
                                </svg>

                            </div>


                            <div class="{{ $isAr ? 'text-right' : 'text-left' }}">

                                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                    {{ $t['current_vessel'] }}
                                </div>

                                <div class="mt-1 font-extrabold text-slate-950 dark:text-white">
                                    {{ $shipment->vessel->name }}
                                </div>


                                @if ($shipment->vessel->imo)
                                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $t['imo'] }} {{ $shipment->vessel->imo }}
                                    </div>
                                @endif

                            </div>

                        </div>


                        {{-- Vessel details --}}

                        <div class="flex flex-wrap gap-2 {{ $isAr ? 'justify-end' : 'justify-start' }}">

                            @if ($shipment->vessel->mmsi)
                                <span
                                    class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $t['mmsi'] }} {{ $shipment->vessel->mmsi }}
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

            <div class="mt-6 {{ $isAr ? 'text-right' : 'text-left' }}">

                <a href="{{ route('shipments.show', $shipment) }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-cyan-400 hover:text-cyan-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-cyan-400 dark:hover:text-cyan-400">

                    <svg class="h-4 w-4 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>

                    {{ $t['back_to_shipment'] }}

                </a>

            </div>

        </div>

    </div>

</x-app-layout>
