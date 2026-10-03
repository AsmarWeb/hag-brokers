<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';

        $t = [
            'title' => $isAr ? 'المهام' : 'Tasks',
            'subtitle' => $isAr ? 'إدارة ومتابعة جميع المهام' : 'Manage and track all tasks',
            'add' => $isAr ? 'إضافة مهمة' : 'Add Task',
            'search' => $isAr ? 'بحث...' : 'Search...',
            'all_statuses' => $isAr ? 'كل الحالات' : 'All Statuses',
            'all_priorities' => $isAr ? 'كل الأولويات' : 'All Priorities',
            'title_field' => $isAr ? 'المهمة' : 'Task',
            'customer' => $isAr ? 'العميل' : 'Customer',
            'lead' => $isAr ? 'الفرصة المحتملة' : 'Lead',
            'assigned_to' => $isAr ? 'المسؤول' : 'Assigned To',
            'priority' => $isAr ? 'الأولوية' : 'Priority',
            'due_at' => $isAr ? 'موعد الاستحقاق' : 'Due At',
            'status' => $isAr ? 'الحالة' : 'Status',
            'actions' => $isAr ? 'الإجراءات' : 'Actions',
            'view' => $isAr ? 'عرض' : 'View',
            'edit' => $isAr ? 'تعديل' : 'Edit',
            'delete' => $isAr ? 'حذف' : 'Delete',
            'no_data' => $isAr ? 'لا توجد مهام' : 'No tasks found',
            'confirm_delete' => $isAr
                ? 'هل أنت متأكد من حذف هذه المهمة؟'
                : 'Are you sure you want to delete this task?',

            'low' => $isAr ? 'منخفضة' : 'Low',
            'medium' => $isAr ? 'متوسطة' : 'Medium',
            'high' => $isAr ? 'عالية' : 'High',
            'urgent' => $isAr ? 'عاجلة' : 'Urgent',

            'pending' => $isAr ? 'قيد الانتظار' : 'Pending',
            'in_progress' => $isAr ? 'قيد التنفيذ' : 'In Progress',
            'completed' => $isAr ? 'مكتملة' : 'Completed',
            'cancelled' => $isAr ? 'ملغاة' : 'Cancelled',
        ];

        $priorityLabels = [
            'low' => $t['low'],
            'medium' => $t['medium'],
            'high' => $t['high'],
            'urgent' => $t['urgent'],
        ];

        $statusLabels = [
            'pending' => $t['pending'],
            'in_progress' => $t['in_progress'],
            'completed' => $t['completed'],
            'cancelled' => $t['cancelled'],
        ];
    @endphp

    <div class="space-y-6" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                    {{ $t['title'] }}
                </h1>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $t['subtitle'] }}
                </p>
            </div>

            <a href="{{ route('admin.tasks.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-600">

                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>

                {{ $t['add'] }}
            </a>
        </div>

        {{-- Filters --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">

            <form method="GET" action="{{ route('admin.tasks.index') }}"
                class="grid grid-cols-1 gap-4 md:grid-cols-4">

                {{-- Search --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        {{ $t['search'] }}
                    </label>

                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ $t['search'] }}"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                </div>

                {{-- Priority --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        {{ $t['priority'] }}
                    </label>

                    <select name="priority"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                        <option value="">
                            {{ $t['all_priorities'] }}
                        </option>

                        @foreach ($priorityLabels as $value => $label)
                            <option value="{{ $value }}" @selected(request('priority') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        {{ $t['status'] }}
                    </label>

                    <select name="status"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                        <option value="">
                            {{ $t['all_statuses'] }}
                        </option>

                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Search Button --}}
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-cyan-500 dark:hover:bg-cyan-600">
                        {{ $isAr ? 'بحث' : 'Search' }}
                    </button>
                </div>

            </form>
        </div>

        {{-- Table --}}
        <div
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">

                    <thead class="bg-slate-50 dark:bg-slate-900/60">
                        <tr>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-500">
                                #
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-500">
                                {{ $t['title_field'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-500">
                                {{ $t['customer'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-500">
                                {{ $t['lead'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-500">
                                {{ $t['assigned_to'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-500">
                                {{ $t['priority'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-500">
                                {{ $t['due_at'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-start text-xs font-semibold uppercase tracking-wider text-slate-500">
                                {{ $t['status'] }}
                            </th>

                            <th
                                class="px-6 py-4 text-end text-xs font-semibold uppercase tracking-wider text-slate-500">
                                {{ $t['actions'] }}
                            </th>

                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">

                        @forelse($tasks as $task)
                            @php
                                $priorityClass = match ($task->priority) {
                                    'urgent' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                    'high'
                                        => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                    'medium' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                    default => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                                };

                                $statusClass = match ($task->status) {
                                    'completed'
                                        => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                    'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                    'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    default => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                };
                            @endphp

                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-700/30">

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                    {{ $task->id }}
                                </td>

                                <td class="px-6 py-4">

                                    <a href="{{ route('admin.tasks.show', $task) }}"
                                        class="font-semibold text-slate-900 hover:text-cyan-600 dark:text-white dark:hover:text-cyan-400">
                                        {{ $task->title }}
                                    </a>

                                    @if ($task->description)
                                        <p class="mt-1 max-w-xs truncate text-xs text-slate-500">
                                            {{ $task->description }}
                                        </p>
                                    @endif

                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700 dark:text-slate-300">
                                    {{ $task->customer?->company_name ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700 dark:text-slate-300">
                                    {{ $task->lead?->name ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700 dark:text-slate-300">
                                    {{ $task->user?->name ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4">

                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $priorityClass }}">
                                        {{ $priorityLabels[$task->priority] ?? ucfirst($task->priority) }}
                                    </span>

                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                    {{ $task->due_at?->format('Y-m-d H:i') ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4">

                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ $statusLabels[$task->status] ?? ucfirst($task->status) }}
                                    </span>

                                </td>

                                <td class="whitespace-nowrap px-6 py-4">

                                    <div class="flex items-center justify-end gap-2">

                                        {{-- View --}}
                                        <a href="{{ route('admin.tasks.show', $task) }}" title="{{ $t['view'] }}"
                                            class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-cyan-600 dark:hover:bg-slate-700">

                                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>

                                        </a>

                                        {{-- Edit --}}
                                        <a href="{{ route('admin.tasks.edit', $task) }}" title="{{ $t['edit'] }}"
                                            class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-700">

                                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>

                                        </a>

                                        {{-- Delete --}}
                                        <form method="POST" action="{{ route('admin.tasks.destroy', $task) }}"
                                            onsubmit="return confirm('{{ $t['confirm_delete'] }}');">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" title="{{ $t['delete'] }}"
                                                class="rounded-lg p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor"
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
                                <td colspan="9" class="px-6 py-16 text-center">

                                    <div class="flex flex-col items-center justify-center">

                                        <svg class="mb-4 h-12 w-12 text-slate-300 dark:text-slate-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" />
                                        </svg>

                                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                            {{ $t['no_data'] }}
                                        </p>

                                    </div>

                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            @if ($tasks->hasPages())
                <div class="border-t border-slate-200 px-6 py-4 dark:border-slate-700">
                    {{ $tasks->withQueryString()->links() }}
                </div>
            @endif

        </div>

    </div>
</x-admin-layout>
