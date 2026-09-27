<!DOCTYPE html>
@php
    $isAr = app()->getLocale() === 'ar';

    $t = [
        'admin_dashboard' => $isAr ? 'لوحة تحكم الإدارة' : 'Admin Dashboard',
        'admin_control' => $isAr ? 'مركز تحكم الإدارة' : 'Admin Control Center',
        'administrator' => $isAr ? 'مدير النظام' : 'Administrator',
        'full_access' => $isAr ? 'صلاحية كاملة على النظام' : 'Full system access',
        'main' => $isAr ? 'الرئيسية' : 'Main',
        'dashboard' => $isAr ? 'لوحة التحكم' : 'Dashboard',
        'crm_sales' => $isAr ? 'إدارة العملاء والمبيعات' : 'CRM & Sales',
        'leads' => $isAr ? 'العملاء المحتملون' : 'Leads',
        'contacts' => $isAr ? 'جهات الاتصال' : 'Contacts',
        'opportunities' => $isAr ? 'الفرص التجارية' : 'Opportunities',
        'activities' => $isAr ? 'الأنشطة' : 'Activities',
        'tasks' => $isAr ? 'المهام' : 'Tasks',
        'shipments' => $isAr ? 'الشحنات' : 'Shipments',
        'quotations' => $isAr ? 'عروض الأسعار' : 'Quotations',
        'customers' => $isAr ? 'العملاء' : 'Customers',
        'users' => $isAr ? 'المستخدمون' : 'Users',
        'operations' => $isAr ? 'العمليات' : 'Operations',
        'documents' => $isAr ? 'المستندات' : 'Documents',
        'notifications' => $isAr ? 'الإشعارات' : 'Notifications',
        'ports' => $isAr ? 'الموانئ' : 'Ports',
        'carriers' => $isAr ? 'الناقلون' : 'Carriers',
        'vessels' => $isAr ? 'السفن' : 'Vessels',
        'reports' => $isAr ? 'التقارير' : 'Reports',
        'settings' => $isAr ? 'الإعدادات' : 'Settings',
        'system' => $isAr ? 'النظام' : 'System',
        'profile' => $isAr ? 'الملف الشخصي' : 'Profile',
        'view_website' => $isAr ? 'عرض الموقع' : 'View Website',
        'logout' => $isAr ? 'تسجيل الخروج' : 'Logout',
        'admin' => $isAr ? 'الإدارة' : 'Admin',
        'language' => $isAr ? 'اللغة' : 'Language',
        'close_menu' => $isAr ? 'إغلاق القائمة' : 'Close menu',
        'open_menu' => $isAr ? 'فتح القائمة' : 'Open menu',
    ];
