{{-- resources/views/admin/opportunities/index.blade.php --}}

<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';

        $t = [
            'title' => $isAr ? 'الفرص' : 'Opportunities',
            'subtitle' => $isAr
                ? 'إدارة فرص الأعمال والصفقات والاستثمارات المحتملة.'
                : 'Manage business opportunities, deals and potential investments.',

            'add' => $isAr ? 'إضافة فرصة' : 'Add Opportunity',
            'search' => $isAr ? 'بحث...' : 'Search...',

            'customer' => $isAr ? 'العميل' : 'Customer',
            'stage' => $isAr ? 'المرحلة' : 'Stage',
            'type' => $isAr ? 'نوع الفرصة' : 'Opportunity Type',
            'verification' => $isAr ? 'التحقق' : 'Verification',
            'visibility' => $isAr ? 'الظهور' : 'Visibility',

            'all' => $isAr ? 'الكل' : 'All',

            'title_col' => $isAr ? 'الفرصة' : 'Opportunity',
            'contact' => $isAr ? 'جهة الاتصال' : 'Contact',
            'value' => $isAr ? 'القيمة' : 'Value',
            'capital' => $isAr ? 'رأس المال' : 'Capital',
            'stage_col' => $isAr ? 'المرحلة' : 'Stage',
            'marketplace' => $isAr ? 'Marketplace' : 'Marketplace',
            'interests' => $isAr ? 'اهتمام' : 'Interests',

            'probability' => $isAr ? 'الاحتمالية' : 'Probability',
            'actions' => $isAr ? 'الإجراءات' : 'Actions',

            'view' => $isAr ? 'عرض' : 'View',
            'edit' => $isAr ? 'تعديل' : 'Edit',
            'delete' => $isAr ? 'حذف' : 'Delete',

            'public' => $isAr ? 'عامة' : 'Public',
            'private' => $isAr ? 'خاصة' : 'Private',
            'invite_only' => $isAr ? 'بدعوة فقط' : 'Invite Only',

            'verified' => $isAr ? 'موثقة' : 'Verified',
            'pending' => $isAr ? 'قيد الانتظار' : 'Pending',
            'under_review' => $isAr ? 'قيد المراجعة' : 'Under Review',
            'rejected' => $isAr ? 'مرفوضة' : 'Rejected',

            'no_data' => $isAr ? 'لا توجد فرص.' : 'No opportunities found.',

            'confirm_delete' => $isAr
                ? 'هل أنت متأكد من حذف هذه الفرصة؟'
                : 'Are you sure you want to delete this opportunity?',
        ];

        $stages = [
            'new' => $isAr ? 'جديدة' : 'New',
            'qualified' => $isAr ? 'مؤهلة' : 'Qualified',
            'quotation' => $isAr ? 'عرض سعر' : 'Quotation',
            'negotiation' => $isAr ? 'تفاوض' : 'Negotiation',
            'won' => $isAr ? 'تم الفوز' : 'Won',
            'lost' => $isAr ? 'خاسرة' : 'Lost',
        ];

        $opportunityTypes = array_combine(\App\Services\Platform\OpportunityCatalog::TYPES, array_map(fn ($type) => __('trade.types.'.$type), \App\Services\Platform\OpportunityCatalog::TYPES));

        $verificationStatuses = [
            'auto_eligible' => __('market.auto_published'),
            'pending' => $t['pending'],
            'under_review' => $t['under_review'],
            'verified' => $t['verified'],
            'rejected' => $t['rejected'],
        ];

        $visibilities = [
            'private' => $t['private'],
            'public' => $t['public'],
            'invite_only' => $t['invite_only'],
        ];
    @endphp

    <div class="space-y-6">

        {{-- =========================================================
            HEADER
        ========================================================== --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-2xl font-bold text-white">
                    {{ $t['title'] }}
                </h1>

                <p class="mt-1 text-sm text-slate-400">
                    {{ $t['subtitle'] }}
                </p>
            </div>



        </div>


        {{-- =========================================================
            FILTERS
        ========================================================== --}}
        <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4 shadow-xl">

            <form method="GET" action="{{ route('admin.opportunities.index') }}"
                class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">

                {{-- Search --}}
                <div class="xl:col-span-2">

                    <label class="mb-2 block text-sm font-medium text-slate-300">
                        {{ $isAr ? 'بحث' : 'Search' }}
                    </label>

                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ $t['search'] }}"
                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400">

                </div>


                {{-- Customer --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-300">
                        {{ $t['customer'] }}
                    </label>

                    <select name="customer_id"
                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                        <option value="">
                            {{ $t['all'] }}
                        </option>

                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>
                                {{ $customer->company_name }}
                            </option>
                        @endforeach

                    </select>

                </div>


                {{-- Stage --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-300">
                        {{ $t['stage'] }}
                    </label>

                    <select name="stage"
                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                        <option value="">
                            {{ $t['all'] }}
                        </option>

                        @foreach ($stages as $key => $label)
                            <option value="{{ $key }}" @selected(request('stage') === $key)>
                                {{ $label }}
                            </option>
                        @endforeach

                    </select>

                </div>


                {{-- Opportunity Type --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-300">
                        {{ $t['type'] }}
                    </label>

                    <select name="opportunity_type"
                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                        <option value="">
                            {{ $t['all'] }}
                        </option>

                        @foreach ($opportunityTypes as $key => $label)
                            <option value="{{ $key }}" @selected(request('opportunity_type') === $key)>
                                {{ $label }}
                            </option>
                        @endforeach

                    </select>

                </div>


                {{-- Verification --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-300">
                        {{ $t['verification'] }}
                    </label>

                    <select name="verification_status"
                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                        <option value="">
                            {{ $t['all'] }}
                        </option>

                        @foreach ($verificationStatuses as $key => $label)
                            <option value="{{ $key }}" @selected(request('verification_status') === $key)>
                                {{ $label }}
                            </option>
                        @endforeach

                    </select>

                </div>


                {{-- Visibility --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-300">
                        {{ $t['visibility'] }}
                    </label>

                    <select name="visibility"
                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                        <option value="">
                            {{ $t['all'] }}
                        </option>

                        @foreach ($visibilities as $key => $label)
                            <option value="{{ $key }}" @selected(request('visibility') === $key)>
                                {{ $label }}
                            </option>
                        @endforeach

                    </select>

                </div>


                {{-- Buttons --}}
                <div class="flex items-end gap-3 md:col-span-2 xl:col-span-5">

                    <a href="{{ route('admin.opportunities.index') }}"
                        class="rounded-xl border border-white/10 bg-slate-800 px-5 py-3 text-sm font-semibold text-slate-300 transition hover:bg-slate-700">
                        {{ $isAr ? 'إعادة تعيين' : 'Reset' }}
                    </a>

                    <button type="submit"
                        class="rounded-xl bg-cyan-400 px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-cyan-300">
                        {{ $isAr ? 'بحث وتصفية' : 'Filter' }}
                    </button>

                </div>

            </form>

        </div>


        {{-- =========================================================
            FLASH
        ========================================================== --}}
        @if (session('success'))
            <div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-300">
                {{ session('success') }}
            </div>
        @endif


        {{-- =========================================================
            TABLE
        ========================================================== --}}
        <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/70 shadow-xl">

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-white/10">

                    <thead class="bg-slate-950/70">

                        <tr>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ $t['title_col'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ $t['type'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ $t['customer'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ $t['value'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ $t['stage_col'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ $t['marketplace'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ $t['interests'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-end text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ $t['actions'] }}
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-white/5">

                        @forelse($opportunities as $opportunity)
                            @php

                                $stageClass = match ($opportunity->stage) {
                                    'new' => 'bg-slate-500/10 text-slate-300 ring-slate-400/20',
                                    'qualified' => 'bg-blue-500/10 text-blue-300 ring-blue-400/20',
                                    'quotation' => 'bg-cyan-500/10 text-cyan-300 ring-cyan-400/20',
                                    'negotiation' => 'bg-amber-500/10 text-amber-300 ring-amber-400/20',
                                    'won' => 'bg-emerald-500/10 text-emerald-300 ring-emerald-400/20',
                                    'lost' => 'bg-red-500/10 text-red-300 ring-red-400/20',
                                    default => 'bg-slate-500/10 text-slate-300 ring-slate-400/20',
                                };

                                $verificationClass = match ($opportunity->verification_status) {
                                    'verified' => 'bg-emerald-500/10 text-emerald-300 ring-emerald-400/20',
                                    'under_review' => 'bg-amber-500/10 text-amber-300 ring-amber-400/20',
                                    'rejected' => 'bg-red-500/10 text-red-300 ring-red-400/20',
                                    default => 'bg-slate-500/10 text-slate-300 ring-slate-400/20',
                                };

                                $visibilityClass = match ($opportunity->visibility) {
                                    'public' => 'bg-cyan-500/10 text-cyan-300 ring-cyan-400/20',
                                    'invite_only' => 'bg-purple-500/10 text-purple-300 ring-purple-400/20',
                                    default => 'bg-slate-500/10 text-slate-300 ring-slate-400/20',
                                };

                                $interestCount =
                                    $opportunity->interests_count ?? ($opportunity->interests?->count() ?? 0);

                            @endphp


                            <tr class="transition hover:bg-white/[0.03]">

                                {{-- Opportunity --}}
                                <td class="px-6 py-4">

                                    <a href="{{ route('admin.opportunities.show', $opportunity) }}"
                                        class="font-semibold text-white transition hover:text-cyan-300">
                                        {{ $opportunity->title }}
                                    </a>

                                    @if ($opportunity->category)
                                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            {{ ucfirst(str_replace('_', ' ', $opportunity->category)) }}
                                        </div>
                                    @elseif ($opportunity->cargo_type)
                                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            {{ $opportunity->cargo_type }}
                                        </div>
                                    @endif

                                </td>


                                {{-- Type --}}
                                <td class="px-6 py-4">

                                    <span class="text-sm text-cyan-300">
                                        {{ $opportunityTypes[$opportunity->opportunity_type] ?? ucfirst($opportunity->opportunity_type ?? '—') }}
                                    </span>

                                </td>


                                {{-- Customer --}}
                                <td class="px-6 py-4 text-sm text-slate-300">
                                    {{ $opportunity->customer?->company_name ?? '—' }}
                                </td>


                                {{-- Value --}}
                                <td class="px-6 py-4 text-sm font-semibold text-white">

                                    @if ($opportunity->estimated_value !== null)
                                        {{ number_format((float) $opportunity->estimated_value, 2) }}

                                        <span class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $opportunity->currency ?? 'USD' }}
                                        </span>
                                    @elseif ($opportunity->required_capital !== null)
                                        <div>
                                            {{ number_format((float) $opportunity->required_capital, 2) }}
                                        </div>

                                        <span class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $opportunity->currency ?? 'USD' }}
                                        </span>
                                    @else
                                        —
                                    @endif

                                </td>


                                {{-- Stage --}}
                                <td class="px-6 py-4">

                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $stageClass }}">
                                        {{ $stages[$opportunity->stage] ?? ucfirst($opportunity->stage) }}
                                    </span>

                                </td>


                                {{-- Marketplace --}}
                                <td class="px-6 py-4">

                                    <div class="flex flex-col items-start gap-2">

                                        <span
                                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $visibilityClass }}">
                                            {{ $visibilities[$opportunity->visibility] ?? ucfirst($opportunity->visibility ?? 'private') }}
                                        </span>

                                        <span
                                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $verificationClass }}">
                                            {{ $verificationStatuses[$opportunity->verification_status] ?? ucfirst($opportunity->verification_status ?? 'pending') }}
                                        </span>

                                    </div>

                                </td>


                                {{-- Interests --}}
                                <td class="px-6 py-4">

                                    <a href="{{ route('admin.opportunities.show', $opportunity) }}"
                                        class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-300 transition hover:border-cyan-400/30 hover:text-cyan-300">

                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5V4H2v16h5m10 0v-5H7v5m10 0H7" />

                                        </svg>

                                        {{ $interestCount }}

                                    </a>

                                </td>


                                {{-- Actions --}}
                                <td class="px-6 py-4">

                                    <div class="flex items-center justify-end gap-2">

                                        {{-- View --}}
                                        <a href="{{ route('admin.opportunities.show', $opportunity) }}"
                                            class="rounded-lg border border-white/10 bg-slate-800 p-2 text-slate-300 transition hover:border-cyan-400/30 hover:text-cyan-300"
                                            title="{{ $t['view'] }}">

                                            <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">

                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />

                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />

                                            </svg>

                                        </a>


                                        {{-- Edit --}}



                                        {{-- Delete --}}


                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="px-6 py-16 text-center">

                                    <div class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ $t['no_data'] }}
                                    </div>

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}
            @if ($opportunities->hasPages())
                <div class="border-t border-white/10 px-6 py-4">
                    {{ $opportunities->withQueryString()->links() }}
                </div>
            @endif

        </div>

    </div>
</x-admin-layout>
