<x-admin-layout>
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div class="flex items-start gap-4">

                {{-- Back --}}
                <a href="{{ route('admin.customers.index') }}"
                    class="mt-1 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                    title="Back to customers">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7 7-7-7 7-7" />
                    </svg>
                </a>

                <div>
                    <p class="text-sm font-medium text-blue-600">
                        HAG Brokers
                    </p>

                    <div class="mt-1 flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                            {{ $customer->company_name ?: 'Unnamed Company' }}
                        </h1>

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
                    </div>

                    <p class="mt-1 text-sm text-slate-500">
                        Customer #{{ $customer->id }}
                    </p>
                </div>

            </div>


            {{-- Actions --}}
            <div class="flex flex-wrap items-center gap-2">

                @if ($customer->email)
                    <a href="mailto:{{ $customer->email }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 7.5 12 13l9-5.5M4.5 19.5h15A1.5 1.5 0 0 0 21 18V6a1.5 1.5 0 0 0-1.5-1.5h-15A1.5 1.5 0 0 0 3 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                        </svg>
                        Email
                    </a>
                @endif

                <a href="{{ route('admin.customers.edit', $customer) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m16.862 4.487 2.651 2.651M4 20l4.5-1 10.313-10.313a1.875 1.875 0 0 0-2.652-2.652L5.849 16.348 4 20Z" />
                    </svg>
                    Edit Customer
                </a>

            </div>

        </div>


        {{-- Success Message --}}
        @if (session('success'))
            <div
                class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                </svg>

                {{ session('success') }}
            </div>
        @endif


        {{-- Customer Overview --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Main Information --}}
            <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-100 px-6 py-5">
                    <h2 class="text-base font-semibold text-slate-900">
                        Customer Information
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Contact and company details.
                    </p>
                </div>


                <div class="grid grid-cols-1 gap-x-8 gap-y-6 px-6 py-6 sm:grid-cols-2">

                    {{-- Company --}}
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Company Name
                        </p>

                        <p class="mt-1.5 text-sm font-semibold text-slate-900">
                            {{ $customer->company_name ?: '—' }}
                        </p>
                    </div>


                    {{-- Contact Person --}}
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Contact Person
                        </p>

                        <p class="mt-1.5 text-sm font-semibold text-slate-900">
                            {{ $customer->contact_person ?: '—' }}
                        </p>
                    </div>


                    {{-- Email --}}
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Email Address
                        </p>

                        @if ($customer->email)
                            <a href="mailto:{{ $customer->email }}"
                                class="mt-1.5 block text-sm font-semibold text-blue-600 hover:text-blue-700">
                                {{ $customer->email }}
                            </a>
                        @else
                            <p class="mt-1.5 text-sm font-semibold text-slate-900">
                                —
                            </p>
                        @endif
                    </div>


                    {{-- Phone --}}
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Phone
                        </p>

                        @if ($customer->phone)
                            <a href="tel:{{ $customer->phone }}"
                                class="mt-1.5 block text-sm font-semibold text-slate-900 hover:text-blue-600">
                                {{ $customer->phone }}
                            </a>
                        @else
                            <p class="mt-1.5 text-sm font-semibold text-slate-900">
                                —
                            </p>
                        @endif
                    </div>


                    {{-- Country --}}
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Country
                        </p>

                        <p class="mt-1.5 text-sm font-semibold text-slate-900">
                            {{ $customer->country_code ?: '—' }}
                        </p>
                    </div>


                    {{-- City --}}
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            City
                        </p>

                        <p class="mt-1.5 text-sm font-semibold text-slate-900">
                            {{ $customer->city ?: '—' }}
                        </p>
                    </div>


                    {{-- Customer ID --}}
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Customer ID
                        </p>

                        <p class="mt-1.5 text-sm font-semibold text-slate-900">
                            #{{ $customer->id }}
                        </p>
                    </div>


                    {{-- Created --}}
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Created
                        </p>

                        <p class="mt-1.5 text-sm font-semibold text-slate-900">
                            {{ $customer->created_at?->format('d M Y, H:i') ?? '—' }}
                        </p>
                    </div>

                </div>

            </div>


            {{-- Customer Summary --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-100 px-6 py-5">
                    <h2 class="text-base font-semibold text-slate-900">
                        Customer Summary
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Account activity overview.
                    </p>
                </div>


                <div class="space-y-4 p-6">

                    {{-- Users --}}
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-slate-600">
                                    Users
                                </p>
                                <p class="text-xs text-slate-400">
                                    Account users
                                </p>
                            </div>
                        </div>

                        <span class="text-xl font-bold text-slate-900">
                            {{ $customer->users->count() }}
                        </span>
                    </div>


                    {{-- Quotations --}}
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 p-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h8M8 11h8M8 15h5" />
                                </svg>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-slate-600">
                                    Quotations
                                </p>
                                <p class="text-xs text-slate-400">
                                    Customer quotes
                                </p>
                            </div>
                        </div>

                        <span class="text-xl font-bold text-slate-900">
                            {{ $customer->quotations->count() }}
                        </span>
                    </div>


                    {{-- Shipments --}}
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 p-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 7h11v10H3zM14 10h4l3 3v4h-7z" />
                                    <circle cx="7" cy="19" r="2" />
                                    <circle cx="18" cy="19" r="2" />
                                </svg>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-slate-600">
                                    Shipments
                                </p>
                                <p class="text-xs text-slate-400">
                                    Customer shipments
                                </p>
                            </div>
                        </div>

                        <span class="text-xl font-bold text-slate-900">
                            {{ $customer->shipments->count() }}
                        </span>
                    </div>

                </div>

            </div>

        </div>


        {{-- Users --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div
                class="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="text-base font-semibold text-slate-900">
                        Customer Users
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Users associated with this customer account.
                    </p>
                </div>

                <span
                    class="inline-flex w-fit items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                    {{ $customer->users->count() }} Users
                </span>

            </div>


            @if ($customer->users->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">

                        <thead class="bg-slate-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    User
                                </th>

                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Email
                                </th>

                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Created
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            @foreach ($customer->users as $user)
                                <tr class="hover:bg-slate-50">

                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">

                                            <div
                                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-xs font-bold text-blue-700">
                                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                            </div>

                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">
                                                    {{ $user->name ?? 'Unnamed User' }}
                                                </p>

                                                <p class="text-xs text-slate-400">
                                                    User #{{ $user->id }}
                                                </p>
                                            </div>

                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $user->email ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ $user->created_at?->format('d M Y') ?? '—' }}
                                    </td>

                                </tr>
                            @endforeach

                        </tbody>

                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <p class="text-sm font-medium text-slate-900">
                        No users associated
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        This customer does not have any users yet.
                    </p>
                </div>

            @endif

        </div>


        {{-- Danger Zone --}}
        <div class="rounded-2xl border border-red-200 bg-white shadow-sm">

            <div class="px-6 py-5">

                <h2 class="text-base font-semibold text-red-700">
                    Danger Zone
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Permanently remove this customer and its account record.
                </p>

                <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" class="mt-4"
                    onsubmit="return confirm('Are you sure you want to delete this customer? This action cannot be undone.')">

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                        Delete Customer
                    </button>

                </form>

            </div>

        </div>

    </div>
</x-admin-layout>
