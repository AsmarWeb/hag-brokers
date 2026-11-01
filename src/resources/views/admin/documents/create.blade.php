<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';
        $t = $t ?? [];

        $customers = $customers ?? collect();
        $shipments = $shipments ?? collect();
        $quotations = $quotations ?? collect();
        $types = $types ?? collect();

        $documentTypes = [
            'bill_of_lading' => $isAr ? 'بوليصة شحن' : 'Bill of Lading',
            'commercial_invoice' => $isAr ? 'فاتورة تجارية' : 'Commercial Invoice',
            'packing_list' => $isAr ? 'قائمة تعبئة' : 'Packing List',
            'quotation' => $isAr ? 'عرض سعر' : 'Quotation',
            'booking_confirmation' => $isAr ? 'تأكيد حجز' : 'Booking Confirmation',
            'certificate' => $isAr ? 'شهادة' : 'Certificate',
            'contract' => $isAr ? 'عقد' : 'Contract',
            'customs' => $isAr ? 'مستند جمركي' : 'Customs Document',
            'invoice' => $isAr ? 'فاتورة' : 'Invoice',
            'other' => $isAr ? 'مستند آخر' : 'Other',
        ];
    @endphp

    <div class="min-h-screen bg-slate-950 text-white" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

        {{-- Header --}}
        <div class="border-b border-white/10 bg-slate-950/80 backdrop-blur-xl">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-start gap-4">

                        <a href="{{ route('admin.documents.index') }}"
                            class="mt-1 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/[0.04] text-slate-400 transition hover:border-cyan-400/30 hover:bg-cyan-400/10 hover:text-cyan-300"
                            title="{{ $isAr ? 'العودة' : 'Back' }}">
                            <svg class="h-5 w-5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>

                        <div>
                            <div class="mb-1 flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-cyan-400 shadow-lg shadow-cyan-400/50"></span>

                                <span class="text-xs font-bold uppercase tracking-[0.2em] text-cyan-400">
                                    HAG Brokers
                                </span>
                            </div>

                            <h1 class="text-2xl font-black tracking-tight sm:text-3xl">
                                {{ $isAr ? 'رفع مستند جديد' : 'Upload New Document' }}
                            </h1>

                            <p class="mt-1 text-sm text-slate-400">
                                {{ $isAr
                                    ? 'أضف مستندًا واربطه بالعميل أو الشحنة أو عرض السعر.'
                                    : 'Upload a document and link it to a customer, shipment, or quotation.' }}
                            </p>
                        </div>

                    </div>

                    <div
                        class="hidden items-center gap-2 rounded-xl border border-cyan-400/10 bg-cyan-400/[0.04] px-4 py-3 sm:flex">
                        <svg class="h-5 w-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
                        </svg>

                        <span class="text-xs font-bold text-slate-300">
                            {{ $isAr ? 'Document Center' : 'Document Center' }}
                        </span>
                    </div>

                </div>

            </div>
        </div>


        {{-- Main --}}
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            {{-- Alerts --}}
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-400/20 bg-red-500/10 p-5">
                    <div class="mb-3 flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-red-500/10 text-red-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3.75m0 3.75h.007M10.29 3.86l-7.36 12.75A1.5 1.5 0 004.23 19h15.54a1.5 1.5 0 001.3-2.25L13.71 3.86a1.5 1.5 0 00-2.6 0z" />
                            </svg>
                        </div>

                        <div>
                            <h3 class="font-bold text-red-300">
                                {{ $isAr ? 'يرجى تصحيح الأخطاء التالية' : 'Please correct the following errors' }}
                            </h3>
                        </div>
                    </div>

                    <ul class="space-y-1 text-sm text-red-200">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <form action="{{ route('admin.documents.store') }}" method="POST" enctype="multipart/form-data"
                id="documentForm">
                @csrf

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                    {{-- LEFT --}}
                    <div class="space-y-6 lg:col-span-2">

                        {{-- Basic information --}}
                        <section
                            class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.035] shadow-2xl shadow-black/20">

                            <div class="border-b border-white/10 px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-400">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M7.5 3.75h6.878a1.5 1.5 0 011.06.44l3.872 3.87a1.5 1.5 0 01.44 1.061v10.129A1.5 1.5 0 0118.25 20.75H7.5A1.5 1.5 0 016 19.25V5.25a1.5 1.5 0 011.5-1.5z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M14.25 3.75v4.5h4.5" />
                                        </svg>
                                    </div>

                                    <div>
                                        <h2 class="font-black">
                                            {{ $isAr ? 'معلومات المستند' : 'Document Information' }}
                                        </h2>

                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $isAr ? 'البيانات الأساسية للمستند' : 'Basic document information' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                                {{-- Name --}}
                                <div class="md:col-span-2">
                                    <label class="mb-2 block text-sm font-bold text-slate-300">
                                        {{ $isAr ? 'اسم المستند' : 'Document Name' }}
                                        <span class="text-red-400">*</span>
                                    </label>

                                    <input type="text" name="name" value="{{ old('name') }}" required
                                        placeholder="{{ $isAr ? 'مثال: بوليصة شحن - HAG-2048' : 'Example: Bill of Lading - HAG-2048' }}"
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3.5 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/10">
                                </div>


                                {{-- Type --}}
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-slate-300">
                                        {{ $isAr ? 'نوع المستند' : 'Document Type' }}
                                        <span class="text-red-400">*</span>
                                    </label>

                                    <select name="type" required
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/10">
                                        <option value="">
                                            {{ $isAr ? 'اختر النوع' : 'Select type' }}
                                        </option>

                                        @foreach ($documentTypes as $value => $label)
                                            <option value="{{ $value }}" @selected(old('type') === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach

                                        @foreach ($types as $type)
                                            @if (!array_key_exists($type, $documentTypes))
                                                <option value="{{ $type }}" @selected(old('type') === $type)>
                                                    {{ ucwords(str_replace('_', ' ', $type)) }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>


                                {{-- Status --}}
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-slate-300">
                                        {{ $isAr ? 'الحالة' : 'Status' }}
                                    </label>

                                    <select name="status"
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/10">
                                        <option value="active" @selected(old('status', 'active') === 'active')}>
                                            {{ $isAr ? 'نشط' : 'Active' }}
                                        </option>

                                        <option value="draft" @selected(old('status') === 'draft')}>
                                            {{ $isAr ? 'مسودة' : 'Draft' }}
                                        </option>

                                        <option value="archived" @selected(old('status') === 'archived')}>
                                            {{ $isAr ? 'مؤرشف' : 'Archived' }}
                                        </option>
                                    </select>
                                </div>

                            </div>

                        </section>


                        {{-- Relationship --}}
                        <section
                            class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.035] shadow-2xl shadow-black/20">

                            <div class="border-b border-white/10 px-6 py-5">

                                <div class="flex items-center gap-3">

                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-400/10 text-violet-400">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 19.128a9.38 9.38 0 003.5-1.5M9 19.128a9.38 9.38 0 01-3.5-1.5M15 19.128v-3.375m-6 3.375v-3.375M12 12a3.375 3.375 0 100-6.75A3.375 3.375 0 0012 12z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 9.75a2.25 2.25 0 100-4.5m-15 4.5a2.25 2.25 0 110-4.5" />
                                        </svg>
                                    </div>

                                    <div>
                                        <h2 class="font-black">
                                            {{ $isAr ? 'ربط المستند' : 'Link Document' }}
                                        </h2>

                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $isAr ? 'حدد الكيان المرتبط بهذا المستند' : 'Choose the entity this document belongs to' }}
                                        </p>
                                    </div>

                                </div>

                            </div>


                            <div class="space-y-5 p-6">

                                {{-- Entity type --}}
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-slate-300">
                                        {{ $isAr ? 'ربط بـ' : 'Link To' }}
                                        <span class="text-red-400">*</span>
                                    </label>

                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                                        <button type="button" data-entity="customer"
                                            class="entity-btn rounded-xl border border-white/10 bg-slate-900/60 px-4 py-4 text-start transition hover:border-cyan-400/30">
                                            <div
                                                class="mb-2 flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-400/10 text-cyan-400">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15.75 7.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M4.5 19.125a7.5 7.5 0 0115 0" />
                                                </svg>
                                            </div>

                                            <div class="text-sm font-bold">
                                                {{ $isAr ? 'عميل' : 'Customer' }}
                                            </div>

                                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                {{ $isAr ? 'Company / Customer' : 'Customer account' }}
                                            </div>
                                        </button>


                                        <button type="button" data-entity="shipment"
                                            class="entity-btn rounded-xl border border-white/10 bg-slate-900/60 px-4 py-4 text-start transition hover:border-cyan-400/30">
                                            <div
                                                class="mb-2 flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-400/10 text-emerald-400">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M3.75 6.75h10.5v10.5H3.75z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M14.25 10.5h3.879a1.5 1.5 0 011.06.44l1.061 1.06a1.5 1.5 0 01.44 1.061v4.189h-6.44" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M7.5 19.5a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm9.75 0a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                                                </svg>
                                            </div>

                                            <div class="text-sm font-bold">
                                                {{ $isAr ? 'شحنة' : 'Shipment' }}
                                            </div>

                                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                {{ $isAr ? 'Shipment / Booking' : 'Shipment / Booking' }}
                                            </div>
                                        </button>


                                        <button type="button" data-entity="quotation"
                                            class="entity-btn rounded-xl border border-white/10 bg-slate-900/60 px-4 py-4 text-start transition hover:border-cyan-400/30">
                                            <div
                                                class="mb-2 flex h-9 w-9 items-center justify-center rounded-lg bg-amber-400/10 text-amber-400">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M7.5 3.75h6.878a1.5 1.5 0 011.06.44l3.872 3.87a1.5 1.5 0 01.44 1.061v10.129A1.5 1.5 0 0118.25 20.75H7.5A1.5 1.5 0 016 19.25V5.25a1.5 1.5 0 011.5-1.5z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9 12h6M9 15h4.5" />
                                                </svg>
                                            </div>

                                            <div class="text-sm font-bold">
                                                {{ $isAr ? 'عرض سعر' : 'Quotation' }}
                                            </div>

                                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                {{ $isAr ? 'Quotation / Offer' : 'Quotation / Offer' }}
                                            </div>
                                        </button>

                                    </div>
                                </div>


                                {{-- Entity ID --}}
                                <input type="hidden" name="documentable_type" id="documentable_type"
                                    value="{{ old('documentable_type') }}">

                                <div>
                                    <label for="documentable_id" class="mb-2 block text-sm font-bold text-slate-300">
                                        {{ $isAr ? 'العنصر المرتبط' : 'Related Record' }}
                                        <span class="text-red-400">*</span>
                                    </label>

                                    <select name="documentable_id" id="documentable_id" required disabled
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/10 disabled:cursor-not-allowed disabled:opacity-50">
                                        <option value="">
                                            {{ $isAr ? 'اختر نوع الربط أولاً' : 'Select link type first' }}
                                        </option>
                                    </select>

                                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $isAr
                                            ? 'يجب ربط المستند بعميل أو شحنة أو عرض سعر.'
                                            : 'The document must be linked to a customer, shipment, or quotation.' }}
                                    </p>
                                </div>

                            </div>

                        </section>


                        {{-- Upload --}}
                        <section
                            class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.035] shadow-2xl shadow-black/20">

                            <div class="border-b border-white/10 px-6 py-5">

                                <div class="flex items-center gap-3">

                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-400">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 16.5V3.75m0 0L7.5 8.25M12 3.75l4.5 4.5" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M5.25 13.5v4.125A2.625 2.625 0 007.875 20.25h8.25a2.625 2.625 0 002.625-2.625V13.5" />
                                        </svg>
                                    </div>

                                    <div>
                                        <h2 class="font-black">
                                            {{ $isAr ? 'الملف' : 'File' }}
                                        </h2>

                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $isAr ? 'ارفع ملف المستند' : 'Upload the document file' }}
                                        </p>
                                    </div>

                                </div>

                            </div>


                            <div class="p-6">

                                <label for="file" id="dropZone"
                                    class="group flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-white/10 bg-slate-950/50 px-6 py-12 text-center transition hover:border-cyan-400/40 hover:bg-cyan-400/[0.03]">

                                    <div
                                        class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-cyan-400/10 text-cyan-400 transition group-hover:scale-105">
                                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 16.5V3.75m0 0L7.5 8.25M12 3.75l4.5 4.5" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M5.25 13.5v4.125A2.625 2.625 0 007.875 20.25h8.25a2.625 2.625 0 002.625-2.625V13.5" />
                                        </svg>
                                    </div>

                                    <div class="text-base font-black text-white">
                                        {{ $isAr ? 'اضغط لاختيار الملف' : 'Click to choose a file' }}
                                    </div>

                                    <div class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                        {{ $isAr ? 'أو اسحب الملف وأسقطه هنا' : 'or drag and drop your file here' }}
                                    </div>

                                    <div class="mt-4 text-xs text-slate-600">
                                        PDF, DOC, DOCX, XLS, XLSX, CSV, JPG, PNG, WEBP
                                    </div>

                                    <input type="file" name="file" id="file" required class="hidden">

                                </label>


                                <div id="filePreview"
                                    class="mt-4 hidden rounded-xl border border-white/10 bg-slate-900/60 p-4">
                                    <div class="flex items-center gap-4">

                                        <div id="fileIcon"
                                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-400">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M7.5 3.75h6.878a1.5 1.5 0 011.06.44l3.872 3.87a1.5 1.5 0 01.44 1.061v10.129A1.5 1.5 0 0118.25 20.75H7.5A1.5 1.5 0 016 19.25V5.25a1.5 1.5 0 011.5-1.5z" />
                                            </svg>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div id="fileName" class="truncate text-sm font-bold text-white"></div>

                                            <div id="fileSize" class="mt-1 text-xs text-slate-500 dark:text-slate-400"></div>
                                        </div>

                                        <button type="button" id="removeFile"
                                            class="rounded-lg p-2 text-slate-500 dark:text-slate-400 transition hover:bg-red-500/10 hover:text-red-400"
                                            title="{{ $isAr ? 'إزالة الملف' : 'Remove file' }}">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 7.5h12M9.75 7.5V5.25h4.5V7.5m-6.75 0v10.875A1.875 1.875 0 009.375 20.25h5.25a1.875 1.875 0 001.875-1.875V7.5M10.5 11.25v5.25m3-5.25v5.25" />
                                            </svg>
                                        </button>

                                    </div>
                                </div>

                            </div>

                        </section>

                    </div>


                    {{-- RIGHT --}}
                    <div class="space-y-6">

                        {{-- Upload summary --}}
                        <section
                            class="overflow-hidden rounded-2xl border border-cyan-400/20 bg-gradient-to-br from-cyan-400/[0.08] to-cyan-500/[0.03] p-6">

                            <div class="mb-5 flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12.75L11.25 15 15 9.75" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
                                    </svg>
                                </div>

                                <div>
                                    <h2 class="font-black">
                                        {{ $isAr ? 'جاهز للرفع' : 'Ready to Upload' }}
                                    </h2>

                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        HAG Brokers Document Center
                                    </p>
                                </div>
                            </div>


                            <div class="space-y-3 text-sm">

                                <div class="flex items-center justify-between rounded-xl bg-black/10 px-4 py-3">
                                    <span class="text-slate-400">
                                        {{ $isAr ? 'الإصدار' : 'Version' }}
                                    </span>

                                    <span class="font-black text-cyan-300">
                                        v1
                                    </span>
                                </div>

                                <div class="flex items-center justify-between rounded-xl bg-black/10 px-4 py-3">
                                    <span class="text-slate-400">
                                        {{ $isAr ? 'الحالة' : 'Status' }}
                                    </span>

                                    <span
                                        class="rounded-full bg-emerald-400/10 px-3 py-1 text-xs font-bold text-emerald-300">
                                        {{ $isAr ? 'نشط' : 'Active' }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between rounded-xl bg-black/10 px-4 py-3">
                                    <span class="text-slate-400">
                                        {{ $isAr ? 'المالك' : 'Owner' }}
                                    </span>

                                    <span class="text-xs font-bold text-slate-300">
                                        {{ auth()->user()->name ?? 'Admin' }}
                                    </span>
                                </div>

                            </div>

                        </section>


                        {{-- Security --}}
                        <section class="rounded-2xl border border-white/10 bg-white/[0.035] p-6">

                            <div class="mb-4 flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-400/10 text-emerald-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 3l7.5 3v5.25c0 4.65-3.18 8.73-7.5 9.75-4.32-1.02-7.5-5.1-7.5-9.75V6L12 3z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
                                    </svg>
                                </div>

                                <h3 class="font-black">
                                    {{ $isAr ? 'أمان المستندات' : 'Document Security' }}
                                </h3>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-500 dark:text-slate-400">
                                <li class="flex gap-2">
                                    <span class="text-emerald-400">✓</span>
                                    {{ $isAr ? 'تسجيل المستخدم الذي قام بالرفع' : 'Uploader is recorded automatically' }}
                                </li>

                                <li class="flex gap-2">
                                    <span class="text-emerald-400">✓</span>
                                    {{ $isAr ? 'دعم إصدارات المستندات' : 'Document versioning supported' }}
                                </li>

                                <li class="flex gap-2">
                                    <span class="text-emerald-400">✓</span>
                                    {{ $isAr ? 'الربط مع سجلات النظام' : 'Linked to system records' }}
                                </li>
                            </ul>

                        </section>


                        {{-- Actions --}}
                        <div class="space-y-3">

                            <button type="submit" id="submitButton"
                                class="flex w-full items-center justify-center gap-3 rounded-xl bg-cyan-400 px-5 py-4 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/10 transition hover:bg-cyan-300 hover:shadow-cyan-400/20 disabled:cursor-not-allowed disabled:opacity-50">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 16.5V3.75m0 0L7.5 8.25M12 3.75l4.5 4.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M5.25 13.5v4.125A2.625 2.625 0 007.875 20.25h8.25a2.625 2.625 0 002.625-2.625V13.5" />
                                </svg>

                                <span id="submitText">
                                    {{ $isAr ? 'رفع المستند' : 'Upload Document' }}
                                </span>
                            </button>


                            <a href="{{ route('admin.documents.index') }}"
                                class="flex w-full items-center justify-center rounded-xl border border-white/10 bg-white/[0.03] px-5 py-4 text-sm font-bold text-slate-300 transition hover:bg-white/[0.06] hover:text-white">
                                {{ $isAr ? 'إلغاء' : 'Cancel' }}
                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </main>

    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const isAr = @json($isAr);

            const customers = @json($customers);
            const shipments = @json($shipments);
            const quotations = @json($quotations);

            const entityType = document.getElementById('documentable_type');
            const entityId = document.getElementById('documentable_id');
            const entityButtons = document.querySelectorAll('.entity-btn');

            const fileInput = document.getElementById('file');
            const dropZone = document.getElementById('dropZone');
            const filePreview = document.getElementById('filePreview');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const removeFile = document.getElementById('removeFile');

            const form = document.getElementById('documentForm');
            const submitButton = document.getElementById('submitButton');
            const submitText = document.getElementById('submitText');


            function activateEntity(type) {

                entityType.value = type;

                entityButtons.forEach(button => {
                    const active = button.dataset.entity === type;

                    button.classList.toggle('border-cyan-400/60', active);
                    button.classList.toggle('bg-cyan-400/[0.06]', active);
                    button.classList.toggle('ring-1', active);
                    button.classList.toggle('ring-cyan-400/20', active);

                    if (!active) {
                        button.classList.remove(
                            'border-cyan-400/60',
                            'bg-cyan-400/[0.06]',
                            'ring-1',
                            'ring-cyan-400/20'
                        );
                        button.classList.add('border-white/10');
                    }
                });


                let records = [];

                if (type === 'customer') {
                    records = customers;
                }

                if (type === 'shipment') {
                    records = shipments;
                }

                if (type === 'quotation') {
                    records = quotations;
                }


                entityId.innerHTML = '';

                const firstOption = document.createElement('option');

                firstOption.value = '';
                firstOption.textContent = isAr ?
                    'اختر السجل' :
                    'Select record';

                entityId.appendChild(firstOption);


                records.forEach(record => {

                    const option = document.createElement('option');

                    option.value = record.id;

                    if (type === 'customer') {
                        option.textContent =
                            record.company_name ||
                            record.contact_person ||
                            record.name ||
                            ('#' + record.id);
                    }

                    if (type === 'shipment') {
                        option.textContent =
                            record.shipment_no ||
                            record.booking_no ||
                            ('#' + record.id);
                    }

                    if (type === 'quotation') {
                        option.textContent =
                            record.reference_no ||
                            ('#' + record.id);
                    }

                    entityId.appendChild(option);
                });


                entityId.disabled = false;
            }


            entityButtons.forEach(button => {
                button.addEventListener('click', function() {
                    activateEntity(this.dataset.entity);
                });
            });


            function formatBytes(bytes) {

                if (!bytes) return '0 B';

                const units = ['B', 'KB', 'MB', 'GB'];

                const index = Math.floor(
                    Math.log(bytes) / Math.log(1024)
                );

                return (
                    parseFloat(
                        (bytes / Math.pow(1024, index)).toFixed(2)
                    ) +
                    ' ' +
                    units[index]
                );
            }


            function showFile(file) {

                if (!file) {
                    filePreview.classList.add('hidden');
                    return;
                }

                fileName.textContent = file.name;
                fileSize.textContent = formatBytes(file.size);

                filePreview.classList.remove('hidden');
            }


            fileInput.addEventListener('change', function() {
                showFile(this.files[0]);
            });


            ['dragenter', 'dragover'].forEach(eventName => {

                dropZone.addEventListener(eventName, function(event) {

                    event.preventDefault();
                    event.stopPropagation();

                    dropZone.classList.add(
                        'border-cyan-400',
                        'bg-cyan-400/[0.05]'
                    );
                });

            });


            ['dragleave', 'drop'].forEach(eventName => {

                dropZone.addEventListener(eventName, function(event) {

                    event.preventDefault();
                    event.stopPropagation();

                    dropZone.classList.remove(
                        'border-cyan-400',
                        'bg-cyan-400/[0.05]'
                    );
                });

            });


            dropZone.addEventListener('drop', function(event) {

                const files = event.dataTransfer.files;

                if (!files.length) return;

                fileInput.files = files;

                showFile(files[0]);
            });


            removeFile.addEventListener('click', function() {

                fileInput.value = '';

                filePreview.classList.add('hidden');
            });


            form.addEventListener('submit', function() {

                submitButton.disabled = true;

                submitText.textContent = isAr ?
                    'جارٍ رفع المستند...' :
                    'Uploading...';

            });


            // Restore old selected entity after validation error.
            const oldEntityType = @json(old('documentable_type'));
            const oldEntityId = @json(old('documentable_id'));

            if (oldEntityType) {

                activateEntity(oldEntityType);

                if (oldEntityId) {
                    entityId.value = oldEntityId;
                }
            }

        });
    </script>

</x-admin-layout>
