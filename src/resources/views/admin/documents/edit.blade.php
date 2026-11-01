<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';
        $t = $t ?? [];

        $customers = $customers ?? collect();
        $shipments = $shipments ?? collect();
        $quotations = $quotations ?? collect();

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

        $currentType = strtolower(class_basename($document->documentable_type ?? ''));
        $currentEntity = match ($currentType) {
            'customer' => 'customer',
            'shipment' => 'shipment',
            'quotation' => 'quotation',
            default => '',
        };

        $currentEntityId = $document->documentable_id;
    @endphp


    <div class="min-h-screen bg-slate-950 text-white" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

        {{-- Header --}}
        <div class="border-b border-white/10 bg-slate-950/80 backdrop-blur-xl">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-start gap-4">

                        <a href="{{ route('admin.documents.index') }}"
                            class="mt-1 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/[0.04] text-slate-400 transition hover:border-cyan-400/30 hover:bg-cyan-400/10 hover:text-cyan-300">
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
                                {{ $isAr ? 'تعديل المستند' : 'Edit Document' }}
                            </h1>

                            <p class="mt-1 text-sm text-slate-400">
                                {{ $document->name }}
                            </p>

                        </div>

                    </div>


                    <div class="flex flex-wrap gap-2">

                        @if (\Illuminate\Support\Facades\Route::has('admin.documents.preview'))
                            <a href="{{ route('admin.documents.preview', $document) }}" target="_blank"
                                class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/[0.03] px-4 py-3 text-sm font-bold text-slate-300 transition hover:bg-white/[0.07] hover:text-white">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 12s3.75-6 9.75-6 9.75 6 9.75 6-3.75 6-9.75 6-9.75-6-9.75-6z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 14.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" />
                                </svg>

                                {{ $isAr ? 'معاينة' : 'Preview' }}
                            </a>
                        @endif


                        @if (\Illuminate\Support\Facades\Route::has('admin.documents.download'))
                            <a href="{{ route('admin.documents.download', $document) }}"
                                class="inline-flex items-center gap-2 rounded-xl bg-cyan-400 px-4 py-3 text-sm font-black text-slate-950 transition hover:bg-cyan-300">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 3.75v11.25m0 0l4.5-4.5M12 15l-4.5-4.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 19.5h13.5" />
                                </svg>

                                {{ $isAr ? 'تحميل' : 'Download' }}
                            </a>
                        @endif

                    </div>

                </div>

            </div>
        </div>


        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-400/20 bg-red-500/10 p-5">

                    <h3 class="mb-3 font-bold text-red-300">
                        {{ $isAr ? 'يرجى تصحيح الأخطاء' : 'Please correct the errors' }}
                    </h3>

                    <ul class="space-y-1 text-sm text-red-200">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>

                </div>
            @endif


            <form action="{{ route('admin.documents.update', $document) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')


                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                    {{-- MAIN --}}
                    <div class="space-y-6 lg:col-span-2">

                        {{-- Document details --}}
                        <section class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.035]">

                            <div class="border-b border-white/10 px-6 py-5">

                                <h2 class="font-black">
                                    {{ $isAr ? 'بيانات المستند' : 'Document Details' }}
                                </h2>

                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'تعديل معلومات المستند الأساسية' : 'Update the basic document information' }}
                                </p>

                            </div>


                            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                                <div class="md:col-span-2">

                                    <label class="mb-2 block text-sm font-bold text-slate-300">
                                        {{ $isAr ? 'اسم المستند' : 'Document Name' }}
                                        <span class="text-red-400">*</span>
                                    </label>

                                    <input type="text" name="name" required
                                        value="{{ old('name', $document->name) }}"
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3.5 text-sm text-white outline-none focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/10">

                                </div>


                                <div>

                                    <label class="mb-2 block text-sm font-bold text-slate-300">
                                        {{ $isAr ? 'نوع المستند' : 'Document Type' }}
                                    </label>

                                    <select name="type"
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3.5 text-sm text-white outline-none focus:border-cyan-400/50">

                                        @foreach ($documentTypes as $value => $label)
                                            <option value="{{ $value }}" @selected(old('type', $document->type) === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach

                                    </select>

                                </div>


                                <div>

                                    <label class="mb-2 block text-sm font-bold text-slate-300">
                                        {{ $isAr ? 'الحالة' : 'Status' }}
                                    </label>

                                    <select name="status"
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3.5 text-sm text-white outline-none focus:border-cyan-400/50">

                                        <option value="active" @selected(old('status', $document->status) === 'active')}>
                                            {{ $isAr ? 'نشط' : 'Active' }}
                                        </option>

                                        <option value="draft" @selected(old('status', $document->status) === 'draft')}>
                                            {{ $isAr ? 'مسودة' : 'Draft' }}
                                        </option>

                                        <option value="archived" @selected(old('status', $document->status) === 'archived')}>
                                            {{ $isAr ? 'مؤرشف' : 'Archived' }}
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </section>


                        {{-- Current relation --}}
                        <section class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.035]">

                            <div class="border-b border-white/10 px-6 py-5">

                                <h2 class="font-black">
                                    {{ $isAr ? 'السجل المرتبط' : 'Linked Record' }}
                                </h2>

                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $isAr
                                        ? 'يمكن تغيير العميل أو الشحنة أو عرض السعر المرتبط'
                                        : 'Change the customer, shipment, or quotation linked to this document' }}
                                </p>

                            </div>


                            <div class="p-6">

                                <input type="hidden" name="documentable_type" id="documentable_type"
                                    value="{{ old('documentable_type', $currentEntity) }}">


                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                                    <button type="button" data-entity="customer"
                                        class="entity-btn rounded-xl border px-4 py-4 text-start transition">
                                        <div class="mb-2 text-cyan-400">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 7.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4.5 19.125a7.5 7.5 0 0115 0" />
                                            </svg>
                                        </div>

                                        <span class="text-sm font-black">
                                            {{ $isAr ? 'عميل' : 'Customer' }}
                                        </span>
                                    </button>


                                    <button type="button" data-entity="shipment"
                                        class="entity-btn rounded-xl border px-4 py-4 text-start transition">
                                        <div class="mb-2 text-emerald-400">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.75 6.75h10.5v10.5H3.75z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M14.25 10.5h3.879a1.5 1.5 0 011.06.44l1.061 1.06a1.5 1.5 0 01.44 1.061v4.189h-6.44" />
                                            </svg>
                                        </div>

                                        <span class="text-sm font-black">
                                            {{ $isAr ? 'شحنة' : 'Shipment' }}
                                        </span>
                                    </button>


                                    <button type="button" data-entity="quotation"
                                        class="entity-btn rounded-xl border px-4 py-4 text-start transition">
                                        <div class="mb-2 text-amber-400">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M7.5 3.75h6.878a1.5 1.5 0 011.06.44l3.872 3.87a1.5 1.5 0 01.44 1.061v10.129A1.5 1.5 0 0118.25 20.75H7.5A1.5 1.5 0 016 19.25V5.25a1.5 1.5 0 011.5-1.5z" />
                                            </svg>
                                        </div>

                                        <span class="text-sm font-black">
                                            {{ $isAr ? 'عرض سعر' : 'Quotation' }}
                                        </span>
                                    </button>

                                </div>


                                <div class="mt-5">

                                    <label for="documentable_id" class="mb-2 block text-sm font-bold text-slate-300">
                                        {{ $isAr ? 'السجل' : 'Record' }}
                                    </label>

                                    <select name="documentable_id" id="documentable_id" required
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3.5 text-sm text-white outline-none focus:border-cyan-400/50"></select>

                                </div>

                            </div>

                        </section>


                        {{-- Replace file --}}
                        <section class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.035]">

                            <div class="border-b border-white/10 px-6 py-5">

                                <h2 class="font-black">
                                    {{ $isAr ? 'استبدال الملف' : 'Replace File' }}
                                </h2>

                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $isAr
                                        ? 'اتركه فارغًا إذا كنت تريد تعديل البيانات فقط'
                                        : 'Leave empty if you only want to update document details' }}
                                </p>

                            </div>


                            <div class="p-6">

                                <div class="mb-4 rounded-xl border border-cyan-400/10 bg-cyan-400/[0.03] p-4">

                                    <div class="flex items-center gap-3">

                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-400/10 text-cyan-400">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M7.5 3.75h6.878a1.5 1.5 0 011.06.44l3.872 3.87a1.5 1.5 0 01.44 1.061v10.129A1.5 1.5 0 01 18.25 20.75H7.5A1.5 1.5 0 016 19.25V5.25a1.5 1.5 0 011.5-1.5z" />
                                            </svg>
                                        </div>

                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-bold text-white">
                                                {{ $document->original_name ?: $document->name }}
                                            </div>

                                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                {{ $isAr ? 'الإصدار الحالي' : 'Current version' }}
                                                v{{ $document->version ?? 1 }}
                                                ·
                                                {{ $document->formatted_file_size ?? '-' }}
                                            </div>
                                        </div>

                                    </div>

                                </div>


                                <label for="file" id="editDropZone"
                                    class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-white/10 bg-slate-950/50 px-6 py-10 text-center transition hover:border-cyan-400/40">

                                    <svg class="mb-4 h-8 w-8 text-cyan-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 16.5V3.75m0 0L7.5 8.25M12 3.75l4.5 4.5" />
                                    </svg>

                                    <span class="text-sm font-bold text-white">
                                        {{ $isAr ? 'اختر ملفًا جديدًا' : 'Choose a new file' }}
                                    </span>

                                    <span class="mt-2 text-xs text-slate-600">
                                        {{ $isAr ? 'اختياري — سيصبح إصدارًا جديدًا' : 'Optional — will create a new version' }}
                                    </span>

                                    <input type="file" name="file" id="file" class="hidden">

                                </label>


                                <div id="newFilePreview"
                                    class="mt-4 hidden rounded-xl border border-emerald-400/10 bg-emerald-400/[0.03] p-4">
                                    <div class="text-sm font-bold text-emerald-300" id="newFileName"></div>
                                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400" id="newFileSize"></div>
                                </div>

                            </div>

                        </section>

                    </div>


                    {{-- SIDEBAR --}}
                    <div class="space-y-6">

                        {{-- Version --}}
                        <section class="rounded-2xl border border-cyan-400/20 bg-cyan-400/[0.05] p-6">

                            <div class="mb-5 flex items-center gap-3">

                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </div>

                                <div>
                                    <h3 class="font-black">
                                        {{ $isAr ? 'الإصدار الحالي' : 'Current Version' }}
                                    </h3>

                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $isAr ? 'إدارة إصدارات المستند' : 'Document version control' }}
                                    </p>
                                </div>

                            </div>


                            <div class="rounded-2xl bg-slate-950/50 p-5 text-center">

                                <div class="text-4xl font-black text-cyan-300">
                                    v{{ $document->version ?? 1 }}
                                </div>

                                <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'الإصدار النشط' : 'Active version' }}
                                </div>

                            </div>


                            @if (\Illuminate\Support\Facades\Route::has('admin.documents.versions'))
                                <a href="{{ route('admin.documents.versions', $document) }}"
                                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/[0.03] px-4 py-3 text-sm font-bold text-slate-300 transition hover:bg-white/[0.07] hover:text-white">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>

                                    {{ $isAr ? 'سجل الإصدارات' : 'Version History' }}
                                </a>
                            @endif

                        </section>


                        {{-- Metadata --}}
                        <section class="rounded-2xl border border-white/10 bg-white/[0.035] p-6">

                            <h3 class="mb-5 font-black">
                                {{ $isAr ? 'معلومات الملف' : 'File Information' }}
                            </h3>


                            <div class="space-y-4 text-sm">

                                <div>
                                    <div class="text-xs text-slate-600">
                                        {{ $isAr ? 'اسم الملف' : 'File name' }}
                                    </div>

                                    <div class="mt-1 break-all font-bold text-slate-300">
                                        {{ $document->original_name ?: '-' }}
                                    </div>
                                </div>


                                <div>
                                    <div class="text-xs text-slate-600">
                                        MIME
                                    </div>

                                    <div class="mt-1 font-mono text-xs text-slate-400">
                                        {{ $document->mime_type ?: '-' }}
                                    </div>
                                </div>


                                <div>
                                    <div class="text-xs text-slate-600">
                                        {{ $isAr ? 'الحجم' : 'Size' }}
                                    </div>

                                    <div class="mt-1 font-bold text-slate-300">
                                        {{ $document->formatted_file_size ?? '-' }}
                                    </div>
                                </div>


                                <div>
                                    <div class="text-xs text-slate-600">
                                        {{ $isAr ? 'تم الرفع بواسطة' : 'Uploaded By' }}
                                    </div>

                                    <div class="mt-1 font-bold text-slate-300">
                                        {{ $document->uploadedBy->name ?? '-' }}
                                    </div>
                                </div>


                                <div>
                                    <div class="text-xs text-slate-600">
                                        {{ $isAr ? 'تاريخ الإنشاء' : 'Created' }}
                                    </div>

                                    <div class="mt-1 font-bold text-slate-300">
                                        {{ optional($document->created_at)->format('Y-m-d H:i') }}
                                    </div>
                                </div>

                            </div>

                        </section>


                        {{-- Actions --}}
                        <section class="rounded-2xl border border-white/10 bg-white/[0.035] p-6">

                            <button type="submit"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-cyan-400 px-5 py-4 text-sm font-black text-slate-950 transition hover:bg-cyan-300">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M5.25 12.75l4.5 4.5 9-10.5" />
                                </svg>

                                {{ $isAr ? 'حفظ التغييرات' : 'Save Changes' }}
                            </button>


                            <a href="{{ route('admin.documents.index') }}"
                                class="mt-3 flex w-full items-center justify-center rounded-xl border border-white/10 bg-white/[0.03] px-5 py-4 text-sm font-bold text-slate-300 transition hover:bg-white/[0.07] hover:text-white">
                                {{ $isAr ? 'إلغاء' : 'Cancel' }}
                            </a>

                        </section>

                    </div>

                </div>

            </form>

        </main>

    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const customers = @json($customers);
            const shipments = @json($shipments);
            const quotations = @json($quotations);

            const entityType = document.getElementById('documentable_type');
            const entityId = document.getElementById('documentable_id');

            const buttons = document.querySelectorAll('.entity-btn');

            const currentEntity = @json(old('documentable_type', $currentEntity));
            const currentEntityId = @json(old('documentable_id', $currentEntityId));

            const fileInput = document.getElementById('file');
            const preview = document.getElementById('newFilePreview');
            const fileName = document.getElementById('newFileName');
            const fileSize = document.getElementById('newFileSize');


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


            function populate(type, selectedId = null) {

                entityType.value = type;

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

                    if (String(record.id) === String(selectedId)) {
                        option.selected = true;
                    }

                    entityId.appendChild(option);

                });


                buttons.forEach(button => {

                    const active =
                        button.dataset.entity === type;

                    button.classList.remove(
                        'border-cyan-400/60',
                        'bg-cyan-400/[0.06]',
                        'ring-1',
                        'ring-cyan-400/20',
                        'border-white/10'
                    );

                    if (active) {

                        button.classList.add(
                            'border-cyan-400/60',
                            'bg-cyan-400/[0.06]',
                            'ring-1',
                            'ring-cyan-400/20'
                        );

                    } else {

                        button.classList.add('border-white/10');

                    }

                });

            }


            buttons.forEach(button => {

                button.addEventListener('click', function() {

                    populate(
                        this.dataset.entity,
                        null
                    );

                });

            });


            if (currentEntity) {
                populate(currentEntity, currentEntityId);
            }


            fileInput.addEventListener('change', function() {

                const file = this.files[0];

                if (!file) {
                    preview.classList.add('hidden');
                    return;
                }

                fileName.textContent = file.name;
                fileSize.textContent = formatBytes(file.size);

                preview.classList.remove('hidden');

            });

        });
    </script>

</x-admin-layout>
