<main class="mx-auto max-w-7xl px-5 py-12" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
    <div class="{{ $panelClass }} rounded-3xl p-6">
        <p class="text-sm font-bold text-cyan-300">HAG BROKERS · FCL</p>
        <h1 class="mt-3 text-3xl font-black">{{ __('sailings.title') }}</h1>
        <p class="mt-3">{{ __('sailings.intro') }}</p>
        @if($errors->any())
            <ul class="my-4" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        @endif
        <form action="{{ route($searchRoute) }}" method="GET" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach(['origin_port_id', 'destination_port_id'] as $field)
                <label class="grid gap-2">{{ __('sailings.'.$field) }}
                    <select name="{{ $field }}" required class="rounded-xl text-slate-900">
                        <option value="">{{ __('sailings.choose') }}</option>
                        @foreach($ports as $port)
                            <option value="{{ $port->id }}" @selected((string) ($filters[$field] ?? '') === (string) $port->id)>{{ $port->name }} ({{ $port->unlocode }})</option>
                        @endforeach
                    </select>
                </label>
            @endforeach
            <label class="grid gap-2">{{ __('sailings.ready_on') }}<input type="date" name="ready_on" min="{{ now('Asia/Dubai')->toDateString() }}" value="{{ $filters['ready_on'] ?? '' }}" class="rounded-xl text-slate-900"></label>
            <label class="grid gap-2">{{ __('sailings.container_type') }}
                <select name="container_type" class="rounded-xl text-slate-900">
                    <option value="">{{ __('sailings.any') }}</option>
                    @foreach(\App\Models\FreightSailing::CONTAINER_TYPES as $type)
                        <option @selected(($filters['container_type'] ?? null) === $type) value="{{ $type }}">{{ __('sailings.containers.'.$type) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-2">{{ __('sailings.quantity') }}<input type="number" name="quantity" min="1" max="10000" value="{{ $filters['quantity'] ?? 1 }}" class="rounded-xl text-slate-900"></label>
            <button class="self-end rounded-xl bg-cyan-400 px-5 py-3 font-bold text-slate-950">{{ __('sailings.search') }}</button>
        </form>
    </div>
    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        @forelse($sailings as $sailing)
            <article class="{{ $panelClass }} rounded-2xl p-5">
                @include('freight.selection-summary', ['snapshot' => app(\App\Services\Freight\SailingCatalog::class)->snapshot($sailing)])
                <form method="GET" action="{{ route('freight.sailings.select', $sailing) }}" class="mt-4 flex flex-wrap items-end gap-3">
                    <input type="hidden" name="ready_on" value="{{ $filters['ready_on'] ?? '' }}">
                    <label class="grid gap-2">{{ __('sailings.container_type') }}
                        <select name="container_type" class="rounded-xl text-slate-900">
                            @foreach($sailing->container_types as $type)
                                <option @selected(($filters['container_type'] ?? null) === $type) value="{{ $type }}">{{ __('sailings.containers.'.$type) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-2">{{ __('sailings.quantity') }}<input type="number" name="quantity" min="1" max="10000" required value="{{ $filters['quantity'] ?? 1 }}" class="rounded-xl text-slate-900"></label>
                    <button class="rounded-xl bg-cyan-400 px-4 py-3 font-bold text-slate-950">{{ __('sailings.request') }}</button>
                </form>
            </article>
        @empty
            <p role="status">{{ __('sailings.empty') }} <a class="underline" href="{{ route('quotations.create') }}">{{ __('sailings.generic_request') }}</a></p>
        @endforelse
    </div>
    <div class="mt-6">{{ $sailings->links() }}</div>
</main>
