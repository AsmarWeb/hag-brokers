@php
    $vessel = $vessel ?? null;
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

    {{-- Carrier --}}
    <div>
        <label for="carrier_id" class="mb-2 block text-sm font-bold text-slate-200">
            Carrier
        </label>

        <select name="carrier_id" id="carrier_id"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
            <option value="">-- Select Carrier --</option>

            @foreach ($carriers as $carrier)
                <option value="{{ $carrier->id }}" @selected(old('carrier_id', $vessel?->carrier_id) == $carrier->id)>
                    {{ $carrier->name }}
                    @if ($carrier->code)
                        ({{ $carrier->code }})
                    @endif
                </option>
            @endforeach
        </select>

        @error('carrier_id')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- Vessel Name --}}
    <div>
        <label for="name" class="mb-2 block text-sm font-bold text-slate-200">
            Vessel Name <span class="text-red-400">*</span>
        </label>

        <input type="text" name="name" id="name" value="{{ old('name', $vessel?->name) }}" required
            placeholder="e.g. HAG Gulf Star"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

        @error('name')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- IMO --}}
    <div>
        <label for="imo" class="mb-2 block text-sm font-bold text-slate-200">
            IMO Number
        </label>

        <input type="text" name="imo" id="imo" value="{{ old('imo', $vessel?->imo) }}"
            placeholder="e.g. 9876543"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

        @error('imo')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- MMSI --}}
    <div>
        <label for="mmsi" class="mb-2 block text-sm font-bold text-slate-200">
            MMSI
        </label>

        <input type="text" name="mmsi" id="mmsi" value="{{ old('mmsi', $vessel?->mmsi) }}"
            placeholder="e.g. 470123456"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

        @error('mmsi')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- Call Sign --}}
    <div>
        <label for="call_sign" class="mb-2 block text-sm font-bold text-slate-200">
            Call Sign
        </label>

        <input type="text" name="call_sign" id="call_sign" value="{{ old('call_sign', $vessel?->call_sign) }}"
            placeholder="e.g. A6ABC"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

        @error('call_sign')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- Flag --}}
    <div>
        <label for="flag" class="mb-2 block text-sm font-bold text-slate-200">
            Flag
        </label>

        <input type="text" name="flag" id="flag" value="{{ old('flag', $vessel?->flag) }}"
            placeholder="e.g. UAE"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

        @error('flag')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- Vessel Type --}}
    <div>
        <label for="vessel_type" class="mb-2 block text-sm font-bold text-slate-200">
            Vessel Type
        </label>

        <select name="vessel_type" id="vessel_type"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
            <option value="">-- Select Type --</option>

            @foreach (['container', 'cargo', 'bulk_carrier', 'tanker', 'ro-ro', 'general_cargo', 'other'] as $type)
                <option value="{{ $type }}" @selected(old('vessel_type', $vessel?->vessel_type) === $type)>
                    {{ ucwords(str_replace(['_', '-'], ' ', $type)) }}
                </option>
            @endforeach
        </select>

        @error('vessel_type')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- Latitude --}}
    <div>
        <label for="latitude" class="mb-2 block text-sm font-bold text-slate-200">
            Latitude
        </label>

        <input type="number" step="0.0000001" min="-90" max="90" name="latitude" id="latitude"
            value="{{ old('latitude', $vessel?->latitude) }}" placeholder="e.g. 25.2048"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

        @error('latitude')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- Longitude --}}
    <div>
        <label for="longitude" class="mb-2 block text-sm font-bold text-slate-200">
            Longitude
        </label>

        <input type="number" step="0.0000001" min="-180" max="180" name="longitude" id="longitude"
            value="{{ old('longitude', $vessel?->longitude) }}" placeholder="e.g. 55.2708"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

        @error('longitude')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- Speed --}}
    <div>
        <label for="speed" class="mb-2 block text-sm font-bold text-slate-200">
            Speed (knots)
        </label>

        <input type="number" step="0.01" min="0" name="speed" id="speed"
            value="{{ old('speed', $vessel?->speed) }}" placeholder="e.g. 14.50"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

        @error('speed')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- Course --}}
    <div>
        <label for="course" class="mb-2 block text-sm font-bold text-slate-200">
            Course (°)
        </label>

        <input type="number" step="0.01" min="0" max="360" name="course" id="course"
            value="{{ old('course', $vessel?->course) }}" placeholder="e.g. 180"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

        @error('course')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- Destination --}}
    <div>
        <label for="destination" class="mb-2 block text-sm font-bold text-slate-200">
            Destination
        </label>

        <input type="text" name="destination" id="destination"
            value="{{ old('destination', $vessel?->destination) }}" placeholder="e.g. Jebel Ali"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

        @error('destination')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- ETA --}}
    <div>
        <label for="eta" class="mb-2 block text-sm font-bold text-slate-200">
            ETA
        </label>

        <input type="datetime-local" name="eta" id="eta"
            value="{{ old('eta', $vessel?->eta?->format('Y-m-d\TH:i')) }}"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

        @error('eta')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- Last Position --}}
    <div>
        <label for="last_position_at" class="mb-2 block text-sm font-bold text-slate-200">
            Last Position At
        </label>

        <input type="datetime-local" name="last_position_at" id="last_position_at"
            value="{{ old('last_position_at', $vessel?->last_position_at?->format('Y-m-d\TH:i')) }}"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">

        @error('last_position_at')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>


    {{-- Status --}}
    <div>
        <label for="status" class="mb-2 block text-sm font-bold text-slate-200">
            Status <span class="text-red-400">*</span>
        </label>

        <select name="status" id="status" required
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20">
            @foreach ([
        'active' => 'Active',
        'inactive' => 'Inactive',
        'maintenance' => 'Maintenance',
        'unknown' => 'Unknown',
    ] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $vessel?->status ?? 'active') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        @error('status')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>

</div>


{{-- Gulf status --}}
<div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">

    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-700 bg-slate-900/70 p-4">
        <input type="hidden" name="is_in_gulf" value="0">

        <input type="checkbox" name="is_in_gulf" value="1" @checked(old('is_in_gulf', $vessel?->is_in_gulf))
            class="h-5 w-5 rounded border-slate-600 bg-slate-800 text-cyan-400 focus:ring-cyan-400">

        <div>
            <p class="font-bold text-white">In Gulf</p>
            <p class="text-xs text-slate-500">
                Vessel is currently operating inside the Gulf region.
            </p>
        </div>
    </label>

</div>
