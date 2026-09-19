<x-admin-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                Ports
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Manage origin and destination ports across the HAG network.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="rounded-2xl bg-slate-900 p-5 text-white">
                    <p class="text-sm text-slate-400">Total Ports</p>
                    <p class="mt-2 text-3xl font-bold">{{ $ports->total() }}</p>
                </div>

                <div class="rounded-2xl bg-white p-5 shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                    <p class="text-sm text-slate-500">Gulf / UAE Network</p>
                    <p class="mt-2 text-3xl font-bold text-cyan-600">
                        {{ $ports->where('is_gulf', true)->count() }}
                    </p>
                </div>
            </div>

            <div
                class="mb-6 rounded-2xl bg-white p-4 shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                <form method="GET" action="{{ route('admin.ports.index') }}" class="flex flex-col gap-3 sm:flex-row">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search port, country, UNLOCODE or code..."
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
                                <th class="px-6 py-4 text-left">Port</th>
                                <th class="px-6 py-4 text-left">Country</th>
                                <th class="px-6 py-4 text-left">Port Code</th>
                                <th class="px-6 py-4 text-left">UNLOCODE</th>
                                <th class="px-6 py-4 text-left">Region</th>
                                <th class="px-6 py-4 text-left">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($ports as $port)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900 dark:text-white">
                                            {{ $port->name }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $port->country ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $port->port_code ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4 font-mono text-xs">
                                        {{ $port->unlocode ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        @if ($port->is_uae)
                                            <span
                                                class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-700">
                                                UAE
                                            </span>
                                        @elseif($port->is_gulf)
                                            <span
                                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                                Gulf
                                            </span>
                                        @else
                                            <span class="text-slate-500">International</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4">
                                        @if ($port->is_active)
                                            <span
                                                class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                                Active
                                            </span>
                                        @else
                                            <span
                                                class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                        No ports found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                {{ $ports->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
