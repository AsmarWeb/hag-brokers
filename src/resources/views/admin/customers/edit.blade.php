<x-admin-layout>
    <div class="mx-auto max-w-5xl space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

            <div class="flex items-start gap-4">

                {{-- Back --}}
                <a href="{{ route('admin.customers.show', $customer) }}"
                    class="mt-1 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                    title="Back to customer">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7 7-7-7 7-7" />
                    </svg>
                </a>

                <div>
                    <p class="text-sm font-medium text-blue-600">
                        HAG Brokers
                    </p>

                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                        Edit Customer
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Update information for
                        <span class="font-medium text-slate-700">
                            {{ $customer->company_name }}
                        </span>
                    </p>
                </div>

            </div>

            <div class="flex items-center gap-2">

                <span
                    class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    Customer #{{ $customer->id }}
                </span>

            </div>

        </div>


        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4">

                <div class="flex gap-3">

                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" d="M12 8v4M12 16h.01" />
                    </svg>

                    <div>
                        <p class="text-sm font-semibold text-red-800">
                            Please correct the following errors:
                        </p>

                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>

                </div>

            </div>
        @endif


        {{-- Form --}}
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="space-y-6">

            @csrf
            @method('PUT')


            {{-- Company Information --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-100 px-6 py-5">

                    <h2 class="text-base font-semibold text-slate-900">
                        Company Information
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Basic company and contact information.
                    </p>

                </div>


                <div class="grid grid-cols-1 gap-6 px-6 py-6 sm:grid-cols-2">

                    {{-- Company Name --}}
                    <div class="sm:col-span-2">

                        <label for="company_name" class="block text-sm font-semibold text-slate-700">
                            Company Name
                            <span class="text-red-500">*</span>
                        </label>

                        <input id="company_name" name="company_name" type="text"
                            value="{{ old('company_name', $customer->company_name) }}" required maxlength="255"
                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
                            placeholder="Enter company name">

                    </div>


                    {{-- Contact Person --}}
                    <div>

                        <label for="contact_person" class="block text-sm font-semibold text-slate-700">
                            Contact Person
                            <span class="text-red-500">*</span>
                        </label>

                        <input id="contact_person" name="contact_person" type="text"
                            value="{{ old('contact_person', $customer->contact_person) }}" required maxlength="255"
                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
                            placeholder="Full name">

                    </div>


                    {{-- Email --}}
                    <div>

                        <label for="email" class="block text-sm font-semibold text-slate-700">
                            Email Address
                            <span class="text-red-500">*</span>
                        </label>

                        <input id="email" name="email" type="email" value="{{ old('email', $customer->email) }}"
                            required maxlength="255"
                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
                            placeholder="company@example.com">

                    </div>


                    {{-- Phone --}}
                    <div>

                        <label for="phone" class="block text-sm font-semibold text-slate-700">
                            Phone
                        </label>

                        <input id="phone" name="phone" type="text" value="{{ old('phone', $customer->phone) }}"
                            maxlength="50"
                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
                            placeholder="+971 50 000 0000">

                    </div>


                    {{-- Country Code --}}
                    <div>

                        <label for="country_code" class="block text-sm font-semibold text-slate-700">
                            Country Code
                            <span class="text-red-500">*</span>
                        </label>

                        <input id="country_code" name="country_code" type="text"
                            value="{{ old('country_code', $customer->country_code) }}" required maxlength="2"
                            minlength="2"
                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm uppercase text-slate-900 outline-none transition placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
                            placeholder="AE">

                        <p class="mt-1.5 text-xs text-slate-400">
                            Use the two-letter country code, e.g. AE, US, GB.
                        </p>

                    </div>


                    {{-- City --}}
                    <div>

                        <label for="city" class="block text-sm font-semibold text-slate-700">
                            City
                        </label>

                        <input id="city" name="city" type="text" value="{{ old('city', $customer->city) }}"
                            maxlength="255"
                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
                            placeholder="Dubai">

                    </div>

                </div>

            </div>


            {{-- Account Status --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-100 px-6 py-5">

                    <h2 class="text-base font-semibold text-slate-900">
                        Account Status
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Control whether this customer account is active.
                    </p>

                </div>


                <div class="px-6 py-6">

                    <label class="flex cursor-pointer items-start gap-4">

                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $customer->is_active) ? 'checked' : '' }}
                            class="mt-1 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500">

                        <span>

                            <span class="block text-sm font-semibold text-slate-900">
                                Active Customer
                            </span>

                            <span class="mt-1 block text-sm text-slate-500">
                                Active customers can continue using their account and accessing available services.
                            </span>

                        </span>

                    </label>

                </div>

            </div>


            {{-- Form Actions --}}
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <a href="{{ route('admin.customers.show', $customer) }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                </a>

                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">

                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5 9.5 17 19 7.5" />
                    </svg>

                    Save Changes

                </button>

            </div>

        </form>

    </div>
</x-admin-layout>
