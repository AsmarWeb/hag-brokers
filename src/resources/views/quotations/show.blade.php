<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center gap-3">

            <a href="{{ route('quotations.index') }}"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-cyan-400 hover:text-cyan-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <div>

                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">
                    Quotation Details
                </h2>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $quotation->reference_no ?? 'QT-' . $quotation->id }}
                </p>

            </div>

        </div>

    </x-slot>


    <div class="min-h-screen bg-slate-50 py-8 dark:bg-slate-950">

        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">


            {{-- Hero --}}
            <div class="relative overflow-hidden rounded-3xl bg-slate-950 p-6 shadow-xl sm:p-8">

                <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl"></div>
                <div class="absolute -bottom-32 -left-24 h-72 w-72 rounded-full bg-blue-500/10 blur-3xl"></div>

                <div class="relative">

                    <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">

                        <div>

                            <div class="text-xs font-bold uppercase tracking-[0.2em] text-cyan-400">
                                HAG Brokers
                            </div>

                            <h1 class="mt-4 text-3xl font-black text-white sm:text-4xl">
                                {{ $quotation->reference_no ?? 'QT-' . $quotation->id }}
                            </h1>

                            <p class="mt-2 text-sm text-slate-400">
                                Sea Freight Quotation Request
                            </p>

                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4">

                            <div class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                Request ID
                            </div>

                            <div class="mt-1 font-extrabold text-white">
                                #{{ $quotation->id }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Success --}}
            @if (session('success'))
                <div
                    class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">

                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>

                    {{ session('success') }}

                </div>
            @endif


            {{-- Route --}}
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">

                    <h2 class="font-extrabold text-slate-950 dark:text-white">
                        Shipping Route
                    </h2>

                </div>


                <div class="grid gap-5 p-6 md:grid-cols-[1fr_auto_1fr] md:items-center">

                    <div class="rounded-2xl bg-slate-50 p-5 dark:bg-slate-950">

                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Origin Port
                        </div>

                        <div class="mt-2 text-xl font-black text-slate-950 dark:text-white">
                            {{ $quotation->originPort->name ?? 'Not available' }}
                        </div>

                    </div>


                    <div class="flex justify-center text-cyan-400">

                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-width="2" d="M5 12h14m-6-6l6 6-6 6" />
                        </svg>

                    </div>


                    <div class="rounded-2xl bg-slate-50 p-5 dark:bg-slate-950">

                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Destination Port
                        </div>

                        <div class="mt-2 text-xl font-black text-slate-950 dark:text-white">
                            {{ $quotation->destinationPort->name ?? 'Not available' }}
                        </div>

                    </div>

                </div>

            </div>


            {{-- Main --}}
            <div class="grid gap-6 lg:grid-cols-3">


                {{-- Cargo details --}}
                <div
                    class="lg:col-span-2 rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">

                        <h2 class="font-extrabold text-slate-950 dark:text-white">
                            Cargo Details
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Information submitted with this quotation request.
                        </p>

                    </div>


                    <div class="grid gap-px bg-slate-200 sm:grid-cols-2 dark:bg-slate-800">

                        <div class="bg-white p-6 dark:bg-slate-900">

                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Cargo Type
                            </div>

                            <div class="mt-2 font-bold text-slate-950 dark:text-white">
                                {{ $quotation->cargo_type ?? '—' }}
                            </div>

                        </div>


                        <div class="bg-white p-6 dark:bg-slate-900">

                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Container Type
                            </div>

                            <div class="mt-2 font-bold text-slate-950 dark:text-white">
                                {{ $quotation->container_type ?? '—' }}
                            </div>

                        </div>


                        <div class="bg-white p-6 dark:bg-slate-900">

                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Quantity
                            </div>

                            <div class="mt-2 font-bold text-slate-950 dark:text-white">
                                {{ $quotation->quantity ?? '—' }}
                            </div>

                        </div>


                        <div class="bg-white p-6 dark:bg-slate-900">

                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Weight
                            </div>

                            <div class="mt-2 font-bold text-slate-950 dark:text-white">
                                {{ $quotation->weight !== null ? $quotation->weight : 'Not specified' }}
                            </div>

                        </div>


                        <div class="bg-white p-6 dark:bg-slate-900 sm:col-span-2">

                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Preferred Shipping Date
                            </div>

                            <div class="mt-2 font-bold text-slate-950 dark:text-white">
                                {{ $quotation->preferred_date
                                    ? \Carbon\Carbon::parse($quotation->preferred_date)->format('d M Y')
                                    : 'Not specified' }}
                            </div>

                        </div>

                    </div>

                </div>


                {{-- Summary --}}
                <div
                    class="rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">

                        <h2 class="font-extrabold text-slate-950 dark:text-white">
                            Request Summary
                        </h2>

                    </div>


                    <div class="space-y-5 p-6">

                        <div>

                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Reference
                            </div>

                            <div class="mt-1 font-bold text-slate-950 dark:text-white">
                                {{ $quotation->reference_no ?? 'QT-' . $quotation->id }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Submitted
                            </div>

                            <div class="mt-1 font-bold text-slate-950 dark:text-white">
                                {{ $quotation->created_at?->format('d M Y, H:i') ?? '—' }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Last Updated
                            </div>

                            <div class="mt-1 font-bold text-slate-950 dark:text-white">
                                {{ $quotation->updated_at?->diffForHumans() ?? '—' }}
                            </div>

                        </div>


                        <div class="rounded-2xl bg-cyan-50 p-4 dark:bg-cyan-400/10">

                            <div class="flex items-start gap-3">

                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-cyan-600 dark:text-cyan-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-8h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                                </svg>

                                <p class="text-xs leading-5 text-cyan-800 dark:text-cyan-300">
                                    Our team can review the submitted shipment requirements and provide the next steps.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Footer actions --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">

                <a href="{{ route('quotations.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                    ← Back to Quotations
                </a>

                <a href="{{ route('quotations.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-400 px-6 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-cyan-400/20 transition hover:bg-cyan-300">
                    New Quote Request

                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>

                </a>

            </div>

        </div>

    </div>

</x-app-layout>
