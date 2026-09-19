<x-admin-layout>

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">

            <div class="flex items-start gap-4">

                <div
                    class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-cyan-400/10 text-2xl font-black text-cyan-400">
                    {{ strtoupper(substr($customer->company_name, 0, 1)) }}
                </div>

                <div>

                    <div class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-cyan-400">
                        Customer #{{ $customer->id }}
                    </div>

                    <h1 class="text-3xl font-black text-white">
                        {{ $customer->company_name }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        {{ $customer->contact_person ?: 'No contact person' }}
                    </p>

                </div>

            </div>

            <div class="flex flex-wrap gap-3">

                <a href="{{ route('admin.customers.edit', $customer) }}"
                    class="rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950 transition hover:bg-cyan-300">
                    Edit Customer
                </a>

                <a href="{{ route('admin.customers.index') }}"
                    class="rounded-xl border border-slate-700 px-5 py-3 text-sm font-bold text-slate-300 transition hover:bg-slate-900 hover:text-white">
                    ← Customers
                </a>

            </div>

        </div>

        {{-- Status --}}
        <div>
            @if ($customer->is_active)
                <span
                    class="inline-flex rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-300">
                    ● Active Customer
                </span>
            @else
                <span
                    class="inline-flex rounded-full border border-red-500/20 bg-red-500/10 px-3 py-1 text-xs font-bold text-red-300">
                    ● Inactive Customer
                </span>
            @endif
        </div>

        {{-- Overview --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

            <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                    Users
                </div>

                <div class="mt-2 text-3xl font-black text-white">
                    {{ $customer->users->count() }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                    Quotations
                </div>

                <div class="mt-2 text-3xl font-black text-white">
                    {{ $customer->quotations->count() }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                    Shipments
                </div>

                <div class="mt-2 text-3xl font-black text-white">
                    {{ $customer->shipments->count() }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                    Country
                </div>

                <div class="mt-2 text-3xl font-black text-cyan-400">
                    {{ strtoupper($customer->country_code) }}
                </div>
            </div>

        </div>

        {{-- Company Details --}}
        <div class="grid gap-6 lg:grid-cols-2">

            <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-6">

                <h2 class="mb-5 text-lg font-black text-white">
                    Company Details
                </h2>

                <div class="space-y-4">

                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                            Company
                        </div>

                        <div class="mt-1 text-sm font-semibold text-slate-200">
                            {{ $customer->company_name }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                            Contact Person
                        </div>

                        <div class="mt-1 text-sm font-semibold text-slate-200">
                            {{ $customer->contact_person ?: '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                            Email
                        </div>

                        <div class="mt-1 text-sm font-semibold text-slate-200">
                            {{ $customer->email ?: '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                            Phone
                        </div>

                        <div class="mt-1 text-sm font-semibold text-slate-200">
                            {{ $customer->phone ?: '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                            Location
                        </div>

                        <div class="mt-1 text-sm font-semibold text-slate-200">
                            {{ $customer->city ?: '—' }}
                            ·
                            {{ strtoupper($customer->country_code) }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                            Created
                        </div>

                        <div class="mt-1 text-sm font-semibold text-slate-200">
                            {{ $customer->created_at?->format('d M Y, H:i') }}
                        </div>
                    </div>

                </div>

            </div>

            {{-- Users --}}
            <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-6">

                <div class="mb-5 flex items-center justify-between">

                    <h2 class="text-lg font-black text-white">
                        Customer Users
                    </h2>

                    <span class="rounded-lg bg-slate-900 px-3 py-1 text-xs font-bold text-slate-400">
                        {{ $customer->users->count() }}
                    </span>

                </div>

                <div class="space-y-3">

                    @forelse($customer->users as $user)
                        <div
                            class="flex items-center justify-between rounded-xl border border-slate-800 bg-slate-900/50 p-4">

                            <div>

                                <div class="font-bold text-white">
                                    {{ $user->name }}
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $user->email }}
                                </div>

                            </div>

                            <span class="rounded-lg bg-cyan-400/10 px-3 py-1 text-xs font-bold text-cyan-300">
                                {{ $user->role }}
                            </span>

                        </div>

                    @empty

                        <div
                            class="rounded-xl border border-dashed border-slate-800 p-6 text-center text-sm text-slate-600">
                            No users assigned to this customer.
                        </div>
                    @endforelse

                </div>

            </div>

        </div>

        {{-- Quotations --}}
        <div class="rounded-2xl border border-slate-800 bg-slate-950/70">

            <div class="border-b border-slate-800 p-6">

                <h2 class="text-lg font-black text-white">
                    Quotations
                </h2>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full min-w-[850px] text-left">

                    <thead class="border-b border-slate-800 bg-slate-900/50">

                        <tr>

                            <th class="px-5 py-4 text-xs font-black uppercase text-slate-600">
                                Reference
                            </th>

                            <th class="px-5 py-4 text-xs font-black uppercase text-slate-600">
                                Route
                            </th>

                            <th class="px-5 py-4 text-xs font-black uppercase text-slate-600">
                                Cargo
                            </th>

                            <th class="px-5 py-4 text-xs font-black uppercase text-slate-600">
                                Status
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-800">

                        @forelse($customer->quotations as $quotation)
                            <tr>

                                <td class="px-5 py-4 font-bold text-cyan-400">
                                    {{ $quotation->reference_no }}
                                </td>

                                <td class="px-5 py-4">

                                    <div class="text-sm text-slate-300">
                                        {{ $quotation->originPort?->name ?: '—' }}
                                    </div>

                                    <div class="my-1 text-xs text-slate-600">
                                        ↓
                                    </div>

                                    <div class="text-sm text-slate-300">
                                        {{ $quotation->destinationPort?->name ?: '—' }}
                                    </div>

                                </td>

                                <td class="px-5 py-4">

                                    <div class="text-sm text-slate-300">
                                        {{ $quotation->cargo_type ?: '—' }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $quotation->container_type ?: '—' }}
                                        ·
                                        Qty {{ $quotation->quantity }}
                                    </div>

                                </td>

                                <td class="px-5 py-4">

                                    <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-bold text-slate-300">
                                        {{ ucfirst($quotation->status) }}
                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-600">
                                    No quotations found.
                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        {{-- Shipments --}}
        <div class="rounded-2xl border border-slate-800 bg-slate-950/70">

            <div class="border-b border-slate-800 p-6">

                <h2 class="text-lg font-black text-white">
                    Shipments
                </h2>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full min-w-[950px] text-left">

                    <thead class="border-b border-slate-800 bg-slate-900/50">

                        <tr>

                            <th class="px-5 py-4 text-xs font-black uppercase text-slate-600">
                                Shipment
                            </th>

                            <th class="px-5 py-4 text-xs font-black uppercase text-slate-600">
                                Route
                            </th>

                            <th class="px-5 py-4 text-xs font-black uppercase text-slate-600">
                                Carrier
                            </th>

                            <th class="px-5 py-4 text-xs font-black uppercase text-slate-600">
                                Vessel
                            </th>

                            <th class="px-5 py-4 text-xs font-black uppercase text-slate-600">
                                Status
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-800">

                        @forelse($customer->shipments as $shipment)
                            <tr>

                                <td class="px-5 py-4">

                                    <div class="font-bold text-cyan-400">
                                        {{ $shipment->shipment_no }}
                                    </div>

                                    @if ($shipment->booking_no)
                                        <div class="mt-1 text-xs text-slate-600">
                                            Booking: {{ $shipment->booking_no }}
                                        </div>
                                    @endif

                                </td>

                                <td class="px-5 py-4">

                                    <div class="text-sm text-slate-300">
                                        {{ $shipment->originPort?->name ?: '—' }}
                                    </div>

                                    <div class="my-1 text-xs text-slate-600">
                                        ↓
                                    </div>

                                    <div class="text-sm text-slate-300">
                                        {{ $shipment->destinationPort?->name ?: '—' }}
                                    </div>

                                </td>

                                <td class="px-5 py-4 text-sm text-slate-300">
                                    {{ $shipment->carrier?->name ?: '—' }}
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-300">
                                    {{ $shipment->vessel?->name ?: '—' }}
                                </td>

                                <td class="px-5 py-4">

                                    <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-bold text-slate-300">
                                        {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-600">
                                    No shipments found.
                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>
</x-admin-layout>
