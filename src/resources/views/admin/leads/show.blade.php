@php
    $isAr = app()->getLocale() === 'ar';

    $t = [
        'lead' => $isAr ? 'العميل المحتمل' : 'Lead',
        'details' => $isAr ? 'التفاصيل' : 'Details',
        'company' => $isAr ? 'الشركة' : 'Company',
        'contact_person' => $isAr ? 'جهة الاتصال' : 'Contact Person',
        'email' => $isAr ? 'البريد الإلكتروني' : 'Email',
        'phone' => $isAr ? 'الهاتف' : 'Phone',
        'country' => $isAr ? 'الدولة' : 'Country',
        'city' => $isAr ? 'المدينة' : 'City',
        'source' => $isAr ? 'المصدر' : 'Source',
        'status' => $isAr ? 'الحالة' : 'Status',
        'assigned_to' => $isAr ? 'المسؤول' : 'Assigned To',
        'notes' => $isAr ? 'ملاحظات' : 'Notes',
        'created' => $isAr ? 'تاريخ الإنشاء' : 'Created',
        'updated' => $isAr ? 'آخر تحديث' : 'Updated',
        'activities' => $isAr ? 'الأنشطة' : 'Activities',
        'back' => $isAr ? 'رجوع' : 'Back',
        'edit' => $isAr ? 'تعديل' : 'Edit',
        'delete' => $isAr ? 'حذف' : 'Delete',
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

    $statusClass = match ($lead->status) {
        'won' => 'bg-emerald-400/10 text-emerald-300 ring-emerald-400/20',
        'lost' => 'bg-rose-400/10 text-rose-300 ring-rose-400/20',
        'qualified' => 'bg-cyan-400/10 text-cyan-300 ring-cyan-400/20',
        'proposal' => 'bg-violet-400/10 text-violet-300 ring-violet-400/20',
        'contacted' => 'bg-amber-400/10 text-amber-300 ring-amber-400/20',
        default => 'bg-slate-700/40 text-slate-300 ring-slate-600',
    };

    $progress =
        [
            'new' => 10,
            'contacted' => 30,
            'qualified' => 50,
            'proposal' => 70,
            'won' => 100,
            'lost' => 100,
        ][$lead->status] ?? 10;
@endphp

<x-admin-layout>

    <x-slot name="header">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <div class="flex flex-wrap items-center gap-3">

                    <h1 class="text-2xl font-bold text-white">
                        {{ $lead->company_name }}
                    </h1>

                    <span class="rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusClass }}">
                        {{ $statusLabels[$lead->status] ?? $lead->status }}
                    </span>

                </div>

                <p class="mt-1 text-sm text-slate-400">
                    {{ $lead->contact_person ?: $t['lead'] }}
                </p>

            </div>

            <div class="flex gap-2">

                <a href="{{ route('admin.leads.index') }}"
                    class="rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">
                    {{ $t['back'] }}
                </a>

                <a href="{{ route('admin.leads.edit', $lead) }}"
                    class="rounded-xl bg-cyan-400 px-4 py-2.5 text-sm font-bold text-slate-950 hover:bg-cyan-300">
                    {{ $t['edit'] }}
                </a>

            </div>

        </div>

    </x-slot>

    <div class="space-y-6 p-4 sm:p-6 lg:p-8">

        @if (session('success'))
            <div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Details --}}
            <section class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl lg:col-span-2">

                <h2 class="mb-6 text-lg font-bold text-white">
                    {{ $t['details'] }}
                </h2>

                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            {{ $t['company'] }}
                        </dt>

                        <dd class="mt-1 text-sm font-semibold text-white">
                            {{ $lead->company_name }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            {{ $t['contact_person'] }}
                        </dt>

                        <dd class="mt-1 text-sm text-slate-300">
                            {{ $lead->contact_person ?: '—' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            {{ $t['email'] }}
                        </dt>

                        <dd class="mt-1 text-sm text-slate-300" dir="ltr">
                            {{ $lead->email ?: '—' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            {{ $t['phone'] }}
                        </dt>

                        <dd class="mt-1 text-sm text-slate-300" dir="ltr">
                            {{ $lead->phone ?: '—' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            {{ $t['country'] }}
                        </dt>

                        <dd class="mt-1 text-sm text-slate-300">
                            {{ $lead->country_code ?: '—' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            {{ $t['city'] }}
                        </dt>

                        <dd class="mt-1 text-sm text-slate-300">
                            {{ $lead->city ?: '—' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            {{ $t['source'] }}
                        </dt>

                        <dd class="mt-1 text-sm text-slate-300">
                            {{ $sourceLabels[$lead->source] ?? ($lead->source ?? '—') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            {{ $t['assigned_to'] }}
                        </dt>

                        <dd class="mt-1 text-sm text-slate-300">
                            {{ optional($lead->assignedTo)->name ?: '—' }}
                        </dd>
                    </div>

                    <div class="sm:col-span-2">

                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            {{ $t['notes'] }}
                        </dt>

                        <dd
                            class="mt-2 whitespace-pre-line rounded-xl border border-slate-800 bg-slate-950/70 p-4 text-sm leading-6 text-slate-300">
                            {{ $lead->notes ?: '—' }}
                        </dd>

                    </div>

                </dl>

            </section>

            {{-- Sidebar --}}
            <aside class="space-y-6">

                {{-- Status --}}
                <section class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl">

                    <h2 class="mb-5 text-lg font-bold text-white">
                        {{ $t['status'] }}
                    </h2>

                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusClass }}">
                        {{ $statusLabels[$lead->status] ?? $lead->status }}
                    </span>

                    <div class="mt-5">

                        <div class="h-2 overflow-hidden rounded-full bg-slate-800">

                            <div class="h-full rounded-full bg-cyan-400" style="width: {{ $progress }}%"></div>

                        </div>

                    </div>

                </section>

                {{-- Dates --}}
                <section class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl">

                    <h2 class="mb-5 text-lg font-bold text-white">
                        {{ $t['created'] }}
                    </h2>

                    <p class="text-sm text-slate-300">
                        {{ optional($lead->created_at)->format('Y-m-d H:i') ?: '—' }}
                    </p>

                    <div class="mt-5 border-t border-slate-800 pt-5">

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            {{ $t['updated'] }}
                        </p>

                        <p class="mt-1 text-sm text-slate-300">
                            {{ optional($lead->updated_at)->format('Y-m-d H:i') ?: '—' }}
                        </p>

                    </div>

                </section>

                {{-- Delete --}}
                <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}"
                    onsubmit="return confirm(@js($t['confirm_delete']))">

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        class="w-full rounded-xl border border-rose-400/20 bg-rose-400/5 px-5 py-3 text-sm font-bold text-rose-300 hover:bg-rose-400/10">
                        {{ $t['delete'] }}
                    </button>

                </form>

            </aside>

        </div>

        {{-- Activities --}}
        @if ($lead->activities && $lead->activities->count())

            <section class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl">

                <h2 class="mb-5 text-lg font-bold text-white">
                    {{ $t['activities'] }}
                </h2>

                <div class="space-y-3">

                    @foreach ($lead->activities as $activity)
                        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">

                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">

                                <span class="font-semibold text-white">
                                    {{ $activity->subject }}
                                </span>

                                <span class="text-xs text-slate-500">
                                    {{ optional($activity->due_at)->format('Y-m-d H:i') ?: '—' }}
                                </span>

                            </div>

                            @if ($activity->description)
                                <p class="mt-2 text-sm text-slate-400">
                                    {{ $activity->description }}
                                </p>
                            @endif

                        </div>
                    @endforeach

                </div>

            </section>

        @endif

    </div>

</x-admin-layout>
