<section id="cargo-vessel-board" class="relative mx-auto max-w-7xl px-5 py-12 lg:px-8 lg:py-16">
    <div class="overflow-hidden rounded-[2rem] border border-cyan-300/15 bg-gradient-to-br from-[#0a2033] to-[#07111f] p-7 text-white shadow-2xl sm:p-10">
        <p class="text-xs font-black tracking-[.2em] text-cyan-300">{{ __('maritime_board.eyebrow') }}</p>
        <div class="mt-3 grid gap-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
            <div><h2 class="text-2xl font-black sm:text-3xl">{{ __('maritime_board.title') }}</h2><p class="mt-3 max-w-3xl text-sm leading-7 text-slate-300">{{ __('maritime_board.intro') }}</p></div>
            <a href="{{ route('maritime-board.index') }}" class="inline-flex items-center justify-center rounded-xl bg-cyan-300 px-6 py-3 text-sm font-black text-slate-950 transition hover:-translate-y-0.5 hover:bg-cyan-200">{{ __('maritime_board.title') }} <span class="ms-2" aria-hidden="true">↗</span></a>
        </div>
        <div class="mt-7 grid gap-3 sm:grid-cols-2">
            <a href="{{ route('maritime-board.index', ['tab' => 'cargoes']) }}" class="rounded-2xl border border-white/10 bg-white/[.05] p-5 transition hover:bg-white/10"><span class="text-xs font-black tracking-wider text-cyan-300">01</span><span class="mt-2 block text-lg font-black">{{ __('maritime_board.cargoes_tab') }}</span><span class="mt-1 block text-sm text-slate-400">{{ __('maritime_board.cargoes_hint') }}</span></a>
            <a href="{{ route('maritime-board.index', ['tab' => 'vessels']) }}" class="rounded-2xl border border-white/10 bg-white/[.05] p-5 transition hover:bg-white/10"><span class="text-xs font-black tracking-wider text-cyan-300">02</span><span class="mt-2 block text-lg font-black">{{ __('maritime_board.vessels_tab') }}</span><span class="mt-1 block text-sm text-slate-400">{{ __('maritime_board.vessels_hint') }}</span></a>
        </div>
    </div>
</section>
