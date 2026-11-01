<x-admin-layout>

    @php
        $isAr = app()->getLocale() === 'ar';
        $versions = $document->versions->sortByDesc('version');

        $documentType = $document->documentable_type
            ? strtolower(class_basename($document->documentable_type))
            : '';

        $entityName = match ($documentType) {
            'customer' => $document->documentable?->company_name,
            'shipment' => $document->documentable?->shipment_no,
            'quotation' => $document->documentable?->reference_no,
            default => null,
        };
    @endphp

    <div class="min-h-screen bg-slate-950 text-white" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <div class="border-b border-white/10 bg-slate-950/80 backdrop-blur-xl">

            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                    <div>

                        <div class="mb-3 flex items-center gap-2 text-sm text-slate-400">

                            <a href="{{ route('admin.documents.index') }}" class="transition hover:text-cyan-400">
                                {{ $isAr ? 'المستندات' : 'Documents' }}
                            </a>

                            <span>/</span>

                            <a href="{{ route('admin.documents.edit', $document) }}"
                                class="transition hover:text-cyan-400">
                                {{ $document->name }}
                            </a>

                            <span>/</span>

                            <span class="text-white">
                                {{ $isAr ? 'الإصدارات' : 'Versions' }}
                            </span>

                        </div>

                        <h1 class="text-2xl font-black tracking-tight sm:text-3xl">
                            {{ $isAr ? 'سجل إصدارات المستند' : 'Document Version History' }}
                        </h1>

                        <p class="mt-2 text-sm text-slate-400">
                            {{ $isAr
                                ? 'تتبع جميع الإصدارات السابقة لهذا المستند.'
                                : 'Track every version and replacement of this document.' }}
                        </p>

                    </div>

                    <div class="flex flex-wrap gap-3">

                        <a href="{{ route('admin.documents.edit', $document) }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/10">

                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>

                            {{ $isAr ? 'رجوع' : 'Back' }}

                        </a>

                        @if (Route::has('admin.documents.download'))
                            <a href="{{ route('admin.documents.download', $document) }}"
                                class="inline-flex items-center gap-2 rounded-xl bg-cyan-400 px-4 py-3 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/10 transition hover:bg-cyan-300">

                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M12 3v12m0 0l4-4m-4 4l-4-4M5 21h14" />
                                </svg>

                                {{ $isAr ? 'تحميل الحالي' : 'Download Current' }}

                            </a>
                        @endif

                    </div>

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- CONTENT --}}
        {{-- ========================================================= --}}

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            {{-- Document Summary --}}

            <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-5 backdrop-blur-xl">

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        {{ $isAr ? 'المستند' : 'Document' }}
                    </p>

                    <p class="mt-2 truncate text-lg font-black text-white">
                        {{ $document->name }}
                    </p>

                </div>


                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-5 backdrop-blur-xl">

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        {{ $isAr ? 'الإصدار الحالي' : 'Current Version' }}
                    </p>

                    <p class="mt-2 text-2xl font-black text-cyan-400">
                        v{{ $document->version }}
                    </p>

                </div>


                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-5 backdrop-blur-xl">

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        {{ $isAr ? 'عدد الإصدارات' : 'Total Versions' }}
                    </p>

                    <p class="mt-2 text-2xl font-black text-white">
                        {{ $versions->count() }}
                    </p>

                </div>


                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-5 backdrop-blur-xl">

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        {{ $isAr ? 'مرتبط بـ' : 'Linked To' }}
                    </p>

                    <p class="mt-2 truncate text-lg font-black text-white">
                        {{ $entityName ?: ucfirst($documentType) }}
                    </p>

                </div>

            </div>


            {{-- Current document --}}

            <div class="mb-8 overflow-hidden rounded-2xl border border-cyan-400/20 bg-cyan-400/[0.03]">

                <div
                    class="flex flex-col gap-4 border-b border-cyan-400/10 p-5 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-center gap-4">

                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-400">

                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M7 3.75h7.5L19 8.25v12A1.75 1.75 0 0117.25 22h-10.5A1.75 1.75 0 015 20.25V5.5A1.75 1.75 0 016.75 3.75H7z" />

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M14 3.75v5h5" />

                            </svg>

                        </div>

                        <div>

                            <div class="flex items-center gap-2">

                                <h2 class="font-black text-white">
                                    {{ $isAr ? 'الإصدار الحالي' : 'Current Version' }}
                                </h2>

                                <span class="rounded-full bg-cyan-400/10 px-2.5 py-1 text-xs font-black text-cyan-300">
                                    v{{ $document->version }}
                                </span>

                            </div>

                            <p class="mt-1 text-sm text-slate-400">
                                {{ $document->original_name ?: $document->name }}
                            </p>

                        </div>

                    </div>

                    <div class="flex gap-2">

                        @if (Route::has('admin.documents.preview'))
                            <a href="{{ route('admin.documents.preview', $document) }}" target="_blank"
                                class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-white/10">
                                {{ $isAr ? 'معاينة' : 'Preview' }}
                            </a>
                        @endif

                        @if (Route::has('admin.documents.download'))
                            <a href="{{ route('admin.documents.download', $document) }}"
                                class="rounded-xl bg-cyan-400 px-4 py-2.5 text-sm font-black text-slate-950 transition hover:bg-cyan-300">
                                {{ $isAr ? 'تحميل' : 'Download' }}
                            </a>
                        @endif

                    </div>

                </div>

            </div>



            {{-- Upload New Version --}}
            <div class="mb-8 rounded-2xl border border-cyan-400/20 bg-white/[0.03] p-6">
                <div class="mb-5">
                    <h2 class="text-lg font-black text-white">
                        {{ $isAr ? 'رفع إصدار جديد' : 'Upload New Version' }}
                    </h2>
                    <p class="mt-2 text-sm text-slate-400">
                        {{ $isAr
                            ? 'سيصبح الملف الجديد هو الإصدار الحالي، مع الاحتفاظ بجميع الإصدارات السابقة.'
                            : 'The new file becomes the current version while all previous versions remain available.' }}
                    </p>
                </div>

                @if (session('success'))
                    <div role="status" class="mb-4 rounded-xl border border-emerald-400/20 bg-emerald-400/10 p-4 text-sm text-emerald-300">
                        {{ session('success') }}
                    </div>
                @endif

                <form
                    action="{{ route('admin.documents.versions.store', $document) }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="flex flex-col gap-4 lg:flex-row lg:items-end"
                >
                    @csrf

                    <div class="min-w-0 flex-1">
                        <label for="version-file" class="mb-2 block text-sm font-bold text-slate-200">
                            {{ $isAr ? 'اختر الملف الجديد' : 'Choose New File' }}
                        </label>

                        <input
                            id="version-file"
                            type="file"
                            name="file"
                            required
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.jpg,.jpeg,.png,.webp"
                            class="block w-full rounded-xl border border-white/10 bg-slate-900 p-3 text-sm text-slate-200 file:me-4 file:rounded-lg file:border-0 file:bg-cyan-400 file:px-4 file:py-2 file:font-bold file:text-slate-950"
                        >

                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            {{ $isAr ? 'الحد الأقصى: 10 ميجابايت' : 'Maximum file size: 10 MB' }}
                        </p>

                        @error('file')
                            <p role="alert" class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="rounded-xl bg-cyan-400 px-6 py-3.5 text-sm font-black text-slate-950 transition hover:bg-cyan-300"
                    >
                        {{ $isAr ? 'رفع الإصدار الجديد' : 'Upload New Version' }}
                    </button>
                </form>
            </div>

            {{-- ===================================================== --}}
            {{-- VERSION TIMELINE --}}
            {{-- ===================================================== --}}

            @if ($versions->count())

                <div class="relative">

                    {{-- Timeline line --}}

                    <div
                        class="absolute top-5 bottom-5 {{ $isAr ? 'right-6' : 'left-6' }} hidden w-px bg-gradient-to-b from-cyan-400/40 via-white/10 to-transparent md:block">
                    </div>


                    <div class="space-y-6">

                        @foreach ($versions as $version)
                            @php
                                $isCurrent = (int) $version->version === (int) $document->version;

                                $disk = Storage::disk($document->disk);

                                $exists = $disk->exists($version->file_path);

                                $size = $version->file_size
                                    ? number_format($version->file_size / 1024 / 1024, 2) . ' MB'
                                    : '-';
                            @endphp

                            <div class="relative md:ps-16">

                                {{-- Timeline node --}}

                                <div
                                    class="absolute top-5 {{ $isAr ? 'right-0' : 'left-0' }} hidden h-12 w-12 items-center justify-center rounded-full border md:flex {{ $isCurrent ? 'border-cyan-400/40 bg-cyan-400/10 text-cyan-300' : 'border-white/10 bg-slate-900 text-slate-400' }}">

                                    <span class="text-xs font-black">
                                        v{{ $version->version }}
                                    </span>

                                </div>


                                {{-- Version Card --}}

                                <div
                                    class="overflow-hidden rounded-2xl border bg-white/[0.03] backdrop-blur-xl {{ $isCurrent ? 'border-cyan-400/20 shadow-xl shadow-cyan-400/5' : 'border-white/10' }}">

                                    <div class="p-5">

                                        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                                            <div class="flex min-w-0 items-start gap-4">

                                                <div
                                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-slate-300 md:hidden">

                                                    <span class="text-xs font-black">
                                                        v{{ $version->version }}
                                                    </span>

                                                </div>

                                                <div class="min-w-0">

                                                    <div class="flex flex-wrap items-center gap-2">

                                                        <h3 class="font-black text-white">
                                                            Version {{ $version->version }}
                                                        </h3>

                                                        @if ($isCurrent)
                                                            <span
                                                                class="rounded-full bg-cyan-400/10 px-2.5 py-1 text-[11px] font-black text-cyan-300">
                                                                {{ $isAr ? 'الحالي' : 'CURRENT' }}
                                                            </span>
                                                        @endif

                                                    </div>

                                                    <p class="mt-1 truncate text-sm text-slate-400">
                                                        {{ $version->original_name ?: $document->name }}
                                                    </p>

                                                </div>

                                            </div>


                                            <div class="grid grid-cols-2 gap-3 text-xs sm:grid-cols-4 lg:min-w-[460px]">

                                                <div>

                                                    <p class="text-slate-500 dark:text-slate-400">
                                                        {{ $isAr ? 'الحجم' : 'Size' }}
                                                    </p>

                                                    <p class="mt-1 font-bold text-slate-200">
                                                        {{ $size }}
                                                    </p>

                                                </div>


                                                <div>

                                                    <p class="text-slate-500 dark:text-slate-400">
                                                        {{ $isAr ? 'النوع' : 'Type' }}
                                                    </p>

                                                    <p class="mt-1 truncate font-bold text-slate-200">
                                                        {{ $version->mime_type ?: '-' }}
                                                    </p>

                                                </div>


                                                <div>

                                                    <p class="text-slate-500 dark:text-slate-400">
                                                        {{ $isAr ? 'رفع بواسطة' : 'Uploaded By' }}
                                                    </p>

                                                    <p class="mt-1 truncate font-bold text-slate-200">
                                                        {{ $version->uploadedBy?->name ?: '-' }}
                                                    </p>

                                                </div>


                                                <div>

                                                    <p class="text-slate-500 dark:text-slate-400">
                                                        {{ $isAr ? 'التاريخ' : 'Date' }}
                                                    </p>

                                                    <p class="mt-1 font-bold text-slate-200">
                                                        {{ $version->created_at?->format('Y-m-d H:i') }}
                                                    </p>

                                                </div>

                                            </div>

                                        </div>

                                    </div>


                                    {{-- Actions --}}

                                    <div class="flex flex-wrap gap-2 border-t border-white/5 bg-black/10 px-5 py-4">

                                        @if ($exists)
                                            @if ($version->mime_type === 'application/pdf' || str_starts_with((string) $version->mime_type, 'image/'))
                                                <a href="{{ route('admin.documents.versions.preview', [$document, $version]) }}"
                                                    target="_blank"
                                                    class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-xs font-bold text-slate-200 transition hover:bg-white/10">
                                                    {{ $isAr ? 'معاينة الإصدار' : 'Preview Version' }}
                                                </a>
                                            @endif

                                            <a href="{{ route('admin.documents.versions.download', [$document, $version]) }}"
                                                class="rounded-lg bg-cyan-400 px-3 py-2 text-xs font-black text-slate-950 transition hover:bg-cyan-300">
                                                {{ $isAr ? 'تحميل الإصدار' : 'Download Version' }}
                                            </a>
                                        @else
                                            <span
                                                class="rounded-lg bg-red-500/10 px-3 py-2 text-xs font-bold text-red-300">
                                                {{ $isAr ? 'الملف غير موجود' : 'File is missing' }}
                                            </span>
                                        @endif

                                    </div>

                                </div>

                            </div>
                        @endforeach

                    </div>

                </div>
            @else
                <div class="rounded-2xl border border-dashed border-white/10 bg-white/[0.02] p-12 text-center">

                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-900 text-slate-500 dark:text-slate-400">

                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M12 6v6l4 2" />

                            <circle cx="12" cy="12" r="9" stroke-width="1.7" />
                        </svg>

                    </div>

                    <h3 class="mt-5 text-lg font-black text-white">
                        {{ $isAr ? 'لا توجد إصدارات' : 'No Versions Found' }}
                    </h3>

                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        {{ $isAr ? 'لم يتم تسجيل أي إصدار لهذا المستند.' : 'No version history has been recorded for this document.' }}
                    </p>

                </div>

            @endif

        </div>

    </div>

</x-admin-layout>
