@php
    $carrier = $carrier ?? null;
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

    {{-- Carrier Name --}}
    <div class="lg:col-span-2">
        <label for="name" class="mb-2 block text-sm font-bold text-slate-200">
            Carrier Name <span class="text-red-400">*</span>
        </label>

        <input type="text" name="name" id="name" value="{{ old('name', $carrier?->name) }}" required
            maxlength="255" placeholder="e.g. Emirates Shipping Line"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

        @error('name')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- Code --}}
    <div>
        <label for="code" class="mb-2 block text-sm font-bold text-slate-200">
            Carrier Code
        </label>

        <input type="text" name="code" id="code" value="{{ old('code', $carrier?->code) }}" maxlength="255"
            placeholder="e.g. ESL"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm uppercase text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

        @error('code')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror

        <p class="mt-2 text-xs text-slate-500">
            Internal or carrier identification code.
        </p>
    </div>


    {{-- Country --}}
    <div>
        <label for="country" class="mb-2 block text-sm font-bold text-slate-200">
            Country
        </label>

        <input type="text" name="country" id="country" value="{{ old('country', $carrier?->country) }}"
            maxlength="255" placeholder="e.g. United Arab Emirates"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

        @error('country')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- Country Code --}}
    <div>
        <label for="country_code" class="mb-2 block text-sm font-bold text-slate-200">
            Country Code
        </label>

        <select name="country_code" id="country_code"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
            <option value="">-- Select Country --</option>

            @foreach ([
        'AE' => 'United Arab Emirates',
        'SA' => 'Saudi Arabia',
        'QA' => 'Qatar',
        'KW' => 'Kuwait',
        'BH' => 'Bahrain',
        'OM' => 'Oman',
        'IQ' => 'Iraq',
        'IR' => 'Iran',
        'IN' => 'India',
        'PK' => 'Pakistan',
        'CN' => 'China',
        'SG' => 'Singapore',
        'JP' => 'Japan',
        'KR' => 'South Korea',
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'NL' => 'Netherlands',
        'DE' => 'Germany',
        'FR' => 'France',
        'IT' => 'Italy',
        'ES' => 'Spain',
        'GR' => 'Greece',
        'TR' => 'Turkey',
        'EG' => 'Egypt',
    ] as $code => $country)
                <option value="{{ $code }}" @selected(old('country_code', $carrier?->country_code) === $code)>
                    {{ $code }} — {{ $country }}
                </option>
            @endforeach

        </select>

        @error('country_code')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- Website --}}
    <div>
        <label for="website" class="mb-2 block text-sm font-bold text-slate-200">
            Website
        </label>

        <input type="url" name="website" id="website" value="{{ old('website', $carrier?->website) }}"
            maxlength="255" placeholder="https://example.com"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

        @error('website')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- API Provider --}}
    <div>
        <label for="api_provider" class="mb-2 block text-sm font-bold text-slate-200">
            API Provider
        </label>

        <input type="text" name="api_provider" id="api_provider"
            value="{{ old('api_provider', $carrier?->api_provider) }}" maxlength="255" placeholder="e.g. Datalastic"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

        @error('api_provider')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror

        <p class="mt-2 text-xs text-slate-500">
            Leave empty if the carrier is managed manually.
        </p>
    </div>

</div>


{{-- Active --}}
<div class="mt-6">

    <input type="hidden" name="is_active" value="0">

    <label
        class="flex cursor-pointer items-center gap-4 rounded-xl border border-slate-700 bg-slate-900/70 p-4 transition hover:border-cyan-400/40">

        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $carrier?->is_active ?? true))
            class="h-5 w-5 rounded border-slate-600 bg-slate-800 text-cyan-400 focus:ring-cyan-400">

        <div>
            <p class="font-bold text-white">
                Active Carrier
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Active carriers can be selected when creating vessels and quotation offers.
            </p>
        </div>

    </label>

    @error('is_active')
        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
    @enderror

</div>
