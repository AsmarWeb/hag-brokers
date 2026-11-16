<x-admin-layout>
    <section class="mx-auto max-w-7xl space-y-6">
        <header class="hag-broker-hero space-y-2">
            <p class="hag-broker-eyebrow">HAG BROKERS · FREIGHT OPERATIONS</p>
            <h1 class="text-2xl font-black">{{ __('freight_bookings.title') }}</h1>
            <p class="max-w-4xl text-sm text-slate-200">{{ __('freight_bookings.index_intro') }}</p>
        </header>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                'all' => 'all_count',
                'request_pending' => 'pending_count',
                'awaiting_carrier' => 'awaiting_count',
                'confirmed' => 'confirmed_count',
                'attention' => 'attention_count',
            ] as $key => $label)
                <a href="{{ route('admin.freight-bookings.index', $key === 'all' ? [] : ['status' => match($key) {'confirmed' => 'carrier_confirmed', 'attention' => 'attention', default => $key}]) }}"
                    class="hag-broker-card block {{ ($status === '' && $key === 'all') || ($key === 'attention' && $status === 'attention') || ($key !== 'attention' && $status === $key) ? 'ring-2 ring-cyan-500' : '' }}">
                    <p class="text-xs font-semibold text-slate-500">{{ __('freight_bookings.'.$label) }}</p>
                    <p class="mt-1 text-2xl font-black tabular-nums">{{ number_format($counts[$key]) }}</p>
                </a>
            @endforeach
        </div>

        <div class="hag-broker-card space-y-3">
            <div>
                <h2 class="font-bold">{{ __('freight_bookings.how_to_use') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('freight_bookings.how_to_use_hint') }}</p>
            </div>
            <form method="GET" class="grid gap-3 sm:grid-cols-[1fr_220px_auto]">
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('freight_bookings.search') }}
                    <input name="q" value="{{ $search }}" placeholder="{{ __('freight_bookings.search_hint') }}" class="rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900">
                    <span class="text-xs font-normal text-slate-500">{{ __('freight_bookings.search_help') }}</span>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('freight_bookings.status') }}
                    <select name="status" class="rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900">
                        <option value="">{{ __('freight_bookings.all_statuses') }}</option>
                        @foreach (['request_pending','awaiting_carrier','carrier_confirmed','admin_rejected','carrier_rejected','cancelled'] as $value)
                            <option value="{{ $value }}" @selected($status === $value)>{{ __('freight_bookings.statuses.'.$value) }}</option>
                        @endforeach
                    </select>
                    <span class="text-xs font-normal text-slate-500">{{ __('freight_bookings.filter_help') }}</span>
                </label>
                <div class="flex items-end gap-2">
                    <button class="hag-broker-action">{{ __('freight_bookings.apply_filters') }}</button>
                    <a href="{{ route('admin.freight-bookings.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold dark:border-slate-700">{{ __('freight_bookings.reset') }}</a>
                </div>
            </form>
        </div>

        <div class="space-y-3">
            @forelse ($bookings as $booking)
                <article class="hag-broker-card space-y-3">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold text-cyan-700 dark:text-cyan-300">{{ __('freight_bookings.request_number', ['id' => $booking->id]) }}</p>
                            <h2 class="mt-1 text-lg font-black">
                                {{ data_get($booking->rate_snapshot, 'origin.name', $booking->quotation?->originPort?->name ?? '—') }}
                                <span aria-hidden="true">→</span>
                                {{ data_get($booking->rate_snapshot, 'destination.name', $booking->quotation?->destinationPort?->name ?? '—') }}
                            </h2>
                            <p class="text-sm text-slate-500">{{ $booking->customer?->company_name ?? __('freight_bookings.customer_not_available') }} · {{ $booking->carrier?->name ?? data_get($booking->rate_snapshot, 'carrier.name', __('freight_bookings.carrier_not_assigned')) }}</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            {{ __('freight_bookings.statuses.'.$booking->status) }}
                        </span>
                    </div>
                    <div class="grid gap-2 text-sm sm:grid-cols-2 lg:grid-cols-4">
                        <p><span class="text-slate-500">{{ __('freight_bookings.quotation_reference') }}:</span> <bdi>{{ $booking->quotation?->reference_no ?? '—' }}</bdi></p>
                        <p><span class="text-slate-500">{{ __('freight_bookings.container') }}:</span> {{ data_get($booking->rate_snapshot, 'quantity', $booking->quotation?->quantity ?? '—') }} × {{ data_get($booking->rate_snapshot, 'container_type', $booking->quotation?->container_type ?? '—') }}</p>
                        <p><span class="text-slate-500">{{ __('freight_bookings.customer_total') }}:</span> {{ number_format((float) $booking->customer_total, 2) }} {{ $booking->currency }}</p>
                        <p><span class="text-slate-500">{{ __('freight_bookings.submitted_at') }}:</span> {{ $booking->submitted_at?->timezone('Asia/Dubai')->format('Y-m-d H:i') ?? '—' }}</p>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 pt-3 dark:border-slate-700">
                        <p class="text-xs text-slate-500">{{ __('freight_bookings.list_row_help') }}</p>
                        <a href="{{ route('admin.freight-bookings.show', $booking) }}" class="hag-broker-action">{{ __('freight_bookings.view_details') }}</a>
                    </div>
                </article>
            @empty
                <div class="hag-broker-empty">
                    <p class="font-bold">{{ __('freight_bookings.no_results') }}</p>
                    <p class="mt-1 text-sm">{{ __('freight_bookings.no_results_help') }}</p>
                </div>
            @endforelse
        </div>

        {{ $bookings->links() }}
    </section>
</x-admin-layout>
