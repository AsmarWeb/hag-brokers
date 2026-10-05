@php
    $isAr = app()->getLocale() === 'ar';
    $dir = $isAr ? 'rtl' : 'ltr';

    $t = [
        'shipment_documents' => $isAr ? 'مستندات الشحنة' : 'Shipment Documents',
        'manage_documents' => $isAr
            ? 'إدارة المستندات والملفات المرتبطة بشحنتك.'
            : 'Manage documents and files related to your shipment.',
        'shipment_details' => $isAr ? 'تفاصيل الشحنة' : 'Shipment Details',
        'all_shipments' => $isAr ? 'جميع الشحنات' : 'All Shipments',

        'documents_files' => $isAr
            ? 'المستندات والملفات المرتبطة بهذه الشحنة.'
            : 'Documents and files associated with this shipment.',

        'origin' => $isAr ? 'ميناء الانطلاق' : 'Origin',
        'destination' => $isAr ? 'ميناء الوصول' : 'Destination',
        'status' => $isAr ? 'الحالة' : 'Status',
        'not_assigned' => $isAr ? 'غير محدد' : 'Not assigned',

        'delivered' => $isAr ? 'تم التسليم' : 'Delivered',
        'in_transit' => $isAr ? 'قيد النقل' : 'In Transit',
        'pending' => $isAr ? 'قيد الانتظار' : 'Pending',
        'cancelled' => $isAr ? 'ملغاة' : 'Cancelled',
        'unknown' => $isAr ? 'غير معروف' : 'Unknown',

        'upload_document' => $isAr ? 'رفع مستند' : 'Upload Document',
        'add_pdf_image' => $isAr ? 'أضف ملف PDF أو صورة إلى هذه الشحنة.' : 'Add a PDF or image to this shipment.',

        'document_type' => $isAr ? 'نوع المستند' : 'Document Type',
        'select_document_type' => $isAr ? 'اختر نوع المستند' : 'Select document type',

        'bill_of_lading' => $isAr ? 'بوليصة الشحن' : 'Bill of Lading',
        'commercial_invoice' => $isAr ? 'الفاتورة التجارية' : 'Commercial Invoice',
        'packing_list' => $isAr ? 'قائمة التعبئة' : 'Packing List',
        'certificate_of_origin' => $isAr ? 'شهادة المنشأ' : 'Certificate of Origin',
        'customs_document' => $isAr ? 'مستند جمركي' : 'Customs Document',
        'other' => $isAr ? 'أخرى' : 'Other',

        'file' => $isAr ? 'الملف' : 'File',
        'choose_file' => $isAr ? 'اختر ملفاً' : 'Choose a file',
        'file_formats' => $isAr ? 'PDF أو JPG أو JPEG أو PNG' : 'PDF, JPG, JPEG or PNG',
        'maximum_size' => $isAr ? 'الحد الأقصى للحجم: 10 ميجابايت' : 'Maximum size: 10 MB',
        'no_file_selected' => $isAr ? 'لم يتم اختيار ملف' : 'No file selected',

        'shipment_documents_list' => $isAr ? 'مستندات الشحنة' : 'Shipment Documents',
        'document_attached_single' => $isAr ? 'مستند مرتبط بهذه الشحنة.' : 'document attached to this shipment.',
        'documents_attached' => $isAr ? 'مستندات مرتبطة بهذه الشحنة.' : 'documents attached to this shipment.',

        'uploaded' => $isAr ? 'تم الرفع' : 'Uploaded',
        'document' => $isAr ? 'مستند' : 'Document',
        'unnamed_document' => $isAr ? 'مستند بدون اسم' : 'Unnamed document',
        'file_type' => $isAr ? 'ملف' : 'FILE',

        'no_documents' => $isAr ? 'لا توجد مستندات بعد' : 'No documents yet',
        'no_documents_uploaded' => $isAr
            ? 'لم يتم رفع أي مستندات لهذه الشحنة. استخدم نموذج الرفع لإضافة أول مستند.'
            : 'No documents have been uploaded for this shipment. Use the upload form to add your first document.',

        'secure_storage' => $isAr ? 'تخزين آمن للمستندات' : 'Secure document storage',
        'secure_storage_text' => $isAr
            ? 'يتم ربط مستندات الشحنة بشحنتك وحمايتها من خلال عمليات التحقق من ملكية العميل.'
            : 'Shipment documents are associated with your shipment and are protected by customer ownership checks.',

        'please_correct' => $isAr ? 'يرجى تصحيح الأخطاء التالية:' : 'Please correct the following errors.',
    ];

    $reference = $shipment->reference_no ?? ($shipment->shipment_no ?? 'SHIP-' . $shipment->id);

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

