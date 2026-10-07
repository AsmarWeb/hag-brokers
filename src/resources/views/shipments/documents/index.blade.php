@php
    $isAr = app()->getLocale() === 'ar';
    $dir = $isAr ? 'rtl' : 'ltr';

    $t = [
        // ---------------------------------------------------------
        // Page
        // ---------------------------------------------------------
        'shipment_documents' => $isAr ? 'مستندات الشحنة' : 'Shipment Documents',

        'manage_documents' => $isAr
            ? 'إدارة المستندات والملفات المرتبطة بهذه الشحنة.'
            : 'Manage documents and files associated with this shipment.',

        'shipment_details' => $isAr ? 'تفاصيل الشحنة' : 'Shipment Details',

        'all_shipments' => $isAr ? 'جميع الشحنات' : 'All Shipments',

        'documents_files' => $isAr
            ? 'جميع المستندات والملفات المرتبطة بهذه الشحنة.'
            : 'All documents and files associated with this shipment.',

        // ---------------------------------------------------------
        // Shipment
        // ---------------------------------------------------------
        'origin' => $isAr ? 'ميناء الانطلاق' : 'Origin',

        'destination' => $isAr ? 'ميناء الوصول' : 'Destination',

        'status' => $isAr ? 'الحالة' : 'Status',

        'not_assigned' => $isAr ? 'غير محدد' : 'Not assigned',

        'delivered' => $isAr ? 'تم التسليم' : 'Delivered',

        'in_transit' => $isAr ? 'قيد النقل' : 'In Transit',

        'pending' => $isAr ? 'قيد الانتظار' : 'Pending',

        'cancelled' => $isAr ? 'ملغاة' : 'Cancelled',

        'unknown' => $isAr ? 'غير معروف' : 'Unknown',

        // ---------------------------------------------------------
        // Upload
        // ---------------------------------------------------------
        'upload_document' => $isAr ? 'رفع مستند' : 'Upload Document',

        'add_pdf_image' => $isAr ? 'أضف ملف PDF أو صورة إلى هذه الشحنة.' : 'Add a PDF or image to this shipment.',

        'document_type' => $isAr ? 'نوع المستند' : 'Document Type',

        'select_document_type' => $isAr ? 'اختر نوع المستند' : 'Select document type',

        'bill_of_lading' => $isAr ? 'بوليصة الشحن' : 'Bill of Lading',

        'commercial_invoice' => $isAr ? 'الفاتورة التجارية' : 'Commercial Invoice',

        'packing_list' => $isAr ? 'قائمة التعبئة' : 'Packing List',

        'certificate_of_origin' => $isAr ? 'شهادة المنشأ' : 'Certificate of Origin',

        'customs_document' => $isAr ? 'مستند جمركي' : 'Customs Document',

        'booking_confirmation' => $isAr ? 'تأكيد الحجز' : 'Booking Confirmation',

        'shipping_instruction' => $isAr ? 'تعليمات الشحن' : 'Shipping Instruction',

        'delivery_order' => $isAr ? 'أمر التسليم' : 'Delivery Order',

        'other' => $isAr ? 'أخرى' : 'Other',

        'file' => $isAr ? 'الملف' : 'File',

        'choose_file' => $isAr ? 'اختر ملفاً' : 'Choose a file',

        'file_formats' => $isAr ? 'PDF أو JPG أو JPEG أو PNG' : 'PDF, JPG, JPEG or PNG',

        'maximum_size' => $isAr ? 'الحد الأقصى للحجم: 10 ميجابايت' : 'Maximum size: 10 MB',

        'no_file_selected' => $isAr ? 'لم يتم اختيار ملف' : 'No file selected',

        // ---------------------------------------------------------
        // Documents list
        // ---------------------------------------------------------
        'shipment_documents_list' => $isAr ? 'مستندات الشحنة' : 'Shipment Documents',

        'document_attached_single' => $isAr ? 'مستند مرتبط بهذه الشحنة.' : 'document attached to this shipment.',

        'documents_attached' => $isAr ? 'مستندات مرتبطة بهذه الشحنة.' : 'documents attached to this shipment.',

        'uploaded' => $isAr ? 'تم الرفع' : 'Uploaded',

        'document' => $isAr ? 'مستند' : 'Document',

        'unnamed_document' => $isAr ? 'مستند بدون اسم' : 'Unnamed document',

        'file_type' => $isAr ? 'ملف' : 'FILE',

        'preview' => $isAr ? 'معاينة' : 'Preview',

        'download' => $isAr ? 'تنزيل' : 'Download',

        'version' => $isAr ? 'الإصدار' : 'Version',

        'uploaded_by' => $isAr ? 'تم الرفع بواسطة' : 'Uploaded by',

        'file_size' => $isAr ? 'حجم الملف' : 'File size',

        'no_documents' => $isAr ? 'لا توجد مستندات بعد' : 'No documents yet',

        'no_documents_uploaded' => $isAr
            ? 'لم يتم رفع أي مستندات لهذه الشحنة. استخدم نموذج الرفع لإضافة أول مستند.'
            : 'No documents have been uploaded for this shipment. Use the upload form to add your first document.',

        // ---------------------------------------------------------
        // Security
        // ---------------------------------------------------------
        'secure_storage' => $isAr ? 'تخزين آمن للمستندات' : 'Secure document storage',

        'secure_storage_text' => $isAr
            ? 'يتم ربط المستندات بهذه الشحنة وحمايتها من خلال التحقق من ملكية العميل.'
            : 'Documents are associated with this shipment and protected through customer ownership verification.',

        // ---------------------------------------------------------
        // Validation
        // ---------------------------------------------------------
        'please_correct' => $isAr ? 'يرجى تصحيح الأخطاء التالية:' : 'Please correct the following errors.',
    ];

    /*
    |--------------------------------------------------------------------------
    | Shipment reference
    |--------------------------------------------------------------------------
    */

    $reference = $shipment->reference_no ?? ($shipment->shipment_no ?? 'SHIP-' . $shipment->id);

    /*
    |--------------------------------------------------------------------------
    | Shipment status
    |--------------------------------------------------------------------------
    */

    $status = $shipment->status ?: 'pending';

    $statusClasses = match ($status) {
        'delivered' => 'bg-emerald-400/10 text-emerald-300 border-emerald-400/20',

        'in_transit' => 'bg-cyan-400/10 text-cyan-300 border-cyan-400/20',

        'pending' => 'bg-amber-400/10 text-amber-300 border-amber-400/20',

        'cancelled' => 'bg-red-400/10 text-red-300 border-red-400/20',

        default => 'bg-slate-400/10 text-slate-300 border-slate-400/20',
    };

    $statusLabel = match ($status) {
        'delivered' => $t['delivered'],
        'in_transit' => $t['in_transit'],
        'pending' => $t['pending'],
        'cancelled' => $t['cancelled'],
        default => $t['unknown'],
    };

    /*
    |--------------------------------------------------------------------------
    | Document count
    |--------------------------------------------------------------------------
    */

    $documentCount = $documents->count();
