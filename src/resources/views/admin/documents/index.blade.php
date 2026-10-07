<x-admin-layout>

    @php
        $isAr = app()->getLocale() === 'ar';

        $t = [
            'title' => $isAr ? 'مركز المستندات' : 'Document Center',
            'subtitle' => $isAr
                ? 'إدارة مستندات الشركة والملفات والنسخ والقوالب من مكان واحد.'
                : 'Manage company documents, files, versions and templates from one place.',

            'total' => $isAr ? 'إجمالي المستندات' : 'Total Documents',
            'active' => $isAr ? 'المستندات النشطة' : 'Active Documents',
            'pdf' => $isAr ? 'ملفات PDF' : 'PDF Documents',
            'images' => $isAr ? 'الصور' : 'Images',
            'recent' => $isAr ? 'آخر 30 يوماً' : 'Last 30 Days',
            'templates' => $isAr ? 'القوالب النشطة' : 'Active Templates',

            'search' => $isAr ? 'البحث في المستندات...' : 'Search documents...',
            'all_types' => $isAr ? 'كل الأنواع' : 'All Types',
            'all_statuses' => $isAr ? 'كل الحالات' : 'All Statuses',
            'all_files' => $isAr ? 'كل الملفات' : 'All Files',
            'pdf_files' => $isAr ? 'PDF' : 'PDF',
            'images_files' => $isAr ? 'صور' : 'Images',
            'office_files' => $isAr ? 'Office / Excel / CSV' : 'Office / Excel / CSV',
            'filter' => $isAr ? 'تصفية' : 'Filter',
            'reset' => $isAr ? 'إعادة ضبط' : 'Reset',

            'upload' => $isAr ? 'رفع مستند' : 'Upload Document',
            'generate_pdf' => $isAr ? 'إنشاء PDF' : 'Generate PDF',
            'templates_btn' => $isAr ? 'القوالب' : 'Templates',

            'document' => $isAr ? 'المستند' : 'Document',
            'type' => $isAr ? 'النوع' : 'Type',
            'linked_to' => $isAr ? 'مرتبط بـ' : 'Linked To',
            'version' => $isAr ? 'الإصدار' : 'Version',
            'size' => $isAr ? 'الحجم' : 'Size',
            'uploaded_by' => $isAr ? 'بواسطة' : 'Uploaded By',
            'date' => $isAr ? 'التاريخ' : 'Date',
            'status' => $isAr ? 'الحالة' : 'Status',
            'actions' => $isAr ? 'الإجراءات' : 'Actions',

            'preview' => $isAr ? 'معاينة' : 'Preview',
            'download' => $isAr ? 'تحميل' : 'Download',
            'edit' => $isAr ? 'تعديل' : 'Edit',
            'versions' => $isAr ? 'الإصدارات' : 'Versions',
            'delete' => $isAr ? 'حذف' : 'Delete',
            'print' => $isAr ? 'طباعة' : 'Print',

            'customer' => $isAr ? 'عميل' : 'Customer',
            'shipment' => $isAr ? 'شحنة' : 'Shipment',
            'quotation' => $isAr ? 'عرض سعر' : 'Quotation',
            'system' => $isAr ? 'النظام' : 'System',
            'no_documents' => $isAr ? 'لا توجد مستندات' : 'No documents found',
            'no_documents_hint' => $isAr
                ? 'ابدأ برفع أول مستند إلى النظام.'
                : 'Start by uploading the first document to the system.',

            'confirm_delete' => $isAr
                ? 'هل أنت متأكد من حذف هذا المستند؟ سيتم حذف جميع نسخه أيضاً.'
                : 'Are you sure you want to delete this document? All versions will also be deleted.',

            'current' => $isAr ? 'الحالي' : 'Current',
            'active_status' => $isAr ? 'نشط' : 'Active',
            'archived_status' => $isAr ? 'مؤرشف' : 'Archived',
            'draft_status' => $isAr ? 'مسودة' : 'Draft',
        ];

        $filters = $filters ?? [
            'search' => request('search', ''),
            'type' => request('type', ''),
            'status' => request('status', ''),
            'mime' => request('mime', ''),
        ];

        $stats = $stats ?? [
            'total' => $documents->total(),
            'active' => 0,
            'pdf' => 0,
            'images' => 0,
            'recent' => 0,
            'templates' => 0,
        ];
    @endphp

    <div class="min-h-screen bg-[#06101d] text-white">

        {{-- ============================================================
             HEADER
        ============================================================= --}}
        <div class="relative overflow-hidden border-b border-white/5">

            <div class="absolute -left-24 -top-24 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl"></div>
            <div class="absolute -right-24 top-0 h-72 w-72 rounded-full bg-blue-500/10 blur-3xl"></div>

            <div class="relative px-4 py-6 sm:px-6 lg:px-8">

                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                    <div class="min-w-0">

                        <div class="mb-2 flex items-center gap-3">

                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-cyan-400/10 ring-1 ring-cyan-400/20">
                                <svg class="h-6 w-6 text-cyan-300" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 3.75h7.5L19 8.25v11A1.75 1.75 0 0 1 17.25 21h-10.5A1.75 1.75 0 0 1 5 19.25V5.5A1.75 1.75 0 0 1 6.75 3.75H7Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 3.75v4.5h4.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.5 12h7M8.5 15.25h7M8.5 18.5h4" />
                                </svg>
                            </div>

                            <div>
                                <h1 class="text-2xl font-black tracking-tight sm:text-3xl">
                                    {{ $t['title'] }}
                                </h1>

                                <p class="mt-1 max-w-2xl text-sm text-slate-400">
                                    {{ $t['subtitle'] }}
                                </p>
                            </div>

                        </div>

                    </div>

                    {{-- Header actions --}}
                    <div class="flex flex-wrap items-center gap-2">

                        <a href="{{ route('admin.documents.pdf.create') }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-bold text-slate-200 transition hover:border-cyan-400/30 hover:bg-cyan-400/10 hover:text-cyan-300">

                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3.75h7.5L18.75 8v12.25H6.75V3.75Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 3.75V8h4.25" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 15h4" />
                            </svg>

                            {{ $t['generate_pdf'] }}
                        </a>

                        <a href="{{ route('admin.documents.create') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-cyan-400 px-4 py-2.5 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/10 transition hover:bg-cyan-300">

                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                            </svg>

                            {{ $t['upload'] }}
                        </a>

                    </div>

                </div>

            </div>
        </div>


        {{-- ============================================================
             STATISTICS
        ============================================================= --}}
        <div class="px-4 py-5 sm:px-6 lg:px-8">

            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">

                {{-- Total --}}
                <div
                    class="group relative overflow-hidden rounded-2xl border border-white/5 bg-white/[0.035] p-4 transition hover:border-cyan-400/20 hover:bg-white/[0.05]">

                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                {{ $t['total'] }}
                            </p>

                            <p class="mt-2 text-2xl font-black text-white">
                                {{ number_format($stats['total']) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-cyan-400/10 p-2 text-cyan-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7 3.75h7.5L19 8.25v11A1.75 1.75 0 0 1 17.25 21h-10.5A1.75 1.75 0 0 1 5 19.25V5.5A1.75 1.75 0 0 1 6.75 3.75H7Z" />
                            </svg>
                        </div>
                    </div>

                </div>


                {{-- Active --}}
                <div
                    class="group rounded-2xl border border-white/5 bg-white/[0.035] p-4 transition hover:border-emerald-400/20 hover:bg-white/[0.05]">

                    <div class="flex items-start justify-between">

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                {{ $t['active'] }}
                            </p>

                            <p class="mt-2 text-2xl font-black text-white">
                                {{ number_format($stats['active']) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-emerald-400/10 p-2 text-emerald-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                            </svg>
                        </div>

                    </div>

                </div>


                {{-- PDF --}}
                <div
                    class="group rounded-2xl border border-white/5 bg-white/[0.035] p-4 transition hover:border-red-400/20 hover:bg-white/[0.05]">

                    <div class="flex items-start justify-between">

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                {{ $t['pdf'] }}
                            </p>

                            <p class="mt-2 text-2xl font-black text-white">
                                {{ number_format($stats['pdf']) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-red-400/10 p-2 text-red-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7 3.75h7.5L19 8.25v11A1.75 1.75 0 0 1 17.25 21h-10.5A1.75 1.75 0 0 1 5 19.25V5.5A1.75 1.75 0 0 1 6.75 3.75H7Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6M9 16h4" />
                            </svg>
                        </div>

                    </div>

                </div>


                {{-- Images --}}
                <div
                    class="group rounded-2xl border border-white/5 bg-white/[0.035] p-4 transition hover:border-violet-400/20 hover:bg-white/[0.05]">

                    <div class="flex items-start justify-between">

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                {{ $t['images'] }}
                            </p>

                            <p class="mt-2 text-2xl font-black text-white">
                                {{ number_format($stats['images']) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-violet-400/10 p-2 text-violet-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <rect x="4" y="4" width="16" height="16" rx="2" />
                                <circle cx="9" cy="9" r="1.5" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20 15-4-4-5 5-2-2-5 5" />
                            </svg>
                        </div>

                    </div>

                </div>


                {{-- Recent --}}
                <div
                    class="group rounded-2xl border border-white/5 bg-white/[0.035] p-4 transition hover:border-blue-400/20 hover:bg-white/[0.05]">

                    <div class="flex items-start justify-between">

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                {{ $t['recent'] }}
                            </p>

                            <p class="mt-2 text-2xl font-black text-white">
                                {{ number_format($stats['recent']) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-blue-400/10 p-2 text-blue-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <circle cx="12" cy="12" r="8" />
                                <path stroke-linecap="round" d="M12 8v4l2.5 2" />
                            </svg>
                        </div>

                    </div>

                </div>


                {{-- Templates --}}
                <div
                    class="group rounded-2xl border border-white/5 bg-white/[0.035] p-4 transition hover:border-amber-400/20 hover:bg-white/[0.05]">

                    <div class="flex items-start justify-between">

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                {{ $t['templates'] }}
                            </p>

                            <p class="mt-2 text-2xl font-black text-white">
                                {{ number_format($stats['templates']) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-amber-400/10 p-2 text-amber-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 5.5A1.5 1.5 0 0 1 5.5 4H18a2 2 0 0 1 2 2v12.5A1.5 1.5 0 0 1 18.5 20H5.5A1.5 1.5 0 0 1 4 18.5v-13Z" />
                                <path stroke-linecap="round" d="M8 8h8M8 12h8M8 16h5" />
                            </svg>
                        </div>

                    </div>

                </div>

            </div>
        </div>


        {{-- ============================================================
             SEARCH + FILTERS
        ============================================================= --}}
        <div class="px-4 pb-5 sm:px-6 lg:px-8">

            <form method="GET" action="{{ route('admin.documents.index') }}"
                class="rounded-2xl border border-white/5 bg-white/[0.035] p-4 shadow-2xl shadow-black/10">

                <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">

                    {{-- Search --}}
                    <div class="lg:col-span-5">

                        <label class="sr-only">
                            {{ $t['search'] }}
                        </label>

                        <div class="relative">

                            <svg class="pointer-events-none absolute start-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <circle cx="11" cy="11" r="6.5" />
                                <path stroke-linecap="round" d="m16 16 4 4" />
                            </svg>

                            <input type="search" name="search" value="{{ $filters['search'] }}"
                                placeholder="{{ $t['search'] }}"
                                class="w-full rounded-xl border border-white/10 bg-slate-950/60 py-3 ps-11 pe-4 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/40 focus:ring-2 focus:ring-cyan-400/10">

                        </div>

                    </div>


                    {{-- Type --}}
                    <div class="lg:col-span-2">

                        <select name="type"
                            class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-slate-300 outline-none focus:border-cyan-400/40 focus:ring-2 focus:ring-cyan-400/10">
                            <option value="">
                                {{ $t['all_types'] }}
                            </option>

                            @foreach ($types as $documentType)
                                <option value="{{ $documentType }}" @selected($filters['type'] === $documentType)>
                                    {{ $documentType }}
                                </option>
                            @endforeach
                        </select>

                    </div>


                    {{-- Status --}}
                    <div class="lg:col-span-2">

                        <select name="status"
                            class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-slate-300 outline-none focus:border-cyan-400/40 focus:ring-2 focus:ring-cyan-400/10">
                            <option value="">
                                {{ $t['all_statuses'] }}
                            </option>

                            <option value="active" @selected($filters['status'] === 'active')}>
                                {{ $t['active_status'] }}
                            </option>

                            <option value="draft" @selected($filters['status'] === 'draft')}>
                                {{ $t['draft_status'] }}
                            </option>

                            <option value="archived" @selected($filters['status'] === 'archived')}>
                                {{ $t['archived_status'] }}
                            </option>

                        </select>

                    </div>


                    {{-- File type --}}
                    <div class="lg:col-span-2">

                        <select name="mime"
                            class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-slate-300 outline-none focus:border-cyan-400/40 focus:ring-2 focus:ring-cyan-400/10">

                            <option value="">
                                {{ $t['all_files'] }}
                            </option>

                            <option value="pdf" @selected($filters['mime'] === 'pdf')}>
                                {{ $t['pdf_files'] }}
                            </option>

                            <option value="image" @selected($filters['mime'] === 'image')}>
                                {{ $t['images_files'] }}
                            </option>

                            <option value="office" @selected($filters['mime'] === 'office')}>
                                {{ $t['office_files'] }}
                            </option>

                        </select>

                    </div>


                    {{-- Buttons --}}
                    <div class="flex gap-2 lg:col-span-1">

                        <button type="submit" title="{{ $t['filter'] }}"
                            class="flex min-w-0 flex-1 items-center justify-center rounded-xl bg-cyan-400 px-3 py-3 text-sm font-black text-slate-950 transition hover:bg-cyan-300">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16M7 12h10M10 19h4" />
                            </svg>
                        </button>

                        <a href="{{ route('admin.documents.index') }}" title="{{ $t['reset'] }}"
                            class="flex items-center justify-center rounded-xl border border-white/10 bg-white/5 px-3 text-slate-400 transition hover:bg-white/10 hover:text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                            </svg>
                        </a>

                    </div>

                </div>

            </form>

        </div>


        {{-- ============================================================
             FLASH MESSAGE
        ============================================================= --}}
        @if (session('success'))
            <div class="px-4 pb-5 sm:px-6 lg:px-8">

                <div
                    class="flex items-center gap-3 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-300">

                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                    </svg>

                    <span>{{ session('success') }}</span>

                </div>

            </div>
        @endif


        {{-- ============================================================
             VALIDATION ERRORS
        ============================================================= --}}
        @if ($errors->any())

            <div class="px-4 pb-5 sm:px-6 lg:px-8">

                <div class="rounded-2xl border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-300">

                    <div class="mb-2 font-black">
                        {{ $isAr ? 'حدث خطأ:' : 'Something went wrong:' }}
                    </div>

                    <ul class="list-disc space-y-1 ps-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                </div>

            </div>

        @endif


        {{-- ============================================================
             DOCUMENTS TABLE
        ============================================================= --}}
        <div class="px-4 pb-8 sm:px-6 lg:px-8">

            <div class="overflow-hidden rounded-2xl border border-white/5 bg-white/[0.025]">

                {{-- Desktop table --}}
                <div class="hidden overflow-x-auto lg:block">

                    <table class="min-w-full divide-y divide-white/5">

                        <thead class="bg-slate-950/50">

                            <tr>

                                <th
                                    class="px-5 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-500">
                                    {{ $t['document'] }}
                                </th>

                                <th
                                    class="px-5 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-500">
                                    {{ $t['type'] }}
                                </th>

                                <th
                                    class="px-5 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-500">
                                    {{ $t['linked_to'] }}
                                </th>

                                <th
                                    class="px-5 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-500">
                                    {{ $t['version'] }}
                                </th>

                                <th
                                    class="px-5 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-500">
                                    {{ $t['size'] }}
                                </th>

                                <th
                                    class="px-5 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-500">
                                    {{ $t['uploaded_by'] }}
                                </th>

                                <th
                                    class="px-5 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-500">
                                    {{ $t['status'] }}
                                </th>

                                <th
                                    class="px-5 py-4 text-end text-xs font-black uppercase tracking-wider text-slate-500">
                                    {{ $t['actions'] }}
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-white/5">

                            @forelse($documents as $document)

                                @php
                                    $owner = $document->documentable;

                                    $ownerType = match (true) {
                                        $owner instanceof \App\Models\Customer => $t['customer'],
                                        $owner instanceof \App\Models\Shipment => $t['shipment'],
                                        $owner instanceof \App\Models\Quotation => $t['quotation'],
                                        default => $t['system'],
                                    };

                                    $ownerName = match (true) {
                                        $owner instanceof \App\Models\Customer => $owner->company_name ??
                                            '#' . $owner->id,

                                        $owner instanceof \App\Models\Shipment => $owner->shipment_no ??
                                            '#' . $owner->id,

                                        $owner instanceof \App\Models\Quotation => $owner->reference_no ??
                                            '#' . $owner->id,

                                        default => '-',
                                    };

                                    $mimeType = $document->mime_type ?? '';

                                    $iconClass = str_contains($mimeType, 'pdf')
                                        ? 'bg-red-400/10 text-red-300'
                                        : (str_starts_with($mimeType, 'image/')
                                            ? 'bg-violet-400/10 text-violet-300'
                                            : 'bg-cyan-400/10 text-cyan-300');
                                @endphp

                                <tr class="group transition hover:bg-white/[0.025]">

                                    {{-- Document --}}
                                    <td class="whitespace-nowrap px-5 py-4">

                                        <div class="flex items-center gap-3">

                                            <div
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $iconClass }}">

                                                @if (str_contains($mimeType, 'pdf'))
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor" stroke-width="1.7">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M7 3.75h7.5L19 8.25v11A1.75 1.75 0 0 1 17.25 21h-10.5A1.75 1.75 0 0 1 5 19.25V5.5A1.75 1.75 0 0 1 6.75 3.75H7Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M14 3.75v4.5h4.5" />
                                                    </svg>
                                                @elseif(str_starts_with($mimeType, 'image/'))
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor" stroke-width="1.7">
                                                        <rect x="4" y="4" width="16" height="16"
                                                            rx="2" />
                                                        <circle cx="9" cy="9" r="1.5" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m20 15-4-4-5 5-2-2-5 5" />
                                                    </svg>
                                                @else
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor" stroke-width="1.7">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M7 3.75h7.5L19 8.25v11A1.75 1.75 0 0 1 17.25 21h-10.5A1.75 1.75 0 0 1 5 19.25V5.5A1.75 1.75 0 0 1 6.75 3.75H7Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M14 3.75v4.5h4.5" />
                                                    </svg>
                                                @endif

                                            </div>


                                            <div class="min-w-0">

                                                <div class="max-w-xs truncate text-sm font-bold text-white">
                                                    {{ $document->name }}
                                                </div>

                                                <div class="mt-0.5 max-w-xs truncate text-xs text-slate-500">
                                                    {{ $document->original_name ?: '-' }}
                                                </div>

                                            </div>

                                        </div>

                                    </td>


                                    {{-- Type --}}
                                    <td class="whitespace-nowrap px-5 py-4">

                                        <span
                                            class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-xs font-bold text-slate-300">
                                            {{ $document->type }}
                                        </span>

                                    </td>


                                    {{-- Linked --}}
                                    <td class="whitespace-nowrap px-5 py-4">

                                        <div class="text-xs font-bold text-slate-400">
                                            {{ $ownerType }}
                                        </div>

                                        <div class="mt-1 max-w-[180px] truncate text-sm text-white">
                                            {{ $ownerName }}
                                        </div>

                                    </td>


                                    {{-- Version --}}
                                    <td class="whitespace-nowrap px-5 py-4">

                                        <div class="flex items-center gap-2">

                                            <span
                                                class="rounded-lg bg-cyan-400/10 px-2 py-1 text-xs font-black text-cyan-300">
                                                v{{ $document->version }}
                                            </span>

                                            @if ($document->versions->count() > 1)
                                                <span class="text-[11px] text-slate-500">
                                                    {{ $document->versions->count() }}
                                                </span>
                                            @endif

                                        </div>

                                    </td>


                                    {{-- Size --}}
                                    <td class="whitespace-nowrap px-5 py-4">

                                        <span class="text-sm text-slate-400">
                                            {{ $document->formatted_file_size }}
                                        </span>

                                    </td>


                                    {{-- Uploaded by --}}
                                    <td class="whitespace-nowrap px-5 py-4">

                                        <div class="text-sm text-slate-300">
                                            {{ $document->uploadedBy?->name ?? '-' }}
                                        </div>

                                        <div class="mt-1 text-xs text-slate-600">
                                            {{ $document->created_at?->format('Y-m-d H:i') }}
                                        </div>

                                    </td>


                                    {{-- Status --}}
                                    <td class="whitespace-nowrap px-5 py-4">

                                        @php
                                            $statusClasses = match ($document->status) {
                                                'active' => 'bg-emerald-400/10 text-emerald-300 border-emerald-400/20',
                                                'archived' => 'bg-slate-400/10 text-slate-400 border-slate-400/20',
                                                'draft' => 'bg-amber-400/10 text-amber-300 border-amber-400/20',
                                                default => 'bg-white/5 text-slate-400 border-white/10',
                                            };
                                        @endphp

                                        <span
                                            class="rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusClasses }}">
                                            {{ ucfirst($document->status ?: 'active') }}
                                        </span>

                                    </td>


                                    {{-- Actions --}}
                                    <td class="whitespace-nowrap px-5 py-4">

                                        <div class="flex items-center justify-end gap-1">

                                            {{-- Preview --}}
                                            <a href="{{ route('admin.documents.preview', $document) }}"
                                                target="_blank" title="{{ $t['preview'] }}"
                                                class="rounded-lg p-2 text-slate-400 transition hover:bg-cyan-400/10 hover:text-cyan-300">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.75 12s3.25-6 9.25-6 9.25 6 9.25 6-3.25 6-9.25 6-9.25-6-9.25-6Z" />
                                                    <circle cx="12" cy="12" r="2.5" />
                                                </svg>
                                            </a>


                                            {{-- Print --}}
                                            <a href="{{ route('admin.documents.preview', $document) }}"
                                                target="_blank" title="{{ $t['print'] }}"
                                                onclick="setTimeout(() => {}, 100)"
                                                class="rounded-lg p-2 text-slate-400 transition hover:bg-blue-400/10 hover:text-blue-300">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6.5 8V4.75h11V8M6 18H4.75A1.75 1.75 0 0 1 3 16.25v-4.5A1.75 1.75 0 0 1 4.75 10h14.5A1.75 1.75 0 0 1 21 11.75v4.5A1.75 1.75 0 0 1 19.25 18H18" />
                                                    <path stroke-linecap="round" d="M6.5 14h11v6h-11z" />
                                                </svg>
                                            </a>


                                            {{-- Download --}}
                                            <a href="{{ route('admin.documents.download', $document) }}"
                                                title="{{ $t['download'] }}"
                                                class="rounded-lg p-2 text-slate-400 transition hover:bg-emerald-400/10 hover:text-emerald-300">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 4v11m0 0 4-4m-4 4-4-4" />
                                                    <path stroke-linecap="round" d="M5 20h14" />
                                                </svg>
                                            </a>


                                            {{-- Edit --}}
                                            <a href="{{ route('admin.documents.edit', $document) }}"
                                                title="{{ $t['edit'] }}"
                                                class="rounded-lg p-2 text-slate-400 transition hover:bg-amber-400/10 hover:text-amber-300">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m14.5 5.5 4 4M5 19l3.5-.75L18.5 8.25a2.12 2.12 0 0 0-3-3L5.5 15.25 5 19Z" />
                                                </svg>
                                            </a>


                                            {{-- Versions --}}
                                            @if ($document->versions->count() > 0)
                                                <a href="{{ route('admin.documents.edit', $document) }}#versions"
                                                    title="{{ $t['versions'] }}"
                                                    class="relative rounded-lg p-2 text-slate-400 transition hover:bg-violet-400/10 hover:text-violet-300">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M7 4h10M7 8h10M7 12h6" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M5 3.75h14v16.5H5z" />
                                                    </svg>

                                                    @if ($document->versions->count() > 1)
                                                        <span
                                                            class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-violet-400 px-1 text-[9px] font-black text-slate-950">
                                                            {{ $document->versions->count() }}
                                                        </span>
                                                    @endif

                                                </a>
                                            @endif


                                            {{-- Delete --}}
                                            <form method="POST"
                                                action="{{ route('admin.documents.destroy', $document) }}"
                                                onsubmit="return confirm(@js($t['confirm_delete']))">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" title="{{ $t['delete'] }}"
                                                    class="rounded-lg p-2 text-slate-400 transition hover:bg-red-400/10 hover:text-red-300">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M5 7h14M9 7V4.5h6V7m-8 0 .75 13h8.5L17 7M10 11v5M14 11v5" />
                                                    </svg>
                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="8">

                                        <div class="flex flex-col items-center justify-center px-6 py-20 text-center">

                                            <div
                                                class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-white/5 text-slate-600">

                                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M7 3.75h7.5L19 8.25v11A1.75 1.75 0 0 1 17.25 21h-10.5A1.75 1.75 0 0 1 5 19.25V5.5A1.75 1.75 0 0 1 6.75 3.75H7Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M14 3.75v4.5h4.5" />
                                                </svg>

                                            </div>

                                            <h3 class="text-lg font-black text-white">
                                                {{ $t['no_documents'] }}
                                            </h3>

                                            <p class="mt-2 max-w-md text-sm text-slate-500">
                                                {{ $t['no_documents_hint'] }}
                                            </p>

                                            <a href="{{ route('admin.documents.create') }}"
                                                class="mt-5 inline-flex items-center gap-2 rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-black text-slate-950 transition hover:bg-cyan-300">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                                                </svg>

                                                {{ $t['upload'] }}
                                            </a>

                                        </div>

                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- ========================================================
                     MOBILE / TABLET CARDS
                ========================================================= --}}
                <div class="divide-y divide-white/5 lg:hidden">

                    @forelse($documents as $document)
                        @php
                            $owner = $document->documentable;

                            $ownerType = match (true) {
                                $owner instanceof \App\Models\Customer => $t['customer'],
                                $owner instanceof \App\Models\Shipment => $t['shipment'],
                                $owner instanceof \App\Models\Quotation => $t['quotation'],
                                default => $t['system'],
                            };

                            $ownerName = match (true) {
                                $owner instanceof \App\Models\Customer => $owner->company_name ?? '#' . $owner->id,

                                $owner instanceof \App\Models\Shipment => $owner->shipment_no ?? '#' . $owner->id,

                                $owner instanceof \App\Models\Quotation => $owner->reference_no ?? '#' . $owner->id,

                                default => '-',
                            };

                            $mimeType = $document->mime_type ?? '';

                            $iconClass = str_contains($mimeType, 'pdf')
                                ? 'bg-red-400/10 text-red-300'
                                : (str_starts_with($mimeType, 'image/')
                                    ? 'bg-violet-400/10 text-violet-300'
                                    : 'bg-cyan-400/10 text-cyan-300');
                        @endphp

                        <div class="p-4">

                            <div class="flex items-start gap-3">

                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $iconClass }}">

                                    @if (str_contains($mimeType, 'pdf'))
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="1.7">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M7 3.75h7.5L19 8.25v11A1.75 1.75 0 0 1 17.25 21h-10.5A1.75 1.75 0 0 1 5 19.25V5.5A1.75 1.75 0 0 1 6.75 3.75H7Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M14 3.75v4.5h4.5" />
                                        </svg>
                                    @elseif(str_starts_with($mimeType, 'image/'))
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="1.7">
                                            <rect x="4" y="4" width="16" height="16" rx="2" />
                                            <circle cx="9" cy="9" r="1.5" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m20 15-4-4-5 5-2-2-5 5" />
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="1.7">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M7 3.75h7.5L19 8.25v11A1.75 1.75 0 0 1 17.25 21h-10.5A1.75 1.75 0 0 1 5 19.25V5.5A1.75 1.75 0 0 1 6.75 3.75H7Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M14 3.75v4.5h4.5" />
                                        </svg>
                                    @endif

                                </div>


                                <div class="min-w-0 flex-1">

                                    <div class="flex items-start justify-between gap-3">

                                        <div class="min-w-0">

                                            <h3 class="truncate text-sm font-black text-white">
                                                {{ $document->name }}
                                            </h3>

                                            <p class="mt-1 truncate text-xs text-slate-500">
                                                {{ $document->original_name ?: '-' }}
                                            </p>

                                        </div>

                                        <span
                                            class="shrink-0 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-2 py-1 text-[10px] font-bold text-emerald-300">
                                            {{ ucfirst($document->status ?: 'active') }}
                                        </span>

                                    </div>


                                    <div class="mt-4 grid grid-cols-2 gap-3">

                                        <div>
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                                {{ $t['type'] }}
                                            </p>

                                            <p class="mt-1 truncate text-xs font-bold text-slate-300">
                                                {{ $document->type }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                                {{ $t['linked_to'] }}
                                            </p>

                                            <p class="mt-1 truncate text-xs font-bold text-slate-300">
                                                {{ $ownerType }} · {{ $ownerName }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                                {{ $t['version'] }}
                                            </p>

                                            <p class="mt-1 text-xs font-bold text-cyan-300">
                                                v{{ $document->version }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                                {{ $t['size'] }}
                                            </p>

                                            <p class="mt-1 text-xs font-bold text-slate-300">
                                                {{ $document->formatted_file_size }}
                                            </p>
                                        </div>

                                    </div>


                                    {{-- Actions --}}
                                    <div class="mt-4 flex flex-wrap gap-2">

                                        <a href="{{ route('admin.documents.preview', $document) }}" target="_blank"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-xs font-bold text-slate-300 hover:bg-white/10">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.75 12s3.25-6 9.25-6 9.25 6 9.25 6-3.25 6-9.25 6-9.25-6-9.25-6Z" />
                                                <circle cx="12" cy="12" r="2.5" />
                                            </svg>
                                            {{ $t['preview'] }}
                                        </a>


                                        <a href="{{ route('admin.documents.download', $document) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-400/20 bg-emerald-400/10 px-3 py-2 text-xs font-bold text-emerald-300 hover:bg-emerald-400/15">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" d="M12 4v11m0 0 4-4m-4 4-4-4" />
                                                <path stroke-linecap="round" d="M5 20h14" />
                                            </svg>
                                            {{ $t['download'] }}
                                        </a>


                                        <a href="{{ route('admin.documents.edit', $document) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-amber-400/20 bg-amber-400/10 px-3 py-2 text-xs font-bold text-amber-300 hover:bg-amber-400/15">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m14.5 5.5 4 4M5 19l3.5-.75L18.5 8.25a2.12 2.12 0 0 0-3-3L5.5 15.25 5 19Z" />
                                            </svg>
                                            {{ $t['edit'] }}
                                        </a>


                                        <a href="{{ route('admin.documents.edit', $document) }}#versions"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-violet-400/20 bg-violet-400/10 px-3 py-2 text-xs font-bold text-violet-300 hover:bg-violet-400/15">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" d="M7 4h10M7 8h10M7 12h6" />
                                                <path stroke-linecap="round" d="M5 3.75h14v16.5H5z" />
                                            </svg>
                                            {{ $t['versions'] }}
                                        </a>


                                        <form method="POST"
                                            action="{{ route('admin.documents.destroy', $document) }}"
                                            onsubmit="return confirm(@js($t['confirm_delete']))">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-red-400/20 bg-red-400/10 px-3 py-2 text-xs font-bold text-red-300 hover:bg-red-400/15">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M5 7h14M9 7V4.5h6V7m-8 0 .75 13h8.5L17 7M10 11v5M14 11v5" />
                                                </svg>

                                                {{ $t['delete'] }}
                                            </button>
                                        </form>

                                    </div>

                                </div>

                            </div>

                        </div>

                    @empty

                        <div class="flex flex-col items-center justify-center px-6 py-16 text-center">

                            <div
                                class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-white/5 text-slate-600">

                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 3.75h7.5L19 8.25v11A1.75 1.75 0 0 1 17.25 21h-10.5A1.75 1.75 0 0 1 5 19.25V5.5A1.75 1.75 0 0 1 6.75 3.75H7Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 3.75v4.5h4.5" />
                                </svg>

                            </div>

                            <h3 class="text-lg font-black text-white">
                                {{ $t['no_documents'] }}
                            </h3>

                            <p class="mt-2 text-sm text-slate-500">
                                {{ $t['no_documents_hint'] }}
                            </p>

                            <a href="{{ route('admin.documents.create') }}"
                                class="mt-5 rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-black text-slate-950 hover:bg-cyan-300">
                                {{ $t['upload'] }}
                            </a>

                        </div>
                    @endforelse

                </div>

            </div>


            {{-- ========================================================
                 PAGINATION
            ========================================================= --}}
            @if ($documents->hasPages())
                <div class="mt-5 rounded-2xl border border-white/5 bg-white/[0.025] px-4 py-3">

                    {{ $documents->links() }}

                </div>
            @endif

        </div>

    </div>

</x-admin-layout>
