<x-admin-layout>

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

            <div>

                <div class="flex items-center gap-2 text-sm text-slate-500">

                    <a href="{{ route('admin.dashboard') }}" class="transition hover:text-slate-900">
                        Dashboard
                    </a>

                    <span>/</span>

                    <span class="text-slate-900">
                        Quotations
                    </span>

                </div>

                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                    Quotations
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Manage customer quotation requests and their current status.
                </p>

            </div>

        </div>



        <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            @foreach ([
                'total' => ['Total Requests', 'text-slate-900'],
                'pending' => ['Pending', 'text-amber-600'],
                'processing' => ['Processing', 'text-blue-600'],
                'approved' => ['Approved', 'text-emerald-600'],
                'completed' => ['Completed', 'text-indigo-600'],
            ] as $key => [$label, $color])
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        {{ $label }}
                    </p>
                    <p class="mt-3 text-3xl font-black {{ $color }}">
                        {{ $quotationStats[$key] }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- Filters --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <form method="GET" action="{{ route('admin.quotations.index') }}"
                class="grid grid-cols-1 gap-4 md:grid-cols-4">

                {{-- Search --}}
                <div class="md:col-span-2">

                    <label for="search" class="mb-2 block text-sm font-medium text-slate-700">
                        Search Reference
                    </label>

                    <div class="relative">

                        <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg>

                        <input id="search" name="search" type="text" value="{{ request('search') }}"
                            placeholder="Quotation reference..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-100">

                    </div>

                </div>


                {{-- Status --}}
                <div>

                    <label for="status" class="mb-2 block text-sm font-medium text-slate-700">
                        Status
                    </label>

                    <select id="status" name="status"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-100">

                        <option value="">
                            All statuses
                        </option>

                        <option value="pending" @selected(request('status') === 'pending')>
                            Pending
                        </option>

                        <option value="processing" @selected(request('status') === 'processing')>
                            Processing
                        </option>

                        <option value="approved" @selected(request('status') === 'approved')>
                            Approved
                        </option>

                        <option value="rejected" @selected(request('status') === 'rejected')>
                            Rejected
                        </option>

                        <option value="completed" @selected(request('status') === 'completed')>
                            Completed
                        </option>

                    </select>

                </div>


                {{-- Actions --}}
                <div class="flex items-end gap-2">

                    <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">

                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg>

                        Filter

                    </button>


                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.quotations.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                            Clear
                        </a>
                    @endif

                </div>

            </form>

        </div>


        {{-- Quotations --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Table Header --}}
            <div
                class="flex flex-col gap-2 border-b border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <h2 class="font-semibold text-slate-900">
                        Quotation Requests
                    </h2>

                    <p class="text-sm text-slate-500">
                        {{ $quotations->total() }}
                        {{ Str::plural('quotation', $quotations->total()) }}
                    </p>

                </div>

                <div class="text-sm text-slate-500">

                    Page {{ $quotations->currentPage() }}
                    of {{ $quotations->lastPage() }}

                </div>

            </div>


            {{-- Desktop Table --}}
            <div class="hidden overflow-x-auto lg:block">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">

                        <tr>

                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Reference
                            </th>

                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Status
                            </th>

                            <th
                                class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Created
                            </th>

                            <th
                                class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100 bg-white">

                        @forelse($quotations as $quotation)
                            @php

                                $status = $quotation->status ?? 'pending';

                                $statusClasses = match ($status) {
                                    'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

                                    'completed' => 'bg-blue-50 text-blue-700 ring-blue-600/20',

                                    'processing' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',

                                    'rejected' => 'bg-red-50 text-red-700 ring-red-600/20',

                                    default => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                };

                                $statusLabel = match ($status) {
                                    'approved' => 'Approved',

                                    'completed' => 'Completed',

                                    'processing' => 'Processing',

                                    'rejected' => 'Rejected',

                                    'pending' => 'Pending',

                                    default => ucwords(str_replace('_', ' ', $status)),
                                };

                            @endphp


                            <tr class="transition hover:bg-slate-50">

                                {{-- Reference --}}
                                <td class="whitespace-nowrap px-6 py-4">

                                    <div class="font-semibold text-slate-900">
                                        {{ $quotation->reference_no ?? '—' }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">
                                        Quotation #{{ $quotation->id }}
                                    </div>

                                </td>


                                {{-- Status --}}
                                <td class="whitespace-nowrap px-6 py-4">

                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClasses }}">
                                        {{ $statusLabel }}
                                    </span>

                                </td>


                                {{-- Created --}}
                                <td class="whitespace-nowrap px-6 py-4">

                                    <div class="text-sm text-slate-700">

                                        {{ $quotation->created_at?->format('d M Y') ?? '—' }}

                                    </div>

                                    <div class="mt-1 text-xs text-slate-400">

                                        {{ $quotation->created_at?->format('H:i') ?? '' }}

                                    </div>

                                </td>


                                {{-- Action --}}
                                <td class="whitespace-nowrap px-6 py-4 text-right">

                                    <a href="{{ route('admin.quotations.show', $quotation) }}"
                                        class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 hover:text-slate-900">

                                        View

                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="m9 18 6-6-6-6" />
                                        </svg>

                                    </a>

                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td colspan="4" class="px-6 py-16 text-center">

                                    <div
                                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">

                                        <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 14h6m-6-4h6m2-5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2Z" />
                                        </svg>

                                    </div>

                                    <h3 class="mt-4 font-semibold text-slate-900">
                                        No quotations found
                                    </h3>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Try changing your search or status filter.
                                    </p>

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Mobile --}}
            <div class="divide-y divide-slate-100 lg:hidden">

                @forelse($quotations as $quotation)
                    @php

                        $status = $quotation->status ?? 'pending';

                        $statusClasses = match ($status) {
                            'approved' => 'bg-emerald-50 text-emerald-700',

                            'completed' => 'bg-blue-50 text-blue-700',

                            'processing' => 'bg-indigo-50 text-indigo-700',

                            'rejected' => 'bg-red-50 text-red-700',

                            default => 'bg-amber-50 text-amber-700',
                        };

                    @endphp


                    <a href="{{ route('admin.quotations.show', $quotation) }}"
                        class="block p-5 transition hover:bg-slate-50">

                        <div class="flex items-start justify-between gap-4">

                            <div>

                                <div class="font-semibold text-slate-900">
                                    {{ $quotation->reference_no ?? '—' }}
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    #{{ $quotation->id }}
                                </div>

                            </div>


                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                                {{ ucwords(str_replace('_', ' ', $status)) }}
                            </span>

                        </div>


                        <div class="mt-4 text-xs text-slate-500">

                            Created:

                            <span class="font-medium text-slate-700">
                                {{ $quotation->created_at?->format('d M Y, H:i') ?? '—' }}
                            </span>

                        </div>

                    </a>

                @empty

                    <div class="px-6 py-16 text-center text-sm text-slate-500">
                        No quotations found.
                    </div>
                @endforelse

            </div>


            {{-- Pagination --}}
            @if ($quotations->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">

                    {{ $quotations->links() }}

                </div>
            @endif

        </div>

    </div>

</x-admin-layout>