@endphp


<x-app-layout>

    {{-- ============================================================
         PAGE HEADER
    ============================================================= --}}

    <x-slot name="header">

        <div dir="{{ $dir }}" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            {{-- Brand / title --}}

            <div class="flex items-center gap-3">

                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl
                    bg-cyan-400 text-sm font-black text-slate-950
                    shadow-lg shadow-cyan-400/20">

                    HAG

                </div>

                <div class="{{ $isAr ? 'text-right' : 'text-left' }}">

                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">

                        {{ $t['shipment_documents'] }}

                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">

                        {{ $t['manage_documents'] }}

                    </p>

                </div>

            </div>


            {{-- Navigation --}}

            <div class="flex flex-wrap gap-2">

                <a href="{{ route('shipments.show', $shipment) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl
                    border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold
                    text-slate-700 shadow-sm transition
                    hover:border-cyan-300 hover:text-cyan-600
                    dark:border-slate-700 dark:bg-slate-900
                    dark:text-slate-200 dark:hover:border-cyan-500
                    dark:hover:text-cyan-400">

                    <span>
                        {{ $isAr ? '→' : '←' }}
                    </span>

                    <span>
                        {{ $t['shipment_details'] }}
                    </span>

                </a>


                <a href="{{ route('shipments.index') }}"
                    class="inline-flex items-center justify-center rounded-xl
                    bg-cyan-500 px-4 py-2.5 text-sm font-extrabold
                    text-slate-950 shadow-lg shadow-cyan-500/20
                    transition hover:bg-cyan-400">

                    {{ $t['all_shipments'] }}

                </a>

            </div>

        </div>

    </x-slot>


    {{-- ============================================================
         MAIN PAGE
    ============================================================= --}}

    <div dir="{{ $dir }}" class="min-h-screen bg-slate-50 dark:bg-slate-950">

        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">


            {{-- ====================================================
                 SUCCESS MESSAGE
            ===================================================== --}}

            @if (session('success'))
                <div
                    class="flex items-start gap-3 rounded-2xl border
                    border-emerald-200 bg-emerald-50 px-5 py-4
                    text-sm text-emerald-800
                    dark:border-emerald-900/50
                    dark:bg-emerald-950/30
                    dark:text-emerald-300">

                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />

                    </svg>

                    <span>
                        {{ session('success') }}
                    </span>

                </div>
            @endif


            {{-- ====================================================
                 VALIDATION ERRORS
            ===================================================== --}}

            @if ($errors->any())

                <div
                    class="rounded-2xl border border-red-200 bg-red-50
                    px-5 py-4 text-sm text-red-700
                    dark:border-red-900/50
                    dark:bg-red-950/30
                    dark:text-red-300">

                    <div class="font-extrabold">

                        {{ $t['please_correct'] }}

                    </div>

                    <ul class="mt-2 list-inside list-disc space-y-1">

                        @foreach ($errors->all() as $error)
                            <li>
                                {{ $error }}
                            </li>
                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- ====================================================
                 SHIPMENT HERO
            ===================================================== --}}

            <section
                class="relative overflow-hidden rounded-3xl border
                border-slate-800 bg-slate-950 p-6 shadow-xl sm:p-8">

                {{-- Decorative background --}}

                <div
                    class="absolute -right-20 -top-20 h-64 w-64
                    rounded-full bg-cyan-500/10 blur-3xl">
                </div>

                <div
                    class="absolute -bottom-24 -left-20 h-64 w-64
                    rounded-full bg-cyan-400/10 blur-3xl">
                </div>


                <div class="relative">

                    <div
                        class="flex flex-col gap-6
                        lg:flex-row lg:items-center lg:justify-between">


                        {{-- Shipment information --}}

                        <div class="{{ $isAr ? 'text-right' : 'text-left' }}">

                            <div
                                class="mb-3 inline-flex items-center gap-2
                                rounded-full border border-cyan-400/20
                                bg-cyan-400/10 px-3 py-1 text-xs
                                font-bold uppercase tracking-wider
                                text-cyan-300">

                                <span class="h-2 w-2 rounded-full bg-cyan-400">
                                </span>

                                HAG Brokers

                            </div>


                            <h1
                                class="text-3xl font-black tracking-tight
                                text-white sm:text-4xl">

                                {{ $reference }}

                            </h1>


                            <p
                                class="mt-3 max-w-2xl text-sm leading-6
                                text-slate-400 sm:text-base">

                                {{ $t['documents_files'] }}

                            </p>

                        </div>


                        {{-- Document icon --}}

                        <div
                            class="flex h-20 w-20 shrink-0 items-center
                            justify-center rounded-2xl border
                            border-cyan-400/20 bg-cyan-400/10">

                            <svg class="h-9 w-9 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M7 3h8l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z" />

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M15 3v5h5M9 13h6M9 17h6" />

                            </svg>

                        </div>

                    </div>


                    {{-- =================================================
                         SHIPMENT SUMMARY
                    ================================================== --}}

                    <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-3">


                        {{-- Origin --}}

                        <div
                            class="rounded-2xl border border-slate-800
                            bg-white/5 p-4
                            {{ $isAr ? 'text-right' : 'text-left' }}">

                            <p
                                class="text-xs font-semibold uppercase
                                tracking-wider text-slate-500">

                                {{ $t['origin'] }}

                            </p>

                            <p class="mt-1 font-bold text-white">

                                {{ $shipment->originPort?->name ?? $t['not_assigned'] }}

                            </p>

                        </div>


                        {{-- Destination --}}

                        <div
                            class="rounded-2xl border border-slate-800
                            bg-white/5 p-4
                            {{ $isAr ? 'text-right' : 'text-left' }}">

                            <p
                                class="text-xs font-semibold uppercase
                                tracking-wider text-slate-500">

                                {{ $t['destination'] }}

                            </p>

                            <p class="mt-1 font-bold text-white">

                                {{ $shipment->destinationPort?->name ?? $t['not_assigned'] }}

                            </p>

                        </div>


                        {{-- Status --}}

                        <div
                            class="rounded-2xl border border-slate-800
                            bg-white/5 p-4
                            {{ $isAr ? 'text-right' : 'text-left' }}">

                            <p
                                class="text-xs font-semibold uppercase
                                tracking-wider text-slate-500">

                                {{ $t['status'] }}

                            </p>

                            <span
                                class="mt-1 inline-flex rounded-full
                                border px-2.5 py-1 text-xs font-bold
                                {{ $statusClasses }}">

                                {{ $statusLabel }}

                            </span>

                        </div>

                    </div>

                </div>

            </section>


            {{-- ====================================================
                 MAIN CONTENT
            ===================================================== --}}

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">


                {{-- =================================================
                     UPLOAD CARD
                ================================================== --}}

                <section
                    class="rounded-3xl border border-slate-200
                    bg-white p-6 shadow-sm
                    dark:border-slate-800 dark:bg-slate-900">


                    {{-- Header --}}

                    <div class="flex items-start gap-4">

                        <div
                            class="flex h-11 w-11 shrink-0
                            items-center justify-center rounded-xl
                            bg-cyan-100 text-cyan-600
                            dark:bg-cyan-400/10
                            dark:text-cyan-400">

                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M12 16V4m0 0L8 8m4-4l4 4M5 20h14" />

                            </svg>

                        </div>


                        <div class="{{ $isAr ? 'text-right' : 'text-left' }}">

                            <h2
                                class="text-lg font-extrabold
                                text-slate-900 dark:text-white">

                                {{ $t['upload_document'] }}

                            </h2>

                            <p
                                class="mt-1 text-sm leading-6
                                text-slate-500 dark:text-slate-400">

                                {{ $t['add_pdf_image'] }}

                            </p>

                        </div>

                    </div>


                    {{-- Upload form --}}

                    <form method="POST" action="{{ route('shipments.documents.store', $shipment) }}"
                        enctype="multipart/form-data" class="mt-6 space-y-5">

                        @csrf


                        {{-- =================================================
                             DOCUMENT TYPE
                        ================================================== --}}

                        <div>

                            <label for="type"
                                class="block text-sm font-bold
                                text-slate-700 dark:text-slate-300">

                                {{ $t['document_type'] }}

                            </label>


                            <select id="type" name="type" required
                                class="mt-2 block w-full rounded-xl
                                border-slate-300 bg-white text-sm
                                text-slate-900 shadow-sm
                                focus:border-cyan-500
                                focus:ring-cyan-500
                                dark:border-slate-700
                                dark:bg-slate-950
                                dark:text-white">


                                <option value="">

                                    {{ $t['select_document_type'] }}

                                </option>


                                <option value="Bill of Lading"
                                    {{ old('type') === 'Bill of Lading' ? 'selected' : '' }}>

                                    {{ $t['bill_of_lading'] }}

                                </option>


                                <option value="Commercial Invoice"
                                    {{ old('type') === 'Commercial Invoice' ? 'selected' : '' }}>

                                    {{ $t['commercial_invoice'] }}

                                </option>


                                <option value="Packing List" {{ old('type') === 'Packing List' ? 'selected' : '' }}>

                                    {{ $t['packing_list'] }}

                                </option>


                                <option value="Certificate of Origin"
                                    {{ old('type') === 'Certificate of Origin' ? 'selected' : '' }}>

                                    {{ $t['certificate_of_origin'] }}

                                </option>


                                <option value="Customs Document"
                                    {{ old('type') === 'Customs Document' ? 'selected' : '' }}>

                                    {{ $t['customs_document'] }}

                                </option>


                                <option value="Booking Confirmation"
                                    {{ old('type') === 'Booking Confirmation' ? 'selected' : '' }}>

                                    {{ $t['booking_confirmation'] }}

                                </option>


                                <option value="Shipping Instruction"
                                    {{ old('type') === 'Shipping Instruction' ? 'selected' : '' }}>

                                    {{ $t['shipping_instruction'] }}

                                </option>


                                <option value="Delivery Order"
                                    {{ old('type') === 'Delivery Order' ? 'selected' : '' }}>

                                    {{ $t['delivery_order'] }}

                                </option>


                                <option value="Other" {{ old('type') === 'Other' ? 'selected' : '' }}>

                                    {{ $t['other'] }}

                                </option>

                            </select>


                            @error('type')
                                <p class="mt-2 text-xs font-semibold
                                    text-red-500">

                                    {{ $message }}

                                </p>
                            @enderror

                        </div>


                        {{-- =================================================
                             FILE
                        ================================================== --}}

                        <div>

                            <label for="file"
                                class="block text-sm font-bold
                                text-slate-700 dark:text-slate-300">

                                {{ $t['file'] }}

                            </label>


                            <label for="file"
                                class="mt-2 flex cursor-pointer
                                flex-col items-center justify-center
                                rounded-2xl border-2 border-dashed
                                border-slate-300 bg-slate-50 px-5 py-8
                                text-center transition
                                hover:border-cyan-400
                                hover:bg-cyan-50/50
                                dark:border-slate-700
                                dark:bg-slate-950
                                dark:hover:border-cyan-500
                                dark:hover:bg-cyan-950/20">


                                <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M12 16V4m0 0L8 8m4-4l4 4M5 20h14" />

                                </svg>


                                <span
                                    class="mt-3 text-sm font-bold
                                    text-slate-700 dark:text-slate-300">

                                    {{ $t['choose_file'] }}

                                </span>


                                <span class="mt-1 text-xs text-slate-400">

                                    {{ $t['file_formats'] }}

                                </span>


                                <span class="mt-1 text-xs text-slate-400">

                                    {{ $t['maximum_size'] }}

                                </span>


                                <input id="file" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png"
                                    required class="sr-only" />

                            </label>


                            {{-- Selected file --}}

                            <p id="selected-file"
                                class="mt-2 truncate text-xs font-semibold
                                text-cyan-600 dark:text-cyan-400">

                                {{ $t['no_file_selected'] }}

                            </p>


                            @error('file')
                                <p class="mt-2 text-xs font-semibold
                                    text-red-500">

                                    {{ $message }}

                                </p>
                            @enderror

                        </div>


                        {{-- =================================================
                             SUBMIT
                        ================================================== --}}

                        <button type="submit" id="upload-button"
                            class="inline-flex w-full items-center
                            justify-center gap-2 rounded-xl
                            bg-cyan-500 px-5 py-3 text-sm font-extrabold
                            text-slate-950 shadow-lg
                            shadow-cyan-500/20 transition
                            hover:bg-cyan-400
                            focus:outline-none focus:ring-2
                            focus:ring-cyan-500
                            focus:ring-offset-2
                            dark:focus:ring-offset-slate-900">


                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 16V4m0 0L8 8m4-4l4 4M5 20h14" />

                            </svg>


                            <span id="upload-button-text">

                                {{ $t['upload_document'] }}

                            </span>

                        </button>

                    </form>

                </section>


                {{-- =================================================
                     DOCUMENTS LIST
                ================================================== --}}

                <section
                    class="overflow-hidden rounded-3xl border
                    border-slate-200 bg-white shadow-sm
                    lg:col-span-2
                    dark:border-slate-800 dark:bg-slate-900">


                    {{-- Header --}}

                    <div class="border-b border-slate-200 px-6 py-5
                        dark:border-slate-800">

                        <div class="flex items-center justify-between gap-4">


                            <div class="{{ $isAr ? 'text-right' : 'text-left' }}">

                                <div class="flex flex-wrap items-center gap-3">

                                    <h2
                                        class="text-lg font-extrabold
                                        text-slate-900 dark:text-white">

                                        {{ $t['shipment_documents_list'] }}

                                    </h2>


                                    {{-- Count badge --}}

                                    <span
                                        class="inline-flex min-w-7 items-center
                                        justify-center rounded-full
                                        bg-cyan-100 px-2 py-1 text-xs
                                        font-black text-cyan-700
                                        dark:bg-cyan-400/10
                                        dark:text-cyan-400">

                                        {{ $documentCount }}

                                    </span>

                                </div>


                                <p
                                    class="mt-1 text-sm text-slate-500
                                    dark:text-slate-400">

                                    @if ($documentCount === 1)
                                        {{ $documentCount }}
                                        {{ $t['document_attached_single'] }}
                                    @else
                                        {{ $documentCount }}
                                        {{ $t['documents_attached'] }}
                                    @endif

                                </p>

                            </div>


                            {{-- Icon --}}

                            <div
                                class="flex h-10 w-10 shrink-0 items-center
                                justify-center rounded-xl
                                bg-slate-100
                                dark:bg-slate-800">

                                <svg class="h-5 w-5 text-slate-500
                                    dark:text-slate-400"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M7 3h8l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z" />

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M15 3v5h5" />

                                </svg>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         DOCUMENT ITEMS
                    ================================================== --}}

                    @forelse ($documents as $document)
                        @php

                            $documentType = $document->type ?? $t['document'];

                            $documentName = $document->original_name ?? ($document->name ?? $t['unnamed_document']);

                            $extension = strtoupper(pathinfo($documentName, PATHINFO_EXTENSION) ?: $t['file_type']);

                            $isPdf = $extension === 'PDF';

                            $isImage = in_array($extension, ['JPG', 'JPEG', 'PNG', 'WEBP'], true);

                            $fileSize = $document->formatted_file_size ?? '-';

                            $version = (int) ($document->version ?? 1);

                            $documentStatus = $document->status ?? 'active';

                            $statusDocumentClasses = match ($documentStatus) {
                                'active'
                                    => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300',

                                'draft'
                                    => 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300',

                                'archived'
                                    => 'border-slate-200 bg-slate-100 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400',

                                default
                                    => 'border-slate-200 bg-slate-100 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400',
                            };

                            $statusDocumentLabel = match ($documentStatus) {
                                'active' => $t['uploaded'],

                                'draft' => $isAr ? 'مسودة' : 'Draft',

                                'archived' => $isAr ? 'مؤرشف' : 'Archived',

                                default => $t['uploaded'],
                            };
                        @endphp


                        <div
                            class="border-b border-slate-100 px-6 py-5
                            last:border-b-0
                            dark:border-slate-800">

                            <div
                                class="flex flex-col gap-5
                                lg:flex-row lg:items-center
                                lg:justify-between">


                                {{-- =================================================
                                     DOCUMENT INFO
                                ================================================== --}}

                                <div class="flex min-w-0 items-start gap-4">


                                    {{-- File icon --}}

                                    <div
                                        class="flex h-12 w-12 shrink-0
                                        items-center justify-center
                                        rounded-xl
                                        {{ $isPdf
                                            ? 'bg-red-100 text-red-600 dark:bg-red-400/10 dark:text-red-400'
                                            : ($isImage
                                                ? 'bg-blue-100 text-blue-600 dark:bg-blue-400/10 dark:text-blue-400'
                                                : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300') }}">


                                        @if ($isPdf)
                                            <span class="text-[10px] font-black">

                                                PDF

                                            </span>
                                        @elseif ($isImage)
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">

                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.7"
                                                    d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z" />

                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.7" d="M8 14l2.5-3 2 2 2-2.5L18 15M8 8h.01" />

                                            </svg>
                                        @else
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">

                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.7"
                                                    d="M7 3h8l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z" />

                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.7" d="M15 3v5h5" />

                                            </svg>
                                        @endif

                                    </div>


                                    {{-- Details --}}

                                    <div
                                        class="min-w-0 flex-1
                                        {{ $isAr ? 'text-right' : 'text-left' }}">


                                        <h3 class="truncate text-sm
                                            font-extrabold text-slate-900
                                            dark:text-white"
                                            title="{{ $documentName }}">

                                            {{ $documentName }}

                                        </h3>


                                        {{-- Metadata --}}

                                        <div
                                            class="mt-2 flex flex-wrap
                                            items-center gap-x-3 gap-y-2
                                            text-xs text-slate-400">


                                            <span class="font-semibold">

                                                {{ $documentType }}

                                            </span>


                                            <span class="hidden sm:inline">
                                                •
                                            </span>


                                            <span>
                                                {{ $extension }}
                                            </span>


                                            <span class="hidden sm:inline">
                                                •
                                            </span>


                                            <span>

                                                {{ $fileSize }}

                                            </span>


                                            <span class="hidden sm:inline">
                                                •
                                            </span>


                                            <span>

                                                {{ $t['version'] }}
                                                {{ $version }}

                                            </span>


                                            @if ($document->created_at)
                                                <span class="hidden sm:inline">
                                                    •
                                                </span>

                                                <span dir="ltr">

                                                    {{ $document->created_at->format('M d, Y · H:i') }}

                                                </span>
                                            @endif

                                        </div>


                                        {{-- Uploaded by --}}

                                        @if ($document->uploadedBy)
                                            <p
                                                class="mt-2 text-xs
                                                text-slate-400">

                                                {{ $t['uploaded_by'] }}:

                                                <span
                                                    class="font-semibold
                                                    text-slate-500
                                                    dark:text-slate-300">

                                                    {{ $document->uploadedBy->name }}

                                                </span>

                                            </p>
                                        @endif

                                    </div>

                                </div>


                                {{-- =================================================
                                     ACTIONS
                                ================================================== --}}

                                <div
                                    class="flex shrink-0 flex-wrap
                                    items-center gap-2
                                    {{ $isAr ? 'lg:justify-start' : 'lg:justify-end' }}">


                                    {{-- Status --}}

                                    <span
                                        class="inline-flex items-center
                                        gap-2 rounded-full border px-3
                                        py-2 text-xs font-bold
                                        {{ $statusDocumentClasses }}">

                                        <span
                                            class="h-1.5 w-1.5 rounded-full
                                            bg-current">
                                        </span>

                                        {{ $statusDocumentLabel }}

                                    </span>


                                    {{-- Preview --}}

                                    <a href="{{ route('shipments.documents.preview', [
                                        'shipment' => $shipment,
                                        'document' => $document,
                                    ]) }}"
                                        target="_blank" rel="noopener noreferrer" title="{{ $t['preview'] }}"
                                        class="inline-flex items-center
                                        justify-center gap-2 rounded-xl
                                        border border-slate-200
                                        bg-white px-3.5 py-2.5 text-xs
                                        font-extrabold text-slate-700
                                        shadow-sm transition
                                        hover:border-cyan-300
                                        hover:text-cyan-600
                                        dark:border-slate-700
                                        dark:bg-slate-950
                                        dark:text-slate-200
                                        dark:hover:border-cyan-500
                                        dark:hover:text-cyan-400">


                                        <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                                d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z" />

                                            <circle cx="12" cy="12" r="2.5" stroke-width="1.7" />

                                        </svg>


                                        <span>
                                            {{ $t['preview'] }}
                                        </span>

                                    </a>


                                    {{-- Download --}}

                                    <a href="{{ route('shipments.documents.download', [
                                        'shipment' => $shipment,
                                        'document' => $document,
                                    ]) }}"
                                        title="{{ $t['download'] }}"
                                        class="inline-flex items-center
                                        justify-center gap-2 rounded-xl
                                        bg-cyan-500 px-3.5 py-2.5
                                        text-xs font-extrabold
                                        text-slate-950 shadow-lg
                                        shadow-cyan-500/20 transition
                                        hover:bg-cyan-400">


                                        <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M12 4v11m0 0l-4-4m4 4l4-4M5 20h14" />

                                        </svg>


                                        <span>
                                            {{ $t['download'] }}
                                        </span>

                                    </a>

                                </div>

                            </div>

                        </div>


                    @empty


                        {{-- =================================================
                             EMPTY STATE
                        ================================================== --}}

                        <div class="px-6 py-16 text-center">


                            <div
                                class="mx-auto flex h-16 w-16
                                items-center justify-center
                                rounded-2xl bg-slate-100
                                dark:bg-slate-800">

                                <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                        d="M7 3h8l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z" />

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                        d="M15 3v5h5" />

                                </svg>

                            </div>


                            <h3
                                class="mt-5 text-lg font-extrabold
                                text-slate-900 dark:text-white">

                                {{ $t['no_documents'] }}

                            </h3>


                            <p
                                class="mx-auto mt-2 max-w-md
                                text-sm leading-6 text-slate-500
                                dark:text-slate-400">

                                {{ $t['no_documents_uploaded'] }}

                            </p>

                        </div>
                    @endforelse

                </section>

            </div>


            {{-- ====================================================
                 SECURITY NOTE
            ===================================================== --}}

            <div
                class="rounded-2xl border border-slate-200
                bg-white px-5 py-4
                dark:border-slate-800 dark:bg-slate-900">

                <div class="flex items-start gap-3">


                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-cyan-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M12 15v2m-6 4h12a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2zM8 7V5a4 4 0 118 0v2" />

                    </svg>


                    <div class="{{ $isAr ? 'text-right' : 'text-left' }}">

                        <p class="text-sm font-bold text-slate-800
                            dark:text-slate-200">

                            {{ $t['secure_storage'] }}

                        </p>


                        <p
                            class="mt-1 text-xs leading-5 text-slate-500
                            dark:text-slate-400">

                            {{ $t['secure_storage_text'] }}

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ================================================================
         JAVASCRIPT
    ================================================================= --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /*
            |--------------------------------------------------------------------------
            | File selection
            |--------------------------------------------------------------------------
            */

            const fileInput =
                document.getElementById('file');

            const selectedFile =
                document.getElementById('selected-file');

            const uploadButton =
                document.getElementById('upload-button');

            const uploadButtonText =
                document.getElementById('upload-button-text');


            if (!fileInput || !selectedFile) {
                return;
            }


            fileInput.addEventListener('change', function() {

                if (
                    this.files &&
                    this.files.length > 0
                ) {

                    const file =
                        this.files[0];

                    selectedFile.textContent =
                        file.name;

                } else {

                    selectedFile.textContent =
                        @json($t['no_file_selected']);

                }

            });


            /*
            |--------------------------------------------------------------------------
            | Prevent double submission
            |--------------------------------------------------------------------------
            */

            const form =
                fileInput.closest('form');


            if (form) {

                form.addEventListener(
                    'submit',
                    function() {

                        if (
                            uploadButton &&
                            uploadButtonText
                        ) {

                            uploadButton.disabled =
                                true;

                            uploadButton.classList.add(
                                'opacity-70',
                                'cursor-not-allowed'
                            );

                            uploadButtonText.textContent =
                                @json($isAr ? 'جاري رفع المستند...' : 'Uploading document...');

                        }

                    }
                );

            }

        });
    </script>

</x-app-layout>
