@php
    $isAr = app()->getLocale() === 'ar';
    $dir = $isAr ? 'rtl' : 'ltr';

    /*
|--------------------------------------------------------------------------
| Laravel Database Notifications
|--------------------------------------------------------------------------
*/

    $unreadCount = $notifications->getCollection()->whereNull('read_at')->count();

    $t = [
        'notifications' => $isAr ? 'الإشعارات' : 'Notifications',

        'subtitle' => $isAr
            ? 'مراقبة جميع إشعارات النظام والعمليات المهمة.'
            : 'Monitor system notifications and important platform activity.',

        'search' => $isAr ? 'بحث في العنوان أو الرسالة أو النوع...' : 'Search title, message or type...',

        'search_button' => $isAr ? 'بحث' : 'Search',

        'unread' => $isAr ? 'غير مقروء' : 'Unread',

        'read' => $isAr ? 'مقروء' : 'Read',

        'system' => $isAr ? 'النظام' : 'System',

        'no_notifications' => $isAr ? 'لا توجد إشعارات' : 'No notifications found.',

        'no_notifications_text' => $isAr
            ? 'لا توجد إشعارات مطابقة لبحثك حالياً.'
            : 'There are no notifications matching your search.',

        'mark_read' => $isAr ? 'تحديد كمقروء' : 'Mark as read',

        'mark_all_read' => $isAr ? 'تحديد الكل كمقروء' : 'Mark all as read',

        'create_notification' => $isAr ? 'إنشاء إشعار' : 'Create Notification',

        'view' => $isAr ? 'عرض' : 'View',

        'total' => $isAr ? 'الإجمالي' : 'Total',

        'unread_count' => $isAr ? 'غير مقروءة' : 'Unread',

        'live_monitoring' => $isAr ? 'مراقبة مباشرة' : 'Live Monitoring',

        'auto_refresh' => $isAr ? 'يتم التحديث تلقائياً' : 'Auto refresh enabled',

        'type' => $isAr ? 'النوع' : 'Type',

        'just_now' => $isAr ? 'الآن' : 'Just now',

        /*
    |--------------------------------------------------------------------------
    | Notification Types
    |--------------------------------------------------------------------------
    */

        'types' => [
            'user.created' => $isAr ? 'مستخدم جديد' : 'New User',

            'opportunity.created' => $isAr ? 'فرصة جديدة' : 'New Opportunity',

            'opportunity.status_changed' => $isAr ? 'تغيير حالة الفرصة' : 'Opportunity Status Changed',

            'opportunity.verification_changed' => $isAr ? 'تغيير التحقق من الفرصة' : 'Opportunity Verification Changed',

            'quotation.created' => $isAr ? 'عرض سعر جديد' : 'New Quotation',

            'quotation.status_changed' => $isAr ? 'تغيير حالة عرض السعر' : 'Quotation Status Changed',

            'shipment.created' => $isAr ? 'شحنة جديدة' : 'New Shipment',

            'shipment.status_changed' => $isAr ? 'تغيير حالة الشحنة' : 'Shipment Status Changed',

            'deal.created' => $isAr ? 'صفقة جديدة' : 'New Deal',

            'deal.status_changed' => $isAr ? 'تغيير حالة الصفقة' : 'Deal Status Changed',
        ],
    ];

@endphp

