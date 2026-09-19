<x-admin-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                Vessels
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Monitor vessels associated with carrier partners.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div
                class="mb-6 rounded-2xl bg-white p-4 shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                <form method="GET" action="{{ route('admin.vessels.index') }}" class="flex flex-col gap-3 sm:flex-row">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search vessel, IMO, MMSI or carrier..."
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                    <button class="rounded-xl bg-cyan-600 px-6 py-2.5 font-semibold text-white">
                        Search
                    </button>
                </form>
            </div>

            <div
                class="overflow-hidden rounded-2xl bg-white shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th class="px-6 py-4 text-left">Vessel</th>
                                <th class="px-6 py-4 text-left">Carrier</th>
                                <th class="px-6 py-4 text-left">IMO</th>
                                <th class="px-6 py-4 text-left">MMSI</th>
                                <th class="px-6 py-4 text-left">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($vessels as $vessel)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900 dark:text-white">
                                            {{ $vessel->name }}
                                        </div>

                                        @if (isset($vessel->flag))
                                            <div class="text-xs text-slate-500">
                                                Flag: {{ $vessel->flag }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $vessel->carrier?->name ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4 font-mono text-xs">
                                        {{ $vessel->imo ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4 font-mono text-xs">
                                        {{ $vessel->mmsi ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            Available
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                        No vessels found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                {{ $vessels->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
