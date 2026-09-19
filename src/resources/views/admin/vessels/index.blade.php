<x-admin-layout>

    <div class="mx-auto max-w-7xl">

        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <p class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-cyan-400">
                    Operations
                </p>

                <h1 class="text-3xl font-black tracking-tight text-white">
                    Vessels
                </h1>

                <p class="mt-2 max-w-2xl text-sm text-slate-400">
                    Manage vessels, carriers, positions and operational status.
                </p>
            </div>

            <a href="{{ route('admin.vessels.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/10 transition hover:bg-cyan-300">
                <span class="text-lg">+</span>
                Add Vessel
            </a>

        </div>


        {{-- Stats --}}
        <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">

            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Total
                </p>

                <p class="mt-2 text-3xl font-black text-white">
                    {{ $vessels->total() }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Active
                </p>

                <p class="mt-2 text-3xl font-black text-emerald-400">
                    {{ $vessels->where('status', 'active')->count() }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    In Gulf
                </p>

                <p class="mt-2 text-3xl font-black text-cyan-400">
                    {{ $vessels->where('is_in_gulf', true)->count() }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    With Position
                </p>

                <p class="mt-2 text-3xl font-black text-amber-400">
                    {{ $vessels->filter(fn($v) => $v->latitude !== null && $v->longitude !== null)->count() }}
                </p>
            </div>

        </div>


        {{-- Search --}}
        <div class="mb-6 rounded-2xl border border-slate-800 bg-slate-950 p-5">

            <form action="{{ route('admin.vessels.index') }}" method="GET" class="flex flex-col gap-3 lg:flex-row">

                <div class="relative flex-1">

                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search by vessel name, IMO, MMSI or carrier..."
                        class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

                </div>

                <select name="status"
                    class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(request('status') === 'active')>
                        Active
                    </option>
                    <option value="inactive" @selected(request('status') === 'inactive')>
                        Inactive
                    </option>
                    <option value="maintenance" @selected(request('status') === 'maintenance')>
                        Maintenance
                    </option>
                    <option value="unknown" @selected(request('status') === 'unknown')>
                        Unknown
                    </option>
                </select>

                <button type="submit"
                    class="rounded-xl bg-slate-800 px-6 py-3 text-sm font-bold text-white transition hover:bg-slate-700">
                    Search
                </button>

                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.vessels.index') }}"
                        class="rounded-xl border border-slate-700 px-6 py-3 text-center text-sm font-bold text-slate-400 transition hover:bg-slate-800 hover:text-white">
                        Clear
                    </a>
                @endif

            </form>

        </div>


        {{-- Desktop Table --}}
        <div class="hidden overflow-hidden rounded-2xl border border-slate-800 bg-slate-950 lg:block">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[1100px] text-left">

                    <thead class="border-b border-slate-800 bg-slate-900/70">

                        <tr>

                            <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                                Vessel
                            </th>

                            <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                                Carrier
                            </th>

                            <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                                IMO / MMSI
                            </th>

                            <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                                Position
                            </th>

                            <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                                Destination
                            </th>

                            <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                                Status
                            </th>

                            <th class="px-6 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-800">

                        @forelse($vessels as $vessel)

                            <tr class="transition hover:bg-slate-900/50">

                                {{-- Vessel --}}
                                <td class="px-6 py-5">

                                    <div class="flex items-center gap-3">

                                        <div
                                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10 text-xl">
                                            🚢
                                        </div>

                                        <div>
                                            <p class="font-bold text-white">
                                                {{ $vessel->name }}
                                            </p>

                                            <p class="mt-1 text-xs text-slate-500">
                                                ID #{{ $vessel->id }}

                                                @if ($vessel->vessel_type)
                                                    · {{ ucwords(str_replace('_', ' ', $vessel->vessel_type)) }}
                                                @endif
                                            </p>
                                        </div>

                                    </div>

                                </td>


                                {{-- Carrier --}}
                                <td class="px-6 py-5">

                                    @if ($vessel->carrier)
                                        <p class="font-semibold text-slate-200">
                                            {{ $vessel->carrier->name }}
                                        </p>

                                        @if ($vessel->carrier->code)
                                            <p class="mt-1 text-xs text-slate-500">
                                                {{ $vessel->carrier->code }}
                                            </p>
                                        @endif
                                    @else
                                        <span class="text-sm text-slate-600">
                                            No carrier
                                        </span>
                                    @endif

                                </td>


                                {{-- IMO / MMSI --}}
                                <td class="px-6 py-5">

                                    <div class="space-y-1 text-sm">

                                        <p>
                                            <span class="text-slate-500">IMO:</span>
                                            <span class="font-mono text-slate-300">
                                                {{ $vessel->imo ?? '—' }}
                                            </span>
                                        </p>

                                        <p>
                                            <span class="text-slate-500">MMSI:</span>
                                            <span class="font-mono text-slate-300">
                                                {{ $vessel->mmsi ?? '—' }}
                                            </span>
                                        </p>

                                    </div>

                                </td>


                                {{-- Position --}}
                                <td class="px-6 py-5">

                                    @if ($vessel->latitude !== null && $vessel->longitude !== null)
                                        <div class="text-sm">

                                            <p class="font-mono text-cyan-400">
                                                {{ number_format($vessel->latitude, 4) }},
                                                {{ number_format($vessel->longitude, 4) }}
                                            </p>

                                            @if ($vessel->speed !== null)
                                                <p class="mt-1 text-xs text-slate-500">
                                                    {{ number_format($vessel->speed, 1) }} knots
                                                </p>
                                            @endif

                                        </div>
                                    @else
                                        <span class="text-sm text-slate-600">
                                            No position
                                        </span>
                                    @endif

                                </td>


                                {{-- Destination --}}
                                <td class="px-6 py-5">

                                    <div>

                                        <p class="text-sm font-semibold text-slate-300">
                                            {{ $vessel->destination ?? '—' }}
                                        </p>

                                        @if ($vessel->eta)
                                            <p class="mt-1 text-xs text-slate-500">
                                                ETA:
                                                {{ $vessel->eta->format('d M Y H:i') }}
                                            </p>
                                        @endif

                                    </div>

                                </td>


                                {{-- Status --}}
                                <td class="px-6 py-5">

                                    <div class="flex flex-col items-start gap-2">

                                        <span
                                            class="inline-flex rounded-full px-3 py-1 text-xs font-bold
                                        @switch($vessel->status)
                                            @case('active')
                                                bg-emerald-400/10 text-emerald-400
                                                @break
                                            @case('maintenance')
                                                bg-amber-400/10 text-amber-400
                                                @break
                                            @default
                                                bg-slate-800 text-slate-400
                                        @endswitch
                                    ">
                                            {{ ucfirst($vessel->status) }}
                                        </span>

                                        @if ($vessel->is_in_gulf)
                                            <span class="text-xs font-bold text-cyan-400">
                                                ● In Gulf
                                            </span>
                                        @endif

                                    </div>

                                </td>


                                {{-- Actions --}}
                                <td class="px-6 py-5">

                                    <div class="flex justify-end gap-2">

                                        <a href="{{ route('admin.vessels.show', $vessel) }}"
                                            class="rounded-lg border border-slate-700 px-3 py-2 text-xs font-bold text-slate-300 transition hover:border-cyan-400 hover:text-cyan-400">
                                            View
                                        </a>

                                        <a href="{{ route('admin.vessels.edit', $vessel) }}"
                                            class="rounded-lg border border-slate-700 px-3 py-2 text-xs font-bold text-slate-300 transition hover:border-cyan-400 hover:text-cyan-400">
                                            Edit
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7" class="px-6 py-16 text-center">

                                    <div class="mx-auto max-w-md">

                                        <div class="mb-4 text-5xl">
                                            🚢
                                        </div>

                                        <h3 class="text-lg font-black text-white">
                                            No vessels found
                                        </h3>

                                        <p class="mt-2 text-sm text-slate-500">
                                            Add your first vessel to start managing vessel operations.
                                        </p>

                                        <a href="{{ route('admin.vessels.create') }}"
                                            class="mt-5 inline-flex rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950">
                                            Add Vessel
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- Mobile Cards --}}
        <div class="space-y-4 lg:hidden">

            @forelse($vessels as $vessel)
                <div class="rounded-2xl border border-slate-800 bg-slate-950 p-5">

                    <div class="flex items-start justify-between gap-4">

                        <div class="flex items-center gap-3">

                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-400/10 text-xl">
                                🚢
                            </div>

                            <div>

                                <h3 class="font-black text-white">
                                    {{ $vessel->name }}
                                </h3>

                                <p class="text-xs text-slate-500">
                                    {{ $vessel->carrier?->name ?? 'No carrier' }}
                                </p>

                            </div>

                        </div>

                        <span
                            class="rounded-full px-3 py-1 text-xs font-bold
                        {{ $vessel->status === 'active' ? 'bg-emerald-400/10 text-emerald-400' : 'bg-slate-800 text-slate-400' }}">
                            {{ ucfirst($vessel->status) }}
                        </span>

                    </div>


                    <div class="mt-5 grid grid-cols-2 gap-4 border-y border-slate-800 py-4">

                        <div>
                            <p class="text-xs text-slate-500">IMO</p>
                            <p class="mt-1 font-mono text-sm text-slate-300">
                                {{ $vessel->imo ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500">MMSI</p>
                            <p class="mt-1 font-mono text-sm text-slate-300">
                                {{ $vessel->mmsi ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500">Destination</p>
                            <p class="mt-1 text-sm text-slate-300">
                                {{ $vessel->destination ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500">Gulf</p>
                            <p
                                class="mt-1 text-sm font-bold {{ $vessel->is_in_gulf ? 'text-cyan-400' : 'text-slate-500' }}">
                                {{ $vessel->is_in_gulf ? 'Yes' : 'No' }}
                            </p>
                        </div>

                    </div>


                    <div class="flex gap-2">

                        <a href="{{ route('admin.vessels.show', $vessel) }}"
                            class="flex-1 rounded-xl border border-slate-700 px-4 py-3 text-center text-sm font-bold text-slate-300">
                            View
                        </a>

                        <a href="{{ route('admin.vessels.edit', $vessel) }}"
                            class="flex-1 rounded-xl bg-cyan-400 px-4 py-3 text-center text-sm font-black text-slate-950">
                            Edit
                        </a>

                    </div>

                </div>

            @empty

                <div class="rounded-2xl border border-slate-800 bg-slate-950 p-10 text-center">

                    <div class="text-5xl">🚢</div>

                    <h3 class="mt-4 font-black text-white">
                        No vessels found
                    </h3>

                    <a href="{{ route('admin.vessels.create') }}"
                        class="mt-5 inline-flex rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950">
                        Add Vessel
                    </a>

                </div>
            @endforelse

        </div>


        {{-- Pagination --}}
        @if ($vessels->hasPages())
            <div class="mt-6">
                {{ $vessels->withQueryString()->links() }}
            </div>
        @endif

    </div>
</x-admin-layout>
