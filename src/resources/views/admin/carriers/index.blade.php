<x-admin-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                Carriers
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Manage shipping lines and logistics carrier partners.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div
                class="mb-6 rounded-2xl bg-white p-4 shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                <form method="GET" action="{{ route('admin.carriers.index') }}" class="flex flex-col gap-3 sm:flex-row">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search carrier, code or country..."
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                    <button class="rounded-xl bg-cyan-600 px-6 py-2.5 font-semibold text-white">
                        Search
                    </button>
                </form>
            </div>

            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @forelse($carriers as $carrier)
                    <div
                        class="rounded-2xl bg-white p-6 shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">

                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-bold text-lg text-slate-900 dark:text-white">
                                    {{ $carrier->name }}
                                </h3>

                                <p class="mt-1 font-mono text-xs text-slate-500">
                                    {{ $carrier->code ?? 'NO CODE' }}
                                </p>
                            </div>

                            @if ($carrier->is_active)
                                <span
                                    class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    Active
                                </span>
                            @else
                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                    Inactive
                                </span>
                            @endif
                        </div>

                        <div class="mt-5 text-sm text-slate-500">
                            {{ $carrier->country ?? 'International' }}
                        </div>

                        <div class="mt-6 grid grid-cols-3 gap-3">
                            <div class="rounded-xl bg-slate-50 p-3 text-center dark:bg-slate-800">
                                <p class="text-xs text-slate-500">Vessels</p>
                                <p class="mt-1 font-bold text-slate-900 dark:text-white">
                                    {{ $carrier->vessels_count }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-3 text-center dark:bg-slate-800">
                                <p class="text-xs text-slate-500">Offers</p>
                                <p class="mt-1 font-bold text-slate-900 dark:text-white">
                                    {{ $carrier->quotation_offers_count }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-3 text-center dark:bg-slate-800">
                                <p class="text-xs text-slate-500">Shipments</p>
                                <p class="mt-1 font-bold text-slate-900 dark:text-white">
                                    {{ $carrier->shipments_count }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div
                        class="md:col-span-2 lg:col-span-3 rounded-2xl bg-white p-12 text-center text-slate-500 shadow">
                        No carriers found.
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $carriers->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
