<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';

        $t = [
            'title' => $isAr ? 'تعديل المهمة' : 'Edit Task',
            'subtitle' => $isAr ? 'تعديل بيانات المهمة ومتابعتها' : 'Update task details and follow-up',
            'back' => $isAr ? 'العودة للمهام' : 'Back to Tasks',

            'title_field' => $isAr ? 'عنوان المهمة' : 'Task Title',
            'description' => $isAr ? 'الوصف' : 'Description',
            'customer' => $isAr ? 'العميل' : 'Customer',
            'lead' => $isAr ? 'الفرصة المحتملة' : 'Lead',
            'user' => $isAr ? 'المسؤول' : 'Assigned To',
            'priority' => $isAr ? 'الأولوية' : 'Priority',
            'due_at' => $isAr ? 'موعد الاستحقاق' : 'Due Date',
            'status' => $isAr ? 'الحالة' : 'Status',

            'save' => $isAr ? 'حفظ التغييرات' : 'Save Changes',
            'cancel' => $isAr ? 'إلغاء' : 'Cancel',
            'select' => $isAr ? 'اختر...' : 'Select...',

            'low' => $isAr ? 'منخفضة' : 'Low',
            'medium' => $isAr ? 'متوسطة' : 'Medium',
            'high' => $isAr ? 'عالية' : 'High',
            'urgent' => $isAr ? 'عاجلة' : 'Urgent',

            'pending' => $isAr ? 'قيد الانتظار' : 'Pending',
            'in_progress' => $isAr ? 'قيد التنفيذ' : 'In Progress',
            'completed' => $isAr ? 'مكتملة' : 'Completed',
            'cancelled' => $isAr ? 'ملغاة' : 'Cancelled',
        ];
    @endphp

    <div class="space-y-6" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                    {{ $t['title'] }}
                </h1>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $t['subtitle'] }}
                </p>
            </div>

            <a href="{{ route('admin.tasks.index') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-cyan-600 dark:text-slate-400 dark:hover:text-cyan-400">

                <svg class="h-5 w-5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>

                {{ $t['back'] }}
            </a>

        </div>

        {{-- Form --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">

            <form method="POST" action="{{ route('admin.tasks.update', $task) }}">

                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    {{-- Title --}}
                    <div class="md:col-span-2">

                        <label for="title" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">

                            {{ $t['title_field'] }}

                            <span class="text-red-500">*</span>

                        </label>

                        <input type="text" id="title" name="title" value="{{ old('title', $task->title) }}"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white">

                        @error('title')
                            <p class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- Priority --}}
                    <div>

                        <label for="priority" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">

                            {{ $t['priority'] }}

                            <span class="text-red-500">*</span>

                        </label>

                        <select id="priority" name="priority" required
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white">

                            <option value="low" @selected(old('priority', $task->priority) === 'low')}>
                                {{ $t['low'] }}
                            </option>

                            <option value="medium" @selected(old('priority', $task->priority) === 'medium')}>
                                {{ $t['medium'] }}
                            </option>

                            <option value="high" @selected(old('priority', $task->priority) === 'high')}>
                                {{ $t['high'] }}
                            </option>

                            <option value="urgent" @selected(old('priority', $task->priority) === 'urgent')}>
                                {{ $t['urgent'] }}
                            </option>

                        </select>

                        @error('priority')
                            <p class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- Status --}}
                    <div>

                        <label for="status" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">

                            {{ $t['status'] }}

                            <span class="text-red-500">*</span>

                        </label>

                        <select id="status" name="status" required
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white">

                            <option value="pending" @selected(old('status', $task->status) === 'pending')}>
                                {{ $t['pending'] }}
                            </option>

                            <option value="in_progress" @selected(old('status', $task->status) === 'in_progress')}>
                                {{ $t['in_progress'] }}
                            </option>

                            <option value="completed" @selected(old('status', $task->status) === 'completed')}>
                                {{ $t['completed'] }}
                            </option>

                            <option value="cancelled" @selected(old('status', $task->status) === 'cancelled')}>
                                {{ $t['cancelled'] }}
                            </option>

                        </select>

                        @error('status')
                            <p class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- Customer --}}
                    <div>

                        <label for="customer_id"
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            {{ $t['customer'] }}
                        </label>

                        <select id="customer_id" name="customer_id"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white">

                            <option value="">
                                {{ $t['select'] }}
                            </option>

                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id', $task->customer_id) == $customer->id)>
                                    {{ $customer->company_name }}
                                </option>
                            @endforeach

                        </select>

                        @error('customer_id')
                            <p class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- Lead --}}
                    <div>

                        <label for="lead_id" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            {{ $t['lead'] }}
                        </label>

                        <select id="lead_id" name="lead_id"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white">

                            <option value="">
                                {{ $t['select'] }}
                            </option>

                            @foreach ($leads as $lead)
                                <option value="{{ $lead->id }}" @selected(old('lead_id', $task->lead_id) == $lead->id)>
                                    {{ $lead->name }}
                                </option>
                            @endforeach

                        </select>

                        @error('lead_id')
                            <p class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- User --}}
                    <div>

                        <label for="user_id" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            {{ $t['user'] }}
                        </label>

                        <select id="user_id" name="user_id"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white">

                            <option value="">
                                {{ $t['select'] }}
                            </option>

                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(old('user_id', $task->user_id) == $user->id)>
                                    {{ $user->name }}
                                </option>
                            @endforeach

                        </select>

                        @error('user_id')
                            <p class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- Due At --}}
                    <div>

                        <label for="due_at" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            {{ $t['due_at'] }}
                        </label>

                        <input type="datetime-local" id="due_at" name="due_at"
                            value="{{ old('due_at', $task->due_at?->format('Y-m-d\TH:i')) }}"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white">

                        @error('due_at')
                            <p class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- Description --}}
                    <div class="md:col-span-2">

                        <label for="description"
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            {{ $t['description'] }}
                        </label>

                        <textarea id="description" name="description" rows="6"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('description', $task->description) }}</textarea>

                        @error('description')
                            <p class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

                {{-- Buttons --}}
                <div
                    class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end dark:border-slate-700">

                    <a href="{{ route('admin.tasks.index') }}"
                        class="inline-flex justify-center rounded-xl border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                        {{ $t['cancel'] }}
                    </a>

                    <button type="submit"
                        class="inline-flex justify-center rounded-xl bg-cyan-500 px-6 py-3 text-sm font-semibold text-white transition hover:bg-cyan-600">
                        {{ $t['save'] }}
                    </button>

                </div>

            </form>

        </div>

    </div>
</x-admin-layout>
