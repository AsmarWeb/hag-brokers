<x-admin-layout>

    <div class="space-y-6">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">

            <a href="{{ route('admin.dashboard') }}" class="transition hover:text-slate-900">
                {{ __('admin_ui.label_038') }}
            </a>

            <span>/</span>

            <a href="{{ route('admin.quotations.index') }}" class="transition hover:text-slate-900">
                {{ __('admin_ui.label_009') }}
            </a>

            <span>/</span>

            <span class="text-slate-900 dark:text-white">
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
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">

            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">

                <div>

                    <div class="flex flex-wrap items-center gap-3">

                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
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


                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">

                        {{ __('admin_ui.label_100') }}

                        <span class="font-medium text-slate-700 dark:text-slate-200">
                            #{{ $quotation->id }}
                        </span>

                    </p>

                </div>


                <a href="{{ route('admin.quotations.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 transition hover:bg-slate-50">

                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0 7-7m-7 7h18" />
                    </svg>

                    {{ __('admin_ui.label_101') }}

                </a>

            </div>

        </div>



        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">{{ __('admin_ui.label_102') }}</h2>

            <form method="POST" action="{{ route('admin.quotations.assign', $quotation) }}"
                  class="flex flex-col gap-3 sm:flex-row">
                @csrf
                @method('PATCH')

                <select name="assigned_to" required
                        class="w-full rounded-xl border-slate-300">
                    <option value="">{{ __('admin_ui.label_103') }}</option>
                    @foreach ($staff as $person)
                        <option value="{{ $person->id }}"
                            @selected(old('assigned_to', $quotation->assigned_to) == $person->id)>
                            {{ $person->name }} — {{ $person->email }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                        class="rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white">
                    {{ __('admin_ui.label_104') }}
                </button>
            </form>

            @error('assigned_to')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">{{ __('admin_ui.label_105') }}</h2>

            <div class="space-y-3">
                @forelse ($history as $event)
                    <div class="rounded-xl border border-slate-100 dark:border-slate-800 p-4">
                        <p class="font-semibold">
                            {{ ucwords(str_replace('_', ' ', $event->event)) }}
                        </p>
                        <p class="text-sm text-slate-600">
                            {{ $event->old_value ?? '—' }}
                            →
                            {{ $event->new_value ?? '—' }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ $historyUsers[$event->user_id] ?? 'System' }}
                            · {{ $event->created_at }}
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('admin_ui.label_106') }}</p>
                @endforelse
            </div>
        </div>



        @if ($quotation->approved_offer_id !== null)
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6">
                <h2 class="mb-3 text-lg font-bold text-emerald-900">
                    {{ __('admin_ui.label_107') }}
                </h2>

                <p class="text-sm text-emerald-800">
                    Offer #{{ $quotation->approved_offer_id }}
                    — Approved {{ $quotation->offer_approved_at }}
                </p>

                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div>
                        <p class="text-xs text-emerald-700">{{ __('admin_ui.label_044') }}</p>
                        <p class="font-bold">
                            {{ $quotation->approved_currency }}
                            {{ number_format((float) $quotation->approved_carrier_amount, 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-emerald-700">{{ __('admin_ui.label_029') }}</p>
                        <p class="font-bold">
                            {{ $quotation->approved_currency }}
                            {{ number_format((float) $quotation->approved_customer_amount, 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-emerald-700">{{ __('admin_ui.label_108') }}</p>
                        <p class="font-bold">
                            {{ $quotation->approved_currency }}
                            {{ number_format((float) $quotation->approved_margin, 2) }}
                        </p>
                    </div>
                </div>

                <p class="mt-4 text-xs text-emerald-800">
                    {{ __('admin_ui.label_109') }}
                </p>
            </div>
        @endif


        {{-- Final Quotation PDF --}}
        @if ($quotation->approved_offer_id !== null)
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
                <h2 class="mb-2 text-lg font-bold text-slate-900 dark:text-white">
                    {{ __('admin_ui.label_110') }}
                </h2>

                <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">
                    Generate an internal draft PDF using the approved customer
                    price. It will not be emailed to the customer.
                </p>

                @if ($finalQuotationDocument)
                    <a href="{{ route('admin.documents.versions', $finalQuotationDocument) }}"
                       class="inline-flex rounded-xl bg-emerald-700 px-5 py-3 font-semibold text-white">
                        {{ __('admin_ui.label_111') }}
                    </a>

                    @if ($finalQuotationDocument->status === 'draft')
                        <form method="POST"
                              class="mt-3"
                              action="{{ route(
                                  'admin.quotations.final-pdf.approve',
                                  [$quotation, $finalQuotationDocument]
                              ) }}">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white">
                                {{ __('admin_ui.label_112') }}
                            </button>
                        </form>
                    @elseif ($finalQuotationDocument->status === 'active')
                        @if ($finalQuotationDelivery)
                            <p class="mt-3 text-sm font-semibold">
                                Email status: {{ ucfirst($finalQuotationDelivery->status) }}
                            </p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Recipient: {{ $finalQuotationDelivery->recipient_email }}
                            </p>
                            @if ($finalQuotationDelivery->failed_at)
                                <p class="text-sm text-red-600">
                                    {{ __('admin_ui.label_165') }}
                                </p>
                            @endif
                        @else
                            <form method="POST"
                                  class="mt-3 space-y-3"
                                  action="{{ route(
                                      'admin.quotations.final-pdf.send',
                                      [$quotation, $finalQuotationDocument]
                                  ) }}">
                                @csrf

                                <label class="block text-sm font-semibold">
                                    {{ __('admin_ui.label_113') }}
                                </label>

                                <select name="recipient_user_id"
                                        required
                                        class="w-full rounded-xl border-slate-300">
                                    <option value="">{{ __('admin_ui.label_114') }}</option>
                                    @foreach ($quotation->customer?->users ?? [] as $recipient)
                                        @if (filter_var($recipient->email, FILTER_VALIDATE_EMAIL))
                                            <option value="{{ $recipient->id }}">
                                                {{ $recipient->name }} — {{ $recipient->email }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>

                                <button type="submit"
                                        class="rounded-xl bg-cyan-700 px-5 py-3 font-semibold text-white">
                                    {{ __('admin_ui.label_115') }}
                                </button>
                            </form>
                        @endif
                    @endif

                    @error('approval')
                        <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                @else
                    <form method="POST"
                          action="{{ route('admin.quotations.final-pdf.store', $quotation) }}">
                        @csrf
                        <button type="submit"
                                class="rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white">
                            {{ __('admin_ui.label_116') }}
                        </button>
                    </form>
                @endif

                @error('pdf')
                    <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endif

        {{-- Carrier Offers --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <h2 class="mb-2 text-lg font-bold text-slate-900 dark:text-white">{{ __('admin_ui.label_117') }}</h2>
            <p class="mb-5 text-sm text-slate-500 dark:text-slate-400">
                {{ __('admin_ui.label_118') }}
            </p>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[650px] text-start text-sm">
                    <thead class="border-b text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="p-3">{{ __('admin_ui.label_002') }}</th>
                            <th class="p-3">{{ __('admin_ui.label_119') }}</th>
                            <th class="p-3">{{ __('admin_ui.label_029') }}</th>
                            <th class="p-3">{{ __('admin_ui.label_120') }}</th>
                            <th class="p-3">{{ __('admin_ui.label_001') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($quotation->offers as $offer)
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <td class="p-3">{{ $offer->carrier?->name ?? '—' }}</td>
                                <td class="p-3">{{ $offer->currency }}
                                    {{ number_format((float) $offer->carrier_amount, 2) }}
                                </td>
                                <td class="p-3">{{ $offer->currency }}
                                    {{ number_format((float) $offer->customer_amount, 2) }}
                                </td>
                                <td class="p-3 font-semibold text-emerald-700">
                                    {{ $offer->currency }}
                                    {{ number_format(
                                        (float) $offer->customer_amount -
                                        (float) $offer->carrier_amount, 2
                                    ) }}
                                </td>
                                <td class="p-3">
                                    <span>{{ ucfirst($offer->status) }}</span>

                                    @if (
                                        $quotation->approved_offer_id === null
                                        && $offer->status === 'pending'
                                    )
                                        <form method="POST"
                                              action="{{ route(
                                                  'admin.quotations.offers.approve',
                                                  [$quotation, $offer]
                                              ) }}"
                                              class="mt-2">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    onclick="return confirm('Approve this carrier offer?')"
                                                    class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold text-white">
                                                {{ __('admin_ui.label_121') }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-5 text-slate-500 dark:text-slate-400">
                                    {{ __('admin_ui.label_122') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($quotation->approved_offer_id === null)
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">
                {{ __('admin_ui.label_123') }}
            </h2>

            <form method="POST"
                  action="{{ route('admin.quotations.offers.store', $quotation) }}"
                  class="grid gap-4 md:grid-cols-2">
                @csrf

                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ __('admin_ui.label_002') }}</label>
                    <select name="carrier_id" required class="w-full rounded-xl border-slate-300">
                        <option value="">{{ __('admin_ui.label_124') }}</option>
                        @foreach ($carriers as $carrier)
                            <option value="{{ $carrier->id }}"
                                @selected(old('carrier_id') == $carrier->id)>
                                {{ $carrier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('carrier_id')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ __('admin_ui.label_125') }}</label>
                    <input name="currency" required maxlength="3"
                           value="{{ old('currency', 'USD') }}"
                           class="w-full rounded-xl border-slate-300">
                    @error('currency')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ __('admin_ui.label_044') }}</label>
                    <input name="carrier_amount" type="number" step="0.01"
                           min="0" required value="{{ old('carrier_amount') }}"
                           class="w-full rounded-xl border-slate-300">
                    @error('carrier_amount')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ __('admin_ui.label_029') }}</label>
                    <input name="customer_amount" type="number" step="0.01"
                           min="0" required value="{{ old('customer_amount') }}"
                           class="w-full rounded-xl border-slate-300">
                    @error('customer_amount')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ __('admin_ui.label_126') }}</label>
                    <input name="departure_date" type="date"
                           value="{{ old('departure_date') }}"
                           class="w-full rounded-xl border-slate-300">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ __('admin_ui.label_019') }}</label>
                    <input name="eta" type="date" value="{{ old('eta') }}"
                           class="w-full rounded-xl border-slate-300">
                    @error('eta')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ __('admin_ui.label_127') }}</label>
                    <input name="transit_days" type="number" min="0"
                           value="{{ old('transit_days') }}"
                           class="w-full rounded-xl border-slate-300">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">{{ __('admin_ui.label_045') }}</label>
                    <textarea name="notes" rows="2"
                              class="w-full rounded-xl border-slate-300">{{ old('notes') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <button type="submit"
                            class="rounded-xl bg-slate-900 px-6 py-3 font-semibold text-white">
                        {{ __('admin_ui.label_128') }}
                    </button>
                </div>
            </form>
        </div>

        @endif

        @error('offer')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror

        {{-- Status Update --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">

            <div class="mb-5">

                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                    {{ __('admin_ui.label_046') }}
                </h2>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('admin_ui.label_129') }}
                </p>

            </div>


            <form method="POST" action="{{ route('admin.quotations.status', $quotation) }}"
                class="flex flex-col gap-4 sm:flex-row sm:items-end">

                @csrf
                @method('PATCH')


                <div class="w-full sm:max-w-sm">

                    <label for="status" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                        {{ __('admin_ui.label_130') }}
                    </label>

                    <select id="status" name="status"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-100">

                        <option value="pending" @selected($status === 'pending')>
                            {{ __('admin_ui.label_027') }}
                        </option>

                        <option value="processing" @selected($status === 'processing')>
                            {{ __('admin_ui.label_039') }}
                        </option>

                        <option value="approved" @selected($status === 'approved')>
                            {{ __('admin_ui.label_040') }}
                        </option>

                        <option value="rejected" @selected($status === 'rejected')>
                            {{ __('admin_ui.label_041') }}
                        </option>

                        <option value="completed" @selected($status === 'completed')>
                            {{ __('admin_ui.label_042') }}
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

                    {{ __('admin_ui.label_046') }}

                </button>

            </form>

        </div>


        {{-- Quotation Information --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- General --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">

                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                    {{ __('admin_ui.label_131') }}
                </h2>


                <div class="mt-5 divide-y divide-slate-100 dark:divide-slate-800">

                    <div class="flex items-center justify-between gap-4 py-3">

                        <span class="text-sm text-slate-500 dark:text-slate-400">
                            {{ __('admin_ui.label_132') }}
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $quotation->reference_no ?? '—' }}
                        </span>

                    </div>


                    <div class="flex items-center justify-between gap-4 py-3">

                        <span class="text-sm text-slate-500 dark:text-slate-400">
                            {{ __('admin_ui.label_133') }}
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            #{{ $quotation->id }}
                        </span>

                    </div>


                    <div class="flex items-center justify-between gap-4 py-3">

                        <span class="text-sm text-slate-500 dark:text-slate-400">
                            {{ __('admin_ui.label_001') }}
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $statusLabel }}
                        </span>

                    </div>


                    <div class="flex items-center justify-between gap-4 py-3">

                        <span class="text-sm text-slate-500 dark:text-slate-400">
                            {{ __('admin_ui.label_018') }}
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $quotation->created_at?->format('d M Y, H:i') ?? '—' }}
                        </span>

                    </div>


                    <div class="flex items-center justify-between gap-4 py-3">

                        <span class="text-sm text-slate-500 dark:text-slate-400">
                            {{ __('admin_ui.label_134') }}
                        </span>

                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $quotation->updated_at?->format('d M Y, H:i') ?? '—' }}
                        </span>

                    </div>

                </div>

            </div>


            {{-- Customer / Additional Data --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">

                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                    {{ __('admin_ui.label_135') }}
                </h2>


                <div class="mt-5 space-y-4">

                    @if (isset($quotation->customer_id))
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-950 p-4">

                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ __('admin_ui.label_136') }}
                            </p>

                            <p class="mt-1 font-semibold text-slate-900 dark:text-white">
                                #{{ $quotation->customer_id }}
                            </p>

                        </div>
                    @endif


                    @if (isset($quotation->origin))
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-950 p-4">

                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ __('admin_ui.label_047') }}
                            </p>

                            <p class="mt-1 font-semibold text-slate-900 dark:text-white">
                                {{ $quotation->origin }}
                            </p>

                        </div>
                    @endif


                    @if (isset($quotation->destination))
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-950 p-4">

                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ __('admin_ui.label_013') }}
                            </p>

                            <p class="mt-1 font-semibold text-slate-900 dark:text-white">
                                {{ $quotation->destination }}
                            </p>

                        </div>
                    @endif


                    @if (isset($quotation->notes) && $quotation->notes)
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-950 p-4">

                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ __('admin_ui.label_045') }}
                            </p>

                            <p class="mt-1 text-sm leading-6 text-slate-700 dark:text-slate-200">
                                {{ $quotation->notes }}
                            </p>

                        </div>
                    @endif


                    @if (
                        !isset($quotation->customer_id) &&
                            !isset($quotation->origin) &&
                            !isset($quotation->destination) &&
                            !isset($quotation->notes))
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-950 p-5 text-sm text-slate-500 dark:text-slate-400">
                            {{ __('admin_ui.label_137') }}
                        </div>
                    @endif

                </div>

            </div>

        </div>


        {{-- All Database Fields --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">

            <div class="mb-5">

                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                    {{ __('admin_ui.label_138') }}
                </h2>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('admin_ui.label_139') }}
                </p>

            </div>


            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                @foreach ($quotation->getAttributes() as $key => $value)
                    @if (!in_array($key, ['id', 'reference_no', 'status', 'created_at', 'updated_at']))
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-950 p-4">

                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ ucwords(str_replace('_', ' ', $key)) }}
                            </p>

                            <p class="mt-2 break-words text-sm font-medium text-slate-800 dark:text-slate-100">

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
