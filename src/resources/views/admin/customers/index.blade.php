<x-admin-layout>
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">

            <div>
                <div class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-cyan-400">
                    HAG Brokers
                </div>

                <h1 class="text-3xl font-black text-white">
                    {{ __('admin_ui.label_031') }}
                </h1>

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Manage HAG Brokers customers and their company accounts.
                </p>
            </div>

            <a href="{{ route('admin.customers.create') }}"
                class="inline-flex items-center justify-center rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/10 transition hover:bg-cyan-300">
                + Add Customer
            </a>

        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div
                class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-sm font-bold text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-sm font-bold text-red-300">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-4">

            <form method="GET" action="{{ route('admin.customers.index') }}"
                class="grid gap-3 md:grid-cols-[1fr_180px_auto]">

                <input type="search" name="search" value="{{ request('search') }}"
                    placeholder="Search company, contact, email, phone..."
                    class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400">

                <select name="status"
                    class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">
                    <option value="">{{ __('admin_ui.label_021') }}</option>
                    <option value="active" @selected(request('status') === 'active')>
                        {{ __('admin_ui.label_006') }}
                    </option>

                    <option value="inactive" @selected(request('status') === 'inactive')>
                        {{ __('admin_ui.label_012') }}
                    </option>
                </select>

                <button type="submit"
                    class="rounded-xl bg-slate-800 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-700">
                    {{ __('admin_ui.label_008') }}
                </button>

            </form>

        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-950/70">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[1000px] text-start">

                    <thead class="border-b border-slate-800 bg-slate-900/70">

                        <tr>

                            <th class="px-5 py-4 text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                {{ __('admin_ui.label_005') }}
                            </th>

                            <th class="px-5 py-4 text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Contact
                            </th>

                            <th class="px-5 py-4 text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                {{ __('admin_ui.label_059') }}
                            </th>

                            <th class="px-5 py-4 text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Activity
                            </th>

                            <th class="px-5 py-4 text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                {{ __('admin_ui.label_001') }}
                            </th>

                            <th class="px-5 py-4 text-end text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                {{ __('admin_ui.label_011') }}
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-800">

                        @forelse($customers as $customer)
                            <tr class="transition hover:bg-slate-900/60">

                                {{-- Customer --}}
                                <td class="px-5 py-5">

                                    <div class="flex items-center gap-3">

                                        <div
                                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10 font-black text-cyan-400">
                                            {{ strtoupper(substr($customer->company_name, 0, 1)) }}
                                        </div>

                                        <div>
                                            <a href="{{ route('admin.customers.show', $customer) }}"
                                                class="font-bold text-white hover:text-cyan-400">
                                                {{ $customer->company_name }}
                                            </a>

                                            <div class="mt-1 text-xs text-slate-600">
                                                ID #{{ $customer->id }}
                                            </div>
                                        </div>

                                    </div>

                                </td>

                                {{-- Contact --}}
                                <td class="px-5 py-5">

                                    <div class="text-sm font-semibold text-slate-300">
                                        {{ $customer->contact_person ?: '—' }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $customer->email ?: 'No email' }}
                                    </div>

                                    @if ($customer->phone)
                                        <div class="mt-1 text-xs text-slate-600">
                                            {{ $customer->phone }}
                                        </div>
                                    @endif

                                </td>

                                {{-- Location --}}
                                <td class="px-5 py-5">

                                    <div class="text-sm text-slate-300">
                                        {{ $customer->city ?: '—' }}
                                    </div>

                                    <div class="mt-1 text-xs font-bold text-cyan-400">
                                        {{ strtoupper($customer->country_code) }}
                                    </div>

                                </td>

                                {{-- Activity --}}
                                <td class="px-5 py-5">

                                    <div class="flex flex-wrap gap-2">

                                        <span class="rounded-lg bg-slate-900 px-2.5 py-1 text-xs text-slate-400">
                                            {{ $customer->users_count }} users
                                        </span>

                                        <span class="rounded-lg bg-slate-900 px-2.5 py-1 text-xs text-slate-400">
                                            {{ $customer->quotations_count }} quotes
                                        </span>

                                        <span class="rounded-lg bg-slate-900 px-2.5 py-1 text-xs text-slate-400">
                                            {{ $customer->shipments_count }} shipments
                                        </span>

                                    </div>

                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-5">

                                    @if ($customer->is_active)
                                        <span
                                            class="inline-flex rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-300">
                                            {{ __('admin_ui.label_006') }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full border border-red-500/20 bg-red-500/10 px-3 py-1 text-xs font-bold text-red-300">
                                            {{ __('admin_ui.label_012') }}
                                        </span>
                                    @endif

                                </td>

                                {{-- Actions --}}
                                <td class="px-5 py-5">

                                    <div class="flex items-center justify-end gap-2">

                                        <a href="{{ route('admin.customers.show', $customer) }}"
                                            class="rounded-lg border border-slate-700 px-3 py-2 text-xs font-bold text-slate-300 transition hover:bg-slate-800 hover:text-white">
                                            {{ __('admin_ui.label_004') }}
                                        </a>

                                        <a href="{{ route('admin.customers.edit', $customer) }}"
                                            class="rounded-lg border border-cyan-400/20 bg-cyan-400/5 px-3 py-2 text-xs font-bold text-cyan-300 transition hover:bg-cyan-400/10">
                                            {{ __('admin_ui.label_003') }}
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="px-5 py-16 text-center">

                                    <div class="mx-auto max-w-md">

                                        <div
                                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-900 text-2xl">
                                            👥
                                        </div>

                                        <h3 class="text-lg font-black text-white">
                                            No customers found
                                        </h3>

                                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                            Try changing your search or create a new customer.
                                        </p>

                                        <a href="{{ route('admin.customers.create') }}"
                                            class="mt-5 inline-flex rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950">
                                            {{ __('admin_ui.label_058') }}
                                        </a>

                                    </div>

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            @if ($customers->hasPages())
                <div class="border-t border-slate-800 px-5 py-4">
                    {{ $customers->links() }}
                </div>
            @endif

        </div>

    </div>
</x-admin-layout>
