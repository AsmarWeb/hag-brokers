@php
    $input = 'w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 placeholder-slate-400 shadow-sm
                focus:border-cyan-500 focus:ring-cyan-500
                dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500
                dark:focus:border-cyan-400 dark:focus:ring-cyan-400 dark:[color-scheme:dark]';
    $filterBox = 'grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900';
    $filterBtn = 'rounded-xl bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:bg-slate-700
                  dark:bg-cyan-300 dark:text-slate-950 dark:hover:bg-cyan-200';
    $card = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-cyan-300 hover:shadow-lg
                dark:border-slate-800 dark:bg-slate-900 dark:shadow-none dark:hover:border-cyan-500/50 dark:hover:bg-slate-900/80 sm:p-6';
    $stat = 'rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60';
    $statLbl = 'text-xs text-slate-500 dark:text-slate-400';
    $statVal = 'mt-1 font-bold text-slate-900 dark:text-slate-100';
    $ref = 'text-xs font-black tracking-wider text-cyan-700 dark:text-cyan-300';
    $title = 'mt-2 text-lg font-black text-slate-900 dark:text-white';
    $badge =
        'rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300';
    $cta = 'rounded-xl bg-cyan-300 px-5 py-3 text-sm font-black text-slate-950 transition hover:bg-cyan-200
                focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 dark:bg-cyan-400 dark:hover:bg-cyan-300 dark:focus:ring-offset-slate-900';
    $empty = 'rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-sm leading-7 text-slate-500
                dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400';
    $tabBase = 'rounded-xl px-5 py-3 text-sm font-black transition';
    $tabOn = 'bg-[#071522] text-white shadow-lg dark:bg-cyan-300 dark:text-slate-950 dark:shadow-none';
    $tabOff =
        'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700';
@endphp

