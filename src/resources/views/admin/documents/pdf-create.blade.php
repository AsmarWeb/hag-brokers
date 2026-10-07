<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';

        $templates = $templates ?? collect();
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
    @endphp


    <div class="min-h-screen bg-slate-950 text-white" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

        {{-- Header --}}
        <div class="border-b border-white/10 bg-slate-950/90 backdrop-blur-xl">

            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-start gap-4">

                        <a href="{{ route('admin.documents.index') }}"
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/[0.04] text-slate-400 transition hover:border-cyan-400/30 hover:bg-cyan-400/10 hover:text-cyan-300">
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
                                {{ $isAr ? 'إنشاء PDF' : 'Generate PDF' }}
                            </h1>


                            <p class="mt-1 text-sm text-slate-400">
                                {{ $isAr
                                    ? 'إنشاء مستند PDF احترافي باستخدام قوالب HAG Brokers.'
                                    : 'Generate a professional PDF using HAG Brokers document templates.' }}
                            </p>

                        </div>

                    </div>


                    <div
                        class="flex items-center gap-3 rounded-xl border border-cyan-400/10 bg-cyan-400/[0.04] px-4 py-3">

                        <svg class="h-5 w-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7.5 3.75h6.878a1.5 1.5 0 011.06.44l3.872 3.87a1.5 1.5 0 01.44 1.061v10.129A1.5 1.5 0 0118.25 20.75H7.5A1.5 1.5 0 016 19.25V5.25a1.5 1.5 0 011.5-1.5z" />

                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 3.75v4.5h4.5" />
                        </svg>


                        <span class="text-xs font-bold text-slate-300">
                            {{ $isAr ? 'PDF Engine' : 'PDF Engine' }}
                        </span>

                    </div>

                </div>

            </div>

        </div>


        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">


            {{-- Errors --}}
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


                        <h3 class="font-bold text-red-300">
                            {{ $isAr ? 'يرجى تصحيح الأخطاء' : 'Please correct the errors' }}
                        </h3>

                    </div>


                    <ul class="space-y-1 text-sm text-red-200">

                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach

                    </ul>

                </div>

            @endif


            <form action="{{ route('admin.documents.pdf.generate') }}" method="POST" id="pdfForm">

                @csrf


                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">


                    {{-- MAIN --}}
                    <div class="space-y-6 lg:col-span-2">


                        {{-- Template --}}
                        <section class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.035]">

                            <div class="border-b border-white/10 px-6 py-5">

                                <div class="flex items-center gap-3">

                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-400">

                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M7.5 3.75h6.878a1.5 1.5 0 011.06.44l3.872 3.87a1.5 1.5 0 01.44 1.061v10.129A1.5 1.5 0 0118.25 20.75H7.5A1.5 1.5 0 016 19.25V5.25a1.5 1.5 0 011.5-1.5z" />

                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 15h4.5" />
                                        </svg>

                                    </div>


                                    <div>

                                        <h2 class="font-black">
                                            {{ $isAr ? 'قالب المستند' : 'Document Template' }}
                                        </h2>

                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $isAr ? 'اختر القالب الذي سيتم استخدامه لإنشاء PDF' : 'Choose the template used to generate the PDF' }}
                                        </p>

                                    </div>

                                </div>

                            </div>


                            <div class="p-6">

                                <label class="mb-2 block text-sm font-bold text-slate-300">

                                    {{ $isAr ? 'القالب' : 'Template' }}

                                    <span class="text-red-400">*</span>

                                </label>


                                <select name="template_id" id="template_id" required
                                    class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-4 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/10">

                                    <option value="">
                                        {{ $isAr ? 'اختر قالبًا' : 'Select a template' }}
                                    </option>


                                    @foreach ($templates as $template)
                                        <option value="{{ $template->id }}" data-type="{{ $template->document_type }}"
                                            data-entity="{{ $template->entity_type }}" @selected(old('template_id') == $template->id)>
                                            {{ $template->name }}
                                        </option>
                                    @endforeach

                                </select>


                                @if ($templates->isEmpty())
                                    <div class="mt-4 rounded-xl border border-amber-400/20 bg-amber-400/[0.05] p-4">

                                        <div class="flex gap-3">

                                            <svg class="h-5 w-5 shrink-0 text-amber-400" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 9v3.75m0 3.75h.007M10.29 3.86l-7.36 12.75A1.5 1.5 0 004.23 19h15.54a1.5 1.5 0 001.3-2.25L13.71 3.86a1.5 1.5 0 00-2.6 0z" />
                                            </svg>


                                            <div>

                                                <div class="text-sm font-bold text-amber-300">
                                                    {{ $isAr ? 'لا توجد قوالب' : 'No templates available' }}
                                                </div>

                                                <p class="mt-1 text-xs text-slate-500">
                                                    {{ $isAr
                                                        ? 'أنشئ Document Template أولاً حتى تتمكن من إنشاء PDF.'
                                                        : 'Create a Document Template first before generating PDFs.' }}
                                                </p>

                                            </div>

                                        </div>

                                    </div>
                                @endif

                            </div>

                        </section>


                        {{-- Document --}}
                        <section class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.035]">

                            <div class="border-b border-white/10 px-6 py-5">

                                <h2 class="font-black">
                                    {{ $isAr ? 'نوع المستند' : 'Document Type' }}
                                </h2>

                            </div>


                            <div class="p-6">

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                                    @foreach ($documentTypes as $value => $label)
                                        <label class="cursor-pointer">

                                            <input type="radio" name="document_type" value="{{ $value }}"
                                                class="peer sr-only document-type-radio" @checked(old('document_type') === $value)>

                                            <div
                                                class="rounded-xl border border-white/10 bg-slate-900/50 p-4 transition peer-checked:border-cyan-400/50 peer-checked:bg-cyan-400/[0.06]">

                                                <div class="flex items-center justify-between">

                                                    <span class="text-sm font-bold text-slate-300">
                                                        {{ $label }}
                                                    </span>

                                                    <span
                                                        class="h-2 w-2 rounded-full bg-slate-700 peer-checked:bg-cyan-400"></span>

                                                </div>

                                            </div>

                                        </label>
                                    @endforeach

                                </div>

                            </div>

                        </section>


                        {{-- Relation --}}
                        <section class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.035]">

                            <div class="border-b border-white/10 px-6 py-5">

                                <h2 class="font-black">
                                    {{ $isAr ? 'مصدر البيانات' : 'Data Source' }}
                                </h2>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $isAr
                                        ? 'اختر السجل الذي سيتم استخدام بياناته داخل المستند'
                                        : 'Choose the record whose data will populate the document' }}
                                </p>

                            </div>


                            <div class="p-6">

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                                    <button type="button" data-entity="customer"
                                        class="source-btn rounded-xl border border-white/10 bg-slate-900/60 p-4 text-start transition hover:border-cyan-400/30">

                                        <div class="mb-3 text-cyan-400">

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
                                        class="source-btn rounded-xl border border-white/10 bg-slate-900/60 p-4 text-start transition hover:border-emerald-400/30">

                                        <div class="mb-3 text-emerald-400">

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
                                        class="source-btn rounded-xl border border-white/10 bg-slate-900/60 p-4 text-start transition hover:border-amber-400/30">

                                        <div class="mb-3 text-amber-400">

                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M7.5 3.75h6.878a1.5 1.5 0 011.06.44l3.872 3.87a1.5 1.5 0 011.06 1.06v10.13A1.5 1.5 0 0118.25 20.75H7.5A1.5 1.5 0 016 19.25V5.25a1.5 1.5 0 011.5-1.5z" />
                                            </svg>

                                        </div>

                                        <span class="text-sm font-black">
                                            {{ $isAr ? 'عرض سعر' : 'Quotation' }}
                                        </span>

                                    </button>

                                </div>


                                <input type="hidden" name="entity_type" id="entity_type"
                                    value="{{ old('entity_type') }}">


                                <div class="mt-5">

                                    <label for="entity_id" class="mb-2 block text-sm font-bold text-slate-300">
                                        {{ $isAr ? 'السجل' : 'Record' }}

                                        <span class="text-red-400">*</span>
                                    </label>


                                    <select name="entity_id" id="entity_id" required disabled
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-4 text-sm text-white outline-none transition focus:border-cyan-400/50 disabled:cursor-not-allowed disabled:opacity-50">

                                        <option value="">
                                            {{ $isAr ? 'اختر نوع المصدر أولاً' : 'Select source type first' }}
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </section>


                        {{-- PDF Options --}}
                        <section class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.035]">

                            <div class="border-b border-white/10 px-6 py-5">

                                <h2 class="font-black">
                                    {{ $isAr ? 'خيارات PDF' : 'PDF Options' }}
                                </h2>

                            </div>


                            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                                <div>

                                    <label class="mb-2 block text-sm font-bold text-slate-300">
                                        {{ $isAr ? 'اسم الملف' : 'File Name' }}
                                    </label>

                                    <input type="text" name="file_name" value="{{ old('file_name') }}"
                                        placeholder="HAG-Document-2048"
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3.5 text-sm text-white outline-none focus:border-cyan-400/50">

                                </div>


                                <div>

                                    <label class="mb-2 block text-sm font-bold text-slate-300">
                                        {{ $isAr ? 'اتجاه الصفحة' : 'Page Orientation' }}
                                    </label>

                                    <select name="orientation"
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3.5 text-sm text-white outline-none focus:border-cyan-400/50">

                                        <option value="portrait">
                                            {{ $isAr ? 'عمودي A4' : 'Portrait A4' }}
                                        </option>

                                        <option value="landscape">
                                            {{ $isAr ? 'أفقي A4' : 'Landscape A4' }}
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </section>

                    </div>


                    {{-- SIDEBAR --}}
                    <div class="space-y-6">


                        {{-- Branding --}}
                        <section
                            class="rounded-2xl border border-cyan-400/20 bg-gradient-to-br from-cyan-400/[0.08] to-blue-500/[0.03] p-6">

                            <div class="mb-5 flex items-center gap-3">

                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-400">

                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 3.75l7.5 3v5.25c0 4.65-3.18 8.73-7.5 9.75-4.32-1.02-7.5-5.1-7.5-9.75V6l7.5-2.25z" />
                                    </svg>

                                </div>


                                <div>

                                    <h3 class="font-black">
                                        HAG Brokers
                                    </h3>

                                    <p class="text-xs text-slate-500">
                                        {{ $isAr ? 'هوية الشركة' : 'Company Branding' }}
                                    </p>

                                </div>

                            </div>


                            <div class="space-y-3 text-sm">

                                <div class="flex items-center gap-3 rounded-xl bg-black/10 px-4 py-3">

                                    <span class="text-emerald-400">✓</span>

                                    <span class="text-slate-300">
                                        {{ $isAr ? 'الشعار تلقائيًا' : 'Automatic logo' }}
                                    </span>

                                </div>


                                <div class="flex items-center gap-3 rounded-xl bg-black/10 px-4 py-3">

                                    <span class="text-emerald-400">✓</span>

                                    <span class="text-slate-300">
                                        {{ $isAr ? 'بيانات الشركة' : 'Company information' }}
                                    </span>

                                </div>


                                <div class="flex items-center gap-3 rounded-xl bg-black/10 px-4 py-3">

                                    <span class="text-emerald-400">✓</span>

                                    <span class="text-slate-300">
                                        {{ $isAr ? 'تذييل المستند' : 'Document footer' }}
                                    </span>

                                </div>

                            </div>

                        </section>


                        {{-- Variables --}}
                        <section class="rounded-2xl border border-white/10 bg-white/[0.035] p-6">

                            <h3 class="font-black">
                                {{ $isAr ? 'المتغيرات المتاحة' : 'Available Variables' }}
                            </h3>

                            <p class="mt-1 text-xs text-slate-500">
                                {{ $isAr ? 'يمكن للقالب استخدام هذه البيانات' : 'Templates can use these variables' }}
                            </p>


                            <div class="mt-5 space-y-2">

                                @foreach (['{{ company . name }}', '{{ company . email }}', '{{ company . phone }}', '{{ customer . company_name }}', '{{ shipment . shipment_no }}', '{{ shipment . booking_no }}', '{{ quotation . reference_no }}', '{{ quotation . total_charge }}'] as $variable)
                                    <div
                                        class="rounded-lg border border-white/5 bg-slate-950/60 px-3 py-2 font-mono text-[11px] text-cyan-300">
                                        {{ $variable }}
                                    </div>
                                @endforeach

                            </div>

                        </section>


                        {{-- Generate --}}
                        <section class="rounded-2xl border border-white/10 bg-white/[0.035] p-6">

                            <div class="mb-5">

                                <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                                    HAG PDF ENGINE
                                </div>

                                <div class="mt-2 text-sm font-bold text-slate-300">
                                    {{ $isAr
                                        ? 'سيتم إنشاء المستند باستخدام بيانات السجل والقالب المختار.'
                                        : 'The document will be generated using the selected record and template.' }}
                                </div>

                            </div>


                            <button type="submit" id="generateButton"
                                class="flex w-full items-center justify-center gap-3 rounded-xl bg-cyan-400 px-5 py-4 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/10 transition hover:bg-cyan-300 disabled:cursor-not-allowed disabled:opacity-50">

                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 3.75v11.25m0 0l4.5-4.5M12 15l-4.5-4.5" />

                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 19.5h13.5" />

                                </svg>


                                <span id="generateText">
                                    {{ $isAr ? 'إنشاء PDF' : 'Generate PDF' }}
                                </span>

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

            const entityType = document.getElementById('entity_type');
            const entityId = document.getElementById('entity_id');

            const sourceButtons = document.querySelectorAll('.source-btn');

            const templateSelect = document.getElementById('template_id');

            const form = document.getElementById('pdfForm');
            const generateButton = document.getElementById('generateButton');
            const generateText = document.getElementById('generateText');

            const isAr = @json($isAr);


            function activateSource(type) {

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


                sourceButtons.forEach(button => {

                    const active =
                        button.dataset.entity === type;

                    button.classList.remove(
                        'border-cyan-400/60',
                        'border-emerald-400/60',
                        'border-amber-400/60',
                        'bg-cyan-400/[0.06]',
                        'bg-emerald-400/[0.06]',
                        'bg-amber-400/[0.06]'
                    );


                    if (active) {

                        if (type === 'customer') {
                            button.classList.add(
                                'border-cyan-400/60',
                                'bg-cyan-400/[0.06]'
                            );
                        }

                        if (type === 'shipment') {
                            button.classList.add(
                                'border-emerald-400/60',
                                'bg-emerald-400/[0.06]'
                            );
                        }

                        if (type === 'quotation') {
                            button.classList.add(
                                'border-amber-400/60',
                                'bg-amber-400/[0.06]'
                            );
                        }

                    }

                });

            }


            sourceButtons.forEach(button => {

                button.addEventListener('click', function() {

                    activateSource(
                        this.dataset.entity
                    );

                });

            });


            /*
             * Automatically select template document type.
             */
            templateSelect.addEventListener('change', function() {

                const selected =
                    this.options[this.selectedIndex];

                const type =
                    selected.dataset.type;

                if (!type) return;


                const radio =
                    document.querySelector(
                        '.document-type-radio[value="' + type + '"]'
                    );

                if (radio) {

                    radio.checked = true;

                }

            });


            /*
             * Prevent double submission.
             */
            form.addEventListener('submit', function() {

                generateButton.disabled = true;

                generateText.textContent = isAr ?
                    'جارٍ إنشاء PDF...' :
                    'Generating PDF...';

            });


            /*
             * Restore old source after validation.
             */
            const oldEntityType =
                @json(old('entity_type'));

            const oldEntityId =
                @json(old('entity_id'));


            if (oldEntityType) {

                activateSource(
                    oldEntityType
                );

                if (oldEntityId) {

                    entityId.value =
                        oldEntityId;

                }

            }

        });
    </script>

</x-admin-layout>
