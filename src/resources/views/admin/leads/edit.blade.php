@php
    $isAr = app()->getLocale() === 'ar';

    $t = [
        'title' => $isAr ? 'تعديل العميل المحتمل' : 'Edit Lead',
        'details' => $isAr ? 'بيانات العميل المحتمل' : 'Lead Information',
        'company' => $isAr ? 'الشركة' : 'Company',
        'contact_person' => $isAr ? 'جهة الاتصال' : 'Contact Person',
        'email' => $isAr ? 'البريد الإلكتروني' : 'Email',
        'phone' => $isAr ? 'الهاتف' : 'Phone',
        'country' => $isAr ? 'رمز الدولة' : 'Country Code',
        'city' => $isAr ? 'المدينة' : 'City',
        'source' => $isAr ? 'المصدر' : 'Lead Source',
        'status' => $isAr ? 'الحالة' : 'Status',
        'assigned_to' => $isAr ? 'المسؤول' : 'Assigned To',
        'notes' => $isAr ? 'ملاحظات' : 'Notes',
        'select' => $isAr ? 'اختر' : 'Select',
        'update' => $isAr ? 'حفظ التعديلات' : 'Update Lead',
        'cancel' => $isAr ? 'إلغاء' : 'Cancel',
    ];

    $statusLabels = [
        'new' => $isAr ? 'جديد' : 'New',
        'contacted' => $isAr ? 'تم التواصل' : 'Contacted',
        'qualified' => $isAr ? 'مؤهل' : 'Qualified',
        'proposal' => $isAr ? 'عرض سعر' : 'Proposal',
        'won' => $isAr ? 'تم الفوز' : 'Won',
        'lost' => $isAr ? 'خسارة' : 'Lost',
    ];

    $sourceLabels = [
        'website' => $isAr ? 'الموقع الإلكتروني' : 'Website',
        'whatsapp' => 'WhatsApp',
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'referral' => $isAr ? 'إحالة' : 'Referral',
        'phone' => $isAr ? 'هاتف' : 'Phone',
        'email' => $isAr ? 'بريد إلكتروني' : 'Email',
        'meeting' => $isAr ? 'اجتماع' : 'Meeting',
        'exhibition' => $isAr ? 'معرض' : 'Exhibition',
        'other' => $isAr ? 'أخرى' : 'Other',
    ];
@endphp

<x-admin-layout>

    <x-slot name="header">

        <div>

            <h1 class="text-2xl font-bold text-white">
                {{ $t['title'] }}
            </h1>

            <p class="mt-1 text-sm text-slate-400">
                {{ $lead->company_name }}
            </p>

        </div>

    </x-slot>

    <div class="p-4 sm:p-6 lg:p-8">

        <form method="POST" action="{{ route('admin.leads.update', $lead) }}" class="mx-auto max-w-5xl space-y-6">

            @csrf
            @method('PUT')

            @if ($errors->any())

                <div class="rounded-xl border border-rose-400/20 bg-rose-400/10 p-4 text-sm text-rose-300">

                    <ul class="list-inside list-disc">

                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach

                    </ul>

                </div>

            @endif

            <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-xl sm:p-6">

                <h2 class="mb-6 text-lg font-bold text-white">
                    {{ $t['details'] }}
                </h2>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    <div>

                        <label for="company_name" class="mb-2 block text-sm font-semibold text-slate-300">
                            {{ $t['company'] }}
                        </label>

                        <input id="company_name" name="company_name"
                            value="{{ old('company_name', $lead->company_name) }}" required
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white focus:border-cyan-400 focus:ring-cyan-400">

                        @error('company_name')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror

                    </div>

                    <div>

                        <label for="contact_person" class="mb-2 block text-sm font-semibold text-slate-300">
                            {{ $t['contact_person'] }}
                        </label>

                        <input id="contact_person" name="contact_person"
                            value="{{ old('contact_person', $lead->contact_person) }}"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white focus:border-cyan-400 focus:ring-cyan-400">

                    </div>

                    <div>

                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-300">
                            {{ $t['email'] }}
                        </label>

                        <input id="email" type="email" name="email" value="{{ old('email', $lead->email) }}"
                            dir="ltr"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white focus:border-cyan-400 focus:ring-cyan-400">

                    </div>

                    <div>

                        <label for="phone" class="mb-2 block text-sm font-semibold text-slate-300">
                            {{ $t['phone'] }}
                        </label>

                        <input id="phone" name="phone" value="{{ old('phone', $lead->phone) }}" dir="ltr"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white focus:border-cyan-400 focus:ring-cyan-400">

                    </div>

                    <div>

                        <label for="country_code" class="mb-2 block text-sm font-semibold text-slate-300">
                            {{ $t['country'] }}
                        </label>

                        <input id="country_code" name="country_code" maxlength="2"
                            value="{{ old('country_code', $lead->country_code) }}" dir="ltr"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm uppercase text-white focus:border-cyan-400 focus:ring-cyan-400">

                    </div>

                    <div>

                        <label for="city" class="mb-2 block text-sm font-semibold text-slate-300">
                            {{ $t['city'] }}
                        </label>

                        <input id="city" name="city" value="{{ old('city', $lead->city) }}"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white focus:border-cyan-400 focus:ring-cyan-400">

                    </div>

                    <div>

                        <label for="source" class="mb-2 block text-sm font-semibold text-slate-300">
                            {{ $t['source'] }}
                        </label>

                        <select id="source" name="source"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white focus:border-cyan-400 focus:ring-cyan-400">

                            <option value="">
                                {{ $t['select'] }}
                            </option>

                            @foreach ($sourceLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('source', $lead->source) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach

                        </select>

                    </div>

                    <div>

                        <label for="status" class="mb-2 block text-sm font-semibold text-slate-300">
                            {{ $t['status'] }}
                        </label>

                        <select id="status" name="status"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white focus:border-cyan-400 focus:ring-cyan-400">

                            @foreach ($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $lead->status) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach

                        </select>

                    </div>

                    <div class="md:col-span-2">

                        <label for="assigned_to" class="mb-2 block text-sm font-semibold text-slate-300">
                            {{ $t['assigned_to'] }}
                        </label>

                        <select id="assigned_to" name="assigned_to"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white focus:border-cyan-400 focus:ring-cyan-400">

                            <option value="">
                                {{ $t['select'] }}
                            </option>

                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected((string) old('assigned_to', $lead->assigned_to) === (string) $user->id)>
                                    {{ $user->name }}
                                </option>
                            @endforeach

                        </select>

                    </div>

                    <div class="md:col-span-2">

                        <label for="notes" class="mb-2 block text-sm font-semibold text-slate-300">
                            {{ $t['notes'] }}
                        </label>

                        <textarea id="notes" name="notes" rows="6"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white focus:border-cyan-400 focus:ring-cyan-400">{{ old('notes', $lead->notes) }}</textarea>

                    </div>

                </div>

            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <a href="{{ route('admin.leads.show', $lead) }}"
                    class="rounded-xl border border-slate-700 px-6 py-3 text-center text-sm font-semibold text-slate-300 hover:bg-slate-800">
                    {{ $t['cancel'] }}
                </a>

                <button type="submit"
                    class="rounded-xl bg-cyan-400 px-6 py-3 text-sm font-bold text-slate-950 hover:bg-cyan-300">
                    {{ $t['update'] }}
                </button>

            </div>

        </form>

    </div>

</x-admin-layout>
