<x-admin-layout>
    <section class="mx-auto max-w-6xl space-y-4">
        <header class="hag-broker-hero">
            <a href="{{ route('admin.freight-sailings.carrier-queue') }}" class="underline">{{ __('carrier_schedules.queue') }}</a>
            <h1 class="mt-2 font-black">{{ __('carrier_schedules.review') }} · <bdi>#{{ $submission->id }}</bdi></h1>
            <p><bdi>{{ $submission->carrier->name }}</bdi> · {{ __('carrier_schedules.'.$submission->status) }}</p>
        </header>
        @if($errors->any())
            <ul role="alert" class="hag-broker-card text-rose-800 dark:text-rose-300">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif
        <p class="hag-broker-muted">{{ __('sailings.dubai_time') }}</p>
        @include('carrier-partner.schedule-plan', ['plan' => $submission->plan])
        @if($submission->status === 'pending')
            <form method="POST" action="{{ route('admin.freight-sailings.carrier-review.store', $submission) }}" class="hag-broker-card space-y-3">
                @csrf @method('PATCH')
                <label class="grid gap-1 text-sm font-semibold">{{ __('carrier_schedules.note') }}
                    <textarea name="review_note" maxlength="500" rows="2" class="rounded-xl border-slate-300 bg-white text-slate-900 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-100">{{ old('review_note') }}</textarea>
                </label>
                <div class="flex flex-wrap gap-4">
                    <button name="decision" value="publish" class="hag-broker-action">{{ __('carrier_schedules.publish') }}</button>
                    <button name="decision" value="return" class="font-semibold text-rose-800 underline dark:text-rose-300">{{ __('carrier_schedules.return') }}</button>
                </div>
            </form>
        @elseif($submission->review_note)
            <p class="hag-broker-card">{{ $submission->review_note }}</p>
        @endif
    </section>
</x-admin-layout>
