<x-admin-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                Documents
            </h2>

            <p class="text-sm text-slate-500 dark:text-slate-400">
                Manage shipment-related documents.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Search --}}
            <div
                class="mb-6 rounded-2xl bg-white p-4 shadow ring-1 ring-slate-200
                       dark:bg-slate-900 dark:ring-slate-800">

                <form method="GET" action="{{ route('admin.documents.index') }}" class="flex flex-col gap-3 sm:flex-row">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search document, type or shipment..."
                        class="w-full rounded-xl border-slate-300
                               dark:border-slate-700
                               dark:bg-slate-800
                               dark:text-white
                               dark:placeholder-slate-400">

                    <button type="submit"
                        class="rounded-xl bg-cyan-600 px-6 py-2.5
                               text-sm font-semibold text-white
                               transition hover:bg-cyan-500">
                        Search
                    </button>

                    @if (request('search'))
                        <a href="{{ route('admin.documents.index') }}"
                            class="rounded-xl bg-slate-100 px-6 py-2.5
                                   text-center text-sm font-semibold text-slate-700
                                   transition hover:bg-slate-200
                                   dark:bg-slate-800
                                   dark:text-slate-300
                                   dark:hover:bg-slate-700">
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            {{-- Documents Table --}}
            <div
                class="overflow-hidden rounded-2xl bg-white shadow
                       ring-1 ring-slate-200
                       dark:bg-slate-900
                       dark:ring-slate-800">

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">

                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold text-slate-700 dark:text-slate-200">
                                    Document
                                </th>

                                <th class="px-6 py-4 text-left font-semibold text-slate-700 dark:text-slate-200">
                                    Type
                                </th>

                                <th class="px-6 py-4 text-left font-semibold text-slate-700 dark:text-slate-200">
                                    Shipment
                                </th>

                                <th class="px-6 py-4 text-left font-semibold text-slate-700 dark:text-slate-200">
                                    Booking
                                </th>

                                <th class="px-6 py-4 text-left font-semibold text-slate-700 dark:text-slate-200">
                                    Uploaded By
                                </th>

                                <th class="px-6 py-4 text-left font-semibold text-slate-700 dark:text-slate-200">
                                    Created
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">

                            @forelse($documents as $document)

                                <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/50">

                                    {{-- Document --}}
                                    <td class="px-6 py-4">

                                        <div class="font-semibold text-slate-900 dark:text-white">
                                            {{ $document->name ?? 'Document #' . $document->id }}
                                        </div>

                                        @if ($document->original_name)
                                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                {{ $document->original_name }}
                                            </div>
                                        @endif

                                    </td>

                                    {{-- Type --}}
                                    <td class="px-6 py-4">

                                        @if ($document->type)
                                            <span
                                                class="inline-flex rounded-full bg-cyan-100
                                                       px-3 py-1 text-xs font-semibold
                                                       text-cyan-700
                                                       dark:bg-cyan-950/50
                                                       dark:text-cyan-300">
                                                {{ $document->type }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">
                                                —
                                            </span>
                                        @endif

                                    </td>

                                    {{-- Shipment --}}
                                    <td class="px-6 py-4">

                                        @if ($document->shipment)
                                            <div class="font-semibold text-slate-900 dark:text-white">
                                                {{ $document->shipment->shipment_no }}
                                            </div>

                                            @if ($document->shipment->status)
                                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                    {{ ucfirst(str_replace('_', ' ', $document->shipment->status)) }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-slate-400">
                                                —
                                            </span>
                                        @endif

                                    </td>

                                    {{-- Booking --}}
                                    <td class="px-6 py-4">

                                        @if ($document->shipment?->booking_no)
                                            <span class="font-medium text-slate-700 dark:text-slate-300">
                                                {{ $document->shipment->booking_no }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">
                                                —
                                            </span>
                                        @endif

                                    </td>

                                    {{-- Uploaded By --}}
                                    <td class="px-6 py-4">

                                        @if ($document->uploadedBy)
                                            <div class="font-medium text-slate-700 dark:text-slate-300">
                                                {{ $document->uploadedBy->name }}
                                            </div>

                                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ $document->uploadedBy->email }}
                                            </div>
                                        @else
                                            <span class="text-slate-400">
                                                —
                                            </span>
                                        @endif

                                    </td>

                                    {{-- Created --}}
                                    <td class="px-6 py-4 text-slate-500 dark:text-slate-400">
                                        {{ $document->created_at?->format('Y-m-d H:i') ?? '—' }}
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="6"
                                        class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                        <div class="text-base font-semibold">
                                            No documents found.
                                        </div>

                                        <div class="mt-1 text-sm">
                                            Try changing your search criteria.
                                        </div>
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>
                    </table>
                </div>

            </div>

            {{-- Pagination --}}
            @if ($documents->hasPages())
                <div class="mt-6">
                    {{ $documents->links() }}
                </div>
            @endif

        </div>
    </div>
</x-admin-layout>
