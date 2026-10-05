@php
    $isAr = app()->getLocale() === 'ar';
    $dir = $isAr ? 'rtl' : 'ltr';

    $unreadCount = $notifications->where('is_read', false)->count();

    $t = [
        'notifications' => $isAr ? 'الإشعارات' : 'Notifications',
        'subtitle' => $isAr
            ? 'ابقَ على اطلاع بآخر تحديثات الشحنات وحسابك.'
            : 'Stay updated with your shipments and account activity.',

        'dashboard' => $isAr ? 'لوحة التحكم' : 'Dashboard',
        'back_dashboard' => $isAr ? 'العودة إلى لوحة التحكم' : 'Back to Dashboard',

        'hero_title' => $isAr ? 'الإشعارات' : 'Notifications',
        'hero_description' => $isAr
            ? 'آخر التحديثات المهمة المتعلقة بالشحنات والمستندات والعمليات والحساب.'
            : 'Important updates about your sea freight operations, shipments, documents and account activity.',

        'your_notifications' => $isAr ? 'إشعاراتك' : 'Your Notifications',
        'recent_notifications' => $isAr ? 'آخر الإشعارات المرتبطة بحسابك.' : 'Recent notifications from your account.',

        'notification' => $isAr ? 'إشعار' : 'notification',
        'notifications_count' => $isAr ? 'إشعارات' : 'notifications',

        'mark_read' => $isAr ? 'تحديد كمقروء' : 'Mark as read',
        'mark_all_read' => $isAr ? 'تحديد الكل كمقروء' : 'Mark all as read',

        'new' => $isAr ? 'جديد' : 'New',

        'no_notifications' => $isAr ? 'لا توجد إشعارات بعد' : 'No notifications yet',

        'no_notifications_text' => $isAr
            ? 'لا توجد لديك إشعارات حالياً. ستظهر هنا التحديثات المتعلقة بالشحنات والحساب والعمليات.'
            : 'You do not have any notifications at the moment. Updates about your shipments and account activity will appear here.',

        'secure_platform' => $isAr ? 'منصة آمنة' : 'Secure Platform',

        'success' => session('success'),

        'new_updates' => $isAr ? 'يتم تحديث الإشعارات تلقائياً.' : 'Notifications update automatically.',

        'default_title' => $isAr ? 'إشعار جديد' : 'New Notification',

        'default_message' => $isAr ? 'لديك إشعار جديد.' : 'You have a new notification.',
    ];

@endphp

