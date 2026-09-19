@props([
    'customer' => null,
    'action',
    'method' => 'POST',
])

<form action="{{ $action }}" method="POST" class="space-y-6">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="rounded-2xl border border-red-500/30 bg-red-500/10 p-5">
            <div class="mb-2 font-bold text-red-300">
                Please fix the following errors:
            </div>

            <ul class="space-y-1 text-sm text-red-200">
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Company Information --}}
    <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-6">

        <div class="mb-6">
            <h2 class="text-lg font-black text-white">
                Company Information
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Basic customer and company information.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">

            {{-- Company Name --}}
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-bold text-slate-300">
                    Company Name
                    <span class="text-cyan-400">*</span>
                </label>

                <input type="text" name="company_name" value="{{ old('company_name', $customer?->company_name) }}"
                    required
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10"
                    placeholder="Example: Gulf Trading LLC">
            </div>

            {{-- Contact Person --}}
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-300">
                    Contact Person
                </label>

                <input type="text" name="contact_person"
                    value="{{ old('contact_person', $customer?->contact_person) }}"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10"
                    placeholder="John Smith">
            </div>

            {{-- Phone --}}
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-300">
                    Phone
                </label>

                <input type="text" name="phone" value="{{ old('phone', $customer?->phone) }}"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10"
                    placeholder="+971 50 000 0000">
            </div>

            {{-- Email --}}
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-300">
                    Email
                </label>

                <input type="email" name="email" value="{{ old('email', $customer?->email) }}"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10"
                    placeholder="company@example.com">
            </div>

            {{-- Country --}}
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-300">
                    Country Code
                    <span class="text-cyan-400">*</span>
                </label>

                <select name="country_code" required
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10">
                    @php
                        $country = old('country_code', $customer?->country_code ?? 'AE');
                    @endphp

                    <option value="AE" @selected($country === 'AE')>
                        AE — United Arab Emirates
                    </option>

                    <option value="SA" @selected($country === 'SA')>
                        SA — Saudi Arabia
                    </option>

                    <option value="OM" @selected($country === 'OM')>
                        OM — Oman
                    </option>

                    <option value="QA" @selected($country === 'QA')>
                        QA — Qatar
                    </option>

                    <option value="BH" @selected($country === 'BH')>
                        BH — Bahrain
                    </option>

                    <option value="KW" @selected($country === 'KW')>
                        KW — Kuwait
                    </option>

                    <option value="IQ" @selected($country === 'IQ')>
                        IQ — Iraq
                    </option>

                    <option value="EG" @selected($country === 'EG')>
                        EG — Egypt
                    </option>

                    <option value="IN" @selected($country === 'IN')>
                        IN — India
                    </option>

                    <option value="CN" @selected($country === 'CN')>
                        CN — China
                    </option>

                    <option value="US" @selected($country === 'US')>
                        US — United States
                    </option>

                    <option value="GB" @selected($country === 'GB')>
                        GB — United Kingdom
                    </option>
                </select>
            </div>

            {{-- City --}}
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-300">
                    City
                </label>

                <input type="text" name="city" value="{{ old('city', $customer?->city) }}"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10"
                    placeholder="Dubai">
            </div>

        </div>
    </div>

    {{-- Status --}}
    <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-6">

        <label class="flex cursor-pointer items-center gap-4">

            <input type="hidden" name="is_active" value="0">

            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $customer?->is_active ?? true))
                class="h-5 w-5 rounded border-slate-700 bg-slate-900 text-cyan-400 focus:ring-cyan-400">

            <span>
                <span class="block font-bold text-white">
                    Active Customer
                </span>

                <span class="block text-sm text-slate-500">
                    Allow this customer to use the HAG Brokers platform.
                </span>
            </span>

        </label>
    </div>

    {{-- Actions --}}
    <div class="flex flex-wrap items-center justify-end gap-3">

        <a href="{{ route('admin.customers.index') }}"
            class="rounded-xl border border-slate-700 px-5 py-3 text-sm font-bold text-slate-300 transition hover:bg-slate-900 hover:text-white">
            Cancel
        </a>

        <button type="submit"
            class="rounded-xl bg-cyan-400 px-6 py-3 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/10 transition hover:bg-cyan-300">
            {{ $customer ? 'Update Customer' : 'Create Customer' }}
        </button>

    </div>

</form>
