@foreach($plan as $row)
    <details class="hag-broker-card" @if($row['action'] !== 'unchanged') open @endif>
        <summary class="cursor-pointer font-bold">{{ __('sailing_import.'.$row['action']) }} · <bdi>{{ $row['data']['voyage_number'] }}</bdi></summary>
        <p class="hag-broker-muted mt-2"><bdi>{{ implode(' · ', $row['label']) }}</bdi></p>
        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            @foreach(['before' => $row['before'], 'after' => $row['data']] as $stage => $details)
                @if($details)
                    <div class="rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-950">
                        <h3 class="font-bold">{{ __('sailing_import.'.$stage) }}</h3>
                        <p><bdi>{{ $details['vessel_name'] }}</bdi></p>
                        @foreach(['departure_at', 'arrival_at', 'cargo_cutoff_at'] as $field)
                            <p>{{ __('sailings.'.$field) }} <bdi>{{ \Carbon\CarbonImmutable::parse($details[$field], 'UTC')->setTimezone('Asia/Dubai')->format('Y-m-d H:i') }}</bdi></p>
                        @endforeach
                        @php($types = is_array($details['container_types']) ? $details['container_types'] : json_decode($details['container_types'], true))
                        <p>{{ implode(' · ', array_map(fn ($type) => __('sailings.containers.'.$type), (array) $types)) }}</p>
                    </div>
                @endif
            @endforeach
        </div>
    </details>
@endforeach