<x-market-layout>
    <section class="mx-auto max-w-7xl space-y-7 px-4 py-10 sm:px-6 lg:py-14"
        dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">

        <header
            class="relative isolate overflow-hidden rounded-[2rem] bg-[#071522] px-6 py-9 text-white shadow-2xl
                   dark:border dark:border-white/10 dark:shadow-none sm:px-10 sm:py-12">
            <div aria-hidden="true" class="absolute -end-24 -top-32 -z-10 h-80 w-80 rounded-full bg-cyan-400/20 blur-3xl">
            </div>
            <p class="text-xs font-black tracking-[.2em] text-cyan-300">{{ __('maritime_board.eyebrow') }}</p>
            <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-5xl">{{ __('maritime_board.title') }}</h1>
            <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-300 sm:text-base">{{ __('maritime_board.intro') }}</p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ route('maritime-board.submit.create', 'cargo') }}"
                    class="rounded-xl bg-cyan-300 px-5 py-3 text-sm font-black text-slate-950 transition hover:bg-cyan-200">{{ __('maritime_board.post_cargo') }}</a>
                <a href="{{ route('maritime-board.submit.create', 'vessel') }}"
                    class="rounded-xl border border-white/20 bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">{{ __('maritime_board.list_vessel') }}</a>
            </div>
        </header>

        @if (session('marine_board_submitted'))
            <div role="status"
                class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900
                                  dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200">
                {{ session('marine_board_submitted') }}
            </div>
        @endif

        <div
            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600
                dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-400">
            {{ __('maritime_board.review_notice') }}
        </div>

        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3 dark:border-slate-800" role="tablist"
            aria-label="{{ __('maritime_board.title') }}">
            <a role="tab" aria-selected="{{ $activeTab === 'cargoes' ? 'true' : 'false' }}"
                href="{{ route('maritime-board.index', ['tab' => 'cargoes']) }}"
                class="{{ $tabBase }} {{ $activeTab === 'cargoes' ? $tabOn : $tabOff }}">
                {{ __('maritime_board.cargoes_tab') }} <span
                    class="ms-1 text-xs opacity-70">({{ $cargoes->total() }})</span>
            </a>
            <a role="tab" aria-selected="{{ $activeTab === 'vessels' ? 'true' : 'false' }}"
                href="{{ route('maritime-board.index', ['tab' => 'vessels']) }}"
                class="{{ $tabBase }} {{ $activeTab === 'vessels' ? $tabOn : $tabOff }}">
                {{ __('maritime_board.vessels_tab') }} <span
                    class="ms-1 text-xs opacity-70">({{ $vessels->total() }})</span>
            </a>
        </div>

        @if ($activeTab === 'cargoes')
            <div class="space-y-4" role="tabpanel">
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('maritime_board.cargoes_hint') }}</p>

                <form method="GET" action="{{ route('maritime-board.index') }}"
                    class="{{ $filterBox }} sm:grid-cols-2 lg:grid-cols-4">
                    <input type="hidden" name="tab" value="cargoes">
                    <select name="cargo_type" class="{{ $input }}">
                        <option value="">{{ __('maritime_board.all_types') }}</option>
                        @foreach (__('maritime_board.cargo_types') as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['cargo_type'] ?? '') === $key)>{{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <select name="loading_port_id" class="{{ $input }}">
                        <option value="">{{ __('maritime_board.loading_port') }}</option>
                        @foreach ($ports as $port)
                            <option value="{{ $port->id }}" @selected((string) ($filters['loading_port_id'] ?? '') === (string) $port->id)>{{ $port->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="discharging_port_id" class="{{ $input }}">
                        <option value="">{{ __('maritime_board.discharging_port') }}</option>
                        @foreach ($ports as $port)
                            <option value="{{ $port->id }}" @selected((string) ($filters['discharging_port_id'] ?? '') === (string) $port->id)>{{ $port->name }}
                            </option>
                        @endforeach
                    </select>
                    <button class="{{ $filterBtn }}">{{ __('maritime_board.filter') }}</button>
                </form>

                @forelse($cargoes as $cargo)
                    <article class="{{ $card }}">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="{{ $ref }}">{{ __('maritime_board.reference') }} ·
                                    {{ $cargo->public_reference }}</p>
                                <h2 class="{{ $title }}">
                                    {{ __('maritime_board.cargo_types.' . $cargo->cargo_type, [], app()->getLocale()) !== 'maritime_board.cargo_types.' . $cargo->cargo_type ? __('maritime_board.cargo_types.' . $cargo->cargo_type) : $cargo->cargo_type }}
                                </h2>
                            </div>
                            <span class="{{ $badge }}">{{ __('maritime_board.available_cargo') }}</span>
                        </div>

                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">
                            {{ $cargo->public_description }}</p>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="{{ $stat }}">
                                <p class="{{ $statLbl }}">{{ __('maritime_board.loading_port') }}</p>
                                <p class="{{ $statVal }}">{{ $cargo->loadingPort?->name }}</p>
                            </div>
                            <div class="{{ $stat }}">
                                <p class="{{ $statLbl }}">{{ __('maritime_board.discharging_port') }}</p>
                                <p class="{{ $statVal }}">{{ $cargo->dischargingPort?->name }}</p>
                            </div>
                            <div class="{{ $stat }}">
                                <p class="{{ $statLbl }}">{{ __('maritime_board.quantity') }}</p>
                                <p class="{{ $statVal }}">{{ number_format((float) $cargo->quantity, 3) }} MT</p>
                            </div>
                            <div class="{{ $stat }}">
                                <p class="{{ $statLbl }}">{{ __('maritime_board.laycan_from') }} –
                                    {{ __('maritime_board.laycan_to') }}</p>
                                <p class="{{ $statVal }}">{{ $cargo->laycan_from?->format('d M Y') }} –
                                    {{ $cargo->laycan_to?->format('d M Y') }}</p>
                                @if ($cargo->readiness_date)
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        {{ __('maritime_board.readiness_date') }}:
                                        {{ $cargo->readiness_date?->format('d M Y') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-5 flex justify-end">
                            <a href="{{ route('maritime-board.inquiry.create', ['kind' => 'cargo', 'reference' => $cargo->public_reference]) }}"
                                class="{{ $cta }}">{{ __('maritime_board.submit_offer_inquire') }}</a>
                        </div>
                    </article>
                @empty
                    <div class="{{ $empty }}">{{ __('maritime_board.empty_cargoes') }}</div>
                @endforelse

                <div>{{ $cargoes->links() }}</div>
            </div>
        @else
            <div class="space-y-4" role="tabpanel">
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('maritime_board.vessels_hint') }}</p>

                <form method="GET" action="{{ route('maritime-board.index') }}"
                    class="{{ $filterBox }} sm:grid-cols-2 lg:grid-cols-5">
                    <input type="hidden" name="tab" value="vessels">
                    <select name="vessel_type" class="{{ $input }}">
                        <option value="">{{ __('maritime_board.all_types') }}</option>
                        @foreach (__('maritime_board.vessel_types') as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['vessel_type'] ?? '') === $key)>{{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <select name="current_port_id" class="{{ $input }}">
                        <option value="">{{ __('maritime_board.current_port') }}</option>
                        @foreach ($ports as $port)
                            <option value="{{ $port->id }}" @selected((string) ($filters['current_port_id'] ?? '') === (string) $port->id)>{{ $port->name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="number" min="0" name="dwt_min" value="{{ $filters['dwt_min'] ?? '' }}"
                        placeholder="{{ __('maritime_board.minimum_dwt') }}" class="{{ $input }}">
                    <input type="date" name="open_from" value="{{ $filters['open_from'] ?? '' }}"
                        aria-label="{{ __('maritime_board.open_from') }}" class="{{ $input }}">
                    <button class="{{ $filterBtn }}">{{ __('maritime_board.filter') }}</button>
                </form>

                @forelse($vessels as $vessel)
                    <article class="{{ $card }}">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="{{ $ref }}">{{ __('maritime_board.reference') }} ·
                                    {{ $vessel->public_reference }}</p>
                                <h2 class="{{ $title }}">
                                    {{ __('maritime_board.vessel_types.' . $vessel->vessel_type, [], app()->getLocale()) !== 'maritime_board.vessel_types.' . $vessel->vessel_type ? __('maritime_board.vessel_types.' . $vessel->vessel_type) : $vessel->vessel_type }}
                                </h2>
                            </div>
                            <span class="{{ $badge }}">{{ __('maritime_board.open_vessel') }}</span>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="{{ $stat }}">
                                <p class="{{ $statLbl }}">{{ __('maritime_board.dwt_capacity') }}</p>
                                <p class="{{ $statVal }}">
                                    {{ number_format((float) $vessel->dwt_capacity, 0) }}
                                    MT</p>
                            </div>
                            <div class="{{ $stat }}">
                                <p class="{{ $statLbl }}">{{ __('maritime_board.current_position') }}</p>
                                <p class="{{ $statVal }}">
                                    {{ $vessel->currentPort?->name ?: $vessel->current_position }}</p>
                            </div>
                            <div class="{{ $stat }}">
                                <p class="{{ $statLbl }}">{{ __('maritime_board.open_date') }}</p>
                                <p class="{{ $statVal }}">{{ $vessel->open_date?->format('d M Y') }}</p>
                            </div>
                            <div class="{{ $stat }}">
                                <p class="{{ $statLbl }}">{{ __('maritime_board.preferred_trading_area') }}
                                </p>
                                <p class="{{ $statVal }}">{{ $vessel->preferred_trading_area }}</p>
                            </div>
                        </div>

                        <div class="mt-5 flex justify-end">
                            <a href="{{ route('maritime-board.inquiry.create', ['kind' => 'vessel', 'reference' => $vessel->public_reference]) }}"
                                class="{{ $cta }}">{{ __('maritime_board.submit_offer_inquire') }}</a>
                        </div>
                    </article>
                @empty
                    <div class="{{ $empty }}">{{ __('maritime_board.empty_vessels') }}</div>
                @endforelse

                <div>{{ $vessels->links() }}</div>
            </div>
        @endif
    </section>
</x-market-layout>
