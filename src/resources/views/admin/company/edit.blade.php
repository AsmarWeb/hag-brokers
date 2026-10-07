<x-admin-layout>

    <x-slot name="header">
        Company Settings
    </x-slot>

    <div class="p-6">

        @if (session('success'))
            <div class="mb-6 rounded-xl bg-emerald-500/10 p-4 text-emerald-400">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.company.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block mb-2">
                    Company Name
                </label>

                <input type="text" name="company_name" value="{{ old('company_name', $company->company_name) }}"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3">
            </div>

            <div>
                <label class="block mb-2">
                    Company Name Arabic
                </label>

                <input type="text" name="company_name_ar"
                    value="{{ old('company_name_ar', $company->company_name_ar) }}"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3">
            </div>

            <div>
                <label class="block mb-2">
                    Email
                </label>

                <input type="email" name="email" value="{{ old('email', $company->email) }}"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3">
            </div>

            <div>
                <label class="block mb-2">
                    Phone
                </label>

                <input type="text" name="phone" value="{{ old('phone', $company->phone) }}"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3">
            </div>

            <div>
                <label class="block mb-2">
                    Address
                </label>

                <textarea name="address" rows="3" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3">{{ old('address', $company->address) }}</textarea>
            </div>

            <div>
                <label class="block mb-2">
                    Logo
                </label>

                <input type="file" name="logo" accept="image/*" class="block w-full">

                @if ($company->logo_path)
                    <img src="{{ asset('storage/' . $company->logo_path) }}" class="mt-4 h-20 object-contain"
                        alt="Company Logo">
                @endif
            </div>

            <div>
                <label class="block mb-2">
                    Stamp
                </label>

                <input type="file" name="stamp" accept="image/*" class="block w-full">

                @if ($company->stamp_path)
                    <img src="{{ asset('storage/' . $company->stamp_path) }}" class="mt-4 h-20 object-contain"
                        alt="Company Stamp">
                @endif
            </div>

            <div>
                <label class="block mb-2">
                    Signature
                </label>

                <input type="file" name="signature" accept="image/*" class="block w-full">

                @if ($company->signature_path)
                    <img src="{{ asset('storage/' . $company->signature_path) }}" class="mt-4 h-20 object-contain"
                        alt="Company Signature">
                @endif
            </div>

            <button type="submit" class="rounded-xl bg-cyan-400 px-6 py-3 font-bold text-slate-950">
                Save Company Settings
            </button>

        </form>

    </div>

</x-admin-layout>
