<x-admin-layout>
    <section class="mx-auto max-w-6xl space-y-5">
        <header class="hag-broker-hero">
            <a href="{{ route('admin.vessels.index') }}" class="text-sm text-cyan-200 underline">{{ __('sailing_import.vessels') }}</a>
            <h1 class="mt-2 font-black">{{ __('carrier_vessels.review_queue') }}</h1>
        </header>

        @if(session('success'))<p role="status">{{ session('success') }}</p>@endif
        @if($errors->any())
            <ul role="alert" class="text-rose-800 dark:text-rose-300">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        @forelse($requests as $proposal)
            <article class="hag-broker-card space-y-3">
                <h2><bdi>{{ $proposal->name }} · IMO {{ $proposal->imo }}</bdi></h2>
                <p class="hag-broker-muted"><bdi>{{ $proposal->carrier->name }} · {{ $proposal->vessel_type }}</bdi></p>
                @if($vessel = $proposal->registeredVessel)
                    <p>{{ __('carrier_vessels.existing') }} ·
                        <a class="underline" href="{{ route('admin.vessels.show', $vessel) }}"><bdi>{{ $vessel->name }}</bdi></a>
                        · <bdi>{{ $vessel->carrier?->name ?? __('carrier_vessels.unassigned') }}</bdi>
                        · <bdi>{{ $vessel->vessel_type ?? '—' }}</bdi>
                    </p>
                @endif
                @php($canApprove = $proposal->carrier->is_active && (!$vessel || (int) $vessel->carrier_id === $proposal->carrier->id))
                @unless($canApprove)<p class="text-sm text-rose-800 dark:text-rose-300">{{ __('carrier_vessels.conflict') }}</p>@endunless
                <form method="POST" action="{{ route('admin.vessels.partner-requests.review', $proposal) }}" class="space-y-3">
                    @csrf @method('PATCH')
                    <label class="grid gap-1 text-sm font-semibold">{{ __('carrier_vessels.note') }}
                        <textarea name="review_note" maxlength="500" rows="2" class="rounded-xl border-slate-300 bg-white text-slate-900 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-100"></textarea>
                    </label>
                    <div class="flex flex-wrap gap-4">
                        @if($canApprove)<button name="decision" value="approve" class="hag-broker-action">{{ __('carrier_vessels.approve') }}</button>@endif
                        <button name="decision" value="reject" class="text-sm font-semibold text-rose-800 underline dark:text-rose-300">{{ __('carrier_vessels.reject') }}</button>
                    </div>
                </form>
            </article>
        @empty
            <p class="hag-broker-empty">{{ __('carrier_vessels.empty') }}</p>
        @endforelse

        {{ $requests->links() }}
    </section>
</x-admin-layout>
