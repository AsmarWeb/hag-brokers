<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';

        $t = [
            'title' => $isAr ? 'تفاصيل النشاط' : 'Activity Details',
            'back' => $isAr ? 'العودة للأنشطة' : 'Back to Activities',
            'edit' => $isAr ? 'تعديل' : 'Edit',
            'delete' => $isAr ? 'حذف' : 'Delete',
            'subject' => $isAr ? 'الموضوع' : 'Subject',
            'description' => $isAr ? 'الوصف' : 'Description',
            'customer' => $isAr ? 'العميل' : 'Customer',
            'lead' => $isAr ? 'الفرصة المحتملة' : 'Lead',
            'user' => $isAr ? 'المسؤول' : 'Assigned To',
            'type' => $isAr ? 'نوع النشاط' : 'Activity Type',
            'due_at' => $isAr ? 'موعد الاستحقاق' : 'Due Date',
            'completed_at' => $isAr ? 'تاريخ الإنجاز' : 'Completed At',
            'status' => $isAr ? 'الحالة' : 'Status',
            'created_at' => $isAr ? 'تاريخ الإنشاء' : 'Created At',
            'updated_at' => $isAr ? 'آخر تحديث' : 'Updated At',

            'call' => $isAr ? 'مكالمة' : 'Call',
            'email' => $isAr ? 'بريد إلكتروني' : 'Email',
            'meeting' => $isAr ? 'اجتماع' : 'Meeting',
            'task' => $isAr ? 'مهمة' : 'Task',
            'note' => $isAr ? 'ملاحظة' : 'Note',

            'pending' => $isAr ? 'قيد الانتظار' : 'Pending',
            'completed' => $isAr ? 'مكتمل' : 'Completed',
            'cancelled' => $isAr ? 'ملغي' : 'Cancelled',

            'confirm_delete' => $isAr
                ? 'هل أنت متأكد من حذف هذا النشاط؟'
                : 'Are you sure you want to delete this activity?',
        ];

        $typeLabels = [
            'call' => $t['call'],
            'email' => $t['email'],
            'meeting' => $t['meeting'],
            'task' => $t['task'],
            'note' => $t['note'],
        ];

        $statusLabels = [
            'pending' => $t['pending'],
            'completed' => $t['completed'],
            'cancelled' => $t['cancelled'],
        ];

        $statusClass = match ($activity->status) {
            'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            default => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        };
    @endphp

    <div class="space-y-6" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <div class="mb-2 flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('admin.activities.index') }}" class="hover:text-cyan-600">
                        {{ $t['back'] }}
                    </a>

                    <span>/</span>

                    <span>{{ $activity->subject }}</span>
                </div>

                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                    {{ $t['title'] }}
                </h1>
            </div>

            <div class="flex flex-wrap gap-2">

                <a href="{{ route('admin.activities.edit', $activity) }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>

                    {{ $t['edit'] }}
                </a>

                <form method="POST" action="{{ route('admin.activities.destroy', $activity) }}"
                    onsubmit="return confirm('{{ $t['confirm_delete'] }}');">

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-red-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-9 0h14" />
                        </svg>

                        {{ $t['delete'] }}
                    </button>

                </form>
            </div>
        </div>

        {{-- Main Card --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Main Information --}}
            <div class="lg:col-span-2">

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">

                    <div class="mb-6 flex items-start justify-between gap-4">

                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                {{ $t['subject'] }}
                            </p>

                            <h2 class="mt-1 text-xl font-bold text-slate-900 dark:text-white">
                                {{ $activity->subject }}
                            </h2>
                        </div>

                        <span class="shrink-0 rounded-full px-3 py-1.5 text-xs font-semibold {{ $statusClass }}">
                            {{ $statusLabels[$activity->status] ?? ucfirst($activity->status) }}
                        </span>

                    </div>

                    <div class="border-t border-slate-200 pt-6 dark:border-slate-700">

                        <p class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $t['description'] }}
                        </p>

                        <div
                            class="rounded-xl bg-slate-50 p-5 text-sm leading-7 text-slate-700 dark:bg-slate-900 dark:text-slate-300">

                            @if ($activity->description)
                                {!! nl2br(e($activity->description)) !!}
                            @else
                                <span class="text-slate-400">
                                    —
                                </span>
                            @endif

                        </div>

                    </div>

                </div>

            </div>

            {{-- Side Information --}}
            <div class="space-y-6">

                {{-- Type --}}
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">

                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        {{ $t['type'] }}
                    </p>

                    <span
                        class="inline-flex rounded-lg bg-cyan-100 px-3 py-1.5 text-sm font-semibold text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400">
                        {{ $typeLabels[$activity->type] ?? ucfirst($activity->type) }}
                    </span>

                </div>

                {{-- Relationships --}}
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">

                    <h3 class="mb-5 text-sm font-bold text-slate-900 dark:text-white">
                        {{ $isAr ? 'الارتباطات' : 'Relationships' }}
                    </h3>

                    <div class="space-y-5">

                        <div>
                            <p class="text-xs text-slate-400">
                                {{ $t['customer'] }}
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200">
                                {{ $activity->customer?->company_name ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                {{ $t['lead'] }}
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200">
                                {{ $activity->lead?->name ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                {{ $t['user'] }}
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200">
                                {{ $activity->user?->name ?? '—' }}
                            </p>
                        </div>

                    </div>

                </div>

                {{-- Dates --}}
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">

                    <h3 class="mb-5 text-sm font-bold text-slate-900 dark:text-white">
                        {{ $isAr ? 'التواريخ' : 'Dates' }}
                    </h3>

                    <div class="space-y-5">

                        <div>
                            <p class="text-xs text-slate-400">
                                {{ $t['due_at'] }}
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200">
                                {{ $activity->due_at?->format('Y-m-d H:i') ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                {{ $t['completed_at'] }}
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200">
                                {{ $activity->completed_at?->format('Y-m-d H:i') ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                {{ $t['created_at'] }}
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200">
                                {{ $activity->created_at?->format('Y-m-d H:i') ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                {{ $t['updated_at'] }}
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200">
                                {{ $activity->updated_at?->format('Y-m-d H:i') ?? '—' }}
                            </p>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
</x-admin-layout>
