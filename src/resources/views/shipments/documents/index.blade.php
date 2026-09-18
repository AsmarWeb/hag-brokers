<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-center gap-3">
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-400 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/20">
                    HAG
                </div>

                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Shipment Documents
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Manage documents related to your shipment.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('shipments.show', $shipment) }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-cyan-300 hover:text-cyan-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-cyan-500 dark:hover:text-cyan-400">
                    ← Shipment Details
                </a>

                <a href="{{ route('shipments.index') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-cyan-500 px-4 py-2.5 text-sm font-extrabold text-slate-950 shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-400">
                    All Shipments
                </a>
            </div>

        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
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
                        Please correct the following errors:
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

                        <div>
                            <div
                                class="mb-3 inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-cyan-300">
                                <span class="h-2 w-2 rounded-full bg-cyan-400"></span>
                                HAG Brokers
                            </div>

                            @php
                                $reference =
                                    $shipment->reference_no ?? ($shipment->shipment_no ?? 'SHIP-' . $shipment->id);
                            @endphp

                            <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                                {{ $reference }}
                            </h1>

                            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-400 sm:text-base">
                                Documents and files associated with this shipment.
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

                        <div class="rounded-2xl border border-slate-800 bg-white/5 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Origin
                            </p>

                            <p class="mt-1 font-bold text-white">
                                {{ $shipment->originPort->name ?? 'Not assigned' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-800 bg-white/5 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Destination
                            </p>

                            <p class="mt-1 font-bold text-white">
                                {{ $shipment->destinationPort->name ?? 'Not assigned' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-800 bg-white/5 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Status
                            </p>

                            @php
                                $status = $shipment->status ?? 'pending';

                                $statusClasses = match ($status) {
                                    'delivered' => 'bg-emerald-400/10 text-emerald-300 border-emerald-400/20',
                                    'in_transit' => 'bg-cyan-400/10 text-cyan-300 border-cyan-400/20',
                                    default => 'bg-amber-400/10 text-amber-300 border-amber-400/20',
                                };
                            @endphp

                            <span
                                class="mt-1 inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusClasses }}">
                                {{ str_replace('_', ' ', ucfirst($status)) }}
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

                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
                                Upload Document
                            </h2>

                            <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                Add a PDF or image to this shipment.
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('shipments.documents.store', $shipment) }}"
                        enctype="multipart/form-data" class="mt-6 space-y-5">
                        @csrf

                        {{-- Type --}}
                        <div>
                            <label for="type" class="block text-sm font-bold text-slate-700 dark:text-slate-300">
                                Document Type
                            </label>

                            <select id="type" name="type" required
                                class="mt-2 block w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                <option value="">Select document type</option>

                                <option value="Bill of Lading"
                                    {{ old('type') === 'Bill of Lading' ? 'selected' : '' }}>
                                    Bill of Lading
                                </option>

                                <option value="Commercial Invoice"
                                    {{ old('type') === 'Commercial Invoice' ? 'selected' : '' }}>
                                    Commercial Invoice
                                </option>

                                <option value="Packing List" {{ old('type') === 'Packing List' ? 'selected' : '' }}>
                                    Packing List
                                </option>

                                <option value="Certificate of Origin"
                                    {{ old('type') === 'Certificate of Origin' ? 'selected' : '' }}>
                                    Certificate of Origin
                                </option>

                                <option value="Customs Document"
                                    {{ old('type') === 'Customs Document' ? 'selected' : '' }}>
                                    Customs Document
                                </option>

                                <option value="Other" {{ old('type') === 'Other' ? 'selected' : '' }}>
                                    Other
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
                                File
                            </label>

                            <label for="file"
                                class="mt-2 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center transition hover:border-cyan-400 hover:bg-cyan-50/50 dark:border-slate-700 dark:bg-slate-950 dark:hover:border-cyan-500 dark:hover:bg-cyan-950/20">
                                <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M12 16V4m0 0L8 8m4-4l4 4M5 20h14" />
                                </svg>

                                <span class="mt-3 text-sm font-bold text-slate-700 dark:text-slate-300">
                                    Choose a file
                                </span>

                                <span class="mt-1 text-xs text-slate-400">
                                    PDF, JPG, JPEG or PNG
                                </span>

                                <span class="mt-1 text-xs text-slate-400">
                                    Maximum size: 10 MB
                                </span>

                                <input id="file" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png"
                                    required class="sr-only"
                                    onchange="document.getElementById('selected-file').textContent = this.files.length ? this.files[0].name : 'No file selected'" />
                            </label>

                            <p id="selected-file"
                                class="mt-2 truncate text-xs font-semibold text-cyan-600 dark:text-cyan-400">
                                No file selected
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

                            Upload Document
                        </button>
                    </form>
                </section>

                {{-- Documents --}}
                <section
                    class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                        <div class="flex items-center justify-between gap-4">

                            <div>
                                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
                                    Shipment Documents
                                </h2>

                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    {{ $documents->count() }}
                                    {{ $documents->count() === 1 ? 'document' : 'documents' }}
                                    attached to this shipment.
                                </p>
                            </div>

                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800">
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
                            $documentType = $document->type ?? 'Document';

                            $documentName = $document->original_name ?? ($document->name ?? 'Unnamed document');

                            $extension = pathinfo($documentName, PATHINFO_EXTENSION);

                            $extension = strtoupper($extension ?: 'FILE');
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

                                    <div class="min-w-0">
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

                                                <span>
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
                                        Uploaded
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
                                No documents yet
                            </h3>

                            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400">
                                No documents have been uploaded for this shipment.
                                Use the upload form to add your first document.
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

                    <div>
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                            Secure document storage
                        </p>

                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                            Shipment documents are associated with your shipment
                            and are protected by customer ownership checks.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
