<x-admin-layout>

    @php
        $isAr = app()->getLocale() === 'ar';

        $t = [
            'title' => $isAr ? 'تعديل جهة الاتصال' : 'Edit Contact',
            'subtitle' => $isAr ? 'تحديث بيانات جهة الاتصال' : 'Update contact information',
            'back' => $isAr ? 'العودة إلى جهات الاتصال' : 'Back to Contacts',

            'customer' => $isAr ? 'العميل' : 'Customer',
            'select_customer' => $isAr ? 'اختر العميل' : 'Select Customer',

            'name' => $isAr ? 'الاسم' : 'Name',
            'job_title' => $isAr ? 'المسمى الوظيفي' : 'Job Title',
            'email' => $isAr ? 'البريد الإلكتروني' : 'Email',
            'phone' => $isAr ? 'الهاتف' : 'Phone',
            'whatsapp' => $isAr ? 'واتساب' : 'WhatsApp',
            'notes' => $isAr ? 'ملاحظات' : 'Notes',

            'primary' => $isAr ? 'جهة الاتصال الأساسية' : 'Primary Contact',

            'primary_help' => $isAr
                ? 'تحديد جهة الاتصال كجهة الاتصال الرئيسية لهذا العميل.'
                : 'Set this person as the primary contact for this customer.',

            'save' => $isAr ? 'حفظ التعديلات' : 'Save Changes',
            'cancel' => $isAr ? 'إلغاء' : 'Cancel',
        ];
    @endphp

    <x-slot name="header">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-2xl font-bold text-white">
                    {{ $t['title'] }}
                </h1>

                <p class="mt-1 text-sm text-slate-400">
                    {{ $t['subtitle'] }}
                </p>
            </div>

            <a href="{{ route('admin.contacts.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">
                {{ $t['back'] }}
            </a>

        </div>

    </x-slot>

    <div class="mx-auto max-w-4xl">

        <form method="POST" action="{{ route('admin.contacts.update', $contact) }}" class="space-y-6">

            @csrf
            @method('PUT')

            {{-- Customer --}}
            <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                <h2 class="mb-5 text-lg font-bold text-white">
                    {{ $t['customer'] }}
                </h2>

                <select name="customer_id" required
                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400">

                    <option value="">
                        {{ $t['select_customer'] }}
                    </option>

                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(old('customer_id', $contact->customer_id) == $customer->id)>
                            {{ $customer->company_name }}
                        </option>
                    @endforeach

                </select>

                @error('customer_id')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror

            </div>

            {{-- Contact --}}
            <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                <h2 class="mb-5 text-lg font-bold text-white">
                    {{ $t['name'] }}
                </h2>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['name'] }}
                            <span class="text-red-400">*</span>
                        </label>

                        <input type="text" name="name" value="{{ old('name', $contact->name) }}" required
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400">

                        @error('name')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['job_title'] }}
                        </label>

                        <input type="text" name="job_title" value="{{ old('job_title', $contact->job_title) }}"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400">

                        @error('job_title')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['email'] }}
                        </label>

                        <input type="email" name="email" value="{{ old('email', $contact->email) }}"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400">

                        @error('email')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['phone'] }}
                        </label>

                        <input type="text" name="phone" value="{{ old('phone', $contact->phone) }}"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400">

                        @error('phone')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            {{ $t['whatsapp'] }}
                        </label>

                        <input type="text" name="whatsapp" value="{{ old('whatsapp', $contact->whatsapp) }}"
                            class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400">

                        @error('whatsapp')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

            </div>

            {{-- Primary --}}
            <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                <label class="flex cursor-pointer items-start gap-3">

                    <input type="checkbox" name="is_primary" value="1" @checked(old('is_primary', $contact->is_primary))
                        class="mt-1 h-4 w-4 rounded border-white/20 bg-slate-950 text-cyan-400 focus:ring-cyan-400">

                    <span>
                        <span class="block font-semibold text-white">
                            {{ $t['primary'] }}
                        </span>

                        <span class="mt-1 block text-sm text-slate-400">
                            {{ $t['primary_help'] }}
                        </span>
                    </span>

                </label>

            </div>

            {{-- Notes --}}
            <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl">

                <label class="mb-2 block text-sm font-medium text-slate-300">
                    {{ $t['notes'] }}
                </label>

                <textarea name="notes" rows="5"
                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400">{{ old('notes', $contact->notes) }}</textarea>

                @error('notes')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror

            </div>

            {{-- Actions --}}
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <a href="{{ route('admin.contacts.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">
                    {{ $t['cancel'] }}
                </a>

                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-cyan-400 px-6 py-3 text-sm font-bold text-slate-950 transition hover:bg-cyan-300">
                    {{ $t['save'] }}
                </button>

            </div>

        </form>

    </div>

</x-admin-layout>
