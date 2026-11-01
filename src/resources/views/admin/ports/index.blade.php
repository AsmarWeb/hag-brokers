<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-black text-white">
                    {{ __('admin_ui.label_086') }}
                </h2>

                <p class="mt-1 text-sm text-slate-400">
                    {{ __('admin_ui.label_088') }}
                </p>
            </div>

            <a href="{{ route('admin.ports.create') }}"
                class="inline-flex items-center justify-center rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950 transition hover:bg-cyan-300">
                + Add Port
            </a>
        </div>
    </x-slot>

    <div class="space-y-6 py-6">

        @if (session('success'))
            <div
                class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm font-semibold text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-4">
            <form method="GET" action="{{ route('admin.ports.index') }}">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search port, country, code or UNLOCODE..."
                        class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 focus:border-cyan-400 focus:ring-cyan-400">

                    <button type="submit"
                        class="rounded-xl bg-slate-800 px-6 py-3 text-sm font-bold text-white hover:bg-slate-700">
                        {{ __('admin_ui.label_008') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-950/60">
            <div class="overflow-x-auto">
                <table class="min-w-full text-start text-sm">
                    <thead class="border-b border-slate-800 bg-slate-900/70">
                        <tr>
                            <th class="px-6 py-4 font-bold text-slate-400">{{ __('admin_ui.label_089') }}</th>
                            <th class="px-6 py-4 font-bold text-slate-400">{{ __('admin_ui.label_010') }}</th>
                            <th class="px-6 py-4 font-bold text-slate-400">UNLOCODE</th>
                            <th class="px-6 py-4 font-bold text-slate-400">{{ __('admin_ui.label_035') }}</th>
                            <th class="px-6 py-4 font-bold text-slate-400">{{ __('admin_ui.label_001') }}</th>
                            <th class="px-6 py-4 text-end font-bold text-slate-400">{{ __('admin_ui.label_011') }}</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-800">
                        @forelse($ports as $port)
                            <tr class="hover:bg-slate-900/60">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-white">
                                        {{ $port->name }}
                                    </div>

                                    @if ($port->port_code)
                                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            {{ $port->port_code }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-slate-300">
                                    {{ $port->country ?? '—' }}
                                </td>

                                <td class="px-6 py-4 font-mono text-cyan-300">
                                    {{ $port->unlocode ?? '—' }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        @if ($port->is_gulf)
                                            <span
                                                class="rounded-full bg-cyan-400/10 px-2.5 py-1 text-xs font-bold text-cyan-300">
                                                {{ __('admin_ui.label_036') }}
                                            </span>
                                        @endif

                                        @if ($port->is_uae)
                                            <span
                                                class="rounded-full bg-emerald-400/10 px-2.5 py-1 text-xs font-bold text-emerald-300">
                                                {{ __('admin_ui.label_090') }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    @if ($port->is_active)
                                        <span
                                            class="rounded-full bg-emerald-400/10 px-3 py-1 text-xs font-bold text-emerald-300">
                                            {{ __('admin_ui.label_006') }}
                                        </span>
                                    @else
                                        <span
                                            class="rounded-full bg-red-400/10 px-3 py-1 text-xs font-bold text-red-300">
                                            {{ __('admin_ui.label_012') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-end">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.ports.edit', $port) }}"
                                            class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-bold text-white hover:bg-slate-700">
                                            {{ __('admin_ui.label_003') }}
                                        </a>

                                        <form method="POST" action="{{ route('admin.ports.destroy', $port) }}"
                                            onsubmit="return confirm('Delete this port?');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="rounded-lg bg-red-500/10 px-3 py-2 text-xs font-bold text-red-300 hover:bg-red-500/20">
                                                {{ __('admin_ui.label_037') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    {{ __('admin_ui.label_091') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($ports->hasPages())
                <div class="border-t border-slate-800 px-6 py-4">
                    {{ $ports->links() }}
                </div>
            @endif
        </div>

    </div>
</x-admin-layout>
