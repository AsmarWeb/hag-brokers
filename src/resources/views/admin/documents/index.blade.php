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

            <div
                class="mb-6 rounded-2xl bg-white p-4 shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                <form method="GET" action="{{ route('admin.documents.index') }}" class="flex flex-col gap-3 sm:flex-row">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search document, type or shipment..."
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                    <button class="rounded-xl bg-cyan-600 px-6 py-2.5 text-sm font-semibold text-white">
                        Search
                    </button>
                </form>
            </div>

            <div
                class="overflow-hidden rounded-2xl bg-white shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold">Document</th>
                                <th class="px-6 py-4 text-left font-semibold">Type</th>
                                <th class="px-6 py-4 text-left font-semibold">Shipment</th>
                                <th class="px-6 py-4 text-left font-semibold">Created</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($documents as $document)
                                <tr>
                                    <td class="px-6 py-4 font-semibold text-slate-900 dark:text-white">
                                        {{ $document->name ?? 'Document #' . $document->id }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $document->type ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $document->shipment?->reference_no ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4 text-slate-500">
                                        {{ $document->created_at?->format('Y-m-d H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                        No documents found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                {{ $documents->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
