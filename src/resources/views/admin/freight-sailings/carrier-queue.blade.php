<x-admin-layout>
    <section class="mx-auto max-w-6xl space-y-4">
        <header class="hag-broker-hero">
            <a href="{{ route('admin.freight-sailings.index') }}" class="underline">{{ __('sailings.admin_title') }}</a>
            <h1 class="mt-2 font-black">{{ __('carrier_schedules.queue') }}</h1>
        </header>
        @if(session('success'))<p role="status">{{ session('success') }}</p>@endif
        @forelse($submissions as $submission)
            <article class="hag-broker-card flex flex-wrap items-center justify-between gap-3">
                <p><bdi>#{{ $submission->id }} · {{ $submission->carrier->name }}</bdi> · {{ __('carrier_schedules.pending') }}</p>
                <a class="hag-broker-action" href="{{ route('admin.freight-sailings.carrier-review', $submission) }}">{{ __('carrier_schedules.review') }}</a>
            </article>
        @empty
            <p class="hag-broker-empty">{{ __('carrier_schedules.empty') }}</p>
        @endforelse
        {{ $submissions->links() }}
    </section>
</x-admin-layout>
