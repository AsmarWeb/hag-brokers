<x-admin-layout>

    <div class="mx-auto max-w-6xl">

        {{-- Header --}}
        <div class="mb-8 flex flex-col justify-between gap-4 lg:flex-row lg:items-center">

            <div>
                <div class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-cyan-400">
                    {{ __('admin_ui.label_048') }}
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-3xl font-black text-white">
                        {{ __('admin_ui.label_049') }}
                    </h1>

                    @if ($shipment->status)
                        <span
                            class="inline-flex items-center rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-bold text-cyan-300">
                            {{ ucwords(str_replace('_', ' ', $shipment->status)) }}
                        </span>
                    @endif
                </div>

                <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                    Update shipment details, customer, route, carrier,
                    vessel, cargo and schedule information.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">

                <a href="{{ route('admin.shipments.show', $shipment) }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-700 px-5 py-3 text-sm font-bold text-slate-300 transition hover:bg-slate-900 hover:text-white">
                    {{ __('admin_ui.label_140') }}
                </a>

                <a href="{{ route('admin.shipments.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-700 px-5 py-3 text-sm font-bold text-slate-300 transition hover:bg-slate-900 hover:text-white">
                    ← Back
                </a>

            </div>

        </div>


        {{-- Shipment Reference --}}
        <div class="mb-6 rounded-2xl border border-slate-800 bg-slate-950/60 p-5">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        {{ __('admin_ui.label_141') }}
                    </p>

                    <p class="mt-1 text-xl font-black text-white">
                        {{ $shipment->shipment_no }}
                    </p>
                </div>

                @if ($shipment->booking_no)
                    <div class="sm:text-end">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            {{ __('admin_ui.label_142') }}
                        </p>

                        <p class="mt-1 font-bold text-cyan-300">
                            {{ $shipment->booking_no }}
                        </p>
                    </div>
                @endif

            </div>

        </div>


        {{-- Information Banner --}}
        <div class="mb-6 rounded-2xl border border-amber-400/20 bg-amber-400/5 p-5">

            <div class="flex gap-4">

                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-400/10 text-lg text-amber-300">
                    ⚠
                </div>

                <div>

                    <h2 class="font-black text-amber-300">
                        {{ __('admin_ui.label_143') }}
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Changes to the carrier, vessel, route, schedule or status
                        may affect the operational tracking information shown
                        to the customer.
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


        {{-- Edit Form --}}
        <x-admin.shipments.form :action="route('admin.shipments.update', $shipment)" method="PUT" :shipment="$shipment" :customers="$customers" :quotations="$quotations"
            :carriers="$carriers" :vessels="$vessels" :ports="$ports" />


        {{-- Danger Zone --}}
        <div class="mt-10 rounded-2xl border border-red-500/20 bg-red-500/5 p-6">

            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                <div>

                    <div class="mb-2 flex items-center gap-2">

                        <span class="text-lg text-red-400">
                            ⚠
                        </span>

                        <h2 class="font-black text-red-300">
                            {{ __('admin_ui.label_144') }}
                        </h2>

                    </div>

                    <p class="max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Deleting a shipment is permanent. Shipments with
                        containers, documents or tracking events cannot be
                        deleted by the controller.
                    </p>

                </div>

                <form action="{{ route('admin.shipments.destroy', $shipment) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this shipment? This action cannot be undone.');">

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl border border-red-500/30 bg-red-500/10 px-5 py-3 text-sm font-black text-red-300 transition hover:bg-red-500/20">
                        {{ __('admin_ui.label_145') }}
                    </button>

                </form>

            </div>

        </div>

    </div>
</x-admin-layout>
