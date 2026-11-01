<x-admin-layout>

    <div class="mx-auto max-w-7xl">

        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

            <div>
                <div class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                    <a href="{{ route('admin.vessels.index') }}" class="transition hover:text-cyan-400">
                        {{ __('admin_ui.label_148') }}
                    </a>

                    <span>/</span>

                    <span class="text-slate-400">{{ __('admin_ui.label_064') }}</span>
                </div>

                <h1 class="text-3xl font-black tracking-tight text-white">
                    {{ __('admin_ui.label_026') }}
                </h1>

                <p class="mt-2 text-sm text-slate-400">
                    Add a vessel to the HAG Brokers fleet database.
                </p>
            </div>

            <a href="{{ route('admin.vessels.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-5 py-3 text-sm font-bold text-slate-200 transition hover:border-cyan-400 hover:text-cyan-400">
                ← Back to Vessels
            </a>

        </div>


        {{-- Form --}}
        <form action="{{ route('admin.vessels.store') }}" method="POST"
            class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-950 shadow-2xl">
            @csrf

            <div class="border-b border-slate-800 px-6 py-5">
                <h2 class="text-lg font-black text-white">
                    {{ __('admin_ui.label_071') }}
                </h2>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Enter identification, carrier and tracking information.
                </p>
            </div>

            <div class="p-6">
                <x-admin.vessels.form :carriers="$carriers" />
            </div>

            <div
                class="flex flex-col-reverse gap-3 border-t border-slate-800 bg-slate-900/40 px-6 py-5 sm:flex-row sm:justify-end">

                <a href="{{ route('admin.vessels.index') }}"
                    class="rounded-xl border border-slate-700 px-6 py-3 text-center text-sm font-bold text-slate-300 transition hover:bg-slate-800 hover:text-white">
                    {{ __('admin_ui.label_007') }}
                </a>

                <button type="submit"
                    class="rounded-xl bg-cyan-400 px-6 py-3 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/10 transition hover:bg-cyan-300">
                    Create Vessel
                </button>

            </div>

        </form>

    </div>
</x-admin-layout>
