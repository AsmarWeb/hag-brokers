<x-admin-layout>

    <div class="mx-auto max-w-7xl">

        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-5 md:flex-row md:items-center md:justify-between">

            <div>

                <div class="mb-2 flex items-center gap-2 text-sm text-slate-500">

                    <a href="{{ route('admin.carriers.index') }}" class="transition hover:text-cyan-400">
                        Carriers
                    </a>

                    <span>/</span>

                    <span class="text-slate-400">
                        {{ $carrier->name }}
                    </span>

                </div>


                <div class="flex items-center gap-4">

                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/10 text-3xl">
                        ⚓
                    </div>

                    <div>

                        <h1 class="text-3xl font-black text-white">
                            {{ $carrier->name }}
                        </h1>

                        <p class="mt-1 text-sm text-slate-500">
                            Carrier #{{ $carrier->id }}

                            @if ($carrier->code)
                                · {{ $carrier->code }}
                            @endif
                        </p>

                    </div>

                </div>

            </div>


            <div class="flex gap-3">

                <a href="{{ route('admin.carriers.edit', $carrier) }}"
                    class="rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950 transition hover:bg-cyan-300">
                    Edit Carrier
                </a>

                <a href="{{ route('admin.carriers.index') }}"
                    class="rounded-xl border border-slate-700 px-5 py-3 text-sm font-bold text-slate-300 transition hover:bg-slate-800 hover:text-white">
                    Back
                </a>

            </div>

        </div>


        {{-- Overview --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Main Info --}}
            <div class="lg:col-span-2 rounded-2xl border border-slate-800 bg-slate-950">

                <div class="border-b border-slate-800 px-6 py-5">

                    <h2 class="font-black text-white">
                        Carrier Information
                    </h2>

                </div>


                <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2">

                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Carrier Name
                        </p>

                        <p class="mt-2 text-lg font-black text-white">
                            {{ $carrier->name }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Carrier Code
                        </p>

                        <p class="mt-2 font-mono text-lg font-bold text-cyan-400">
                            {{ $carrier->code ?? '—' }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Country
                        </p>

                        <p class="mt-2 font-bold text-white">
                            {{ $carrier->country ?? '—' }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Country Code
                        </p>

                        <p class="mt-2 font-mono font-bold text-white">
                            {{ $carrier->country_code ?? '—' }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Website
                        </p>

                        @if ($carrier->website)
                            <a href="{{ $carrier->website }}" target="_blank" rel="noopener noreferrer"
                                class="mt-2 inline-block font-semibold text-cyan-400 hover:text-cyan-300">
                                {{ $carrier->website }}
                                ↗
                            </a>
                        @else
                            <p class="mt-2 text-slate-600">
                                —
                            </p>
                        @endif

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            API Provider
                        </p>

                        <p class="mt-2 font-bold text-white">
                            {{ $carrier->api_provider ?? 'Manual Management' }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- Status --}}
            <div class="rounded-2xl border border-slate-800 bg-slate-950">

                <div class="border-b border-slate-800 px-6 py-5">

                    <h2 class="font-black text-white">
                        Operations
                    </h2>

                </div>


                <div class="space-y-5 p-6">

                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Status
                        </p>

                        <span
                            class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-bold
                        {{ $carrier->is_active ? 'bg-emerald-400/10 text-emerald-400' : 'bg-slate-800 text-slate-500' }}">
                            {{ $carrier->is_active ? 'Active' : 'Inactive' }}
                        </span>

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Vessels
                        </p>

                        <p class="mt-2 text-3xl font-black text-cyan-400">
                            {{ $carrier->vessels_count ?? $carrier->vessels->count() }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Quotation Offers
                        </p>

                        <p class="mt-2 text-3xl font-black text-white">
                            {{ $carrier->quotation_offers_count ?? $carrier->quotationOffers->count() }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Shipments
                        </p>

                        <p class="mt-2 text-3xl font-black text-white">
                            {{ $carrier->shipments_count ?? $carrier->shipments->count() }}
                        </p>

                    </div>

                </div>

            </div>

        </div>


        {{-- Vessels --}}
        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-800 bg-slate-950">

            <div
                class="flex flex-col gap-3 border-b border-slate-800 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <h2 class="font-black text-white">
                        Carrier Vessels
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Vessels currently assigned to this carrier.
                    </p>

                </div>


                <a href="{{ route('admin.vessels.create') }}"
                    class="rounded-xl bg-cyan-400 px-4 py-2.5 text-center text-xs font-black text-slate-950 transition hover:bg-cyan-300">
                    + Add Vessel
                </a>

            </div>


            @if ($carrier->vessels->count())

                <div class="divide-y divide-slate-800">

                    @foreach ($carrier->vessels as $vessel)
                        <div class="flex flex-col gap-4 px-6 py-5 md:flex-row md:items-center md:justify-between">

                            <div class="flex items-center gap-4">

                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-400/10">
                                    🚢
                                </div>

                                <div>

                                    <p class="font-bold text-white">
                                        {{ $vessel->name }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">

                                        @if ($vessel->imo)
                                            IMO {{ $vessel->imo }}
                                        @else
                                            No IMO
                                        @endif

                                        ·

                                        {{ ucfirst($vessel->status) }}

                                    </p>

                                </div>

                            </div>


                            <div class="flex items-center gap-3">

                                @if ($vessel->is_in_gulf)
                                    <span class="rounded-full bg-cyan-400/10 px-3 py-1 text-xs font-bold text-cyan-400">
                                        In Gulf
                                    </span>
                                @endif


                                <a href="{{ route('admin.vessels.show', $vessel) }}"
                                    class="rounded-lg border border-slate-700 px-4 py-2 text-xs font-bold text-slate-300 transition hover:border-cyan-400 hover:text-cyan-400">
                                    View
                                </a>

                            </div>

                        </div>
                    @endforeach

                </div>
            @else
                <div class="px-6 py-12 text-center">

                    <div class="text-4xl">
                        🚢
                    </div>

                    <p class="mt-3 font-bold text-white">
                        No vessels assigned
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        This carrier does not have any vessels yet.
                    </p>

                </div>

            @endif

        </div>

    </div>

</x-admin-layout>
