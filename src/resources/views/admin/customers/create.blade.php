<x-admin-layout>
    <div class="mx-auto max-w-5xl">

        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">

            <div>
                <div class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-cyan-400">
                    {{ __('admin_ui.label_031') }}
                </div>

                <h1 class="text-3xl font-black text-white">
                    {{ __('admin_ui.label_058') }}
                </h1>

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Create a new customer account for HAG Brokers.
                </p>
            </div>

            <a href="{{ route('admin.customers.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-700 px-5 py-3 text-sm font-bold text-slate-300 transition hover:bg-slate-900 hover:text-white">
                ← Back to Customers
            </a>

        </div>

        <x-admin.customers.form :action="route('admin.customers.store')" />

    </div>
</x-admin-layout>