<x-admin-layout>


    {{-- =========================================================
    HEADER
========================================================== --}}

    <x-slot name="header">

        <div dir="{{ $dir }}" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <div class="flex items-center gap-3">

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl
                    bg-cyan-400 text-sm font-black text-slate-950
                    shadow-lg shadow-cyan-400/20">
                        HAG
                    </div>

                    <div>

                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                            {{ $t['notifications'] }}
                        </h2>

                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ $t['subtitle'] }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- Header Actions --}}

            <div class="flex flex-wrap items-center gap-2">

                {{-- Create Notification --}}
                <a href="{{ route('admin.notifications.create') }}"
                    class="inline-flex items-center justify-center rounded-xl
                    bg-cyan-500 px-4 py-2.5
                    text-sm font-black text-slate-950
                    shadow-lg shadow-cyan-500/20
                    transition hover:bg-cyan-400
                    focus:outline-none focus:ring-2
                    focus:ring-cyan-400
                    focus:ring-offset-2
                    dark:focus:ring-offset-slate-950">

                    <svg class="me-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14" />

                    </svg>

                    {{ $t['create_notification'] }}

                </a>

                {{-- Mark all as read --}}
                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('admin.notifications.markAllRead') }}">

                        @csrf
                        @method('PATCH')

                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl
                            border border-cyan-200 bg-white px-4 py-2.5
                            text-sm font-bold text-cyan-700 shadow-sm
                            transition hover:border-cyan-400 hover:bg-cyan-50
                            dark:border-cyan-900/50 dark:bg-slate-900
                            dark:text-cyan-400 dark:hover:border-cyan-600
                            dark:hover:bg-cyan-950/30">

                            <svg class="me-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M5 13l4 4L19 7" />

                            </svg>

                            {{ $t['mark_all_read'] }}

                        </button>

                    </form>
                @endif

            </div>

        </div>

    </x-slot>


    {{-- =========================================================
    PAGE
========================================================== --}}

    <div dir="{{ $dir }}" class="min-h-screen bg-slate-50 dark:bg-slate-950">

        <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">


            {{-- =====================================================
            STATISTICS
        ====================================================== --}}

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">


                {{-- Total --}}

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5
                shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                {{ $t['total'] }}
                            </p>

                            <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">
                                {{ $notifications->total() }}
                            </p>

                        </div>

                        <div
                            class="flex h-12 w-12 items-center justify-center
                        rounded-xl bg-slate-100 text-slate-500
                        dark:bg-slate-800 dark:text-slate-300">

                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>

                        </div>

                    </div>

                </div>


                {{-- Unread --}}

                <div
                    class="rounded-2xl border border-cyan-200 bg-cyan-50 p-5
                shadow-sm dark:border-cyan-900/40
                dark:bg-cyan-950/20">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm font-medium text-cyan-700 dark:text-cyan-300">
                                {{ $t['unread_count'] }}
                            </p>

                            <p class="mt-2 text-3xl font-black text-cyan-700 dark:text-cyan-300">
                                {{ $unreadCount }}
                            </p>

                        </div>

                        <div
                            class="flex h-12 w-12 items-center justify-center
                        rounded-xl bg-cyan-100 text-cyan-600
                        dark:bg-cyan-400/10 dark:text-cyan-400">

                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>

                        </div>

                    </div>

                </div>


                {{-- Live monitoring --}}

                <div
                    class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5
                shadow-sm dark:border-emerald-900/40
                dark:bg-emerald-950/20">

                    <div class="flex items-center gap-3">

                        <span class="relative flex h-3 w-3">

                            <span
                                class="absolute inline-flex h-full w-full
                            animate-ping rounded-full bg-emerald-400 opacity-60"></span>

                            <span
                                class="relative inline-flex h-3 w-3
                            rounded-full bg-emerald-500"></span>

                        </span>

                        <div>

                            <p class="text-sm font-bold text-emerald-700 dark:text-emerald-300">
                                {{ $t['live_monitoring'] }}
                            </p>

                            <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">
                                {{ $t['auto_refresh'] }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =====================================================
            SEARCH
        ====================================================== --}}

            <div
                class="rounded-2xl border border-slate-200 bg-white p-4
            shadow-sm dark:border-slate-800 dark:bg-slate-900">

                <form method="GET" action="{{ route('admin.notifications.index') }}"
                    class="flex flex-col gap-3 sm:flex-row">

                    <div class="relative flex-1">

                        <svg class="absolute start-3 top-1/2 h-5 w-5
                        -translate-y-1/2 text-slate-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="m21 21-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z" />
                        </svg>

                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="{{ $t['search'] }}"
                            class="w-full rounded-xl border-slate-300
                        bg-white py-3 ps-10 pe-4 text-sm
                        text-slate-900 outline-none transition
                        focus:border-cyan-400 focus:ring-cyan-400
                        dark:border-slate-700 dark:bg-slate-800
                        dark:text-white">

                    </div>

                    <button type="submit"
                        class="rounded-xl bg-cyan-600 px-6 py-3
                    text-sm font-bold text-white shadow-lg
                    shadow-cyan-600/20 transition
                    hover:bg-cyan-500">
                        {{ $t['search_button'] }}
                    </button>

                </form>

            </div>


            {{-- =====================================================
            NOTIFICATIONS
        ====================================================== --}}

            <div class="space-y-4">

                @forelse ($notifications as $notification)
                    @php

                        $isRead = $notification->read_at !== null;

                        $data = is_array($notification->data) ? $notification->data : [];

                        $type = $data['type'] ?? 'system';

                        $title = $data['title'] ?? ($data['message'] ?? ($t['types'][$type] ?? $type));

                        $message = $data['message'] ?? $title;

                        $viewUrl = $data['url'] ?? null;

                        /*
                    |--------------------------------------------------------------------------
                    | Fallback URLs
                    |--------------------------------------------------------------------------
                    */

                        if (!$viewUrl && isset($data['opportunity_id'])) {
                            if (Route::has('admin.opportunities.show')) {
                                $viewUrl = route('admin.opportunities.show', $data['opportunity_id']);
                            }
                        } elseif (!$viewUrl && isset($data['quotation_id'])) {
                            if (Route::has('admin.quotations.show')) {
                                $viewUrl = route('admin.quotations.show', $data['quotation_id']);
                            }
                        } elseif (!$viewUrl && isset($data['shipment_id'])) {
                            if (Route::has('admin.shipments.show')) {
                                $viewUrl = route('admin.shipments.show', $data['shipment_id']);
                            }
                        } elseif (!$viewUrl && isset($data['deal_id'])) {
                            if (Route::has('admin.deals.show')) {
                                $viewUrl = route('admin.deals.show', $data['deal_id']);
                            }
                        } elseif (!$viewUrl && isset($data['user_id'])) {
                            if (Route::has('admin.users.index')) {
                                $viewUrl = route('admin.users.index');
                            }
                        }

                        $typeLabel = $t['types'][$type] ?? $type;

                    @endphp


                    <div
                        class="group rounded-2xl border p-5 shadow-sm transition
                    {{ $isRead
                        ? 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900'
                        : 'border-cyan-200 bg-cyan-50/50 dark:border-cyan-900/50 dark:bg-cyan-950/20' }}">

                        <div
                            class="flex flex-col gap-4 sm:flex-row
                        sm:items-start sm:justify-between">


                            {{-- Main content --}}

                            <div class="flex min-w-0 flex-1 gap-4">


                                {{-- Icon --}}

                                <div
                                    class="flex h-11 w-11 shrink-0 items-center
                                justify-center rounded-xl
                                {{ $isRead
                                    ? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                    : 'bg-cyan-100 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-400' }}">

                                    @if (str_contains($type, 'opportunity'))
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                    @elseif (str_contains($type, 'quotation'))
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6l4 4v12a2 2 0 01-2 2z" />
                                        </svg>
                                    @elseif (str_contains($type, 'shipment'))
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                                d="M3 7h11v10H3zM14 10h4l3 3v4h-7zM6 19a2 2 0 100-4 2 2 0 000 4zm12 0a2 2 0 100-4 2 2 0 000 4z" />
                                        </svg>
                                    @elseif (str_contains($type, 'user'))
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                                d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m6-10a4 4 0 100-8 4 4 0 000 8zm8 4v6m3-3h-6" />
                                        </svg>
                                    @elseif (str_contains($type, 'deal'))
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                                d="M12 3v18m9-9H3m15.5-5.5L5.5 17.5m13 0L5.5 6.5" />
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    @endif

                                </div>


                                {{-- Text --}}

                                <div class="min-w-0 flex-1">

                                    <div class="flex flex-wrap items-center gap-2">

                                        <h3
                                            class="font-bold
                                        {{ $isRead ? 'text-slate-800 dark:text-slate-200' : 'text-slate-950 dark:text-white' }}">
                                            {{ $title }}
                                        </h3>


                                        @if (!$isRead)
                                            <span
                                                class="inline-flex items-center gap-1
                                            rounded-full bg-cyan-100 px-2.5 py-1
                                            text-xs font-semibold text-cyan-700
                                            dark:bg-cyan-400/10
                                            dark:text-cyan-300">

                                                <span class="h-1.5 w-1.5 rounded-full bg-cyan-500"></span>

                                                {{ $t['unread'] }}

                                            </span>
                                        @else
                                            <span
                                                class="rounded-full bg-slate-100
                                            px-2.5 py-1 text-xs font-semibold
                                            text-slate-500 dark:bg-slate-800
                                            dark:text-slate-400">
                                                {{ $t['read'] }}
                                            </span>
                                        @endif

                                    </div>


                                    {{-- Message --}}

                                    <p
                                        class="mt-2 text-sm leading-6
                                    text-slate-600 dark:text-slate-300">
                                        {{ $message }}
                                    </p>


                                    {{-- Meta --}}

                                    <div
                                        class="mt-4 flex flex-wrap items-center
                                    gap-x-5 gap-y-2 text-xs
                                    text-slate-500 dark:text-slate-400">

                                        <span>
                                            {{ $t['type'] }}:

                                            <strong class="text-slate-700 dark:text-slate-300">
                                                {{ $typeLabel }}
                                            </strong>
                                        </span>


                                        @if ($notification->created_at)
                                            <span>
                                                {{ $notification->created_at->diffForHumans() }}
                                            </span>

                                            <span class="text-slate-400">
                                                {{ $notification->created_at->format('Y-m-d H:i') }}
                                            </span>
                                        @endif

                                    </div>


                                    {{-- Technical type --}}

                                    <div class="mt-3">

                                        <span
                                            class="inline-flex rounded-full
                                        bg-slate-100 px-2.5 py-1
                                        font-mono text-[10px] font-bold
                                        text-slate-600 dark:bg-slate-800
                                        dark:text-slate-400">
                                            {{ $type }}
                                        </span>

                                    </div>

                                </div>

                            </div>


                            {{-- =================================================
                            ACTIONS
                        ================================================== --}}

                            <div class="flex shrink-0 flex-wrap
                            items-center gap-2">

                                @if ($viewUrl)
                                    <a href="{{ $viewUrl }}"
                                        class="inline-flex items-center
                                    justify-center rounded-xl border
                                    border-slate-200 bg-white px-3 py-2
                                    text-xs font-bold text-slate-700
                                    transition hover:border-cyan-300
                                    hover:text-cyan-600
                                    dark:border-slate-700
                                    dark:bg-slate-900
                                    dark:text-slate-200
                                    dark:hover:border-cyan-500
                                    dark:hover:text-cyan-400">
                                        {{ $t['view'] }}
                                    </a>
                                @endif


                                @if (!$isRead)
                                    <form method="POST"
                                        action="{{ route('admin.notifications.markRead', $notification->id) }}">

                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                            class="inline-flex items-center
                                        justify-center rounded-xl
                                        bg-cyan-600 px-3 py-2
                                        text-xs font-bold text-white
                                        transition hover:bg-cyan-500">
                                            {{ $t['mark_read'] }}
                                        </button>

                                    </form>
                                @endif

                            </div>

                        </div>

                    </div>

                @empty

                    {{-- Empty state --}}

                    <div
                        class="rounded-2xl border border-slate-200
                    bg-white p-12 text-center shadow-sm
                    dark:border-slate-800 dark:bg-slate-900">

                        <div
                            class="mx-auto flex h-16 w-16 items-center
                        justify-center rounded-2xl
                        bg-slate-100 dark:bg-slate-800">

                            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>

                        </div>

                        <h3 class="mt-5 text-lg font-bold
                        text-slate-900 dark:text-white">
                            {{ $t['no_notifications'] }}
                        </h3>

                        <p
                            class="mx-auto mt-2 max-w-md text-sm
                        text-slate-500 dark:text-slate-400">
                            {{ $t['no_notifications_text'] }}
                        </p>

                    </div>
                @endforelse

            </div>


            {{-- =========================================================
            PAGINATION
        ========================================================== --}}

            @if ($notifications->hasPages())
                <div class="pt-2">
                    {{ $notifications->links() }}
                </div>
            @endif


            {{-- =========================================================
            FOOTER
        ========================================================== --}}

            <div
                class="flex flex-col items-center justify-between gap-3
            pt-2 text-xs text-slate-400 sm:flex-row">

                <span>
                    HAG Brokers — Admin Notification Center
                </span>

                <div class="flex items-center gap-2">

                    <span class="h-2 w-2 rounded-full bg-cyan-500"></span>

                    <span>
                        {{ $isAr ? 'النظام يعمل' : 'System Active' }}
                    </span>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
    AUTO REFRESH
========================================================== --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const refreshInterval = 30000;

            setInterval(function() {

                const activeElement = document.activeElement;

                if (
                    activeElement &&
                    (
                        activeElement.tagName === 'INPUT' ||
                        activeElement.tagName === 'TEXTAREA' ||
                        activeElement.tagName === 'SELECT'
                    )
                ) {
                    return;
                }

                window.location.reload();

            }, refreshInterval);

        });
    </script>

</x-admin-layout>
