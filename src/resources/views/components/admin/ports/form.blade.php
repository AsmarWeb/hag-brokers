<div class="grid gap-6 md:grid-cols-2">

    {{-- Port Name --}}
    <div>
        <label class="mb-2 block text-sm font-bold text-slate-300">
            Port Name *
        </label>

        <input type="text" name="name" value="{{ old('name', $port->name ?? '') }}" required
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white focus:border-cyan-400 focus:ring-cyan-400"
            placeholder="Jebel Ali Port">

        @error('name')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Country --}}
    <div>
        <label class="mb-2 block text-sm font-bold text-slate-300">
            Country
        </label>

        <input type="text" name="country" value="{{ old('country', $port->country ?? '') }}"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white"
            placeholder="United Arab Emirates">
    </div>

    {{-- Country Code --}}
    <div>
        <label class="mb-2 block text-sm font-bold text-slate-300">
            Country Code
        </label>

        <input type="text" name="country_code" value="{{ old('country_code', $port->country_code ?? 'AE') }}"
            maxlength="2" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 uppercase text-white"
            placeholder="AE">
    </div>

    {{-- Port Code --}}
    <div>
        <label class="mb-2 block text-sm font-bold text-slate-300">
            Port Code
        </label>

        <input type="text" name="port_code" value="{{ old('port_code', $port->port_code ?? '') }}"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white" placeholder="JEA">
    </div>

    {{-- UNLOCODE --}}
    <div>
        <label class="mb-2 block text-sm font-bold text-slate-300">
            UNLOCODE
        </label>

        <input type="text" name="unlocode" value="{{ old('unlocode', $port->unlocode ?? '') }}"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 uppercase text-white"
            placeholder="AEJEA">

        @error('unlocode')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Timezone --}}
    <div>
        <label class="mb-2 block text-sm font-bold text-slate-300">
            Timezone
        </label>

        <input type="text" name="timezone" value="{{ old('timezone', $port->timezone ?? 'Asia/Dubai') }}"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white"
            placeholder="Asia/Dubai">
    </div>

    {{-- Latitude --}}
    <div>
        <label class="mb-2 block text-sm font-bold text-slate-300">
            Latitude
        </label>

        <input type="number" step="any" name="latitude" value="{{ old('latitude', $port->latitude ?? '') }}"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white" placeholder="25.011">
    </div>

    {{-- Longitude --}}
    <div>
        <label class="mb-2 block text-sm font-bold text-slate-300">
            Longitude
        </label>

        <input type="number" step="any" name="longitude" value="{{ old('longitude', $port->longitude ?? '') }}"
            class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white" placeholder="55.061">
    </div>

</div>

{{-- Flags --}}
<div class="mt-6 flex flex-wrap gap-6">

    <label class="flex items-center gap-3 text-sm font-bold text-slate-300">
        <input type="checkbox" name="is_gulf" value="1" @checked(old('is_gulf', $port->is_gulf ?? true))
            class="rounded border-slate-700 bg-slate-900 text-cyan-400">
        Gulf Port
    </label>

    <label class="flex items-center gap-3 text-sm font-bold text-slate-300">
        <input type="checkbox" name="is_uae" value="1" @checked(old('is_uae', $port->is_uae ?? false))
            class="rounded border-slate-700 bg-slate-900 text-cyan-400">
        UAE Port
    </label>

    <label class="flex items-center gap-3 text-sm font-bold text-slate-300">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $port->is_active ?? true))
            class="rounded border-slate-700 bg-slate-900 text-cyan-400">
        Active
    </label>

</div>