<x-app-layout>
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
                            {{ $t['notifications'] }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ $t['subtitle'] }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">

                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.markAllRead') }}">
                        @csrf
                        @method('PATCH')

                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl border border-cyan-200 bg-white px-4 py-2.5 text-sm font-bold text-cyan-700 shadow-sm transition hover:border-cyan-400 hover:bg-cyan-50 dark:border-cyan-900/50 dark:bg-slate-900 dark:text-cyan-400 dark:hover:border-cyan-600 dark:hover:bg-cyan-950/30">
                            <svg class="me-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M5 13l4 4L19 7" />
                            </svg>

                            {{ $t['mark_all_read'] }}
                        </button>
                    </form>
                @endif

                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-cyan-300 hover:text-cyan-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-cyan-500 dark:hover:text-cyan-400">

                    <span class="{{ $isAr ? 'order-2 ms-2' : 'me-2' }}">
                        {{ $isAr ? '→' : '←' }}
                    </span>

                    {{ $t['dashboard'] }}
                </a>
            </div>
        </div>
    </x-slot>

    <div dir="{{ $dir }}" class="min-h-screen bg-slate-50 dark:bg-slate-950">

        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

            {{-- Success message --}}
            @if (session('success'))
                <div
                    class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300">

                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>

                    <span>{{ session('success') }}</span>
                </div>
            @endif


            {{-- Hero --}}
            <section
                class="relative overflow-hidden rounded-3xl border border-slate-800 bg-slate-950 p-6 shadow-xl sm:p-8">

                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cyan-500/10 blur-3xl">
                </div>

                <div class="absolute -bottom-24 -left-20 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl">
                </div>

                <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">

                    <div>
                        <div
                            class="mb-3 inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-cyan-300">

                            <span class="h-2 w-2 rounded-full bg-cyan-400"></span>

                            HAG Brokers
                        </div>

                        <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                            {{ $t['hero_title'] }}
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-400 sm:text-base">
                            {{ $t['hero_description'] }}
                        </p>

                        {{-- Auto update indicator --}}
                        <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                            <span class="relative flex h-2.5 w-2.5">
                                <span
                                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-60">
                                </span>

                                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-cyan-500">
                                </span>
                            </span>

                            {{ $t['new_updates'] }}
                        </div>
                    </div>

                    <div
                        class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl border border-cyan-400/20 bg-cyan-400/10">

                        <svg class="h-9 w-9 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                </div>
            </section>


            {{-- Notification list --}}
            <section
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                {{-- Section header --}}
                <div class="border-b border-slate-200 px-5 py-5 dark:border-slate-800 sm:px-6">

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
                                {{ $t['your_notifications'] }}
                            </h2>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ $t['recent_notifications'] }}
                            </p>
                        </div>

                        @if ($notifications->total() > 0)
                            <div class="flex flex-wrap items-center gap-2">

                                <div
                                    class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">

                                    {{ $notifications->total() }}

                                    {{ $notifications->total() === 1 ? $t['notification'] : $t['notifications_count'] }}
                                </div>

                                @if ($unreadCount > 0)
                                    <div
                                        class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-bold text-cyan-700 dark:bg-cyan-400/10 dark:text-cyan-300">

                                        {{ $unreadCount }}

                                        {{ $isAr ? 'غير مقروء' : 'unread' }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>


                {{-- Notifications --}}
                @forelse ($notifications as $notification)
                    @php
                        $isRead = (bool) $notification->is_read;

                        $title = $notification->title ?? ($notification->subject ?? $t['default_title']);

                        $message =
                            $notification->message ??
                            ($notification->body ?? ($notification->content ?? $t['default_message']));

                        $date = $notification->created_at;
                    @endphp


                    <div
                        class="group border-b border-slate-100 px-5 py-5 transition last:border-b-0 dark:border-slate-800 sm:px-6
                    {{ $isRead ? 'bg-white dark:bg-slate-900' : 'bg-cyan-50/40 dark:bg-cyan-950/10' }}">

                        <div class="flex gap-4">

                            {{-- Icon --}}
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl
                            {{ $isRead
                                ? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                : 'bg-cyan-100 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-400' }}">

                                @if ($isRead)
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                @else
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                @endif
                            </div>


                            {{-- Content --}}
                            <div class="min-w-0 flex-1">

                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                                    <div class="min-w-0">

                                        <div class="flex flex-wrap items-center gap-2">

                                            {{-- Notification title --}}
                                            <a href="{{ route('notifications.show', $notification) }}"
                                                class="text-sm font-extrabold transition
                                            {{ $isRead
                                                ? 'text-slate-800 hover:text-cyan-600 dark:text-slate-200 dark:hover:text-cyan-400'
                                                : 'text-slate-950 hover:text-cyan-600 dark:text-white dark:hover:text-cyan-400' }}">

                                                {{ $title }}
                                            </a>


                                            {{-- New badge --}}
                                            @unless ($isRead)
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full bg-cyan-100 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-cyan-700 dark:bg-cyan-400/10 dark:text-cyan-300">

                                                    <span class="h-1.5 w-1.5 rounded-full bg-cyan-500">
                                                    </span>

                                                    {{ $t['new'] }}
                                                </span>
                                            @endunless

                                        </div>


                                        {{-- Message --}}
                                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                            {{ $message }}
                                        </p>


                                        {{-- Date --}}
                                        @if ($date)
                                            <div class="mt-3 flex items-center gap-2 text-xs text-slate-400">

                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">

                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>

                                                <span>
                                                    {{ $date->diffForHumans() }}
                                                </span>

                                                <span class="text-slate-600 dark:text-slate-700">
                                                    •
                                                </span>

                                                <span>
                                                    {{ $date->format('M d, Y · H:i') }}
                                                </span>
                                            </div>
                                        @endif

                                    </div>


                                    {{-- Mark as read --}}
                                    @unless ($isRead)
                                        <form method="POST" action="{{ route('notifications.markRead', $notification) }}"
                                            class="shrink-0">

                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                class="inline-flex items-center justify-center rounded-xl border border-cyan-200 bg-white px-3 py-2 text-xs font-bold text-cyan-700 transition hover:border-cyan-400 hover:bg-cyan-50 dark:border-cyan-900/50 dark:bg-slate-900 dark:text-cyan-400 dark:hover:border-cyan-600 dark:hover:bg-cyan-950/30">

                                                <svg class="me-1.5 h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">

                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.8" d="M5 13l4 4L19 7" />
                                                </svg>

                                                {{ $t['mark_read'] }}

                                            </button>

                                        </form>
                                    @endunless

                                </div>
                            </div>

                        </div>
                    </div>

                @empty

                    {{-- Empty state --}}
                    <div class="px-6 py-16 text-center">

                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">

                            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />

                            </svg>
                        </div>


                        <h3 class="mt-5 text-lg font-extrabold text-slate-900 dark:text-white">

                            {{ $t['no_notifications'] }}

                        </h3>


                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400">

                            {{ $t['no_notifications_text'] }}

                        </p>


                        <a href="{{ route('dashboard') }}"
                            class="mt-6 inline-flex items-center justify-center rounded-xl bg-cyan-500 px-5 py-3 text-sm font-extrabold text-slate-950 shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-400">

                            {{ $t['back_dashboard'] }}

                        </a>

                    </div>
                @endforelse


                {{-- Pagination --}}
                @if ($notifications->hasPages())
                    <div class="border-t border-slate-200 px-5 py-5 dark:border-slate-800 sm:px-6">

                        {{ $notifications->links() }}

                    </div>
                @endif

            </section>


            {{-- Footer --}}
            <div class="flex flex-col items-center justify-between gap-3 text-xs text-slate-400 sm:flex-row">

                <p>
                    HAG Brokers — Sea Freight Solutions
                </p>

                <div class="flex items-center gap-2">

                    <span class="h-2 w-2 rounded-full bg-cyan-500"></span>

                    <span>
                        {{ $t['secure_platform'] }}
                    </span>

                </div>

            </div>

        </div>
    </div>


    {{-- =========================================================
    AUTO REFRESH
    Refresh notifications every 30 seconds
========================================================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const refreshInterval = 30000;

            setInterval(function() {

                // Don't refresh while the user is submitting a form.
                const activeElement = document.activeElement;

                if (
                    activeElement &&
                    (
                        activeElement.tagName === 'INPUT' ||
                        activeElement.tagName === 'TEXTAREA' ||
                        activeElement.tagName === 'SELECT' ||
                        activeElement.tagName === 'BUTTON'
                    )
                ) {
                    return;
                }

                window.location.reload();

            }, refreshInterval);

        });
    </script>

</x-app-layout>
