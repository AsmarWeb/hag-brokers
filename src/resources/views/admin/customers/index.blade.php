<x-admin-layout>
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-blue-600">
                    HAG Brokers
                </p>

                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    Customers
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Manage customer accounts connected to HAG Brokers.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1.5 text-sm font-medium text-blue-700">
                    {{ $customers->total() }} Customers
                </span>
            </div>
        </div>


        {{-- Search / Filters --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('admin.customers.index') }}" class="flex flex-col gap-3 sm:flex-row">

                <div class="relative flex-1">

                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z" />
                        </svg>
                    </div>

                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search company, contact person, email..."
                        class="block w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                </div>

                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
                    Search
                </button>

                @if (request()->filled('search'))
                    <a href="{{ route('admin.customers.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Clear
                    </a>
                @endif

            </form>
        </div>


        {{-- Customers Table --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Desktop --}}
            <div class="hidden overflow-x-auto md:block">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">
                        <tr>

                            <th scope="col"
                                class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Customer
                            </th>

                            <th scope="col"
                                class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Email
                            </th>

                            <th scope="col"
                                class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Customer ID
                            </th>

                            <th scope="col"
                                class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Joined
                            </th>

                            <th scope="col"
                                class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Status
                            </th>

                        </tr>
                    </thead>


                    <tbody class="divide-y divide-slate-100 bg-white">

                        @forelse ($customers as $customer)
                            <tr class="transition hover:bg-slate-50">

                                {{-- Customer --}}
                                <td class="whitespace-nowrap px-6 py-4">

                                    <div class="flex items-center gap-3">

                                        {{-- Avatar --}}
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-sm font-bold text-blue-700">
                                            {{ strtoupper(substr($customer->company_name ?? 'C', 0, 1)) }}
                                        </div>


                                        <div>

                                            {{-- Company --}}
                                            <div class="font-semibold text-slate-900">
                                                {{ $customer->company_name ?: 'Unnamed Company' }}
                                            </div>

                                            {{-- Contact Person --}}
                                            <div class="text-xs text-slate-500">
                                                {{ $customer->contact_person ?: 'No contact person' }}
                                            </div>

                                        </div>

                                    </div>

                                </td>


                                {{-- Email --}}
                                <td class="whitespace-nowrap px-6 py-4">

                                    @if ($customer->email)
                                        <a href="mailto:{{ $customer->email }}"
                                            class="text-sm text-slate-700 transition hover:text-blue-600">
                                            {{ $customer->email }}
                                        </a>
                                    @else
                                        <span class="text-sm text-slate-400">
                                            —
                                        </span>
                                    @endif

                                </td>


                                {{-- Customer ID --}}
                                <td class="whitespace-nowrap px-6 py-4">

                                    <span
                                        class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                        #{{ $customer->id }}
                                    </span>

                                </td>


                                {{-- Joined --}}
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">

                                    @if ($customer->created_at)
                                        <div class="font-medium text-slate-700">
                                            {{ $customer->created_at->format('d M Y') }}
                                        </div>

                                        <div class="text-xs text-slate-400">
                                            {{ $customer->created_at->format('H:i') }}
                                        </div>
                                    @else
                                        —
                                    @endif

                                </td>


                                {{-- Status --}}
                                <td class="whitespace-nowrap px-6 py-4 text-right">

                                    @if ($customer->is_active)
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Active
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            Inactive
                                        </span>
                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="px-6 py-16 text-center">

                                    <div
                                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
                                        <svg class="h-7 w-7 text-slate-400" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.7">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                            <circle cx="9" cy="7" r="4" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                                        </svg>
                                    </div>

                                    <h3 class="mt-4 text-sm font-semibold text-slate-900">
                                        No customers found
                                    </h3>

                                    <p class="mt-1 text-sm text-slate-500">

                                        @if (request()->filled('search'))
                                            No customers match your search.
                                        @else
                                            There are no customer accounts yet.
                                        @endif

                                    </p>

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Mobile --}}
            <div class="divide-y divide-slate-100 md:hidden">

                @forelse ($customers as $customer)
                    <div class="p-4">

                        <div class="flex items-start justify-between gap-3">

                            <div class="flex min-w-0 items-center gap-3">

                                {{-- Avatar --}}
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-sm font-bold text-blue-700">
                                    {{ strtoupper(substr($customer->company_name ?? 'C', 0, 1)) }}
                                </div>


                                <div class="min-w-0">

                                    <p class="truncate font-semibold text-slate-900">
                                        {{ $customer->company_name ?: 'Unnamed Company' }}
                                    </p>

                                    <p class="truncate text-sm text-slate-500">
                                        {{ $customer->contact_person ?: 'No contact person' }}
                                    </p>

                                    @if ($customer->email)
                                        <p class="truncate text-xs text-slate-400">
                                            {{ $customer->email }}
                                        </p>
                                    @endif

                                </div>

                            </div>


                            {{-- Status --}}
                            @if ($customer->is_active)
                                <span
                                    class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Active
                                </span>
                            @else
                                <span
                                    class="inline-flex shrink-0 items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    Inactive
                                </span>
                            @endif

                        </div>


                        {{-- Customer Information --}}
                        <div class="mt-4 grid grid-cols-2 gap-3">

                            {{-- Customer ID --}}
                            <div class="rounded-xl bg-slate-50 p-3">

                                <p class="text-xs font-medium text-slate-400">
                                    Customer ID
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    #{{ $customer->id }}
                                </p>

                            </div>


                            {{-- User ID --}}
                            <div class="rounded-xl bg-slate-50 p-3">

                                <p class="text-xs font-medium text-slate-400">
                                    User ID
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    #{{ $customer->users->first()?->id ?? '—' }}
                                </p>

                            </div>


                            {{-- Country --}}
                            <div class="rounded-xl bg-slate-50 p-3">

                                <p class="text-xs font-medium text-slate-400">
                                    Country
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $customer->country_code ?: '—' }}
                                </p>

                            </div>


                            {{-- City --}}
                            <div class="rounded-xl bg-slate-50 p-3">

                                <p class="text-xs font-medium text-slate-400">
                                    City
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                    {{ $customer->city ?: '—' }}
                                </p>

                            </div>


                            {{-- Joined --}}
                            <div class="col-span-2 rounded-xl bg-slate-50 p-3">

                                <p class="text-xs font-medium text-slate-400">
                                    Joined
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-800">

                                    @if ($customer->created_at)
                                        {{ $customer->created_at->format('d M Y, H:i') }}
                                    @else
                                        —
                                    @endif

                                </p>

                            </div>

                        </div>


                        {{-- Actions --}}
                        <div class="mt-4 flex gap-2">

                            @if ($customer->email)
                                <a href="mailto:{{ $customer->email }}"
                                    class="inline-flex flex-1 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Contact
                                </a>
                            @endif

                            <a href="{{ route('admin.customers.show', $customer) }}"
                                class="inline-flex flex-1 items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                                View Customer
                            </a>

                        </div>

                    </div>

                @empty

                    <div class="px-6 py-16 text-center">

                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
                            <svg class="h-7 w-7 text-slate-400" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                            </svg>
                        </div>

                        <h3 class="mt-4 text-sm font-semibold text-slate-900">
                            No customers found
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">

                            @if (request()->filled('search'))
                                No customers match your search.
                            @else
                                No customer accounts are available.
                            @endif

                        </p>

                    </div>
                @endforelse

            </div>

        </div>


        {{-- Pagination --}}
        @if ($customers->hasPages())
            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:px-6">
                {{ $customers->links() }}
            </div>
        @endif

    </div>
</x-admin-layout>
