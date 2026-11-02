<x-admin-layout>

    <x-slot name="header">
        {{ __('admin_ui.label_073') }}
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
                    {{ __('admin_ui.label_074') }}
                </label>

                <input type="text" name="company_name" value="{{ old('company_name', $company->company_name) }}"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3">
            </div>

            <div>
                <label class="block mb-2">
                    {{ __('admin_ui.label_075') }}
                </label>

                <input type="text" name="company_name_ar"
                    value="{{ old('company_name_ar', $company->company_name_ar) }}"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3">
            </div>

            <div>
                <label class="block mb-2">
                    {{ __('admin_ui.label_033') }}
                </label>

                <input type="email" name="email" value="{{ old('email', $company->email) }}"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3">
            </div>

            <div>
                <label class="block mb-2">
                    {{ __('admin_ui.label_034') }}
                </label>

                <input type="text" name="phone" value="{{ old('phone', $company->phone) }}"
                    class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3">
            </div>

            <div>
                <label class="block mb-2">
                    {{ __('admin_ui.label_076') }}
                </label>

                <textarea name="address" rows="3" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3">{{ old('address', $company->address) }}</textarea>
            </div>

            <div>
                <label class="block mb-2">
                    {{ __('admin_ui.label_077') }}
                </label>

                <input type="file" name="logo" accept="image/*" class="block w-full">

                @if ($company->logo_path)
                    <img src="{{ asset('storage/' . $company->logo_path) }}" class="mt-4 h-20 object-contain"
                        alt="Company Logo">
                @endif
            </div>

            <div>
                <label class="block mb-2">
                    {{ __('admin_ui.label_078') }}
                </label>

                <input type="file" name="stamp" accept="image/*" class="block w-full">

                @if ($company->stamp_path)
                    <img src="{{ asset('storage/' . $company->stamp_path) }}" class="mt-4 h-20 object-contain"
                        alt="Company Stamp">
                @endif
            </div>

            <div>
                <label class="block mb-2">
                    {{ __('admin_ui.label_079') }}
                </label>

                <input type="file" name="signature" accept="image/*" class="block w-full">

                @if ($company->signature_path)
                    <img src="{{ asset('storage/' . $company->signature_path) }}" class="mt-4 h-20 object-contain"
                        alt="Company Signature">
                @endif
            </div>

            <fieldset class="rounded-2xl border border-slate-300 p-5 dark:border-slate-700">
                <legend class="px-2 font-bold">{{ __('invoices.issuer_billing') }}</legend>
                <p class="hag-broker-muted mb-4">{{ __('invoices.issuer_missing') }}</p>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div><label for="company-trn" class="mb-2 block text-sm font-semibold">TRN</label><input id="company-trn" type="text" name="tax_number" dir="ltr" inputmode="numeric" maxlength="15" pattern="[0-9]{15}" value="{{ old('tax_number', $company->tax_number) }}" class="w-full rounded-xl border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></div>
                    <div><label for="company-city" class="mb-2 block text-sm font-semibold">{{ __('invoices.city') }}</label><input id="company-city" type="text" name="city" value="{{ old('city', $company->city) }}" maxlength="100" class="w-full rounded-xl border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></div>
                    <div><label for="company-country" class="mb-2 block text-sm font-semibold">{{ __('invoices.country') }}</label><input id="company-country" type="text" name="country" value="{{ old('country', $company->country) }}" maxlength="100" class="w-full rounded-xl border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></div>
                </div>
            </fieldset>

            <button type="submit" class="rounded-xl bg-cyan-400 px-6 py-3 font-bold text-slate-950">
                {{ __('admin_ui.label_080') }}
            </button>

        </form>

    </div>

</x-admin-layout>
