<x-carrier-layout :workspace="true">
    <section class="space-y-5">
        <header class="carrier-hero space-y-2">
            <a href="{{ route('carrier.freight-bookings.index') }}" class="text-sm font-bold underline">{{ __('carrier_freight_bookings.back') }}</a>
            <p class="carrier-eyebrow">HAG BROKERS / {{ __('carrier_freight_bookings.request_number', ['id' => $booking->id]) }}</p>
            <h1 class="text-3xl font-black">{{ __('carrier_freight_bookings.details') }}</h1>
            <p class="text-sm text-slate-200">{{ __('carrier_freight_bookings.intro') }}</p>
        </header>

        @if(session('carrier_status'))
            <p role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">{{ session('carrier_status') }}</p>
        @endif
        @if($errors->any())
            <ul role="alert" class="space-y-1 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-950 dark:border-rose-800 dark:bg-rose-950 dark:text-rose-200">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        <article class="hag-broker-card space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-xl font-black">{{ data_get($snapshot, 'origin.name', $booking->quotation?->originPort?->name ?? '—') }} → {{ data_get($snapshot, 'destination.name', $booking->quotation?->destinationPort?->name ?? '—') }}</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold dark:bg-slate-800">{{ __('carrier_freight_bookings.statuses.'.$booking->status) }}</span>
            </div>
            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">{{ __('carrier_freight_bookings.origin_code') }}</dt><dd class="font-bold"><bdi>{{ data_get($snapshot, 'origin.unlocode', '—') }}</bdi></dd></div>
                <div><dt class="text-slate-500">{{ __('carrier_freight_bookings.destination_code') }}</dt><dd class="font-bold"><bdi>{{ data_get($snapshot, 'destination.unlocode', '—') }}</bdi></dd></div>
                <div><dt class="text-slate-500">{{ __('carrier_freight_bookings.container') }}</dt><dd class="font-bold">{{ data_get($snapshot, 'container_type', '—') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('carrier_freight_bookings.quantity') }}</dt><dd class="font-bold">{{ data_get($snapshot, 'quantity', '—') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('carrier_freight_bookings.cargo_type') }}</dt><dd class="font-bold">{{ $booking->quotation?->cargo_type ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('carrier_freight_bookings.preferred_date') }}</dt><dd class="font-bold">{{ $booking->quotation?->preferred_date ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('carrier_freight_bookings.customer_total') }}</dt><dd class="text-xl font-black tabular-nums">{{ number_format((float) $booking->customer_total, 2) }} {{ $booking->currency }}</dd></div>
                <div><dt class="text-slate-500">{{ __('carrier_freight_bookings.carrier_reference') }}</dt><dd class="font-bold"><bdi>{{ $booking->carrier_reference ?? '—' }}</bdi></dd></div>
            </dl>
            <p class="text-xs text-slate-500">{{ __('carrier_freight_bookings.price_disclaimer') }}</p>
        </article>

        <article class="hag-broker-card space-y-4">
            <h2 class="text-lg font-black">{{ __('carrier_freight_bookings.rate_terms') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><h3 class="text-sm font-bold">{{ __('carrier_freight_bookings.included_charges') }}</h3><ul class="mt-1 list-disc ps-5 text-sm">@forelse(data_get($snapshot, 'included_charges', []) as $item)<li>{{ $item }}</li>@empty<li>{{ __('carrier_freight_bookings.not_specified') }}</li>@endforelse</ul></div>
                <div><h3 class="text-sm font-bold">{{ __('carrier_freight_bookings.excluded_charges') }}</h3><ul class="mt-1 list-disc ps-5 text-sm">@forelse(data_get($snapshot, 'excluded_charges', []) as $item)<li>{{ $item }}</li>@empty<li>{{ __('carrier_freight_bookings.not_specified') }}</li>@endforelse</ul></div>
            </div>
            @if(filled(data_get($snapshot, 'terms')))
                <p class="rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-900">{{ data_get($snapshot, 'terms') }}</p>
            @endif
        </article>

        @if($booking->status === 'awaiting_carrier')
            <article class="hag-broker-card space-y-4">
                <h2 class="text-lg font-black">{{ __('carrier_freight_bookings.response') }}</h2>
                <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('carrier_freight_bookings.response_help') }}</p>
                <form method="POST" action="{{ route('carrier.freight-bookings.decision', $booking->id) }}" class="space-y-4">
                    @csrf @method('PATCH')
                    <label class="grid gap-1 text-sm font-semibold">{{ __('carrier_freight_bookings.carrier_reference') }}
                        <input name="carrier_reference" value="{{ old('carrier_reference') }}" maxlength="120" class="rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900" />
                        <span class="text-xs font-normal text-slate-500">{{ __('carrier_freight_bookings.reference_help') }}</span>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">{{ __('carrier_freight_bookings.rejection_reason') }}
                        <textarea name="note" rows="3" maxlength="1500" class="rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900">{{ old('note') }}</textarea>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button name="decision" value="accept" class="hag-broker-action" onclick="return confirm(@js(__('carrier_freight_bookings.confirm_accept')))">{{ __('carrier_freight_bookings.accept') }}</button>
                        <button name="decision" value="reject" class="rounded-xl border border-rose-300 px-4 py-2 text-sm font-bold text-rose-700" onclick="return confirm(@js(__('carrier_freight_bookings.confirm_reject')))">{{ __('carrier_freight_bookings.reject') }}</button>
                    </div>
                </form>
            </article>
        @else
            @if($booking->status_note)
                <p class="hag-broker-card whitespace-pre-line text-sm">{{ $booking->status_note }}</p>
            @endif
            <p class="hag-broker-card text-sm">{{ __('carrier_freight_bookings.processed_read_only') }}</p>
        @endif
    </section>
</x-carrier-layout>
