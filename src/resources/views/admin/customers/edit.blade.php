<x-admin-layout>

    <div class="mx-auto max-w-5xl">

        <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">

            <div>
                <div class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-cyan-400">
                    Customers / Edit
                </div>

                <h1 class="text-3xl font-black text-white">
                    Edit Customer
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Update {{ $customer->company_name }}.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">

                <a href="{{ route('admin.customers.show', $customer) }}"
                    class="rounded-xl border border-slate-700 px-5 py-3 text-sm font-bold text-slate-300 transition hover:bg-slate-900 hover:text-white">
                    View Customer
                </a>

                <a href="{{ route('admin.customers.index') }}"
                    class="rounded-xl border border-slate-700 px-5 py-3 text-sm font-bold text-slate-300 transition hover:bg-slate-900 hover:text-white">
                    Back
                </a>

            </div>

        </div>

        <x-admin.customers.form :customer="$customer" :action="route('admin.customers.update', $customer)" method="PUT" />

        <div class="mt-6 flex justify-end">

            <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to delete this customer?');">
                @csrf
                @method('DELETE')

                <button type="submit"
                    class="rounded-xl border border-red-500/30 bg-red-500/10 px-5 py-3 text-sm font-bold text-red-300 transition hover:bg-red-500/20">
                    Delete Customer
                </button>
            </form>

        </div>

    </div>
</x-admin-layout>