@endphp

<x-app-layout>

    <x-slot name="header">
        <div dir="{{ $dir }}" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-center gap-3">

                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-400 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/20">
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

            <div class="flex flex-wrap gap-2">

                <a href="{{ route('shipments.show', $shipment) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-cyan-300 hover:text-cyan-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-cyan-500 dark:hover:text-cyan-400">
                    <span>
                        {{ $isAr ? '→' : '←' }}
                    </span>

                    <span>
                        {{ $t['shipment_details'] }}
                    </span>
                </a>

                <a href="{{ route('shipments.index') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-cyan-500 px-4 py-2.5 text-sm font-extrabold text-slate-950 shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-400">
                    {{ $t['all_shipments'] }}
                </a>

            </div>

        </div>
    </x-slot>

    <div dir="{{ $dir }}" class="min-h-screen bg-slate-50 dark:bg-slate-950">

        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

            {{-- Success --}}
            @if (session('success'))
                <div
                    class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>

                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Validation errors --}}
            @if ($errors->any())
                <div
                    class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300">
                    <div class="font-extrabold">
                        {{ $t['please_correct'] }}
                    </div>

                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Shipment header --}}
            <section
                class="relative overflow-hidden rounded-3xl border border-slate-800 bg-slate-950 p-6 shadow-xl sm:p-8">

                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cyan-500/10 blur-3xl"></div>

                <div class="absolute -bottom-24 -left-20 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl"></div>

                <div class="relative">

                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

                        <div class="{{ $isAr ? 'text-right' : 'text-left' }}">

                            <div
                                class="mb-3 inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-cyan-300">
                                <span class="h-2 w-2 rounded-full bg-cyan-400"></span>
                                HAG Brokers
                            </div>

                            <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                                {{ $reference }}
                            </h1>

                            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-400 sm:text-base">
                                {{ $t['documents_files'] }}
                            </p>

                        </div>

                        <div
                            class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl border border-cyan-400/20 bg-cyan-400/10">
                            <svg class="h-9 w-9 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M7 3h8l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z" />

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M15 3v5h5M9 13h6M9 17h6" />
                            </svg>
                        </div>

                    </div>

                    {{-- Shipment summary --}}
                    <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-3">

                        <div
                            class="rounded-2xl border border-slate-800 bg-white/5 p-4 {{ $isAr ? 'text-right' : 'text-left' }}">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                {{ $t['origin'] }}
                            </p>

                            <p class="mt-1 font-bold text-white">
                                {{ $shipment->originPort?->name ?? $t['not_assigned'] }}
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-slate-800 bg-white/5 p-4 {{ $isAr ? 'text-right' : 'text-left' }}">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                {{ $t['destination'] }}
                            </p>

                            <p class="mt-1 font-bold text-white">
                                {{ $shipment->destinationPort?->name ?? $t['not_assigned'] }}
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-slate-800 bg-white/5 p-4 {{ $isAr ? 'text-right' : 'text-left' }}">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                {{ $t['status'] }}
                            </p>

                            <span
                                class="mt-1 inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusClasses }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                    </div>

                </div>
            </section>

            {{-- Main grid --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                {{-- Upload --}}
                <section
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="flex items-start gap-4">

                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-100 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M12 16V4m0 0L8 8m4-4l4 4M5 20h14" />
                            </svg>
                        </div>

                        <div class="{{ $isAr ? 'text-right' : 'text-left' }}">

                            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
                                {{ $t['upload_document'] }}
                            </h2>

                            <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                {{ $t['add_pdf_image'] }}
                            </p>

                        </div>

                    </div>

                    <form method="POST" action="{{ route('shipments.documents.store', $shipment) }}"
                        enctype="multipart/form-data" class="mt-6 space-y-5">

                        @csrf

                        {{-- Type --}}
                        <div>

                            <label for="type" class="block text-sm font-bold text-slate-700 dark:text-slate-300">
                                {{ $t['document_type'] }}
                            </label>

                            <select id="type" name="type" required
                                class="mt-2 block w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">

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

                                <option value="Other" {{ old('type') === 'Other' ? 'selected' : '' }}>
                                    {{ $t['other'] }}
                                </option>

                            </select>

                            @error('type')
                                <p class="mt-2 text-xs font-semibold text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- File --}}
                        <div>

                            <label for="file" class="block text-sm font-bold text-slate-700 dark:text-slate-300">
                                {{ $t['file'] }}
                            </label>

                            <label for="file"
                                class="mt-2 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center transition hover:border-cyan-400 hover:bg-cyan-50/50 dark:border-slate-700 dark:bg-slate-950 dark:hover:border-cyan-500 dark:hover:bg-cyan-950/20">

                                <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M12 16V4m0 0L8 8m4-4l4 4M5 20h14" />
                                </svg>

                                <span class="mt-3 text-sm font-bold text-slate-700 dark:text-slate-300">
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

                            <p id="selected-file"
                                class="mt-2 truncate text-xs font-semibold text-cyan-600 dark:text-cyan-400">
                                {{ $t['no_file_selected'] }}
                            </p>

                            @error('file')
                                <p class="mt-2 text-xs font-semibold text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-cyan-500 px-5 py-3 text-sm font-extrabold text-slate-950 shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">

                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 16V4m0 0L8 8m4-4l4 4M5 20h14" />
                            </svg>

                            {{ $t['upload_document'] }}

                        </button>

                    </form>
                </section>

                {{-- Documents --}}
                <section
                    class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">

                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">

                        <div class="flex items-center justify-between gap-4">

                            <div class="{{ $isAr ? 'text-right' : 'text-left' }}">

                                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
                                    {{ $t['shipment_documents_list'] }}
                                </h2>

                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">

                                    {{ $documents->count() }}

                                    @if ($documents->count() === 1)
                                        {{ $t['document_attached_single'] }}
                                    @else
                                        {{ $t['documents_attached'] }}
                                    @endif

                                </p>

                            </div>

                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800">
                                <svg class="h-5 w-5 text-slate-500 dark:text-slate-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M7 3h8l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z" />

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M15 3v5h5" />
                                </svg>
                            </div>

                        </div>
                    </div>

                    @forelse ($documents as $document)
                        @php
                            $documentType = $document->type ?? $t['document'];

                            $documentName = $document->original_name ?? ($document->name ?? $t['unnamed_document']);

                            $extension = pathinfo($documentName, PATHINFO_EXTENSION);
                            $extension = strtoupper($extension ?: $t['file_type']);
                        @endphp

                        <div class="border-b border-slate-100 px-6 py-5 last:border-b-0 dark:border-slate-800">

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                                <div class="flex min-w-0 items-center gap-4">

                                    {{-- File icon --}}
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300">

                                        @if ($extension === 'PDF')
                                            <span class="text-[10px] font-black">
                                                PDF
                                            </span>
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

                                    <div class="min-w-0 {{ $isAr ? 'text-right' : 'text-left' }}">

                                        <h3 class="truncate text-sm font-extrabold text-slate-900 dark:text-white">
                                            {{ $documentName }}
                                        </h3>

                                        <div
                                            class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-400">

                                            <span>
                                                {{ $documentType }}
                                            </span>

                                            <span class="hidden sm:inline">
                                                •
                                            </span>

                                            <span>
                                                {{ $extension }}
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

                                    </div>

                                </div>

                                {{-- File status --}}
                                <div class="shrink-0">

                                    <span
                                        class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                                        {{ $t['uploaded'] }}
                                    </span>

                                </div>

                            </div>
                        </div>

                    @empty

                        <div class="px-6 py-16 text-center">

                            <div
                                class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                                <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                        d="M7 3h8l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z" />

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                        d="M15 3v5h5" />
                                </svg>
                            </div>

                            <h3 class="mt-5 text-lg font-extrabold text-slate-900 dark:text-white">
                                {{ $t['no_documents'] }}
                            </h3>

                            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400">
                                {{ $t['no_documents_uploaded'] }}
                            </p>

                        </div>
                    @endforelse

                </section>
            </div>

            {{-- Security note --}}
            <div
                class="rounded-2xl border border-slate-200 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900">

                <div class="flex items-start gap-3">

                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-cyan-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M12 15v2m-6 4h12a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2zM8 7V5a4 4 0 118 0v2" />
                    </svg>

                    <div class="{{ $isAr ? 'text-right' : 'text-left' }}">

                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                            {{ $t['secure_storage'] }}
                        </p>

                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                            {{ $t['secure_storage_text'] }}
                        </p>

                    </div>

                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('file');
            const selectedFile = document.getElementById('selected-file');

            if (!fileInput || !selectedFile) {
                return;
            }

            fileInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    selectedFile.textContent = this.files[0].name;
                } else {
                    selectedFile.textContent = @json($t['no_file_selected']);
                }
            });
        });
    </script>

</x-app-layout>
