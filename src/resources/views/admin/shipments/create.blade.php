<x-admin-layout>

    <div class="mx-auto max-w-6xl">

        {{-- Header --}}
        <div class="mb-8 flex flex-col justify-between gap-4 lg:flex-row lg:items-center">

            <div>
                <div class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-cyan-400">
                    {{ __('admin_ui.label_048') }}
                </div>

                <h1 class="text-3xl font-black text-white">
                    {{ __('admin_ui.label_146') }}
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
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
                        {{ __('admin_ui.label_147') }}
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        A shipment represents the actual sea-freight movement
                        after a customer request and quotation process.
                        Assign the customer, quotation, route, carrier,
                        vessel and shipping schedule below.
                    </p>

                </div>

            </div>

        </div>


        {{-- Validation Errors --}}
        @if ($errors->any())

            <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/5 p-5">

                <div class="mb-3 flex items-center gap-3">

                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-500/10 text-red-400">
                        !
                    </div>

                    <h2 class="font-black text-red-300">
                        {{ __('admin_ui.label_050') }}
                    </h2>

                </div>

                <ul class="space-y-1 text-sm text-red-300">

                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif


        {{-- Shipment Form --}}
        <x-admin.shipments.form :action="route('admin.shipments.store')" method="POST" :shipment="null" :customers="$customers" :quotations="$quotations"
            :carriers="$carriers" :vessels="$vessels" :ports="$ports" />

    </div>
</x-admin-layout>
