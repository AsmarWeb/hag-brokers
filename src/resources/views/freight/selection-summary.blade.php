<section class="glass mt-6 rounded-2xl border border-slate-200 p-5 dark:border-slate-700">
    <h2 class="font-bold">{{ __('sailings.selected') }}</h2>
    <p><bdi>{{ $snapshot['origin']['name'] }} → {{ $snapshot['destination']['name'] }}</bdi></p>
    <p><bdi>{{ $snapshot['carrier'] }} · {{ $snapshot['vessel_name'] }} · {{ $snapshot['voyage_number'] }}</bdi></p>
    <p>{{ __('sailings.departure_at') }} <bdi>{{ \Carbon\CarbonImmutable::parse($snapshot['departure_at'])->setTimezone('Asia/Dubai')->format('Y-m-d H:i') }}</bdi></p>
    <p>{{ __('sailings.arrival_at') }} <bdi>{{ \Carbon\CarbonImmutable::parse($snapshot['arrival_at'])->setTimezone('Asia/Dubai')->format('Y-m-d H:i') }}</bdi></p>
    <p>{{ __('sailings.cargo_cutoff_at') }} <bdi>{{ \Carbon\CarbonImmutable::parse($snapshot['cargo_cutoff_at'])->setTimezone('Asia/Dubai')->format('Y-m-d H:i') }}</bdi></p>
    <p class="text-sm">{{ __('sailings.dubai_time') }} · {{ __('sailings.on_request') }}</p>
</section>
