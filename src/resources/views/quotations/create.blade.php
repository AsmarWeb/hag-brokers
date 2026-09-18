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
                    Request a Quote
                </h2>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Submit your sea freight requirements.
                </p>

            </div>

        </div>

    </x-slot>


    <div class="min-h-screen bg-slate-50 py-8 dark:bg-slate-950">

        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <div class="relative overflow-hidden rounded-3xl bg-slate-950 p-6 shadow-xl sm:p-8">

                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl"></div>

                <div class="relative">

                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-cyan-400">
                        <span class="h-2 w-2 rounded-full bg-cyan-400"></span>
                        HAG Brokers
                    </div>

                    <h1 class="mt-4 text-3xl font-black tracking-tight text-white">
                        Request a Freight Quote
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-400">
                        Provide your shipment details and our team can review your sea freight requirements.
                    </p>

                </div>

            </div>


            {{-- Validation errors --}}
            @if ($errors->any())

                <div
                    class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-5 dark:border-red-400/20 dark:bg-red-400/10">

                    <div class="font-bold text-red-700 dark:text-red-400">
                        Please check the following:
                    </div>

                    <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-red-600 dark:text-red-300">

                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- Form --}}
            <form method="POST" action="{{ route('quotations.store') }}" class="mt-6 space-y-6">

                @csrf


                {{-- Route --}}
                <div
                    class="rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">

                        <h2 class="font-extrabold text-slate-950 dark:text-white">
                            Shipping Route
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Select the origin and destination ports.
                        </p>

                    </div>


                    <div class="grid gap-6 p-6 md:grid-cols-2">

                        <div>

                            <label for="origin_port_id"
                                class="block text-sm font-bold text-slate-700 dark:text-slate-300">
                                Origin Port
                            </label>

                            <select id="origin_port_id" name="origin_port_id" required
                                class="mt-2 block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-cyan-400 focus:ring-cyan-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white">

                                <option value="">
                                    Select origin port
                                </option>

                                {{-- 
                                    Ports will be added here when passed
                                    from the controller.
                                --}}

                            </select>

                            @error('origin_port_id')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        <div>

                            <label for="destination_port_id"
                                class="block text-sm font-bold text-slate-700 dark:text-slate-300">
                                Destination Port
                            </label>

                            <select id="destination_port_id" name="destination_port_id" required
                                class="mt-2 block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-cyan-400 focus:ring-cyan-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white">

                                <option value="">
                                    Select destination port
                                </option>

                            </select>

                            @error('destination_port_id')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>

                </div>


                {{-- Cargo --}}
                <div
                    class="rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">

                        <h2 class="font-extrabold text-slate-950 dark:text-white">
                            Cargo Information
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Tell us about the cargo you need to ship.
                        </p>

                    </div>


                    <div class="grid gap-6 p-6 md:grid-cols-2">

                        <div>

                            <label for="cargo_type" class="block text-sm font-bold text-slate-700 dark:text-slate-300">
                                Cargo Type
                            </label>

                            <input id="cargo_type" name="cargo_type" type="text" value="{{ old('cargo_type') }}"
                                required maxlength="255" placeholder="e.g. General Cargo"
                                class="mt-2 block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-cyan-400 focus:ring-cyan-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white">

                            @error('cargo_type')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        <div>

                            <label for="container_type"
                                class="block text-sm font-bold text-slate-700 dark:text-slate-300">
                                Container Type
                            </label>

                            <select id="container_type" name="container_type" required
                                class="mt-2 block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-cyan-400 focus:ring-cyan-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white">

                                <option value="">
                                    Select container type
                                </option>

                                <option value="20ft" @selected(old('container_type') === '20ft')>
                                    20ft
                                </option>

                                <option value="40ft" @selected(old('container_type') === '40ft')>
                                    40ft
                                </option>

                                <option value="40ft_hc" @selected(old('container_type') === '40ft_hc')>
                                    40ft High Cube
                                </option>

                                <option value="45ft" @selected(old('container_type') === '45ft')>
                                    45ft
                                </option>

                                <option value="LCL" @selected(old('container_type') === 'LCL')>
                                    LCL
                                </option>

                            </select>

                            @error('container_type')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        <div>

                            <label for="quantity" class="block text-sm font-bold text-slate-700 dark:text-slate-300">
                                Quantity
                            </label>

                            <input id="quantity" name="quantity" type="number" min="1"
                                value="{{ old('quantity', 1) }}" required
                                class="mt-2 block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-cyan-400 focus:ring-cyan-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white">

                            @error('quantity')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        <div>

                            <label for="weight" class="block text-sm font-bold text-slate-700 dark:text-slate-300">
                                Weight
                            </label>

                            <input id="weight" name="weight" type="number" step="0.01" min="0"
                                value="{{ old('weight') }}" placeholder="e.g. 1500"
                                class="mt-2 block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-cyan-400 focus:ring-cyan-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white">

                            <p class="mt-2 text-xs text-slate-400">
                                Enter the cargo weight if available.
                            </p>

                            @error('weight')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        <div class="md:col-span-2">

                            <label for="preferred_date"
                                class="block text-sm font-bold text-slate-700 dark:text-slate-300">
                                Preferred Shipping Date
                            </label>

                            <input id="preferred_date" name="preferred_date" type="date"
                                value="{{ old('preferred_date') }}"
                                class="mt-2 block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-cyan-400 focus:ring-cyan-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white">

                            @error('preferred_date')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>

                </div>


                {{-- Submit --}}
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                    <a href="{{ route('quotations.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                        Cancel
                    </a>

                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-400 px-7 py-3 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/20 transition hover:bg-cyan-300">

                        Submit Quote Request

                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 12h14m-6-6l6 6-6 6" />
                        </svg>

                    </button>

                </div>

            </form>

        </div>

    </div>

</x-app-layout>
