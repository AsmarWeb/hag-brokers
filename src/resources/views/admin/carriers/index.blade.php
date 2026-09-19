<x-admin-layout>

    <div class="mx-auto max-w-7xl">

        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <p class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-cyan-400">
                    Operations
                </p>

                <h1 class="text-3xl font-black tracking-tight text-white">
                    Carriers
                </h1>

                <p class="mt-2 max-w-2xl text-sm text-slate-400">
                    Manage shipping lines and logistics carriers used by HAG Brokers.
                </p>

            </div>


            <a href="{{ route('admin.carriers.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/10 transition hover:bg-cyan-300">
                <span class="text-lg">+</span>
                Add Carrier
            </a>

        </div>


        {{-- Stats --}}
        <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">

            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-5">

                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Total
                </p>

                <p class="mt-2 text-3xl font-black text-white">
                    {{ $carriers->total() }}
                </p>

            </div>


            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-5">

                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Active
                </p>

                <p class="mt-2 text-3xl font-black text-emerald-400">
                    {{ $carriers->where('is_active', true)->count() }}
                </p>

            </div>


            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-5">

                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Vessels
                </p>

                <p class="mt-2 text-3xl font-black text-cyan-400">
                    {{ $carriers->sum('vessels_count') }}
                </p>

            </div>


            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-5">

                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    API Connected
                </p>

                <p class="mt-2 text-3xl font-black text-amber-400">
                    {{ $carriers->filter(fn($carrier) => filled($carrier->api_provider))->count() }}
                </p>

            </div>

        </div>


        {{-- Search --}}
        <div class="mb-6 rounded-2xl border border-slate-800 bg-slate-950 p-5">

            <form action="{{ route('admin.carriers.index') }}" method="GET" class="flex flex-col gap-3 lg:flex-row">

                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by carrier name, code or country..."
                    class="flex-1 rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">


                <select name="status"
                    class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                    <option value="">
                        All Statuses
                    </option>

                    <option value="active" @selected(request('status') === 'active')>
                        Active
                    </option>

                    <option value="inactive" @selected(request('status') === 'inactive')>
                        Inactive
                    </option>

                </select>


                <button type="submit"
                    class="rounded-xl bg-slate-800 px-6 py-3 text-sm font-bold text-white transition hover:bg-slate-700">
                    Search
                </button>


                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.carriers.index') }}"
                        class="rounded-xl border border-slate-700 px-6 py-3 text-center text-sm font-bold text-slate-400 transition hover:bg-slate-800 hover:text-white">
                        Clear
                    </a>
                @endif

            </form>

        </div>


        {{-- Desktop Table --}}
        <div class="hidden overflow-hidden rounded-2xl border border-slate-800 bg-slate-950 lg:block">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[1000px] text-left">

                    <thead class="border-b border-slate-800 bg-slate-900/70">

                        <tr>

                            <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                                Carrier
                            </th>

                            <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                                Country
                            </th>

                            <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                                Website
                            </th>

                            <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                                API
                            </th>

                            <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">
                                Vessels
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

                        @forelse($carriers as $carrier)
                            <tr class="transition hover:bg-slate-900/50">

                                {{-- Carrier --}}
                                <td class="px-6 py-5">

                                    <div class="flex items-center gap-3">

                                        <div
                                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10 text-xl">
                                            ⚓
                                        </div>

                                        <div>

                                            <p class="font-bold text-white">
                                                {{ $carrier->name }}
                                            </p>

                                            <div class="mt-1 flex items-center gap-2">

                                                <span class="text-xs text-slate-500">
                                                    ID #{{ $carrier->id }}
                                                </span>

                                                @if ($carrier->code)
                                                    <span
                                                        class="rounded-md bg-slate-800 px-2 py-0.5 font-mono text-[10px] font-bold text-cyan-400">
                                                        {{ $carrier->code }}
                                                    </span>
                                                @endif

                                            </div>

                                        </div>

                                    </div>

                                </td>


                                {{-- Country --}}
                                <td class="px-6 py-5">

                                    <div>

                                        <p class="text-sm font-semibold text-slate-300">
                                            {{ $carrier->country ?? '—' }}
                                        </p>

                                        @if ($carrier->country_code)
                                            <p class="mt-1 font-mono text-xs text-slate-500">
                                                {{ $carrier->country_code }}
                                            </p>
                                        @endif

                                    </div>

                                </td>


                                {{-- Website --}}
                                <td class="px-6 py-5">

                                    @if ($carrier->website)
                                        <a href="{{ $carrier->website }}" target="_blank" rel="noopener noreferrer"
                                            class="text-sm font-semibold text-cyan-400 transition hover:text-cyan-300">
                                            Visit Website ↗
                                        </a>
                                    @else
                                        <span class="text-sm text-slate-600">
                                            —
                                        </span>
                                    @endif

                                </td>


                                {{-- API --}}
                                <td class="px-6 py-5">

                                    @if ($carrier->api_provider)
                                        <span
                                            class="inline-flex rounded-full bg-cyan-400/10 px-3 py-1 text-xs font-bold text-cyan-400">
                                            {{ $carrier->api_provider }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full bg-slate-800 px-3 py-1 text-xs font-bold text-slate-500">
                                            Manual
                                        </span>
                                    @endif

                                </td>


                                {{-- Vessels --}}
                                <td class="px-6 py-5">

                                    <div class="flex items-center gap-2">

                                        <span class="text-lg font-black text-white">
                                            {{ $carrier->vessels_count }}
                                        </span>

                                        <span class="text-xs text-slate-500">
                                            vessels
                                        </span>

                                    </div>

                                </td>


                                {{-- Status --}}
                                <td class="px-6 py-5">

                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-bold
                                    {{ $carrier->is_active ? 'bg-emerald-400/10 text-emerald-400' : 'bg-slate-800 text-slate-500' }}">
                                        {{ $carrier->is_active ? 'Active' : 'Inactive' }}
                                    </span>

                                </td>


                                {{-- Actions --}}
                                <td class="px-6 py-5">

                                    <div class="flex justify-end gap-2">

                                        <a href="{{ route('admin.carriers.show', $carrier) }}"
                                            class="rounded-lg border border-slate-700 px-3 py-2 text-xs font-bold text-slate-300 transition hover:border-cyan-400 hover:text-cyan-400">
                                            View
                                        </a>

                                        <a href="{{ route('admin.carriers.edit', $carrier) }}"
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
                                            ⚓
                                        </div>

                                        <h3 class="text-lg font-black text-white">
                                            No carriers found
                                        </h3>

                                        <p class="mt-2 text-sm text-slate-500">
                                            Add your first carrier to start managing shipping lines.
                                        </p>

                                        <a href="{{ route('admin.carriers.create') }}"
                                            class="mt-5 inline-flex rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950">
                                            Add Carrier
                                        </a>

                                    </div>

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- Mobile --}}
        <div class="space-y-4 lg:hidden">

            @forelse($carriers as $carrier)
                <div class="rounded-2xl border border-slate-800 bg-slate-950 p-5">

                    <div class="flex items-start justify-between gap-4">

                        <div class="flex items-center gap-3">

                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-400/10 text-xl">
                                ⚓
                            </div>

                            <div>

                                <h3 class="font-black text-white">
                                    {{ $carrier->name }}
                                </h3>

                                <p class="text-xs text-slate-500">
                                    {{ $carrier->code ?? 'No code' }}
                                </p>

                            </div>

                        </div>


                        <span
                            class="rounded-full px-3 py-1 text-xs font-bold
                        {{ $carrier->is_active ? 'bg-emerald-400/10 text-emerald-400' : 'bg-slate-800 text-slate-500' }}">
                            {{ $carrier->is_active ? 'Active' : 'Inactive' }}
                        </span>

                    </div>


                    <div class="mt-5 grid grid-cols-2 gap-4 border-y border-slate-800 py-4">

                        <div>

                            <p class="text-xs text-slate-500">
                                Country
                            </p>

                            <p class="mt-1 text-sm text-slate-300">
                                {{ $carrier->country ?? '—' }}
                            </p>

                        </div>


                        <div>

                            <p class="text-xs text-slate-500">
                                Vessels
                            </p>

                            <p class="mt-1 text-sm font-black text-cyan-400">
                                {{ $carrier->vessels_count }}
                            </p>

                        </div>


                        <div>

                            <p class="text-xs text-slate-500">
                                API
                            </p>

                            <p class="mt-1 text-sm text-slate-300">
                                {{ $carrier->api_provider ?? 'Manual' }}
                            </p>

                        </div>


                        <div>

                            <p class="text-xs text-slate-500">
                                Code
                            </p>

                            <p class="mt-1 font-mono text-sm text-slate-300">
                                {{ $carrier->code ?? '—' }}
                            </p>

                        </div>

                    </div>


                    <div class="flex gap-2">

                        <a href="{{ route('admin.carriers.show', $carrier) }}"
                            class="flex-1 rounded-xl border border-slate-700 px-4 py-3 text-center text-sm font-bold text-slate-300">
                            View
                        </a>

                        <a href="{{ route('admin.carriers.edit', $carrier) }}"
                            class="flex-1 rounded-xl bg-cyan-400 px-4 py-3 text-center text-sm font-black text-slate-950">
                            Edit
                        </a>

                    </div>

                </div>

            @empty

                <div class="rounded-2xl border border-slate-800 bg-slate-950 p-10 text-center">

                    <div class="text-5xl">
                        ⚓
                    </div>

                    <h3 class="mt-4 font-black text-white">
                        No carriers found
                    </h3>

                    <a href="{{ route('admin.carriers.create') }}"
                        class="mt-5 inline-flex rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950">
                        Add Carrier
                    </a>

                </div>
            @endforelse

        </div>


        {{-- Pagination --}}
        @if ($carriers->hasPages())
            <div class="mt-6">
                {{ $carriers->withQueryString()->links() }}
            </div>
        @endif

    </div>
</x-admin-layout>
