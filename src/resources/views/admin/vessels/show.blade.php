<x-admin-layout>

    <div class="mx-auto max-w-7xl">

        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-5 md:flex-row md:items-center md:justify-between">

            <div>

                <div class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">

                    <a href="{{ route('admin.vessels.index') }}" class="hover:text-cyan-400">
                        {{ __('admin_ui.label_148') }}
                    </a>

                    <span>/</span>

                    <span class="text-slate-400">
                        {{ $vessel->name }}
                    </span>

                </div>

                <div class="flex items-center gap-4">

                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/10 text-3xl">
                        🚢
                    </div>

                    <div>

                        <h1 class="text-3xl font-black text-white">
                            {{ $vessel->name }}
                        </h1>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Vessel #{{ $vessel->id }}
                        </p>

                    </div>

                </div>

            </div>


            <div class="flex gap-3">

                <a href="{{ route('admin.vessels.edit', $vessel) }}"
                    class="rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950 transition hover:bg-cyan-300">
                    {{ __('admin_ui.label_070') }}
                </a>

                <a href="{{ route('admin.vessels.index') }}"
                    class="rounded-xl border border-slate-700 px-5 py-3 text-sm font-bold text-slate-300 transition hover:bg-slate-800 hover:text-white">
                    {{ __('admin_ui.label_030') }}
                </a>

            </div>

        </div>


        {{-- Main grid --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Vessel Information --}}
            <div class="lg:col-span-2 rounded-2xl border border-slate-800 bg-slate-950">

                <div class="border-b border-slate-800 px-6 py-5">

                    <h2 class="font-black text-white">
                        {{ __('admin_ui.label_071') }}
                    </h2>

                </div>

                <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2">

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            {{ __('admin_ui.label_002') }}
                        </p>

                        <p class="mt-2 font-bold text-white">
                            {{ $vessel->carrier?->name ?? 'Not assigned' }}
                        </p>
                    </div>


                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Vessel Type
                        </p>

                        <p class="mt-2 font-bold text-white">
                            {{ $vessel->vessel_type ? ucwords(str_replace(['_', '-'], ' ', $vessel->vessel_type)) : '—' }}
                        </p>
                    </div>


                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            IMO
                        </p>

                        <p class="mt-2 font-mono text-white">
                            {{ $vessel->imo ?? '—' }}
                        </p>
                    </div>


                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            MMSI
                        </p>

                        <p class="mt-2 font-mono text-white">
                            {{ $vessel->mmsi ?? '—' }}
                        </p>
                    </div>


                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Call Sign
                        </p>

                        <p class="mt-2 font-mono text-white">
                            {{ $vessel->call_sign ?? '—' }}
                        </p>
                    </div>


                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Flag
                        </p>

                        <p class="mt-2 font-bold text-white">
                            {{ $vessel->flag ?? '—' }}
                        </p>
                    </div>


                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            {{ __('admin_ui.label_013') }}
                        </p>

                        <p class="mt-2 font-bold text-white">
                            {{ $vessel->destination ?? '—' }}
                        </p>
                    </div>


                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            {{ __('admin_ui.label_001') }}
                        </p>

                        <span
                            class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $vessel->status === 'active' ? 'bg-emerald-400/10 text-emerald-400' : 'bg-slate-800 text-slate-400' }}">
                            {{ ucfirst($vessel->status) }}
                        </span>
                    </div>

                </div>

            </div>


            {{-- Status Card --}}
            <div class="rounded-2xl border border-slate-800 bg-slate-950">

                <div class="border-b border-slate-800 px-6 py-5">

                    <h2 class="font-black text-white">
                        Operational Status
                    </h2>

                </div>

                <div class="space-y-5 p-6">

                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Gulf Status
                        </p>

                        <p
                            class="mt-2 text-lg font-black {{ $vessel->is_in_gulf ? 'text-cyan-400' : 'text-slate-500' }}">
                            {{ $vessel->is_in_gulf ? 'Inside Gulf' : 'Outside Gulf' }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Speed
                        </p>

                        <p class="mt-2 text-lg font-black text-white">
                            {{ $vessel->speed !== null ? number_format($vessel->speed, 2) . ' knots' : '—' }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Course
                        </p>

                        <p class="mt-2 text-lg font-black text-white">
                            {{ $vessel->course !== null ? number_format($vessel->course, 2) . '°' : '—' }}
                        </p>

                    </div>

                </div>

            </div>

        </div>


        {{-- Position --}}
        <div class="mt-6 rounded-2xl border border-slate-800 bg-slate-950">

            <div class="border-b border-slate-800 px-6 py-5">

                <h2 class="font-black text-white">
                    Current Position
                </h2>

            </div>

            <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-3">

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Latitude
                    </p>

                    <p class="mt-2 font-mono text-cyan-400">
                        {{ $vessel->latitude !== null ? number_format($vessel->latitude, 7) : '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Longitude
                    </p>

                    <p class="mt-2 font-mono text-cyan-400">
                        {{ $vessel->longitude !== null ? number_format($vessel->longitude, 7) : '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Last Position
                    </p>

                    <p class="mt-2 text-slate-300">
                        {{ $vessel->last_position_at ? $vessel->last_position_at->format('d M Y H:i') : 'No data' }}
                    </p>
                </div>

            </div>

        </div>


        {{-- ETA --}}
        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">

            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-6">

                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Estimated Time of Arrival
                </p>

                <p class="mt-3 text-2xl font-black text-white">

                    {{ $vessel->eta ? $vessel->eta->format('d M Y') : '—' }}

                </p>

                @if ($vessel->eta)
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $vessel->eta->format('H:i') }}
                    </p>
                @endif

            </div>


            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-6">

                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    {{ __('admin_ui.label_002') }}
                </p>

                <p class="mt-3 text-2xl font-black text-white">
                    {{ $vessel->carrier?->name ?? 'Not assigned' }}
                </p>

                @if ($vessel->carrier?->code)
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $vessel->carrier->code }}
                    </p>
                @endif

            </div>

        </div>

    </div>
</x-admin-layout>
