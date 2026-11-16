<x-admin-layout>
    @php
        $snapshot = $booking->rate_snapshot ?? [];
        $canSend = $booking->status === 'request_pending';
        $canReject = $booking->status === 'request_pending';
        $canCancel = in_array($booking->status, ['request_pending', 'awaiting_carrier'], true);
    @endphp
    <section class="mx-auto max-w-6xl space-y-6">
        <header class="hag-broker-hero space-y-2">
            <a href="{{ route('admin.freight-bookings.index') }}" class="text-sm font-bold underline">{{ __('freight_bookings.back_to_list') }}</a>
            <p class="hag-broker-eyebrow">HAG BROKERS · {{ __('freight_bookings.request_number', ['id' => $booking->id]) }}</p>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-black">{{ __('freight_bookings.details_title') }}</h1>
                <span class="rounded-full bg-white/15 px-3 py-1 text-sm font-bold">{{ __('freight_bookings.statuses.'.$booking->status) }}</span>
            </div>
            <p class="max-w-4xl text-sm text-slate-200">{{ __('freight_bookings.detail_intro') }}</p>
        </header>

        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-900">
                <p class="font-bold">{{ __('freight_bookings.action_error_title') }}</p>
                <ul class="mt-1 list-disc ps-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="grid gap-4 lg:grid-cols-2">
            <article class="hag-broker-card space-y-3">
                <h2 class="font-black">{{ __('freight_bookings.route_and_cargo') }}</h2>
                <p class="text-xl font-black">{{ data_get($snapshot, 'origin.name', $booking->quotation?->originPort?->name ?? '—') }} → {{ data_get($snapshot, 'destination.name', $booking->quotation?->destinationPort?->name ?? '—') }}</p>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <p><span class="text-slate-500">{{ __('freight_bookings.origin_code') }}:</span> <bdi>{{ data_get($snapshot, 'origin.unlocode', '—') }}</bdi></p>
                    <p><span class="text-slate-500">{{ __('freight_bookings.destination_code') }}:</span> <bdi>{{ data_get($snapshot, 'destination.unlocode', '—') }}</bdi></p>
                    <p><span class="text-slate-500">{{ __('freight_bookings.container') }}:</span> {{ data_get($snapshot, 'container_type', $booking->quotation?->container_type ?? '—') }}</p>
                    <p><span class="text-slate-500">{{ __('freight_bookings.quantity') }}:</span> {{ data_get($snapshot, 'quantity', $booking->quotation?->quantity ?? '—') }}</p>
                    <p><span class="text-slate-500">{{ __('freight_bookings.cargo_type') }}:</span> {{ $booking->quotation?->cargo_type ?? '—' }}</p>
                    <p><span class="text-slate-500">{{ __('freight_bookings.preferred_date') }}:</span> {{ $booking->quotation?->preferred_date ?? '—' }}</p>
                </div>
                <p class="text-xs text-slate-500">{{ __('freight_bookings.snapshot_help') }}</p>
            </article>

            <article class="hag-broker-card space-y-3">
                <h2 class="font-black">{{ __('freight_bookings.parties_and_reference') }}</h2>
                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500">{{ __('freight_bookings.customer') }}</dt><dd class="font-bold">{{ $booking->customer?->company_name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('freight_bookings.carrier') }}</dt><dd class="font-bold">{{ $booking->carrier?->name ?? data_get($snapshot, 'carrier.name', __('freight_bookings.carrier_not_assigned')) }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('freight_bookings.quotation_reference') }}</dt><dd class="font-bold"><bdi>{{ $booking->quotation?->reference_no ?? '—' }}</bdi></dd></div>
                    <div><dt class="text-slate-500">{{ __('freight_bookings.carrier_reference') }}</dt><dd class="font-bold"><bdi>{{ $booking->carrier_reference ?? '—' }}</bdi></dd></div>
                    <div><dt class="text-slate-500">{{ __('freight_bookings.submitted_at') }}</dt><dd>{{ $booking->submitted_at?->timezone('Asia/Dubai')->format('Y-m-d H:i') ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('freight_bookings.confirmed_at') }}</dt><dd>{{ $booking->confirmed_at?->timezone('Asia/Dubai')->format('Y-m-d H:i') ?? '—' }}</dd></div>
                </dl>
                <p class="text-xs text-slate-500">{{ __('freight_bookings.party_help') }}</p>
            </article>
        </div>

        <article class="hag-broker-card space-y-3">
            <div>
                <h2 class="font-black">{{ __('freight_bookings.price_breakdown') }}</h2>
                <p class="text-xs text-slate-500">{{ __('freight_bookings.price_breakdown_help') }}</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-900">
                    <p class="text-xs text-slate-500">{{ __('freight_bookings.supplier_total_private') }}</p>
                    <p class="mt-1 text-xl font-black tabular-nums">{{ number_format((float) $booking->supplier_total, 2) }} {{ $booking->currency }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('freight_bookings.supplier_total_help') }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-900">
                    <p class="text-xs text-slate-500">{{ __('freight_bookings.customer_total') }}</p>
                    <p class="mt-1 text-xl font-black tabular-nums">{{ number_format((float) $booking->customer_total, 2) }} {{ $booking->currency }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('freight_bookings.customer_total_help') }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-900">
                    <p class="text-xs text-slate-500">{{ __('freight_bookings.commission_private') }}</p>
                    <p class="mt-1 text-xl font-black tabular-nums">{{ number_format((float) $booking->commission_total, 2) }} {{ $booking->currency }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('freight_bookings.commission_help') }}</p>
                </div>
            </div>
            <p class="text-xs text-slate-500">{{ __('freight_bookings.financial_snapshot_help') }}</p>
        </article>

        <article class="hag-broker-card space-y-3">
            <h2 class="font-black">{{ __('freight_bookings.rate_terms') }}</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                <div><h3 class="text-sm font-bold">{{ __('freight_bookings.included_charges') }}</h3><ul class="mt-1 list-disc ps-5 text-sm">@forelse(data_get($snapshot, 'included_charges', []) as $item)<li>{{ $item }}</li>@empty<li>{{ __('freight_bookings.not_specified') }}</li>@endforelse</ul></div>
                <div><h3 class="text-sm font-bold">{{ __('freight_bookings.excluded_charges') }}</h3><ul class="mt-1 list-disc ps-5 text-sm">@forelse(data_get($snapshot, 'excluded_charges', []) as $item)<li>{{ $item }}</li>@empty<li>{{ __('freight_bookings.not_specified') }}</li>@endforelse</ul></div>
            </div>
            @if (filled(data_get($snapshot, 'terms')))
                <div class="rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-900">{{ data_get($snapshot, 'terms') }}</div>
            @endif
        </article>

        <article class="hag-broker-card space-y-4">
            <div>
                <h2 class="font-black">{{ __('freight_bookings.admin_actions') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('freight_bookings.admin_actions_intro') }}</p>
            </div>
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-950">
                <p class="font-bold">{{ __('freight_bookings.carrier_confirmation_warning_title') }}</p>
                <p class="mt-1">{{ __('freight_bookings.carrier_confirmation_warning') }}</p>
            </div>
            @if ($canSend || $canReject || $canCancel)
                <form method="POST" action="{{ route('admin.freight-bookings.status', $booking) }}" class="grid gap-3">
                    @csrf
                    @method('PATCH')
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('freight_bookings.action_note') }}
                        <textarea name="note" rows="3" required minlength="3" maxlength="1500" class="rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900" placeholder="{{ __('freight_bookings.action_note_hint') }}">{{ old('note') }}</textarea>
                        <span class="text-xs font-normal text-slate-500">{{ __('freight_bookings.action_note_help') }}</span>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @if ($canSend)
                            <button name="action" value="mark_sent" class="hag-broker-action" onclick="return confirm(@js(__('freight_bookings.confirm_mark_sent')))">{{ __('freight_bookings.mark_sent') }}</button>
                        @endif
                        @if ($canReject)
                            <button name="action" value="reject" class="rounded-xl border border-rose-300 px-4 py-2 text-sm font-bold text-rose-700" onclick="return confirm(@js(__('freight_bookings.confirm_reject')))">{{ __('freight_bookings.reject_request') }}</button>
                        @endif
                        @if ($canCancel)
                            <button name="action" value="cancel" class="rounded-xl border border-slate-400 px-4 py-2 text-sm font-bold" onclick="return confirm(@js(__('freight_bookings.confirm_cancel')))">{{ __('freight_bookings.cancel_request') }}</button>
                        @endif
                    </div>
                </form>
            @else
                <p class="rounded-xl bg-slate-50 p-4 text-sm dark:bg-slate-900">{{ __('freight_bookings.no_actions_available') }}</p>
            @endif
            @if ($booking->status_note)
                <div class="rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-900"><b>{{ __('freight_bookings.latest_note') }}:</b> {{ $booking->status_note }}</div>
            @endif
        </article>

        <article class="hag-broker-card space-y-4">
            <div>
                <h2 class="font-black">{{ __('freight_bookings.timeline') }}</h2>
                <p class="text-sm text-slate-500">{{ __('freight_bookings.timeline_help') }}</p>
            </div>
            <ol class="space-y-4 border-s-2 border-slate-200 ps-4 dark:border-slate-700">
                @forelse ($booking->events as $event)
                    <li class="relative">
                        <span class="absolute -start-[1.38rem] top-1 h-3 w-3 rounded-full bg-cyan-500 ring-4 ring-white dark:ring-slate-900"></span>
                        <p class="font-bold">{{ __('freight_bookings.events.'.$event->event_key) }}</p>
                        <p class="text-xs text-slate-500">{{ $event->created_at?->timezone('Asia/Dubai')->format('Y-m-d H:i') }} · {{ $event->actor?->name ?? __('freight_bookings.system_actor') }}</p>
                        @if ($event->from_status || $event->to_status)
                            <p class="mt-1 text-sm">{{ $event->from_status ? __('freight_bookings.statuses.'.$event->from_status) : __('freight_bookings.not_applicable') }} → {{ $event->to_status ? __('freight_bookings.statuses.'.$event->to_status) : __('freight_bookings.not_applicable') }}</p>
                        @endif
                        @if ($event->note)<p class="mt-1 whitespace-pre-line text-sm text-slate-600 dark:text-slate-300">{{ $event->note }}</p>@endif
                    </li>
                @empty
                    <li class="text-sm text-slate-500">{{ __('freight_bookings.timeline_empty') }}</li>
                @endforelse
            </ol>
        </article>
    </section>
</x-admin-layout>
