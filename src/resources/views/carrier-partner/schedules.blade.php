<x-carrier-layout :workspace="true">
    <section class="space-y-5">
        <header class="carrier-hero">
            <p class="carrier-eyebrow"><bdi>{{ $carrier->name }} · {{ $carrier->code }}</bdi></p>
            <h1 class="mt-2 text-3xl font-black">{{ __('carrier_schedules.title') }}</h1>
            <p class="mt-2 text-slate-200">{{ __('carrier_schedules.intro') }}</p>
        </header>

        @if(session('success'))<p role="status">{{ session('success') }}</p>@endif
        @if($errors->any())
            <ul role="alert" class="hag-broker-card text-rose-800 dark:text-rose-300">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        <div class="hag-broker-card space-y-3">
            <a class="font-semibold underline" href="{{ route('carrier.schedules.template') }}">{{ __('sailing_import.template') }}</a>
            <p class="hag-broker-muted">{{ __('sailing_import.format') }}</p>
            <form method="POST" action="{{ route('carrier.schedules.preview') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                @csrf
                <label class="grid min-w-0 gap-2 text-sm font-semibold">{{ __('sailing_import.file') }}
                    <input type="file" name="schedule" accept=".csv,text/csv" required class="max-w-full">
                </label>
                <button class="hag-broker-action">{{ __('sailing_import.preview') }}</button>
            </form>
        </div>

        @isset($plan)
            <section class="space-y-3">
                <h2 class="text-xl font-black">{{ __('sailing_import.preview') }}</h2>
                <p class="hag-broker-muted">{{ __('sailings.dubai_time') }}</p>
                @include('carrier-partner.schedule-plan')
                @if(collect($plan)->contains(fn ($row) => $row['action'] !== 'unchanged'))
                    <form method="POST" action="{{ route('carrier.schedules.submit') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <button class="hag-broker-action">{{ __('carrier_schedules.submit') }}</button>
                    </form>
                @endif
            </section>
        @endisset

        <section class="hag-broker-card space-y-3">
            <h2>{{ __('carrier_schedules.history') }}</h2>
            @forelse($submissions as $submission)
                <article class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                    <p class="font-bold"><bdi>#{{ $submission->id }}</bdi> · {{ __('carrier_schedules.'.$submission->status) }}</p>
                    <p class="hag-broker-muted"><bdi>{{ $submission->created_at->timezone('Asia/Dubai')->format('Y-m-d H:i') }}</bdi></p>
                    @if($submission->review_note)<p class="break-words">{{ $submission->review_note }}</p>@endif
                </article>
            @empty
                <p class="hag-broker-muted">{{ __('carrier_schedules.empty') }}</p>
            @endforelse
            {{ $submissions->links() }}
        </section>
    </section>
</x-carrier-layout>
