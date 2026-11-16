<x-carrier-layout :workspace="true">
    <section class="space-y-5">
        <header class="carrier-hero space-y-2">
            <p class="carrier-eyebrow">HAG BROKERS / {{ __('carrier_freight_bookings.title') }}</p>
            <h1 class="text-3xl font-black">{{ __('carrier_freight_bookings.title') }}</h1>
            <p class="max-w-3xl text-sm text-slate-200">{{ __('carrier_freight_bookings.intro') }}</p>
        </header>

        @if(session('carrier_status'))
            <p role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">{{ session('carrier_status') }}</p>
        @endif

        <div class="hag-broker-card space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-lg font-black">{{ $carrier->name }}</h2>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-900 dark:bg-amber-950 dark:text-amber-200">{{ $bookings->total() }} · {{ __('carrier_freight_bookings.awaiting_response') }}</span>
            </div>
            @forelse($bookings as $booking)
                @php($snapshot = $booking->rate_snapshot ?? [])
                <article class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="space-y-1">
                            <p class="text-xs font-bold text-slate-500">{{ __('carrier_freight_bookings.request_number', ['id' => $booking->id]) }}</p>
                            <h3 class="text-lg font-black">{{ data_get($snapshot, 'origin.name', $booking->quotation?->originPort?->name ?? '—') }} → {{ data_get($snapshot, 'destination.name', $booking->quotation?->destinationPort?->name ?? '—') }}</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ data_get($snapshot, 'container_type', '—') }} · {{ __('carrier_freight_bookings.quantity') }}: {{ data_get($snapshot, 'quantity', '—') }}</p>
                        </div>
                        <div class="text-end">
                            <p class="text-xs text-slate-500">{{ __('carrier_freight_bookings.customer_total') }}</p>
                            <p class="text-xl font-black tabular-nums">{{ number_format((float) $booking->customer_total, 2) }} {{ $booking->currency }}</p>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-xs text-slate-500">{{ __('carrier_freight_bookings.submitted_at') }}: {{ $booking->submitted_at?->timezone('Asia/Dubai')->format('Y-m-d H:i') ?? '—' }}</p>
                        <a class="hag-broker-action" href="{{ route('carrier.freight-bookings.show', $booking->id) }}">{{ __('carrier_freight_bookings.review') }}</a>
                    </div>
                </article>
            @empty
                <p class="hag-broker-empty">{{ __('carrier_freight_bookings.empty') }}</p>
            @endforelse
            {{ $bookings->links() }}
        </div>
    </section>
</x-carrier-layout>
