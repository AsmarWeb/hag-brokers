@php
    $isAr = app()->getLocale() === 'ar';
    $dir = $isAr ? 'rtl' : 'ltr';

    $t = [
        'title' => $isAr ? 'إرسال إشعار' : 'Send Notification',
        'subtitle' => $isAr
            ? 'إرسال رسالة مباشرة إلى أحد مستخدمي HAG Brokers.'
            : 'Send a direct message to a HAG Brokers user.',

        'recipient' => $isAr ? 'المستلم' : 'Recipient',
        'select_user' => $isAr ? 'اختر المستخدم' : 'Select user',
        'notification_title' => $isAr ? 'عنوان الإشعار' : 'Notification title',
        'message' => $isAr ? 'الرسالة' : 'Message',
        'optional_url' => $isAr ? 'رابط اختياري' : 'Optional URL',
        'url_help' => $isAr
            ? 'يمكنك إضافة رابط يفتح عند الضغط على زر فتح الإشعار.'
            : 'Add a link that the user can open from the notification.',

        'send' => $isAr ? 'إرسال الإشعار' : 'Send Notification',
        'cancel' => $isAr ? 'إلغاء' : 'Cancel',
        'back' => $isAr ? 'الإشعارات' : 'Notifications',

        'recipient_help' => $isAr
            ? 'اختر المستخدم الذي سيستلم هذا الإشعار.'
            : 'Choose the user who should receive this notification.',

        'preview' => $isAr ? 'معاينة الإشعار' : 'Notification Preview',
        'preview_title' => $isAr ? 'عنوان الإشعار' : 'Notification title',
        'preview_message' => $isAr ? 'ستظهر رسالتك هنا...' : 'Your message will appear here...',

        'no_users' => $isAr ? 'لا يوجد مستخدمون متاحون.' : 'No users available.',

        'required' => $isAr ? 'مطلوب' : 'Required',
    ];
@endphp

