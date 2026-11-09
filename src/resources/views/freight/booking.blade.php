@php($booking = $quotation->freightBookingOffer)
@php($expired = $booking && $booking->expires_at->lte(now()))
<section class="mx-auto my-6 max-w-5xl space-y-4 rounded-xl border border-slate-300 bg-white p-6 text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
    <h2 class="text-lg font-bold">{{ __('freight_booking.title') }}</h2>

    @foreach ($errors->getMessages() as $field => $messages)
        @if (in_array($field, ['freight_booking', 'expires_at', 'terms', 'revision', 'carrier_reference', 'space_confirmed'], true))
            @foreach ($messages as $message)
                <p class="text-red-600" role="alert">{{ $message }}</p>
            @endforeach
        @endif
    @endforeach

    @if ($booking)
        <p>{{ $booking->currency }} {{ $booking->amount }}</p>
        <p>{{ __('freight_booking.valid_until') }} · {{ $booking->expires_at->setTimezone('Asia/Dubai')->format('Y-m-d H:i') }}</p>
        @if ($booking->terms)
            <p class="whitespace-pre-line">{{ $booking->terms }}</p>
        @endif

        @if ($booking->confirmed_at)
            <p class="font-bold">{{ __('freight_booking.confirmed') }}</p>
            <p>{{ __('freight_booking.reference') }} · {{ $booking->carrier_reference }}</p>
        @elseif ($booking->accepted_at)
            <p>{{ __('freight_booking.accepted') }}</p>
        @elseif ($expired)
            <p>{{ __('freight_booking.expired') }}</p>
        @elseif (!$admin && $quotation->status !== 'rejected')
            <form method="POST" action="{{ route('quotations.freight-booking.accept', $quotation) }}">
                @csrf
                <input type="hidden" name="revision" value="{{ $booking->revision }}">
                <button class="rounded-lg bg-cyan-700 px-4 py-2 font-semibold text-white">{{ __('freight_booking.accept') }}</button>
            </form>
        @endif
    @else
        <p>{{ __('freight_booking.waiting') }}</p>
    @endif

    @if ($admin && $quotation->status !== 'rejected')
        @if ($booking?->accepted_at && !$booking->confirmed_at)
            <form method="POST" action="{{ route('admin.quotations.freight-booking.confirm', $quotation) }}" class="space-y-3">
                @csrf
                <label class="block">
                    {{ __('freight_booking.reference') }}
                    <input name="carrier_reference" value="{{ old('carrier_reference') }}" maxlength="100" required class="block w-full rounded border-slate-400 dark:bg-slate-950">
                </label>
                <label class="block">
                    <input type="checkbox" name="space_confirmed" value="1" required>
                    {{ __('freight_booking.space_confirmed') }}
                </label>
                <button class="rounded-lg bg-cyan-700 px-4 py-2 font-semibold text-white">{{ __('freight_booking.confirm') }}</button>
            </form>
        @elseif (!$booking?->accepted_at && $quotation->approved_offer_id)
            <form method="POST" action="{{ route('admin.quotations.freight-booking.publish', $quotation) }}" class="space-y-3">
                @csrf
                <input type="hidden" name="revision" value="{{ $booking?->revision }}">
                <label class="block">
                    {{ __('freight_booking.valid_until') }}
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $booking?->expires_at->setTimezone('Asia/Dubai')->format('Y-m-d\TH:i')) }}" required class="block rounded border-slate-400 dark:bg-slate-950">
                </label>
                <label class="block">
                    {{ __('freight_booking.terms') }}
                    <textarea name="terms" maxlength="3000" class="block w-full rounded border-slate-400 dark:bg-slate-950">{{ old('terms', $booking?->terms) }}</textarea>
                </label>
                <button class="rounded-lg bg-cyan-700 px-4 py-2 font-semibold text-white">{{ __('freight_booking.publish') }}</button>
            </form>
        @elseif (!$booking)
            <p>{{ __('freight_booking.approve_first') }}</p>
        @endif
    @endif
</section>
