<x-admin-layout>

    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-black text-white">
                Edit Port
            </h2>

            <p class="mt-1 text-sm text-slate-400">
                Update port information.
            </p>
        </div>
    </x-slot>

    <div class="py-6">

        <form method="POST" action="{{ route('admin.ports.update', $port) }}">

            @csrf
            @method('PUT')

            <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-6">

                <x-admin.ports.form :port="$port" />

            </div>

            <div class="mt-6 flex justify-end gap-3">

                <a href="{{ route('admin.ports.index') }}"
                    class="rounded-xl bg-slate-800 px-5 py-3 text-sm font-bold text-white">
                    Cancel
                </a>

                <button type="submit"
                    class="rounded-xl bg-cyan-400 px-6 py-3 text-sm font-black text-slate-950 hover:bg-cyan-300">
                    Update Port
                </button>

            </div>

        </form>

    </div>

</x-admin-layout>
