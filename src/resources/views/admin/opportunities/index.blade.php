{{-- resources/views/admin/opportunities/index.blade.php --}}

<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';

        $t = [
            'title' => $isAr ? 'الفرص' : 'Opportunities',
            'subtitle' => $isAr
                ? 'إدارة فرص المبيعات والصفقات المحتملة.'
                : 'Manage sales opportunities and potential deals.',
            'add' => $isAr ? 'إضافة فرصة' : 'Add Opportunity',
            'search' => $isAr ? 'بحث...' : 'Search...',
            'customer' => $isAr ? 'العميل' : 'Customer',
            'stage' => $isAr ? 'المرحلة' : 'Stage',
            'all' => $isAr ? 'الكل' : 'All',
            'title_col' => $isAr ? 'الفرصة' : 'Opportunity',
            'contact' => $isAr ? 'جهة الاتصال' : 'Contact',
            'value' => $isAr ? 'القيمة' : 'Value',
            'probability' => $isAr ? 'الاحتمالية' : 'Probability',
            'expected_close' => $isAr ? 'الإغلاق المتوقع' : 'Expected Close',
            'actions' => $isAr ? 'الإجراءات' : 'Actions',
            'view' => $isAr ? 'عرض' : 'View',
            'edit' => $isAr ? 'تعديل' : 'Edit',
            'delete' => $isAr ? 'حذف' : 'Delete',
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
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">
                    {{ $t['title'] }}
                </h1>

                <p class="mt-1 text-sm text-slate-400">
                    {{ $t['subtitle'] }}
                </p>
            </div>

            <a href="{{ route('admin.opportunities.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-400 px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-cyan-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>

                {{ $t['add'] }}
            </a>
        </div>

        {{-- Filters --}}
        <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4 shadow-xl">
            <form method="GET" action="{{ route('admin.opportunities.index') }}"
                class="grid grid-cols-1 gap-4 md:grid-cols-3">

                {{-- Search --}}
                <div>
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
                        <option value="">{{ $t['all'] }}</option>

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
                        <option value="">{{ $t['all'] }}</option>

                        @foreach ($stages as $key => $label)
                            <option value="{{ $key }}" @selected(request('stage') === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3 flex justify-end gap-3">
                    <a href="{{ route('admin.opportunities.index') }}"
                        class="rounded-xl border border-white/10 bg-slate-800 px-5 py-3 text-sm font-semibold text-slate-300 transition hover:bg-slate-700">
                        {{ $isAr ? 'إعادة تعيين' : 'Reset' }}
                    </a>

                    <button type="submit"
                        class="rounded-xl bg-cyan-400 px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-cyan-300">
                        {{ $isAr ? 'بحث' : 'Filter' }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Flash --}}
        @if (session('success'))
            <div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        {{-- Table --}}
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
                                {{ $t['customer'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ $t['contact'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ $t['value'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ $t['stage'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-400">
                                {{ $t['probability'] }}
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
                            @endphp

                            <tr class="transition hover:bg-white/[0.03]">

                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.opportunities.show', $opportunity) }}"
                                        class="font-semibold text-white transition hover:text-cyan-300">
                                        {{ $opportunity->title }}
                                    </a>

                                    @if ($opportunity->cargo_type)
                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ $opportunity->cargo_type }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-300">
                                    {{ $opportunity->customer?->company_name ?? '—' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-400">
                                    {{ $opportunity->contact?->name ?? '—' }}
                                </td>

                                <td class="px-6 py-4 text-sm font-semibold text-white">
                                    @if ($opportunity->estimated_value !== null)
                                        {{ number_format((float) $opportunity->estimated_value, 2) }}
                                        {{ $opportunity->currency ?? 'USD' }}
                                    @else
                                        —
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $stageClass }}">
                                        {{ $stages[$opportunity->stage] ?? ucfirst($opportunity->stage) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-300">
                                    {{ $opportunity->probability !== null ? $opportunity->probability . '%' : '—' }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">

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

                                        <a href="{{ route('admin.opportunities.edit', $opportunity) }}"
                                            class="rounded-lg border border-white/10 bg-slate-800 p-2 text-slate-300 transition hover:border-amber-400/30 hover:text-amber-300"
                                            title="{{ $t['edit'] }}">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </a>

                                        <form method="POST"
                                            action="{{ route('admin.opportunities.destroy', $opportunity) }}"
                                            onsubmit="return confirm('{{ $t['confirm_delete'] }}')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="rounded-lg border border-white/10 bg-slate-800 p-2 text-slate-300 transition hover:border-red-400/30 hover:text-red-300"
                                                title="{{ $t['delete'] }}">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-9 0h14" />
                                                </svg>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="text-sm text-slate-500">
                                        {{ $t['no_data'] }}
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($opportunities->hasPages())
                <div class="border-t border-white/10 px-6 py-4">
                    {{ $opportunities->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
