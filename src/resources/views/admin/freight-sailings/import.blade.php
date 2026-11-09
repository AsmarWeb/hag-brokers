<x-admin-layout>
    <x-slot name="header">{{ __('sailing_import.title') }}</x-slot>
    <main class="mx-auto max-w-6xl space-y-4 px-5 py-8">
        <nav class="flex flex-wrap gap-4">
            <a class="underline" href="{{ route('admin.freight-sailings.index') }}">{{ __('sailings.admin_title') }}</a>
            <a class="underline" href="{{ route('admin.carriers.index') }}">{{ __('sailing_import.carriers') }}</a>
            <a class="underline" href="{{ route('admin.vessels.index') }}">{{ __('sailing_import.vessels') }}</a>
            <a class="underline" href="{{ route('admin.ports.index') }}">{{ __('sailing_import.ports') }}</a>
        </nav>

        <p>{{ __('sailing_import.instructions') }}</p>
        <p>{{ __('sailing_import.format') }}</p>
        <a class="inline-block font-bold underline" href="{{ route('admin.freight-sailings.import.template') }}">{{ __('sailing_import.template') }}</a>
        @if($errors->any())
            <ul role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        @endif
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.freight-sailings.import.preview') }}" class="flex flex-wrap items-end gap-4">
            @csrf
            <label class="grid gap-2">{{ __('sailing_import.file') }}
                <input type="file" name="schedule" accept=".csv,text/csv" required>
            </label>
            <button class="rounded-xl bg-cyan-400 px-4 py-3 font-bold text-slate-950">{{ __('sailing_import.preview') }}</button>
        </form>

        @isset($plan)
            <p>{{ __('sailing_import.review') }}</p>
            @foreach($plan as $row)
                <article class="rounded-xl border p-4">
                    <p class="font-bold">{{ __('sailing_import.'.$row['action']) }} · <bdi>{{ $row['data']['voyage_number'] }}</bdi></p>
                    <p><bdi>{{ $row['label'][0] }} · {{ $row['label'][1] }} · {{ $row['label'][2] }} → {{ $row['label'][3] }}</bdi></p>
                    <div class="mt-2 grid gap-4 sm:grid-cols-2">
                        @foreach(['before' => $row['action'] === 'update' ? $row['before'] : null, 'after' => $row['data']] as $stage => $details)
                            @if($details)
                                <div>
                                    <h2 class="font-semibold">{{ __('sailing_import.'.$stage) }}</h2>
                                    <p>{{ $details['vessel_name'] }} · {{ __('sailings.statuses.'.$details['status']) }}</p>
                                    @foreach(['departure_at', 'arrival_at', 'cargo_cutoff_at'] as $field)
                                        <p>{{ __('sailings.'.$field) }} <bdi>{{ \Carbon\CarbonImmutable::parse($details[$field], 'UTC')->setTimezone('Asia/Dubai')->format('Y-m-d H:i') }}</bdi></p>
                                    @endforeach
                                    <p><bdi>{{ implode(' · ', (array) (is_string($details['container_types']) ? json_decode($details['container_types'], true) : $details['container_types'])) }}</bdi></p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </article>
            @endforeach
            <form method="POST" action="{{ route('admin.freight-sailings.import.publish') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <button class="rounded-xl bg-cyan-400 px-4 py-3 font-bold text-slate-950">{{ __('sailing_import.publish') }}</button>
            </form>
        @endisset
    </main>
</x-admin-layout>