<x-admin-layout>

    <x-slot name="header">
        <div dir="{{ $dir }}" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-400 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/20">
                        HAG
                    </div>

                    <div>
                        <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            {{ $t['title'] }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ $t['subtitle'] }}
                        </p>
                    </div>
                </div>
            </div>

            <a href="{{ route('admin.notifications.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-cyan-300 hover:text-cyan-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-cyan-500 dark:hover:text-cyan-400">
                <span class="{{ $isAr ? 'order-2 ms-2' : 'me-2' }}">
                    {{ $isAr ? '→' : '←' }}
                </span>

                {{ $t['back'] }}
            </a>
        </div>
    </x-slot>

    <div dir="{{ $dir }}" class="min-h-screen bg-slate-50 dark:bg-slate-950">
        <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

            {{-- Success --}}
            @if (session('success'))
                <div
                    class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>

                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Validation errors --}}
            @if ($errors->any())
                <div
                    class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 dark:border-red-900/50 dark:bg-red-950/20">
                    <div class="flex gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01M10.29 3.86l-7.82 14a2 2 0 001.75 2.64h15.56a2 2 0 001.75-2.64l-7.82-14a2 2 0 00-3.67 0z" />
                        </svg>

                        <div>
                            <p class="font-bold text-red-800 dark:text-red-300">
                                {{ $isAr ? 'يرجى تصحيح الأخطاء التالية:' : 'Please correct the following errors:' }}
                            </p>

                            <ul class="mt-2 list-disc space-y-1 ps-5 text-sm text-red-700 dark:text-red-400">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Hero --}}
            <section
                class="relative overflow-hidden rounded-3xl border border-slate-800 bg-slate-950 p-6 shadow-xl sm:p-8">
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cyan-500/10 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-20 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl"></div>

                <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center">
                    <div
                        class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl border border-cyan-400/20 bg-cyan-400/10">
                        <svg class="h-8 w-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" />
                        </svg>
                    </div>

                    <div>
                        <div
                            class="mb-2 inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-cyan-300">
                            <span class="h-2 w-2 rounded-full bg-cyan-400"></span>
                            HAG Brokers
                        </div>

                        <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                            {{ $t['title'] }}
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-400 sm:text-base">
                            {{ $t['subtitle'] }}
                        </p>
                    </div>
                </div>
            </section>

            {{-- Main --}}
            <div x-data="{
                title: @js(old('title', '')),
                message: @js(old('message', '')),
                selectedUser: @js(old('user_id', '')),
            }" class="grid gap-6 lg:grid-cols-[1fr_360px]">

                {{-- Form --}}
                <section
                    class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-5 py-5 dark:border-slate-800 sm:px-6">
                        <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
                            {{ $t['title'] }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ $isAr
                                ? 'أدخل بيانات الإشعار ثم أرسله مباشرة إلى المستخدم.'
                                : 'Enter the notification details and send it directly to the user.' }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('admin.notifications.store') }}"
                        class="space-y-6 p-5 sm:p-6">
                        @csrf

                        {{-- Recipient --}}
                        <div>
                            <label for="user_id"
                                class="mb-2 block text-sm font-bold text-slate-800 dark:text-slate-200">
                                {{ $t['recipient'] }}

                                <span class="text-red-500">*</span>
                            </label>

                            <select id="user_id" name="user_id" x-model="selectedUser" required
                                class="block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-400 focus:ring-cyan-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                <option value="">
                                    {{ $t['select_user'] }}
                                </option>

                                @forelse ($users as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name ?: $user->email }}
                                        — {{ $user->email }}
                                    </option>
                                @empty
                                    <option value="">
                                        {{ $t['no_users'] }}
                                    </option>
                                @endforelse
                            </select>

                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                {{ $t['recipient_help'] }}
                            </p>

                            @error('user_id')
                                <p class="mt-2 text-xs font-semibold text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Title --}}
                        <div>
                            <label for="title"
                                class="mb-2 block text-sm font-bold text-slate-800 dark:text-slate-200">
                                {{ $t['notification_title'] }}

                                <span class="text-red-500">*</span>
                            </label>

                            <input id="title" type="text" name="title" value="{{ old('title') }}"
                                x-model="title" maxlength="255" required
                                placeholder="{{ $isAr ? 'مثال: تحديث مهم على طلبك' : 'Example: Important update about your request' }}"
                                class="block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-cyan-400 focus:ring-cyan-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-600">

                            @error('title')
                                <p class="mt-2 text-xs font-semibold text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Message --}}
                        <div>
                            <label for="message"
                                class="mb-2 block text-sm font-bold text-slate-800 dark:text-slate-200">
                                {{ $t['message'] }}

                                <span class="text-red-500">*</span>
                            </label>

                            <textarea id="message" name="message" rows="6" maxlength="5000" x-model="message" required
                                placeholder="{{ $isAr ? 'اكتب الرسالة التي تريد إرسالها إلى المستخدم...' : 'Write the message you want to send to the user...' }}"
                                class="block w-full resize-y rounded-xl border-slate-300 bg-white px-4 py-3 text-sm leading-6 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-cyan-400 focus:ring-cyan-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-600"></textarea>

                            <div class="mt-2 flex items-center justify-between">
                                @error('message')
                                    <p class="text-xs font-semibold text-red-500">
                                        {{ $message }}
                                    </p>
                                @else
                                    <span></span>
                                @enderror

                                <span class="text-xs text-slate-400">
                                    <span x-text="message.length"></span>/5000
                                </span>
                            </div>
                        </div>

                        {{-- URL --}}
                        <div>
                            <label for="url"
                                class="mb-2 block text-sm font-bold text-slate-800 dark:text-slate-200">
                                {{ $t['optional_url'] }}
                            </label>

                            <input id="url" type="text" name="url" value="{{ old('url') }}"
                                placeholder="{{ $isAr ? 'مثال: /shipments/123' : 'Example: /shipments/123' }}"
                                class="block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-cyan-400 focus:ring-cyan-400 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-600">

                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                {{ $t['url_help'] }}
                            </p>

                            @error('url')
                                <p class="mt-2 text-xs font-semibold text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Actions --}}
                        <div
                            class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 dark:border-slate-800 sm:flex-row sm:justify-end">
                            <a href="{{ route('admin.notifications.index') }}"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-cyan-300 hover:text-cyan-600 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:border-cyan-500 dark:hover:text-cyan-400">
                                {{ $t['cancel'] }}
                            </a>

                            <button type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-cyan-500 px-6 py-3 text-sm font-black text-slate-950 shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-slate-900">
                                <svg class="me-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M22 2L11 13" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M22 2l-7 20-4-9-9-4 20-7z" />
                                </svg>

                                {{ $t['send'] }}
                            </button>
                        </div>
                    </form>
                </section>

                {{-- Preview --}}
                <aside class="space-y-6">

                    <section
                        class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-5 py-5 dark:border-slate-800">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-100 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                </div>

                                <div>
                                    <h3 class="font-extrabold text-slate-900 dark:text-white">
                                        {{ $t['preview'] }}
                                    </h3>

                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        HAG Brokers
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-5">
                            <div
                                class="rounded-2xl border border-cyan-200 bg-cyan-50/60 p-4 dark:border-cyan-900/40 dark:bg-cyan-950/20">

                                <div class="flex gap-3">
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-500 text-slate-950">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-extrabold text-slate-900 dark:text-white"
                                            x-text="title || '{{ $t['preview_title'] }}'"></p>

                                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400"
                                            x-text="message || '{{ $t['preview_message'] }}'"></p>

                                        <div class="mt-3 flex items-center gap-2 text-[11px] text-slate-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-cyan-500"></span>

                                            <span>
                                                {{ $isAr ? 'إشعار من إدارة HAG Brokers' : 'Notification from HAG Brokers Admin' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">
                                    {{ $isAr ? 'إرسال آمن' : 'Secure Delivery' }}
                                </h3>

                                <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                    {{ $isAr
                                        ? 'سيتم حفظ الإشعار في نظام Laravel Notifications الخاص بالمستخدم.'
                                        : 'The notification will be stored in the user’s Laravel Notifications system.' }}
                                </p>
                            </div>
                        </div>
                    </section>

                </aside>

            </div>

            <div class="flex flex-col items-center justify-between gap-3 text-xs text-slate-400 sm:flex-row">
                <p>HAG Brokers — Sea Freight Solutions</p>

                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                    <span>
                        {{ $isAr ? 'نظام الإشعارات' : 'Notification System' }}
                    </span>
                </div>
            </div>

        </div>
    </div>

</x-admin-layout>
