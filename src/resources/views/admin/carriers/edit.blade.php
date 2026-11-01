<x-admin-layout>

    <div class="mx-auto max-w-5xl">

        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

            <div>

                <div class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">

                    <a href="{{ route('admin.carriers.index') }}" class="transition hover:text-cyan-400">
                        {{ __('admin_ui.label_016') }}
                    </a>

                    <span>/</span>

                    <span class="text-slate-400">
                        {{ __('admin_ui.label_003') }}
                    </span>

                </div>

                <h1 class="text-3xl font-black tracking-tight text-white">
                    {{ __('admin_ui.label_062') }}
                </h1>

                <p class="mt-2 text-sm text-slate-400">
                    Update carrier information and integration settings.
                </p>

            </div>


            <a href="{{ route('admin.carriers.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-5 py-3 text-sm font-bold text-slate-200 transition hover:border-cyan-400 hover:text-cyan-400">
                ← Back to Carriers
            </a>

        </div>


        {{-- Main form --}}
        <form action="{{ route('admin.carriers.update', $carrier) }}" method="POST"
            class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-950 shadow-2xl">

            @csrf
            @method('PUT')

            <div class="border-b border-slate-800 px-6 py-5">

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                    <div>

                        <h2 class="text-lg font-black text-white">
                            {{ $carrier->name }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Carrier ID #{{ $carrier->id }}
                        </p>

                    </div>


                    <span
                        class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold {{ $carrier->is_active ? 'bg-emerald-400/10 text-emerald-400' : 'bg-slate-800 text-slate-400' }}">
                        {{ $carrier->is_active ? 'Active' : 'Inactive' }}
                    </span>

                </div>

            </div>


            <div class="p-6">

                <x-admin.carriers.form :carrier="$carrier" />

            </div>


            <div
                class="flex flex-col gap-4 border-t border-slate-800 bg-slate-900/40 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                {{-- Delete --}}
                <div>

                    <form id="delete-carrier-form" action="{{ route('admin.carriers.destroy', $carrier) }}"
                        method="POST">
                        @csrf
                        @method('DELETE')
                    </form>

                    <button type="submit" form="delete-carrier-form"
                        onclick="return confirm('Are you sure you want to delete this carrier?')"
                        class="rounded-xl border border-red-500/30 px-6 py-3 text-sm font-bold text-red-400 transition hover:bg-red-500/10">
                        Delete Carrier
                    </button>

                </div>


                <div class="flex flex-col gap-3 sm:flex-row">

                    <a href="{{ route('admin.carriers.index') }}"
                        class="rounded-xl border border-slate-700 px-6 py-3 text-center text-sm font-bold text-slate-300 transition hover:bg-slate-800 hover:text-white">
                        {{ __('admin_ui.label_007') }}
                    </a>

                    <button type="submit"
                        class="rounded-xl bg-cyan-400 px-6 py-3 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/10 transition hover:bg-cyan-300">
                        {{ __('admin_ui.label_063') }}
                    </button>

                </div>

            </div>

        </form>

    </div>
</x-admin-layout>
