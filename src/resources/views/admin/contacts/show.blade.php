<x-admin-layout>

    @php
        $isAr = app()->getLocale() === 'ar';

        $t = [
            'title' => $isAr ? 'تفاصيل جهة الاتصال' : 'Contact Details',
            'subtitle' => $isAr
                ? 'عرض معلومات جهة الاتصال والعميل المرتبط بها'
                : 'View contact and customer information',
            'back' => $isAr ? 'العودة إلى جهات الاتصال' : 'Back to Contacts',

            'contact_information' => $isAr ? 'معلومات جهة الاتصال' : 'Contact Information',
            'customer' => $isAr ? 'العميل' : 'Customer',
            'name' => $isAr ? 'الاسم' : 'Name',
            'job_title' => $isAr ? 'المسمى الوظيفي' : 'Job Title',
            'email' => $isAr ? 'البريد الإلكتروني' : 'Email',
            'phone' => $isAr ? 'الهاتف' : 'Phone',
            'whatsapp' => $isAr ? 'واتساب' : 'WhatsApp',
            'primary' => $isAr ? 'جهة الاتصال الأساسية' : 'Primary Contact',
            'yes' => $isAr ? 'نعم' : 'Yes',
            'no' => $isAr ? 'لا' : 'No',

            'notes' => $isAr ? 'ملاحظات' : 'Notes',

            'edit' => $isAr ? 'تعديل' : 'Edit',
            'delete' => $isAr ? 'حذف' : 'Delete',

            'customer_details' => $isAr ? 'بيانات العميل' : 'Customer Details',

            'company' => $isAr ? 'اسم الشركة' : 'Company',
            'city' => $isAr ? 'المدينة' : 'City',
            'country' => $isAr ? 'الدولة' : 'Country',
            'status' => $isAr ? 'الحالة' : 'Status',
            'active' => $isAr ? 'نشط' : 'Active',
            'inactive' => $isAr ? 'غير نشط' : 'Inactive',

            'confirm_delete' => $isAr
                ? 'هل أنت متأكد من حذف جهة الاتصال؟'
                : 'Are you sure you want to delete this contact?',

            'not_available' => '—',
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

            <div class="flex flex-wrap gap-2">

                <a href="{{ route('admin.contacts.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">
                    {{ $t['back'] }}
                </a>

                <a href="{{ route('admin.contacts.edit', $contact) }}"
                    class="inline-flex items-center justify-center rounded-xl bg-cyan-400 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-cyan-300">
                    {{ $t['edit'] }}
                </a>

            </div>

        </div>

    </x-slot>

    <div class="space-y-6">

        {{-- Contact Information --}}
        <div class="rounded-2xl border border-white/10 bg-slate-900/70 shadow-xl">

            <div class="border-b border-white/10 px-6 py-5">

                <div class="flex items-center gap-4">

                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/10 text-xl font-black text-cyan-300">
                        {{ strtoupper(substr($contact->name, 0, 1)) }}
                    </div>

                    <div>
                        <h2 class="text-xl font-bold text-white">
                            {{ $contact->name }}
                        </h2>

                        @if ($contact->job_title)
                            <p class="mt-1 text-sm text-slate-400">
                                {{ $contact->job_title }}
                            </p>
                        @endif
                    </div>

                </div>

            </div>

            <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">

                {{-- Customer --}}
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        {{ $t['customer'] }}
                    </p>

                    @if ($contact->customer)
                        <a href="{{ route('admin.customers.show', $contact->customer) }}"
                            class="mt-2 inline-block text-base font-semibold text-cyan-300 hover:text-cyan-200">
                            {{ $contact->customer->company_name }}
                        </a>
                    @else
                        <p class="mt-2 text-slate-400">
                            {{ $t['not_available'] }}
                        </p>
                    @endif
                </div>

                {{-- Job --}}
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        {{ $t['job_title'] }}
                    </p>

                    <p class="mt-2 text-base text-white">
                        {{ $contact->job_title ?: $t['not_available'] }}
                    </p>
                </div>

                {{-- Email --}}
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        {{ $t['email'] }}
                    </p>

                    @if ($contact->email)
                        <a href="mailto:{{ $contact->email }}"
                            class="mt-2 inline-block text-base text-cyan-300 hover:text-cyan-200">
                            {{ $contact->email }}
                        </a>
                    @else
                        <p class="mt-2 text-slate-400">
                            {{ $t['not_available'] }}
                        </p>
                    @endif
                </div>

                {{-- Phone --}}
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        {{ $t['phone'] }}
                    </p>

                    @if ($contact->phone)
                        <a href="tel:{{ $contact->phone }}"
                            class="mt-2 inline-block text-base text-white hover:text-cyan-300">
                            {{ $contact->phone }}
                        </a>
                    @else
                        <p class="mt-2 text-slate-400">
                            {{ $t['not_available'] }}
                        </p>
                    @endif
                </div>

                {{-- WhatsApp --}}
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        {{ $t['whatsapp'] }}
                    </p>

                    <p class="mt-2 text-base text-white">
                        {{ $contact->whatsapp ?: $t['not_available'] }}
                    </p>
                </div>

                {{-- Primary --}}
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        {{ $t['primary'] }}
                    </p>

                    <div class="mt-2">

                        @if ($contact->is_primary)
                            <span
                                class="inline-flex rounded-full bg-emerald-400/10 px-3 py-1 text-sm font-bold text-emerald-300">
                                {{ $t['yes'] }}
                            </span>
                        @else
                            <span
                                class="inline-flex rounded-full bg-slate-400/10 px-3 py-1 text-sm font-bold text-slate-400">
                                {{ $t['no'] }}
                            </span>
                        @endif

                    </div>
                </div>

            </div>

        </div>

        {{-- Customer Details --}}
        @if ($contact->customer)
            <div class="rounded-2xl border border-white/10 bg-slate-900/70 shadow-xl">

                <div class="border-b border-white/10 px-6 py-5">
                    <h2 class="text-lg font-bold text-white">
                        {{ $t['customer_details'] }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2 lg:grid-cols-4">

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            {{ $t['company'] }}
                        </p>

                        <p class="mt-2 font-semibold text-white">
                            {{ $contact->customer->company_name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            {{ $t['city'] }}
                        </p>

                        <p class="mt-2 text-white">
                            {{ $contact->customer->city ?: $t['not_available'] }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            {{ $t['country'] }}
                        </p>

                        <p class="mt-2 text-white">
                            {{ $contact->customer->country_code ?: $t['not_available'] }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            {{ $t['status'] }}
                        </p>

                        <div class="mt-2">

                            @if ($contact->customer->is_active)
                                <span
                                    class="inline-flex rounded-full bg-emerald-400/10 px-3 py-1 text-xs font-bold text-emerald-300">
                                    {{ $t['active'] }}
                                </span>
                            @else
                                <span
                                    class="inline-flex rounded-full bg-red-400/10 px-3 py-1 text-xs font-bold text-red-300">
                                    {{ $t['inactive'] }}
                                </span>
                            @endif

                        </div>
                    </div>

                </div>

            </div>
        @endif

        {{-- Notes --}}
        @if ($contact->notes)
            <div class="rounded-2xl border border-white/10 bg-slate-900/70 shadow-xl">

                <div class="border-b border-white/10 px-6 py-5">
                    <h2 class="text-lg font-bold text-white">
                        {{ $t['notes'] }}
                    </h2>
                </div>

                <div class="p-6">
                    <p class="whitespace-pre-line text-sm leading-7 text-slate-300">
                        {{ $contact->notes }}
                    </p>
                </div>

            </div>
        @endif

        {{-- Delete --}}
        <div class="flex justify-end">

            <form method="POST" action="{{ route('admin.contacts.destroy', $contact) }}"
                onsubmit="return confirm('{{ $t['confirm_delete'] }}')">

                @csrf
                @method('DELETE')

                <button type="submit"
                    class="rounded-xl border border-red-400/20 bg-red-400/10 px-5 py-3 text-sm font-bold text-red-300 transition hover:bg-red-400/20">
                    {{ $t['delete'] }}
                </button>

            </form>

        </div>

    </div>

</x-admin-layout>
