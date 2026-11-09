<x-admin-layout>
    <section class="mx-auto max-w-7xl space-y-6">
        <header class="hag-broker-hero">
            <p class="hag-broker-eyebrow">HAG BROKERS · {{ __('sailings.workspace') }}</p>
            <h1 class="mt-2 font-black">{{ __('sailings.admin_title') }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-200">{{ __('sailings.admin_intro') }}</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a class="hag-broker-action" href="{{ route('admin.freight-sailings.create') }}">+ {{ __('sailings.create') }}</a>
                <a class="inline-flex min-h-11 items-center rounded-xl border border-cyan-200 px-4 py-2 text-sm font-bold text-white hover:bg-white/10" href="{{ route('admin.freight-sailings.import') }}">{{ __('sailing_import.title') }}</a>
            </div>
        </header>

        @if(session('success'))
            <p role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">{{ session('success') }}</p>
        @endif

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach(['' => __('sailings.all_sailings'), 'draft' => __('sailings.statuses.draft'), 'published' => __('sailings.statuses.published'), 'cancelled' => __('sailings.statuses.cancelled')] as $key => $label)
                <a href="{{ route('admin.freight-sailings.index', ['status' => $key, 'q' => $search]) }}"
                    @if($status === $key) aria-current="true" @endif
                    class="hag-broker-card block transition hover:shadow-md {{ $status === $key ? 'ring-2 ring-cyan-600 dark:ring-cyan-400' : '' }}">
                    <p class="hag-broker-muted">{{ $label }}</p>
                    <p class="hag-broker-number mt-1">{{ $key === '' ? $statusCounts->sum() : ($statusCounts[$key] ?? 0) }}</p>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.freight-sailings.index') }}" class="hag-broker-card flex flex-wrap items-end gap-3" role="search">
            <label class="grid min-w-0 flex-1 basis-56 gap-2 text-sm font-semibold">
                {{ __('sailings.admin_search') }}
                <input type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="{{ __('sailings.admin_search_hint') }}"
                    class="w-full border-slate-300 bg-white text-slate-900 placeholder:text-slate-600 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-400">
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                {{ __('sailings.status') }}
                <select name="status" class="border-slate-300 bg-white text-slate-900 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-100">
                    <option value="">{{ __('sailings.all_sailings') }}</option>
                    @foreach(\App\Models\FreightSailing::STATUSES as $value)
                        <option value="{{ $value }}" @selected($status === $value)>{{ __('sailings.statuses.'.$value) }}</option>
                    @endforeach
                </select>
            </label>
            <button class="hag-broker-action">{{ __('sailings.admin_search') }}</button>
            @if($search !== '' || $status !== '')
                <a class="self-center text-sm font-semibold underline" href="{{ route('admin.freight-sailings.index') }}">{{ __('sailings.clear_filters') }}</a>
            @endif
        </form>

        <div class="flex flex-wrap justify-between gap-2 hag-broker-muted">
            <p>{{ __('sailings.result_count', ['count' => $sailings->total()]) }}</p>
            <p>{{ __('sailings.dubai_time') }}</p>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            @forelse($sailings as $sailing)
                <article class="hag-broker-card flex flex-col gap-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-sm font-bold"><bdi>{{ $sailing->voyage_number }}</bdi></span>
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ match($sailing->status) {
                            'published' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200',
                            'cancelled' => 'bg-rose-100 text-rose-900 dark:bg-rose-950 dark:text-rose-200',
                            default => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200',
                        } }}">{{ __('sailings.statuses.'.$sailing->status) }}</span>
                    </div>
                    <h2 class="flex flex-wrap items-center gap-2 font-bold">
                        <a class="hover:underline" href="{{ route('admin.ports.edit', $sailing->originPort) }}"><bdi>{{ $sailing->originPort->name }}</bdi></a>
                        <span aria-hidden="true" class="text-cyan-700 dark:text-cyan-300">{{ app()->isLocale('ar') ? '←' : '→' }}</span>
                        <a class="hover:underline" href="{{ route('admin.ports.edit', $sailing->destinationPort) }}"><bdi>{{ $sailing->destinationPort->name }}</bdi></a>
                    </h2>
                    <p class="hag-broker-muted">
                        <a class="underline" href="{{ route('admin.carriers.edit', $sailing->carrier) }}"><bdi>{{ $sailing->carrier->name }}</bdi></a> ·
                        @if($sailing->vessel)
                            <a class="underline" href="{{ route('admin.vessels.edit', $sailing->vessel) }}"><bdi>{{ $sailing->vessel_name }}</bdi></a>
                        @else
                            <bdi>{{ $sailing->vessel_name }}</bdi>
                        @endif
                    </p>
                    <dl class="grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3 dark:bg-slate-950 sm:grid-cols-3">
                        @foreach(['departure_at', 'arrival_at', 'cargo_cutoff_at'] as $field)
                            <div>
                                <dt class="hag-broker-muted">{{ __('sailings.'.$field) }}</dt>
                                <dd class="text-sm font-semibold"><bdi>{{ $sailing->$field->setTimezone('Asia/Dubai')->format('Y-m-d H:i') }}</bdi></dd>
                            </div>
                        @endforeach
                    </dl>
                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-3 dark:border-slate-700">
                        <span class="hag-broker-muted">{{ implode(' · ', array_map(fn ($type) => __('sailings.containers.'.$type), $sailing->container_types)) }}</span>
                        <div class="flex flex-wrap gap-3 text-sm font-semibold">
                            <a class="underline" href="{{ route('admin.freight-sailings.edit', $sailing) }}">{{ __('sailings.edit') }}</a>
                            @if($sailing->status !== 'cancelled')
                                <details>
                                    <summary class="cursor-pointer text-rose-800 dark:text-rose-300">{{ __('sailings.cancel_short') }}</summary>
                                    <form method="POST" action="{{ route('admin.freight-sailings.cancel', $sailing) }}" class="mt-2">
                                        @csrf @method('PATCH')
                                        <button class="rounded-lg bg-rose-100 px-3 py-2 text-rose-950 dark:bg-rose-950 dark:text-rose-200">{{ __('sailings.cancel') }}</button>
                                    </form>
                                </details>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <p class="hag-broker-empty xl:col-span-2">{{ __('sailings.admin_empty') }}</p>
            @endforelse
        </div>
        {{ $sailings->links() }}
    </section>
</x-admin-layout>
