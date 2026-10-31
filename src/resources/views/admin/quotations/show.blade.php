<x-admin-layout>

    <div class="space-y-6">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm text-slate-500">

            <a href="{{ route('admin.dashboard') }}" class="transition hover:text-slate-900">
                Dashboard
            </a>

            <span>/</span>

            <a href="{{ route('admin.quotations.index') }}" class="transition hover:text-slate-900">
                Quotations
            </a>

            <span>/</span>

            <span class="text-slate-900">
                {{ $quotation->reference_no ?? 'Quotation' }}
            </span>

        </div>


        {{-- Success Message --}}
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4">

                <div class="flex items-center gap-3">

                    <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13 4 4L19 7" />
                    </svg>

                    <p class="text-sm font-medium text-emerald-800">
                        {{ session('success') }}
                    </p>

                </div>

            </div>
        @endif


        {{-- Header --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">

                <div>

                    <div class="flex flex-wrap items-center gap-3">

                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                            {{ $quotation->reference_no ?? 'Quotation' }}
                        </h1>


                        @php

                            $status = $quotation->status ?? 'pending';

                            $statusClasses = match ($status) {
                                'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',

                                'completed' => 'bg-blue-50 text-blue-700 ring-blue-600/20',

                                'processing' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',

                                'rejected' => 'bg-red-50 text-red-700 ring-red-600/20',

                                default => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                            };

                            $statusLabel = ucwords(str_replace('_', ' ', $status));

                        @endphp


                        <span
                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClasses }}">
                            {{ $statusLabel }}
                        </span>

                    </div>


                    <p class="mt-2 text-sm text-slate-500">

                        Quotation ID:

                        <span class="font-medium text-slate-700">
                            #{{ $quotation->id }}
                        </span>

                    </p>

                </div>


                <a href="{{ route('admin.quotations.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">

                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0 7-7m-7 7h18" />
                    </svg>

                    Back to Quotations

                </a>

            </div>

        </div>



        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-slate-900">Assigned Staff</h2>

            <form method="POST" action="{{ route('admin.quotations.assign', $quotation) }}"
                  class="flex flex-col gap-3 sm:flex-row">
                @csrf
                @method('PATCH')

                <select name="assigned_to" required
                        class="w-full rounded-xl border-slate-300">
                    <option value="">Select staff member</option>
                    @foreach ($staff as $person)
                        <option value="{{ $person->id }}"
                            @selected(old('assigned_to', $quotation->assigned_to) == $person->id)>
                            {{ $person->name }} — {{ $person->email }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                        class="rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white">
                    Assign
                </button>
            </form>

            @error('assigned_to')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-slate-900">Activity History</h2>

            <div class="space-y-3">
                @forelse ($history as $event)
                    <div class="rounded-xl border border-slate-100 p-4">
                        <p class="font-semibold">
                            {{ ucwords(str_replace('_', ' ', $event->event)) }}
                        </p>
                        <p class="text-sm text-slate-600">
                            {{ $event->old_value ?? '—' }}
                            →
                            {{ $event->new_value ?? '—' }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $historyUsers[$event->user_id] ?? 'System' }}
                            · {{ $event->created_at }}
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No activity recorded yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Status Update --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="mb-5">

                <h2 class="text-lg font-semibold text-slate-900">
                    Update Status
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Change the current status of this quotation.
                </p>

            </div>


            <form method="POST" action="{{ route('admin.quotations.status', $quotation) }}"
                class="flex flex-col gap-4 sm:flex-row sm:items-end">

                @csrf
                @method('PATCH')


                <div class="w-full sm:max-w-sm">

                    <label for="status" class="mb-2 block text-sm font-medium text-slate-700">
                        Quotation Status
                    </label>

                    <select id="status" name="status"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-100">

                        <option value="pending" @selected($status === 'pending')>
                            Pending
                        </option>

                        <option value="processing" @selected($status === 'processing')>
                            Processing
                        </option>

                        <option value="approved" @selected($status === 'approved')>
                            Approved
                        </option>

                        <option value="rejected" @selected($status === 'rejected')>
                            Rejected
                        </option>

                        <option value="completed" @selected($status === 'completed')>
                            Completed
                        </option>

                    </select>

                    @error('status')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">

                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 7" />
                    </svg>

                    Update Status

                </button>

            </form>

        </div>


        {{-- Quotation Information --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- General --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h2 class="text-lg font-semibold text-slate-900">
                    Quotation Information
                </h2>


                <div class="mt-5 divide-y divide-slate-100">

                    <div class="flex items-center justify-between gap-4 py-3">

                        <span class="text-sm text-slate-500">
                            Reference Number
                        </span>

                        <span class="text-sm font-semibold text-slate-900">
                            {{ $quotation->reference_no ?? '—' }}
                        </span>

                    </div>


                    <div class="flex items-center justify-between gap-4 py-3">

                        <span class="text-sm text-slate-500">
                            Quotation ID
                        </span>

                        <span class="text-sm font-semibold text-slate-900">
                            #{{ $quotation->id }}
                        </span>

                    </div>


                    <div class="flex items-center justify-between gap-4 py-3">

                        <span class="text-sm text-slate-500">
                            Status
                        </span>

                        <span class="text-sm font-semibold text-slate-900">
                            {{ $statusLabel }}
                        </span>

                    </div>


                    <div class="flex items-center justify-between gap-4 py-3">

                        <span class="text-sm text-slate-500">
                            Created
                        </span>

                        <span class="text-sm font-semibold text-slate-900">
                            {{ $quotation->created_at?->format('d M Y, H:i') ?? '—' }}
                        </span>

                    </div>


                    <div class="flex items-center justify-between gap-4 py-3">

                        <span class="text-sm text-slate-500">
                            Last Updated
                        </span>

                        <span class="text-sm font-semibold text-slate-900">
                            {{ $quotation->updated_at?->format('d M Y, H:i') ?? '—' }}
                        </span>

                    </div>

                </div>

            </div>


            {{-- Customer / Additional Data --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h2 class="text-lg font-semibold text-slate-900">
                    Request Details
                </h2>


                <div class="mt-5 space-y-4">

                    @if (isset($quotation->customer_id))
                        <div class="rounded-xl bg-slate-50 p-4">

                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                Customer ID
                            </p>

                            <p class="mt-1 font-semibold text-slate-900">
                                #{{ $quotation->customer_id }}
                            </p>

                        </div>
                    @endif


                    @if (isset($quotation->origin))
                        <div class="rounded-xl bg-slate-50 p-4">

                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                Origin
                            </p>

                            <p class="mt-1 font-semibold text-slate-900">
                                {{ $quotation->origin }}
                            </p>

                        </div>
                    @endif


                    @if (isset($quotation->destination))
                        <div class="rounded-xl bg-slate-50 p-4">

                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                Destination
                            </p>

                            <p class="mt-1 font-semibold text-slate-900">
                                {{ $quotation->destination }}
                            </p>

                        </div>
                    @endif


                    @if (isset($quotation->notes) && $quotation->notes)
                        <div class="rounded-xl bg-slate-50 p-4">

                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                Notes
                            </p>

                            <p class="mt-1 text-sm leading-6 text-slate-700">
                                {{ $quotation->notes }}
                            </p>

                        </div>
                    @endif


                    @if (
                        !isset($quotation->customer_id) &&
                            !isset($quotation->origin) &&
                            !isset($quotation->destination) &&
                            !isset($quotation->notes))
                        <div class="rounded-xl bg-slate-50 p-5 text-sm text-slate-500">
                            No additional quotation details available.
                        </div>
                    @endif

                </div>

            </div>

        </div>


        {{-- All Database Fields --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="mb-5">

                <h2 class="text-lg font-semibold text-slate-900">
                    Quotation Data
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Information stored for this quotation.
                </p>

            </div>


            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                @foreach ($quotation->getAttributes() as $key => $value)
                    @if (!in_array($key, ['id', 'reference_no', 'status', 'created_at', 'updated_at']))
                        <div class="rounded-xl bg-slate-50 p-4">

                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ ucwords(str_replace('_', ' ', $key)) }}
                            </p>

                            <p class="mt-2 break-words text-sm font-medium text-slate-800">

                                @if (is_null($value))
                                    —
                                @else
                                    {{ $value }}
                                @endif

                            </p>

                        </div>
                    @endif
                @endforeach

            </div>

        </div>

    </div>

</x-admin-layout>
