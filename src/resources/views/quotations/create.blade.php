<x-app-layout>

    @php
        $isAr = app()->getLocale() === 'ar';
        $dir = $isAr ? 'rtl' : 'ltr';

        $t = [
            'request_quote' => $isAr ? 'طلب عرض سعر' : 'Request a Quote',
            'submit_requirements' => $isAr
                ? 'أرسل متطلبات الشحن البحري الخاصة بك.'
                : 'Submit your sea freight requirements.',

            'freight_quote' => $isAr ? 'طلب عرض سعر للشحن' : 'Request a Freight Quote',
            'header_description' => $isAr
                ? 'أدخل تفاصيل شحنتك وسيراجع فريقنا متطلبات الشحن البحري الخاصة بك.'
                : 'Provide your shipment details and our team can review your sea freight requirements.',

            'please_check' => $isAr ? 'يرجى التحقق من المعلومات التالية:' : 'Please check the following:',

            'shipping_route' => $isAr ? 'مسار الشحن' : 'Shipping Route',
            'route_description' => $isAr
                ? 'حدد ميناء الانطلاق وميناء الوصول.'
                : 'Select the origin and destination ports.',

            'origin_port' => $isAr ? 'ميناء الانطلاق' : 'Origin Port',
            'destination_port' => $isAr ? 'ميناء الوصول' : 'Destination Port',

            'select_origin' => $isAr ? 'اختر ميناء الانطلاق' : 'Select origin port',

            'select_destination' => $isAr ? 'اختر ميناء الوصول' : 'Select destination port',

            'cargo_information' => $isAr ? 'معلومات الشحنة' : 'Cargo Information',
            'cargo_description' => $isAr
                ? 'أخبرنا عن البضائع التي تريد شحنها.'
                : 'Tell us about the cargo you need to ship.',

            'cargo_type' => $isAr ? 'نوع البضائع' : 'Cargo Type',
            'cargo_placeholder' => $isAr ? 'مثال: بضائع عامة' : 'e.g. General Cargo',

            'container_type' => $isAr ? 'نوع الحاوية' : 'Container Type',
            'select_container' => $isAr ? 'اختر نوع الحاوية' : 'Select container type',

            'high_cube' => $isAr ? '40 قدم High Cube' : '40ft High Cube',

            'quantity' => $isAr ? 'الكمية' : 'Quantity',

            'weight' => $isAr ? 'الوزن' : 'Weight',
            'weight_placeholder' => $isAr ? 'مثال: 1500' : 'e.g. 1500',

            'weight_help' => $isAr ? 'أدخل وزن الشحنة إذا كان متوفرًا.' : 'Enter the cargo weight if available.',

            'preferred_date' => $isAr ? 'تاريخ الشحن المفضل' : 'Preferred Shipping Date',

            'cancel' => $isAr ? 'إلغاء' : 'Cancel',

            'submit_request' => $isAr ? 'إرسال طلب عرض السعر' : 'Submit Quote Request',
        ];
    @endphp


    <x-slot name="header">

        <div class="flex items-center gap-3">

            <a href="{{ route('quotations.index') }}"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-cyan-400 hover:text-cyan-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">

                <svg class="h-5 w-5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>

            </a>

            <div>

                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">
                    {{ $t['request_quote'] }}
                </h2>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $t['submit_requirements'] }}
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
                        {{ $t['freight_quote'] }}
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-400">
                        {{ $t['header_description'] }}
                    </p>

                </div>

            </div>


            {{-- Validation errors --}}
            @if ($errors->any())

                <div
                    class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-5 dark:border-red-400/20 dark:bg-red-400/10">

                    <div class="font-bold text-red-700 dark:text-red-400">
                        {{ $t['please_check'] }}
                    </div>

                    <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-red-600 dark:text-red-300">

                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- Form --}}
            <form method="POST" action="{{ route('quotations.store') }}" class="mt-6 space-y-6"
                dir="{{ $dir }}">

                @csrf


                {{-- Route --}}
                <div
                    class="rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">

                        <h2 class="font-extrabold text-slate-950 dark:text-white">
                            {{ $t['shipping_route'] }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ $t['route_description'] }}
                        </p>

                    </div>



                    {{-- HAG SMART PORT PICKER --}}
                    <div
                        x-data="{
                            origin: @js((string) old('origin_port_id', '')),
                            destination: @js((string) old('destination_port_id', '')),
                            originSearch: '',
                            destinationSearch: '',
                            showOrigin: true,
                            showDestination: true,
                            ports: @js($ports->map(fn ($port) => [
                                'id' => (string) $port->id,
                                'name' => $port->name,
                                'country' => $port->country,
                                'code' => $port->unlocode ?? '',
                            ])->values()->all()),

                            filteredPorts(field) {
                                const term = (
                                    field === 'origin'
                                        ? this.originSearch
                                        : this.destinationSearch
                                ).toLocaleLowerCase().trim();

                                const other = field === 'origin'
                                    ? this.destination
                                    : this.origin;

                                return this.ports.filter(port => {
                                    if (port.id === other) return false;

                                    if (!term) return true;

                                    return [
                                        port.name,
                                        port.country,
                                        port.code
                                    ].some(value =>
                                        String(value || '')
                                            .toLocaleLowerCase()
                                            .includes(term)
                                    );
                                });
                            },

                            selectedPort(field) {
                                const id = field === 'origin'
                                    ? this.origin
                                    : this.destination;

                                return this.ports.find(port => port.id === id);
                            }
                        }"
                        class="space-y-7 p-6">

                        <div>
                            <div class="mb-4 flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-400 font-black text-slate-950">
                                    1
                                </span>
                                <h3 class="font-bold text-slate-900 dark:text-white">
                                    {{ $t['origin_port'] }}
                                </h3>
                            </div>

                            <input type="hidden"
                                name="origin_port_id"
                                :value="origin">

                            <input type="search"
                                x-model="originSearch"
                                placeholder="{{ $isAr ? 'ابحث باسم الميناء أو الدولة أو الرمز...' : 'Search port, country or code...' }}"
                                class="mb-4 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">

                            <div class="grid max-h-72 grid-cols-2 gap-3 overflow-y-auto p-1 sm:grid-cols-3">
                                <template x-for="port in filteredPorts('origin')" :key="port.id">
                                    <button type="button"
                                        @click="origin = port.id"
                                        :aria-pressed="origin === port.id"
                                        :class="origin === port.id
                                            ? 'scale-[1.02] border-cyan-400 bg-cyan-400/15 shadow-lg shadow-cyan-400/10'
                                            : 'border-slate-200 bg-slate-50 hover:border-cyan-400 dark:border-slate-700 dark:bg-slate-800'"
                                        class="relative min-h-28 rounded-2xl border p-4 text-center transition-all">

                                        <span class="mb-2 block text-2xl">⚓</span>

                                        <span class="block text-sm font-bold text-slate-900 dark:text-white"
                                            x-text="port.name"></span>

                                        <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400"
                                            x-text="port.country"></span>

                                        <span class="mt-1 block text-xs font-semibold text-cyan-600 dark:text-cyan-400"
                                            x-text="port.code"></span>

                                        <span x-show="origin === port.id"
                                            class="absolute end-2 top-2 font-bold text-cyan-500">
                                            ✓
                                        </span>
                                    </button>
                                </template>
                            </div>

                            <p x-show="filteredPorts('origin').length === 0"
                                class="mt-3 text-sm text-slate-500">
                                {{ $isAr ? 'لا توجد موانئ مطابقة.' : 'No matching ports.' }}
                            </p>

                            <div x-show="selectedPort('origin')" x-cloak
                                class="mt-4 rounded-xl border border-cyan-400/30 bg-cyan-400/10 p-3 text-sm text-slate-800 dark:text-cyan-100">
                                <span class="font-bold">
                                    {{ $isAr ? 'ميناء الانطلاق المحدد:' : 'Selected origin:' }}
                                </span>
                                <span x-text="selectedPort('origin')?.name"></span>
                            </div>

                            @error('origin_port_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="border-t border-slate-200 pt-6 dark:border-slate-800">
                            <div class="mb-4 flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-400 font-black text-slate-950">
                                    2
                                </span>
                                <h3 class="font-bold text-slate-900 dark:text-white">
                                    {{ $t['destination_port'] }}
                                </h3>
                            </div>

                            <input type="hidden"
                                name="destination_port_id"
                                :value="destination">

                            <input type="search"
                                x-model="destinationSearch"
                                placeholder="{{ $isAr ? 'ابحث عن ميناء الوصول...' : 'Search destination port...' }}"
                                class="mb-4 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">

                            <div class="grid max-h-72 grid-cols-2 gap-3 overflow-y-auto p-1 sm:grid-cols-3">
                                <template x-for="port in filteredPorts('destination')" :key="port.id">
                                    <button type="button"
                                        @click="destination = port.id"
                                        :aria-pressed="destination === port.id"
                                        :class="destination === port.id
                                            ? 'scale-[1.02] border-cyan-400 bg-cyan-400/15 shadow-lg shadow-cyan-400/10'
                                            : 'border-slate-200 bg-slate-50 hover:border-cyan-400 dark:border-slate-700 dark:bg-slate-800'"
                                        class="relative min-h-28 rounded-2xl border p-4 text-center transition-all">

                                        <span class="mb-2 block text-2xl">🚢</span>

                                        <span class="block text-sm font-bold text-slate-900 dark:text-white"
                                            x-text="port.name"></span>

                                        <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400"
                                            x-text="port.country"></span>

                                        <span class="mt-1 block text-xs font-semibold text-cyan-600 dark:text-cyan-400"
                                            x-text="port.code"></span>

                                        <span x-show="destination === port.id"
                                            class="absolute end-2 top-2 font-bold text-cyan-500">
                                            ✓
                                        </span>
                                    </button>
                                </template>
                            </div>

                            <p x-show="filteredPorts('destination').length === 0"
                                class="mt-3 text-sm text-slate-500">
                                {{ $isAr ? 'لا توجد موانئ مطابقة.' : 'No matching ports.' }}
                            </p>

                            <div x-show="selectedPort('destination')" x-cloak
                                class="mt-4 rounded-xl border border-cyan-400/30 bg-cyan-400/10 p-3 text-sm text-slate-800 dark:text-cyan-100">
                                <span class="font-bold">
                                    {{ $isAr ? 'ميناء الوصول المحدد:' : 'Selected destination:' }}
                                </span>
                                <span x-text="selectedPort('destination')?.name"></span>
                            </div>

                            @error('destination_port_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <p x-show="origin && destination && origin === destination"
                            class="rounded-xl bg-red-50 p-3 text-sm text-red-600"
                            x-cloak>
                            {{ $isAr ? 'يجب اختيار ميناءين مختلفين.' : 'Please select two different ports.' }}
                        </p>

                    </div>



                {{-- Cargo --}}
                <div
                    class="rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">

                        <h2 class="font-extrabold text-slate-950 dark:text-white">
                            {{ $t['cargo_information'] }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ $t['cargo_description'] }}
                        </p>

                    </div>


                    <div class="grid gap-6 p-6 md:grid-cols-2">

                        <div>

                            <label for="cargo_type" class="block text-sm font-bold text-slate-700 dark:text-slate-300">
                                {{ $t['cargo_type'] }}
                            </label>

                            <input id="cargo_type" name="cargo_type" type="text" value="{{ old('cargo_type') }}"
                                required maxlength="255" placeholder="{{ $t['cargo_placeholder'] }}"
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
                                {{ $t['container_type'] }}
                            </label>

                            <select id="container_type" name="container_type" required
                                class="mt-2 block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-cyan-400 focus:ring-cyan-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white">

                                <option value="">
                                    {{ $t['select_container'] }}
                                </option>

                                <option value="20ft" @selected(old('container_type') === '20ft')>
                                    20ft
                                </option>

                                <option value="40ft" @selected(old('container_type') === '40ft')>
                                    40ft
                                </option>

                                <option value="40ft_hc" @selected(old('container_type') === '40ft_hc')>
                                    {{ $t['high_cube'] }}
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
                                {{ $t['quantity'] }}
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
                                {{ $t['weight'] }}
                            </label>

                            <input id="weight" name="weight" type="number" step="0.01" min="0"
                                value="{{ old('weight') }}" placeholder="{{ $t['weight_placeholder'] }}"
                                class="mt-2 block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-cyan-400 focus:ring-cyan-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white">

                            <p class="mt-2 text-xs text-slate-400">
                                {{ $t['weight_help'] }}
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
                                {{ $t['preferred_date'] }}
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

                        {{ $t['cancel'] }}

                    </a>

                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-400 px-7 py-3 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/20 transition hover:bg-cyan-300">

                        {{ $t['submit_request'] }}

                        <svg class="h-4 w-4 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 12h14m-6-6l6 6-6 6" />

                        </svg>

                    </button>

                </div>

            </form>

        </div>

    </div>

</x-app-layout>
