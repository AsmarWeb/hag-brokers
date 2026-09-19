<x-admin-layout>

    <div class="mx-auto max-w-6xl">

        {{-- Header --}}
        <div class="mb-8 flex flex-col justify-between gap-4 lg:flex-row lg:items-center">

            <div>
                <div class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-cyan-400">
                    HAG Brokers / Shipments
                </div>

                <h1 class="text-3xl font-black text-white">
                    Create Shipment
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                    Create a new shipment and connect it with the customer,
                    quotation, route, carrier, vessel, cargo and schedule.
                </p>
            </div>

            <a href="{{ route('admin.shipments.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-700 px-5 py-3 text-sm font-bold text-slate-300 transition hover:bg-slate-900 hover:text-white">
                ← Back to Shipments
            </a>

        </div>


        {{-- Information Banner --}}
        <div class="mb-6 rounded-2xl border border-cyan-400/20 bg-cyan-400/5 p-5">

            <div class="flex gap-4">

                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10 text-lg text-cyan-400">
                    ⚓
                </div>

                <div>
                    <h2 class="font-black text-cyan-300">
                        Create an operational shipment
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-slate-500">
                        A shipment represents the actual sea-freight movement
                        after a customer request and quotation process.
                        You can assign the customer, quotation, route,
                        carrier, vessel and shipping schedule below.
                    </p>
                </div>

            </div>

        </div>


        {{-- Create Form --}}
        <x-admin.shipments.form :action="route('admin.shipments.store')" method="POST" />

    </div>
</x-admin-layout>
