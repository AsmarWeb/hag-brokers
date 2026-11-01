@php
    $isAr = app()->getLocale() === 'ar';

    $t = [
        'leads' => $isAr ? 'العملاء المحتملون' : 'Leads',
        'company' => $isAr ? 'الشركة' : 'Company',
        'contact_person' => $isAr ? 'جهة الاتصال' : 'Contact Person',
        'email' => $isAr ? 'البريد الإلكتروني' : 'Email',
        'phone' => $isAr ? 'الهاتف' : 'Phone',
        'source' => $isAr ? 'المصدر' : 'Source',
        'status' => $isAr ? 'الحالة' : 'Status',
        'assigned_to' => $isAr ? 'المسؤول' : 'Assigned To',
        'actions' => $isAr ? 'الإجراءات' : 'Actions',
        'search' => $isAr ? 'البحث عن شركة أو شخص أو بريد...' : 'Search company, person or email...',
        'filter' => $isAr ? 'تصفية' : 'Filter',
        'all' => $isAr ? 'الكل' : 'All',
        'add' => $isAr ? 'إضافة عميل محتمل' : 'Add Lead',
        'view' => $isAr ? 'عرض' : 'View',
        'edit' => $isAr ? 'تعديل' : 'Edit',
        'delete' => $isAr ? 'حذف' : 'Delete',
        'no_leads' => $isAr ? 'لا يوجد عملاء محتملون.' : 'No leads found.',
        'confirm_delete' => $isAr
            ? 'هل أنت متأكد من حذف هذا العميل المحتمل؟'
            : 'Are you sure you want to delete this lead?',
    ];

    $statusLabels = [
        'new' => $isAr ? 'جديد' : 'New',
        'contacted' => $isAr ? 'تم التواصل' : 'Contacted',
        'qualified' => $isAr ? 'مؤهل' : 'Qualified',
        'proposal' => $isAr ? 'عرض سعر' : 'Proposal',
        'won' => $isAr ? 'تم الفوز' : 'Won',
        'lost' => $isAr ? 'خسارة' : 'Lost',
    ];

    $sourceLabels = [
        'website' => $isAr ? 'الموقع الإلكتروني' : 'Website',
        'whatsapp' => 'WhatsApp',
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'referral' => $isAr ? 'إحالة' : 'Referral',
        'phone' => $isAr ? 'هاتف' : 'Phone',
        'email' => $isAr ? 'بريد إلكتروني' : 'Email',
        'meeting' => $isAr ? 'اجتماع' : 'Meeting',
        'exhibition' => $isAr ? 'معرض' : 'Exhibition',
        'other' => $isAr ? 'أخرى' : 'Other',
    ];
@endphp