@endphp

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ $title ?? $t['admin_dashboard'] }} — HAG Brokers
    </title>

    <link rel="preconnect" href="https://fonts.bunny.net">

    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet">

    <link rel="icon" type="image/png" href="{{ asset('storage/favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">

    {{-- =========================================================
        Mobile Overlay
    ========================================================== --}}
    <div id="admin-mobile-overlay" class="fixed inset-0 z-40 hidden bg-slate-950/70 backdrop-blur-sm lg:hidden"
        onclick="closeAdminSidebar()"></div>


    {{-- =========================================================
        Sidebar
    ========================================================== --}}
    <aside id="admin-sidebar"
        class="fixed inset-y-0 start-0 z-50 flex w-72 -translate-x-full flex-col border-e border-slate-800 bg-slate-950 shadow-2xl transition-transform duration-300 lg:translate-x-0 rtl:translate-x-full rtl:lg:translate-x-0">

        {{-- Brand --}}
        <div class="flex h-20 shrink-0 items-center border-b border-slate-800 px-5">

            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">

                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-400 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/20">
                    HAG
                </div>

                <div class="min-w-0">
                    <div class="truncate text-base font-extrabold text-white">
                        HAG Brokers
                    </div>

                    <div class="truncate text-xs font-medium text-slate-500">
                        {{ $t['admin_control'] }}
                    </div>
                </div>

            </a>

            {{-- Mobile close --}}
            <button type="button" onclick="closeAdminSidebar()"
                class="ms-auto rounded-lg p-2 text-slate-400 transition hover:bg-slate-800 hover:text-white lg:hidden"
                aria-label="{{ $t['close_menu'] }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

        </div>


        {{-- Admin indicator --}}
        <div class="px-4 pt-5">

            <div class="rounded-2xl border border-cyan-400/10 bg-cyan-400/5 px-4 py-3">

                <div class="flex items-center gap-3">

                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M12 15v2m-6 4h12a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2zM8 7V5a4 4 0 118 0v2" />
                        </svg>
                    </div>

                    <div class="min-w-0">

                        <p class="text-xs font-bold uppercase tracking-wider text-cyan-400">
                            {{ $t['administrator'] }}
                        </p>

                        <p class="mt-0.5 truncate text-xs text-slate-500">
                            {{ $t['full_access'] }}
                        </p>

                    </div>

                </div>

            </div>

        </div>


        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-4 py-6">

            {{-- Main --}}
            <p class="mb-3 px-3 text-[10px] font-extrabold uppercase tracking-[0.18em] text-slate-600">
                {{ $t['main'] }}
            </p>

            <div class="space-y-1">

                {{-- Dashboard --}}
                <a href="{{ route('admin.dashboard') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
                    {{ request()->routeIs('admin.dashboard')
                        ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
                        : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                    <svg class="h-5 w-5 shrink-0
                        {{ request()->routeIs('admin.dashboard') ? 'text-slate-950' : 'text-slate-500 group-hover:text-cyan-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10" />
                    </svg>

                    <span>
                        {{ $t['dashboard'] }}
                    </span>

                </a>

                <div class="px-3 pt-6 pb-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-600">
                        {{ $t['crm_sales'] }}
                    </p>
                </div>

                <a href="{{ route('admin.leads.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
   {{ request()->routeIs('admin.leads.*')
       ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
       : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                    <span>◌</span>
                    <span>{{ $t['leads'] }}</span>
                </a>

                <a href="{{ route('admin.contacts.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
   {{ request()->routeIs('admin.contacts.*')
       ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
       : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                    <span>◎</span>
                    <span>{{ $t['contacts'] }}</span>
                </a>

                <a href="{{ route('admin.opportunities.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
   {{ request()->routeIs('admin.opportunities.*')
       ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
       : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                    <span>◆</span>
                    <span>{{ $t['opportunities'] }}</span>
                </a>

                <a href="{{ route('admin.activities.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
   {{ request()->routeIs('admin.activities.*')
       ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
       : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                    <span>◷</span>
                    <span>{{ $t['activities'] }}</span>
                </a>

                <a href="{{ route('admin.tasks.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
   {{ request()->routeIs('admin.tasks.*')
       ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
       : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                    <span>✓</span>
                    <span>{{ $t['tasks'] }}</span>
                </a>


                {{-- Shipments --}}
                <a href="{{ route('admin.shipments.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
                    {{ request()->routeIs('admin.shipments.*')
                        ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
                        : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                    <svg class="h-5 w-5 shrink-0
                        {{ request()->routeIs('admin.shipments.*') ? 'text-slate-950' : 'text-slate-500 group-hover:text-cyan-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M3 7h11v10H3zM14 10h4l3 3v4h-7z" />

                        <circle cx="7" cy="17" r="2" stroke-width="1.7" />

                        <circle cx="18" cy="17" r="2" stroke-width="1.7" />
                    </svg>

                    <span>
                        {{ $t['shipments'] }}
                    </span>

                </a>


                {{-- Quotations --}}
                <a href="{{ route('admin.quotations.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
                    {{ request()->routeIs('admin.quotations.*')
                        ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
                        : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                    <svg class="h-5 w-5 shrink-0
                        {{ request()->routeIs('admin.quotations.*') ? 'text-slate-950' : 'text-slate-500 group-hover:text-cyan-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M7 3h8l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z" />

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M15 3v5h5M9 13h6M9 17h4" />
                    </svg>

                    <span>
                        {{ $t['quotations'] }}
                    </span>

                </a>


                {{-- Customers --}}
                <a href="{{ route('admin.customers.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
                    {{ request()->routeIs('admin.customers.*')
                        ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
                        : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                    <svg class="h-5 w-5 shrink-0
                        {{ request()->routeIs('admin.customers.*') ? 'text-slate-950' : 'text-slate-500 group-hover:text-cyan-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2" />

                        <circle cx="9" cy="7" r="4" stroke-width="1.7" />

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
                    </svg>

                    <span>
                        {{ $t['customers'] }}
                    </span>

                </a>


                {{-- Users --}}
                <a href="{{ route('admin.users.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
                    {{ request()->routeIs('admin.users.*')
                        ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
                        : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                    <svg class="h-5 w-5 shrink-0
                        {{ request()->routeIs('admin.users.*') ? 'text-slate-950' : 'text-slate-500 group-hover:text-cyan-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1M12 12a4 4 0 100-8 4 4 0 000 8z" />
                    </svg>

                    <span>
                        {{ $t['users'] }}
                    </span>

                </a>

            </div>


            {{-- Operations --}}
            <p class="mb-3 mt-8 px-3 text-[10px] font-extrabold uppercase tracking-[0.18em] text-slate-600">
                {{ $t['operations'] }}
            </p>

            <div class="space-y-1">

                {{-- Shipments --}}
                <a href="{{ route('admin.shipments.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
        {{ request()->routeIs('admin.shipments.*')
            ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
            : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                    <svg class="h-5 w-5 shrink-0
            {{ request()->routeIs('admin.shipments.*') ? 'text-slate-950' : 'text-slate-500 group-hover:text-cyan-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M3 7h11v10H3zM14 10h4l3 3v4h-7zM7 17a2 2 0 11-4 0M18 17a2 2 0 11-4 0" />

                    </svg>

                    <span>
                        {{ $t['shipments'] }}
                    </span>

                </a>


                {{-- Documents --}}
                <a href="{{ route('admin.documents.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
        {{ request()->routeIs('admin.documents.*')
            ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
            : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                    <svg class="h-5 w-5 shrink-0
            {{ request()->routeIs('admin.documents.*') ? 'text-slate-950' : 'text-slate-500 group-hover:text-cyan-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M7 3h8l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z" />

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 3v5h5" />

                    </svg>

                    <span>
                        {{ $t['documents'] }}
                    </span>

                </a>


                {{-- Quotations --}}
                <a href="{{ route('admin.quotations.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
        {{ request()->routeIs('admin.quotations.*')
            ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
            : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                    <svg class="h-5 w-5 shrink-0
            {{ request()->routeIs('admin.quotations.*') ? 'text-slate-950' : 'text-slate-500 group-hover:text-cyan-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M6 3h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2z" />

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M8 8h8M8 12h8M8 16h5" />

                    </svg>

                    <span>
                        {{ $t['quotations'] }}
                    </span>

                </a>


                {{-- Notifications --}}
                <a href="{{ route('admin.notifications.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
        {{ request()->routeIs('admin.notifications.*')
            ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
            : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                    <svg class="h-5 w-5 shrink-0
            {{ request()->routeIs('admin.notifications.*') ? 'text-slate-950' : 'text-slate-500 group-hover:text-cyan-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />

                    </svg>

                    <span>
                        {{ $t['notifications'] }}
                    </span>

                </a>


                {{-- Ports --}}
                <a href="{{ route('admin.ports.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
        {{ request()->routeIs('admin.ports.*')
            ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
            : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                    <svg class="h-5 w-5 shrink-0
            {{ request()->routeIs('admin.ports.*') ? 'text-slate-950' : 'text-slate-500 group-hover:text-cyan-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M3 20h18M5 20v-8h14v8M8 12V7h8v5M10 7V4h4v3" />

                    </svg>

                    <span>
                        {{ $t['ports'] }}
                    </span>

                </a>


                {{-- Carriers --}}
                <a href="{{ route('admin.carriers.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
        {{ request()->routeIs('admin.carriers.*')
            ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
            : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                    <svg class="h-5 w-5 shrink-0
            {{ request()->routeIs('admin.carriers.*') ? 'text-slate-950' : 'text-slate-500 group-hover:text-cyan-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M4 19h16M6 16h12M7 13h10M9 10h6M11 7h2" />

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M5 19v2M19 19v2" />

                    </svg>

                    <span>
                        {{ $t['carriers'] }}
                    </span>

                </a>


                {{-- Vessels --}}
                <a href="{{ route('admin.vessels.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
        {{ request()->routeIs('admin.vessels.*')
            ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
            : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                    <svg class="h-5 w-5 shrink-0
            {{ request()->routeIs('admin.vessels.*') ? 'text-slate-950' : 'text-slate-500 group-hover:text-cyan-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M3 17l2-7h14l2 7M5 17h14M7 10V6h10v4M9 6V3h6v3" />

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M4 20c1.5 1 3 1 4.5 0s3-1 4.5 0 3-1 4.5 0 3-1 4.5 0" />

                    </svg>

                    <span>
                        {{ $t['vessels'] }}
                    </span>

                </a>


                {{-- Reports --}}
                <a href="{{ route('admin.reports.index') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
        {{ request()->routeIs('admin.reports.*')
            ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
            : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                    <svg class="h-5 w-5 shrink-0
            {{ request()->routeIs('admin.reports.*') ? 'text-slate-950' : 'text-slate-500 group-hover:text-cyan-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M4 19V5M4 19h17M8 16v-5M12 16V7M16 16v-9M20 16V4" />

                    </svg>

                    <span>
                        {{ $t['reports'] }}
                    </span>

                </a>

                <a href="{{ route('admin.settings.edit') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
    {{ request()->routeIs('admin.settings.*')
        ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
        : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                        stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10.5 6h3m-6.75 3.75h10.5M5.25 9.75v7.5A2.25 2.25 0 007.5 19.5h9a2.25 2.25 0 002.25-2.25v-7.5M8.25 9.75V6.75A2.25 2.25 0 0110.5 4.5h3a2.25 2.25 0 012.25 2.25v3M12 13.5v3" />
                    </svg>

                    <span>
                        {{ $t['settings'] }}
                    </span>

                </a>

            </div>


            {{-- System --}}
            <p class="mb-3 mt-8 px-3 text-[10px] font-extrabold uppercase tracking-[0.18em] text-slate-600">
                {{ $t['system'] }}
            </p>

            <div class="space-y-1">

                {{-- Profile --}}
                <a href="{{ route('profile.edit') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold text-slate-400 transition hover:bg-slate-900 hover:text-white">

                    <svg class="h-5 w-5 shrink-0 text-slate-500 group-hover:text-cyan-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M20 21a8 8 0 00-16 0M12 13a5 5 0 100-10 5 5 0 000 10z" />
                    </svg>

                    <span>
                        {{ $t['profile'] }}
                    </span>

                </a>


                {{-- Home --}}
                <a href="{{ route('home') }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold text-slate-400 transition hover:bg-slate-900 hover:text-white">

                    <svg class="h-5 w-5 shrink-0 text-slate-500 group-hover:text-cyan-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10" />
                    </svg>

                    <span>
                        {{ $t['view_website'] }}
                    </span>

                </a>

            </div>

        </nav>


        {{-- User / Logout --}}
        <div class="shrink-0 border-t border-slate-800 p-4">

            <div class="rounded-2xl bg-slate-900 p-3">

                <div class="flex items-center gap-3">

                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-cyan-400 font-extrabold text-slate-950">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>

                    <div class="min-w-0 flex-1">

                        <p class="truncate text-sm font-bold text-white">
                            {{ auth()->user()->name ?? $t['administrator'] }}
                        </p>

                        <p class="truncate text-xs text-slate-500">
                            {{ auth()->user()->email ?? '' }}
                        </p>

                    </div>

                </div>


                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf

                    <button type="submit"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-slate-400 transition hover:bg-red-500/10 hover:text-red-400">

                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M10 17l5-5-5-5M15 12H3M21 19V5a2 2 0 00-2-2h-6" />
                        </svg>

                        <span>
                            {{ $t['logout'] }}
                        </span>

                    </button>

                </form>

            </div>

        </div>

    </aside>


    {{-- =========================================================
        Main Wrapper
    ========================================================== --}}
    <div class="min-h-screen lg:ms-72 rtl:lg:ms-0 rtl:lg:me-72">

        {{-- =====================================================
            Top Header
        ====================================================== --}}
        <header
            class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/90">

            <div class="flex h-20 items-center justify-between px-4 sm:px-6 lg:px-8">

                {{-- Left --}}
                <div class="flex items-center gap-3">

                    {{-- Mobile menu --}}
                    <button type="button" onclick="openAdminSidebar()"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-cyan-300 hover:text-cyan-600 lg:hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-cyan-500 dark:hover:text-cyan-400"
                        aria-label="{{ $t['open_menu'] }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>


                    {{-- Mobile brand --}}
                    <div class="flex items-center gap-3 lg:hidden">

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400 text-xs font-black text-slate-950">
                            HAG
                        </div>

                        <div class="hidden sm:block">
                            <p class="text-sm font-extrabold text-slate-900 dark:text-white">
                                HAG Brokers
                            </p>

                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                {{ $t['admin'] }}
                            </p>
                        </div>

                    </div>

                </div>


                {{-- Right --}}
                <div class="flex items-center gap-2 sm:gap-3">

                    {{-- Language --}}
                    <div
                        class="hidden items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold dark:border-slate-700 dark:bg-slate-900 sm:flex">

                        <span class="text-slate-400">
                            {{ $t['language'] }}
                        </span>

                        <a href="{{ route('language.switch', 'en') }}"
                            class="{{ app()->getLocale() === 'en'
                                ? 'text-cyan-600 dark:text-cyan-400'
                                : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                            EN
                        </a>

                        <span class="text-slate-300 dark:text-slate-700">
                            |
                        </span>

                        <a href="{{ route('language.switch', 'ar') }}"
                            class="{{ app()->getLocale() === 'ar'
                                ? 'text-cyan-600 dark:text-cyan-400'
                                : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                            AR
                        </a>

                    </div>


                    {{-- Notifications --}}
                    <a href="{{ route('notifications.index') }}"
                        class="relative flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-cyan-300 hover:text-cyan-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-cyan-500 dark:hover:text-cyan-400"
                        aria-label="{{ $t['notifications'] }}">

                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>

                    </a>


                    {{-- User --}}
                    <div
                        class="hidden items-center gap-3 border-s border-slate-200 ps-3 dark:border-slate-800 sm:flex">

                        <div class="text-end">

                            <p class="text-sm font-bold text-slate-900 dark:text-white">
                                {{ auth()->user()->name ?? $t['administrator'] }}
                            </p>

                            <p class="text-xs text-slate-400">
                                {{ $t['administrator'] }}
                            </p>

                        </div>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-cyan-400 text-sm font-extrabold text-slate-950">
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </div>

                    </div>

                </div>

            </div>

        </header>


        {{-- =====================================================
            Page Content
        ====================================================== --}}
        <main>

            @if (isset($header))
                <div class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div class="px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </div>
            @endif

            {{ $slot }}

        </main>

    </div>


    {{-- =========================================================
        Sidebar JavaScript
    ========================================================== --}}
    <script>
        function openAdminSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('admin-mobile-overlay');

            if (!sidebar || !overlay) {
                return;
            }

            const isRtl = document.documentElement.dir === 'rtl';

            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.remove('translate-x-full');

            sidebar.classList.add(isRtl ? 'translate-x-0' : 'translate-x-0');

            overlay.classList.remove('hidden');

            document.body.classList.add('overflow-hidden');
        }


        function closeAdminSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('admin-mobile-overlay');

            if (!sidebar || !overlay) {
                return;
            }

            const isRtl = document.documentElement.dir === 'rtl';

            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add(isRtl ? 'translate-x-full' : '-translate-x-full');

            overlay.classList.add('hidden');

            document.body.classList.remove('overflow-hidden');
        }


        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAdminSidebar();
            }
        });


        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                const overlay = document.getElementById('admin-mobile-overlay');

                if (overlay) {
                    overlay.classList.add('hidden');
                }

                document.body.classList.remove('overflow-hidden');
            }
        });
    </script>

</body>

</html>