<x-admin-layout>

    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-2xl font-bold text-white">
                    {{ $t['leads'] }}
                </h1>

                <p class="mt-1 text-sm text-slate-400">
                    {{ $isAr ? 'إدارة العملاء المحتملين ومتابعة فرص المبيعات.' : 'Manage prospects and sales opportunities.' }}
                </p>
            </div>

            <a href="{{ route('admin.leads.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-400 px-5 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-cyan-400/20 transition hover:bg-cyan-300">
                <span class="text-lg">+</span>
                {{ $t['add'] }}
            </a>

        </div>
    </x-slot>

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">

        {{-- Success --}}
        @if (session('success'))
            <div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-4 shadow-xl">

            <form method="GET" action="{{ route('admin.leads.index') }}"
                class="grid grid-cols-1 gap-3 md:grid-cols-4">

                <div class="md:col-span-2">

                    <label for="search" class="sr-only">
                        {{ $t['search'] }}
                    </label>

                    <input id="search" type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ $t['search'] }}"
                        class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white placeholder-slate-500 focus:border-cyan-400 focus:ring-cyan-400">

                </div>

                <select name="status"
                    class="rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white focus:border-cyan-400 focus:ring-cyan-400">
                    <option value="">
                        {{ $t['all'] }} — {{ $t['status'] }}
                    </option>

                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                <select name="source"
                    class="rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white focus:border-cyan-400 focus:ring-cyan-400">
                    <option value="">
                        {{ $t['all'] }} — {{ $t['source'] }}
                    </option>

                    @foreach ($sourceLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('source') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                <div class="flex justify-end gap-2 md:col-span-4">

                    <a href="{{ route('admin.leads.index') }}"
                        class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">
                        {{ $t['all'] }}
                    </a>

                    <button type="submit"
                        class="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-bold text-slate-950 hover:bg-cyan-300">
                        {{ $t['filter'] }}
                    </button>

                </div>

            </form>

        </div>

        {{-- Leads Table --}}
        <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80 shadow-xl">

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-slate-800">

                    <thead class="bg-slate-950/70">

                        <tr class="text-start text-xs font-semibold uppercase tracking-wider text-slate-400">

                            <th class="px-5 py-4">
                                {{ $t['company'] }}
                            </th>

                            <th class="px-5 py-4">
                                {{ $t['contact_person'] }}
                            </th>

                            <th class="px-5 py-4">
                                {{ $t['phone'] }}
                            </th>

                            <th class="px-5 py-4">
                                {{ $t['source'] }}
                            </th>

                            <th class="px-5 py-4">
                                {{ $t['status'] }}
                            </th>

                            <th class="px-5 py-4">
                                {{ $t['assigned_to'] }}
                            </th>

                            <th class="px-5 py-4 text-end">
                                {{ $t['actions'] }}
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-800">

                        @forelse($leads as $lead)
                            @php

                                $statusClass = match ($lead->status) {
                                    'won' => 'bg-emerald-400/10 text-emerald-300 ring-emerald-400/20',

                                    'lost' => 'bg-rose-400/10 text-rose-300 ring-rose-400/20',

                                    'qualified' => 'bg-cyan-400/10 text-cyan-300 ring-cyan-400/20',

                                    'proposal' => 'bg-violet-400/10 text-violet-300 ring-violet-400/20',

                                    'contacted' => 'bg-amber-400/10 text-amber-300 ring-amber-400/20',

                                    default => 'bg-slate-700/40 text-slate-300 ring-slate-600',
                                };

                            @endphp

                            <tr class="transition hover:bg-slate-800/40">

                                <td class="whitespace-nowrap px-5 py-4">

                                    <a href="{{ route('admin.leads.show', $lead) }}"
                                        class="font-semibold text-white hover:text-cyan-300">
                                        {{ $lead->company_name }}
                                    </a>

                                    @if ($lead->email)
                                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            {{ $lead->email }}
                                        </div>
                                    @endif

                                </td>

                                <td class="px-5 py-4 text-sm text-slate-300">
                                    {{ $lead->contact_person ?: '—' }}
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-300">
                                    {{ $lead->phone ?: '—' }}
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-300">
                                    {{ $sourceLabels[$lead->source] ?? ($lead->source ?? '—') }}
                                </td>

                                <td class="px-5 py-4">

                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusClass }}">
                                        {{ $statusLabels[$lead->status] ?? $lead->status }}
                                    </span>

                                </td>

                                <td class="px-5 py-4 text-sm text-slate-300">
                                    {{ optional($lead->assignedTo)->name ?: '—' }}
                                </td>

                                <td class="px-5 py-4">

                                    <div class="flex justify-end gap-2">

                                        <a href="{{ route('admin.leads.show', $lead) }}"
                                            class="rounded-lg border border-slate-700 px-3 py-2 text-xs font-semibold text-slate-300 hover:border-cyan-400 hover:text-cyan-300">
                                            {{ $t['view'] }}
                                        </a>

                                        <a href="{{ route('admin.leads.edit', $lead) }}"
                                            class="rounded-lg border border-slate-700 px-3 py-2 text-xs font-semibold text-slate-300 hover:border-cyan-400 hover:text-cyan-300">
                                            {{ $t['edit'] }}
                                        </a>

                                        <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}"
                                            onsubmit="return confirm(@js($t['confirm_delete']))">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="rounded-lg border border-rose-400/20 px-3 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-400/10">
                                                {{ $t['delete'] }}
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7" class="px-5 py-14 text-center text-sm text-slate-500 dark:text-slate-400">
                                    {{ $t['no_leads'] }}
                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            @if ($leads->hasPages())
                <div class="border-t border-slate-800 px-5 py-4">

                    {{ $leads->withQueryString()->links() }}

                </div>
            @endif

        </div>

    </div>

</x-admin-layout>
