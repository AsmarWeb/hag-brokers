<!DOCTYPE html>

@php
    $isAr = app()->getLocale() === 'ar';

    /*
    |--------------------------------------------------------------------------
    | Authenticated User
    |--------------------------------------------------------------------------
    */

    $authUser = auth()->user();

    /*
    |--------------------------------------------------------------------------
    | Laratrust
    |--------------------------------------------------------------------------
    |
    | Admin role gets full access.
    | All other users are controlled by permissions.
    |
    */

    $isAdmin = $authUser?->hasRole('admin|super-admin') ?? false;

    $can = function (string $permission) use ($authUser, $isAdmin): bool {
        if (!$authUser) {
            return false;
        }

        return $isAdmin || $authUser->hasPermission($permission);
    };

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */

    // Dashboard
    $canViewDashboard = $can('dashboard.view');

    // CRM
    $canViewLeads = $can('leads.view');
    $canViewContacts = $can('contacts.view');
    $canViewOpportunities = $can('opportunities.view');
    $canViewActivities = $can('activities.view');
    $canViewTasks = $can('tasks.view');

    // Shipments
    $canViewShipments = $can('shipments.view');
    $canCreateShipments = $can('shipments.create');

    // Quotations
    $canViewQuotations = $can('quotations.view');
    $canCreateQuotations = $can('quotations.create');

    // Customers
    $canViewCustomers = $can('customers.view');

    // Users
    $canViewUsers = $can('users.view');

    // Operations
    $canViewDocuments = $can('documents.view');
    $canViewNotifications = $can('notifications.view');
    $canViewPorts = $can('ports.view');
    $canViewCarriers = $can('carriers.view');
    $canViewVessels = $can('vessels.view');

    // Reports / Settings
    $canViewReports = $can('reports.view');
    $canViewSettings = $can('settings.view');

    /*
    |--------------------------------------------------------------------------
    | Navigation Groups
    |--------------------------------------------------------------------------
    */

    $hasCrmAccess =
        $canViewLeads ||
        $canViewContacts ||
        $canViewOpportunities ||
        $canViewActivities ||
        $canViewTasks ||
        $canViewShipments ||
        $canViewQuotations ||
        $canViewCustomers ||
        $canViewUsers;

    $hasOperationsAccess =
        $canViewShipments ||
        $canViewDocuments ||
        $canViewQuotations ||
        $canViewNotifications ||
        $canViewPorts ||
        $canViewCarriers ||
        $canViewVessels ||
        $canViewReports ||
        $canViewSettings;

    /*
    |--------------------------------------------------------------------------
    | Translations
    |--------------------------------------------------------------------------
    */

    $t = [
        'admin_dashboard' => $isAr ? 'لوحة تحكم الإدارة' : 'Admin Dashboard',

        'admin_control' => $isAr ? 'إدارة أعمال الوساطة' : 'Brokerage Management',

        'administrator' => $isAr ? 'مدير النظام' : 'Administrator',

        'full_access' => $isAr ? 'صلاحية كاملة على النظام' : 'Full system access',

        'main' => $isAr ? 'الرئيسية' : 'Main',

        'dashboard' => $isAr ? 'لوحة التحكم' : 'Dashboard',

        'crm_sales' => $isAr ? 'العلاقات والفرص التجارية' : 'Relationships & Opportunities',

        'leads' => $isAr ? 'العملاء المحتملون' : 'Leads',

        'contacts' => $isAr ? 'جهات الاتصال' : 'Contacts',

        'opportunities' => $isAr ? 'الفرص التجارية' : 'Opportunities',

        'activities' => $isAr ? 'الأنشطة' : 'Activities',

        'tasks' => $isAr ? 'المهام' : 'Tasks',

        'shipments' => $isAr ? 'الشحنات' : 'Shipments',

        'quotations' => $isAr ? 'عروض الأسعار' : 'Quotations',

        'customers' => $isAr ? 'العملاء' : 'Customers',

        'users' => $isAr ? 'المستخدمون' : 'Users',

        'operations' => $isAr ? 'التنفيذ والخدمات' : 'Execution & Services',

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

    /*
    |--------------------------------------------------------------------------
    | Current User Role
    |--------------------------------------------------------------------------
    */

    $userRole = 'User';

    if ($authUser) {
        if ($isAdmin) {
            $userRole = $isAr ? 'مدير النظام' : 'Administrator';
        } elseif (method_exists($authUser, 'roles')) {
            $role = $authUser->roles()->first();

            if ($role) {
                $userRole = $role->display_name ?? ($role->name ?? 'User');
            }
        }
    }

@endphp


<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

<head>
<script>
(() => {
    let collapsed = false, width = 288;
    try {
        collapsed = localStorage.getItem('hag-sidebar-collapsed') === 'true';
        const saved = Number(localStorage.getItem('hag-sidebar-width'));
        if (Number.isFinite(saved) && saved >= 240 && saved <= 380) width = saved;
    } catch (_) {}
    document.documentElement.dataset.hagSidebar = collapsed ? 'closed' : 'open';
    document.documentElement.style.setProperty('--hag-sidebar-width', width + 'px');
})();

</script>

    <!-- HAG admin theme bootstrap -->
    <script>
        (() => {
            const key = 'hag-admin-theme';
            const root = document.documentElement;
            const system = window.matchMedia('(prefers-color-scheme: dark)');
            let preference = null;
            try {
                const saved = localStorage.getItem(key);
                if (saved === 'light' || saved === 'dark') preference = saved;
            } catch (_) {}

            function apply(mode) {
                const dark = mode === 'dark';
                root.classList.toggle('dark', dark);
                root.style.colorScheme = mode;
                const button = document.getElementById('hag-admin-theme-toggle');
                if (button) {
                    button.setAttribute('aria-pressed', String(dark));
                    const label = dark ? button.dataset.lightLabel : button.dataset.darkLabel;
                    button.setAttribute('aria-label', label);
                    button.title = label;
                }
            }

            apply(preference || (system.matches ? 'dark' : 'light'));
            document.addEventListener('DOMContentLoaded', () => {
                const button = document.getElementById('hag-admin-theme-toggle');
                apply(preference || (system.matches ? 'dark' : 'light'));
                if (button) button.addEventListener('click', () => {
                    preference = root.classList.contains('dark') ? 'light' : 'dark';
                    try { localStorage.setItem(key, preference); } catch (_) {}
                    apply(preference);
                });
            });

            const followSystem = () => {
                if (!preference) apply(system.matches ? 'dark' : 'light');
            };
            if (system.addEventListener) system.addEventListener('change', followSystem);
            else if (system.addListener) system.addListener(followSystem);

            window.addEventListener('storage', event => {
                if (event.key !== key && event.key !== null) return;
                preference = event.newValue === 'dark' || event.newValue === 'light'
                    ? event.newValue : null;
                apply(preference || (system.matches ? 'dark' : 'light'));
            });
        })();
    </script>


    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ $title ?? $t['admin_dashboard'] }} — HAG Brokers
    </title>

    <link rel="preconnect" href="https://fonts.bunny.net">

    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900|noto-sans-arabic:400,500,600,700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/png" href="{{ asset('storage/favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>


<body class="hag-admin min-h-screen bg-slate-100 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">


    <a href="#admin-content" class="hag-skip-link">{{ $isAr ? 'الانتقال إلى المحتوى' : 'Skip to content' }}</a>

    {{-- =========================================================
        Mobile Overlay
    ========================================================== --}}

    <div id="admin-mobile-overlay" class="fixed inset-0 z-40 hidden bg-slate-950/70 backdrop-blur-sm lg:hidden"
        onclick="closeAdminSidebar()"></div>


    {{-- =========================================================
        Sidebar
    ========================================================== --}}

    <aside id="admin-sidebar" aria-label="{{ $isAr ? 'قائمة إدارة الأعمال' : 'Business navigation' }}"
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


        {{-- =====================================================
            User Access Indicator
        ====================================================== --}}

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

                        <p class="truncate text-xs font-bold uppercase tracking-wider text-cyan-400">
                            {{ $userRole }}
                        </p>

                        <p class="mt-0.5 truncate text-xs text-slate-500">

                            @if ($isAdmin)
                                {{ $t['full_access'] }}
                            @else
                                {{ $isAr ? 'الصلاحيات المحددة للحساب' : 'Assigned permissions' }}
                            @endif

                        </p>

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
            Navigation
        ====================================================== --}}

        <nav class="flex-1 overflow-y-auto px-4 py-6">


            {{-- =================================================
                Main
            ================================================== --}}

            <p class="mb-3 px-3 text-[10px] font-extrabold uppercase tracking-[0.18em] text-slate-600">
                {{ $t['main'] }}
            </p>


            <div class="space-y-1">


                {{-- Dashboard --}}

                @if ($canViewDashboard || $isAdmin)
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
                @endif


            </div>


            {{-- =================================================
                CRM & Sales
            ================================================== --}}

            @if ($hasCrmAccess)

                <p class="mb-3 mt-8 px-3 text-[10px] font-extrabold uppercase tracking-[0.18em] text-slate-600">
                    {{ $t['crm_sales'] }}
                </p>


                <div class="space-y-1">


                    {{-- Leads --}}

                    @if ($canViewLeads)
                        <a href="{{ route('admin.leads.index') }}"
                            class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
                            {{ request()->routeIs('admin.leads.*')
                                ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
                                : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                            <span class="text-base">
                                ◌
                            </span>

                            <span>
                                {{ $t['leads'] }}
                            </span>

                        </a>
                    @endif


                    {{-- Contacts --}}

                    @if ($canViewContacts)
                        <a href="{{ route('admin.contacts.index') }}"
                            class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
                            {{ request()->routeIs('admin.contacts.*')
                                ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
                                : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                            <span class="text-base">
                                ◎
                            </span>

                            <span>
                                {{ $t['contacts'] }}
                            </span>

                        </a>
                    @endif


                    {{-- Opportunities --}}

                    @if ($canViewOpportunities)
                        <a href="{{ route('admin.opportunities.index') }}"
                            class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
                            {{ request()->routeIs('admin.opportunities.*')
                                ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
                                : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                            <span class="text-base">
                                ◆
                            </span>

                            <span>
                                {{ $t['opportunities'] }}
                            </span>

                        </a>
                    @endif


                    {{-- Activities --}}

                    @if ($canViewActivities)
                        <a href="{{ route('admin.activities.index') }}"
                            class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
                            {{ request()->routeIs('admin.activities.*')
                                ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
                                : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                            <span class="text-base">
                                ◷
                            </span>

                            <span>
                                {{ $t['activities'] }}
                            </span>

                        </a>
                    @endif


                    {{-- Tasks --}}

                    @if ($canViewTasks)
                        <a href="{{ route('admin.tasks.index') }}"
                            class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
                            {{ request()->routeIs('admin.tasks.*')
                                ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
                                : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                            <span class="text-base">
                                ✓
                            </span>

                            <span>
                                {{ $t['tasks'] }}
                            </span>

                        </a>
                    @endif


                    {{-- Shipments --}}

                    @if ($canViewShipments)
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
                    @endif


                    {{-- Quotations --}}

                    @if ($canViewQuotations)
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
                    @endif


                    {{-- Customers --}}

                    @if ($canViewCustomers)
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
                    @endif


                    {{-- Users --}}

                    @if ($canViewUsers)
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
                    @endif

                </div>

            @endif


            {{-- =================================================
                Operations
            ================================================== --}}

            @if ($hasOperationsAccess)

                <p class="mb-3 mt-8 px-3 text-[10px] font-extrabold uppercase tracking-[0.18em] text-slate-600">
                    {{ $t['operations'] }}
                </p>


                <div class="space-y-1">


                    {{-- Shipments --}}

                    @if ($canViewShipments)
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
                    @endif


                    {{-- Documents --}}

                    @if ($canViewDocuments)
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

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M15 3v5h5" />

                            </svg>

                            <span>
                                {{ $t['documents'] }}
                            </span>

                        </a>
                    @endif

                    @if (Route::has('admin.document-templates.index'))
                        <a href="{{ route('admin.document-templates.index') }}"
                            class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
        {{ request()->routeIs('admin.document-templates.*')
            ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
            : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                            <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M7 3.75h7.5L19 8.25v12A1.75 1.75 0 0117.25 22h-10.5A1.75 1.75 0 015 20.25V5.5A1.75 1.75 0 016.75 3.75H7z" />

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M14 3.75v5h5" />

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M8.5 14h7M8.5 17h5" />
                            </svg>

                            <span>
                                {{ $isAr ? 'قوالب المستندات' : 'Document Templates' }}
                            </span>

                        </a>
                    @endif

                    @if (Route::has('admin.company.edit'))
                        <a href="{{ route('admin.company.edit') }}"
                            class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
        {{ request()->routeIs('admin.company.*')
            ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
            : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 21h18M5 21V5a2 2 0 012-2h6a2 2 0 012 2v16M15 21V9a2 2 0 012-2h2a2 2 0 012 2v12M9 7h2M9 11h2M9 15h2" />
                            </svg>

                            <span>
                                {{ $isAr ? 'إعدادات الشركة' : 'Company Settings' }}
                            </span>
                        </a>
                    @endif


                    {{-- Quotations --}}

                    @if ($canViewQuotations)
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
                    @endif


                    {{-- Notifications --}}

                    @if ($canViewNotifications)
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
                    @endif


                    {{-- Ports --}}

                    @if ($canViewPorts)
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
                    @endif


                    {{-- Carriers --}}

                    @if ($canViewCarriers)
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
                    @endif


                    {{-- Vessels --}}

                    @if ($canViewVessels)
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
                    @endif


                    {{-- Phase 9D financial reports: administrative financial data only. --}}
                    @if ($isAdmin)
                        <a href="{{ route('admin.finance.index') }}"
                            class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
                            {{ request()->routeIs('admin.finance.*') ? 'bg-cyan-400 text-slate-950' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <svg class="h-5 w-5 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 5h16v14H4zM8 9h8M8 13h3M8 16h8" />
                            </svg>
                            <span>{{ __('finance.sidebar') }}</span>
                        </a>
                    @endif

                    @if ($isAdmin)
                        <a href="{{ route('admin.invoice-drafts.index') }}"
                            class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition {{ request()->routeIs('admin.invoice-drafts.*') ? 'bg-cyan-400 text-slate-950' : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">
                            <svg class="h-5 w-5 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M7 3h10l3 3v15H4V3h3m1 6h8m-8 4h8m-8 4h5" /></svg>
                            <span>{{ __('invoice_drafts.title') }}</span>
                        </a>
                    @endif

                    {{-- Reports --}}

                    @if ($canViewReports)
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
                    @endif


                    {{-- Settings --}}

                    @if ($canViewSettings)
                        <a href="{{ route('admin.settings.edit') }}"
                            class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition
                            {{ request()->routeIs('admin.settings.*')
                                ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/10'
                                : 'text-slate-400 hover:bg-slate-900 hover:text-white' }}">

                            <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">

                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.5 6h3m-6.75 3.75h10.5M5.25 9.75v7.5A2.25 2.25 0 007.5 19.5h9a2.25 2.25 0 002.25-2.25v-7.5M8.25 9.75V6.75A2.25 2.25 0 0110.5 4.5h3a2.25 2.25 0 012.25 2.25v3M12 13.5v3" />

                            </svg>

                            <span>
                                {{ $t['settings'] }}
                            </span>

                        </a>
                    @endif


                </div>

            @endif


            {{-- =================================================
                System
            ================================================== --}}

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


                {{-- Website --}}

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


        {{-- =========================================================
            User / Logout
        ========================================================== --}}

        <div class="shrink-0 border-t border-slate-800 p-4">

            <div class="rounded-2xl bg-slate-900 p-3">


                <div class="flex items-center gap-3">


                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-cyan-400 font-extrabold text-slate-950">

                        {{ strtoupper(substr($authUser?->name ?? 'A', 0, 1)) }}

                    </div>


                    <div class="min-w-0 flex-1">

                        <p class="truncate text-sm font-bold text-white">

                            {{ $authUser?->name ?? $t['administrator'] }}

                        </p>


                        <p class="truncate text-xs text-slate-500">

                            {{ $authUser?->email ?? '' }}

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



        <div id="hag-sidebar-resize" role="separator" tabindex="0" aria-orientation="vertical"
            aria-controls="admin-sidebar" aria-valuemin="240" aria-valuemax="380" aria-valuenow="288"
            aria-label="{{ $isAr ? 'تغيير عرض القائمة الجانبية' : 'Resize sidebar' }}"
            title="{{ $isAr ? 'اسحب لتغيير العرض، أو استخدم مفاتيح الأسهم. انقر مرتين لإعادة العرض.' : 'Drag to resize, or use arrow keys. Double-click to reset.' }}"></div>
    </aside>


    {{-- =========================================================
        Main Wrapper
    ========================================================== --}}

    <div class="hag-shell min-h-screen">


        {{-- =====================================================
            Top Header
        ====================================================== --}}

        <header
            class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/90">

            <div class="flex h-20 items-center justify-between px-4 sm:px-6 lg:px-8">


                {{-- Left --}}

                <div class="flex items-center gap-3">


                    {{-- Mobile menu --}}

                    <button type="button" id="hag-sidebar-toggle" aria-controls="admin-sidebar" aria-expanded="false" data-open-label="{{ $t['open_menu'] }}" data-close-label="{{ $t['close_menu'] }}"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-cyan-300 hover:text-cyan-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-cyan-500 dark:hover:text-cyan-400"
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


                    {{-- HAG admin light/dark toggle --}}
                    <button id="hag-admin-theme-toggle" type="button"
                        data-light-label="{{ $isAr ? 'تفعيل الوضع النهاري' : 'Switch to light mode' }}"
                        data-dark-label="{{ $isAr ? 'تفعيل الوضع الليلي' : 'Switch to dark mode' }}"
                        aria-label="{{ $isAr ? 'تبديل مظهر لوحة الإدارة' : 'Toggle admin appearance' }}"
                        aria-pressed="false"
                        class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-cyan-400 hover:text-cyan-700 dark:border-slate-700 dark:bg-slate-900 dark:text-cyan-400 dark:hover:border-cyan-500">
                        <svg class="h-5 w-5 dark:hidden" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.9 13a9 9 0 0 1-9.9-9.9A9 9 0 1 0 20.9 13Z" />
                        </svg>
                        <svg class="hidden h-5 w-5 dark:block" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="4" />
                            <path stroke-linecap="round" d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.4 1.4m11.2 11.2L19 19M5 19l1.4-1.4M17.6 6.4 19 5" />
                        </svg>
                    </button>

                    {{-- Language --}}

                    <div
                        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold dark:border-slate-700 dark:bg-slate-900">

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

                    @if ($canViewNotifications)
                        <a href="{{ route('admin.notifications.index') }}"
                            class="relative flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-cyan-300 hover:text-cyan-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-cyan-500 dark:hover:text-cyan-400"
                            aria-label="{{ $t['notifications'] }}">

                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />

                            </svg>

                        </a>
                    @endif


                    {{-- User --}}

                    <div
                        class="hidden items-center gap-3 border-s border-slate-200 ps-3 dark:border-slate-800 sm:flex">

                        <div class="text-end">

                            <p class="text-sm font-bold text-slate-900 dark:text-white">
                                {{ $authUser?->name ?? $t['administrator'] }}
                            </p>

                            <p class="text-xs text-slate-400">
                                {{ $userRole }}
                            </p>

                        </div>


                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-cyan-400 text-sm font-extrabold text-slate-950">

                            {{ strtoupper(substr($authUser?->name ?? 'A', 0, 1)) }}

                        </div>

                    </div>


                </div>

            </div>

        </header>


        {{-- =====================================================
            Page Content
        ====================================================== --}}

        <main id="admin-content" class="hag-admin-content" tabindex="-1">

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
(() => {
    const root = document.documentElement;
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('admin-mobile-overlay');
    const toggle = document.getElementById('hag-sidebar-toggle');
    const handle = document.getElementById('hag-sidebar-resize');
    const desktop = window.matchMedia('(min-width: 1024px)');
    const rtl = () => root.dir === 'rtl';
    let mobileOpen = false, drag = null, swipe = null;
    let width = parseInt(root.style.getPropertyValue('--hag-sidebar-width'), 10) || 288;
    if (!sidebar || !overlay || !toggle || !handle) return;

    const save = (key, value) => { try { localStorage.setItem(key, String(value)); } catch (_) {} };
    const isOpen = () => desktop.matches ? root.dataset.hagSidebar !== 'closed' : mobileOpen;
    function sync() {
        const open = isOpen();
        root.dataset.hagMobileSidebar = mobileOpen ? 'open' : 'closed';
        overlay.classList.toggle('hidden', desktop.matches || !mobileOpen);
        document.body.classList.toggle('overflow-hidden', !desktop.matches && mobileOpen);
        sidebar.inert = !open;
        sidebar.setAttribute('aria-hidden', String(!open));
        toggle.setAttribute('aria-expanded', String(open));
        const label = open ? toggle.dataset.closeLabel : toggle.dataset.openLabel;
        toggle.setAttribute('aria-label', label);
        toggle.title = label;
        if (!desktop.matches && mobileOpen) {
            sidebar.setAttribute('role', 'dialog');
            sidebar.setAttribute('aria-modal', 'true');
        } else {
            sidebar.removeAttribute('role');
            sidebar.removeAttribute('aria-modal');
        }
        handle.setAttribute('aria-valuenow', String(width));
        handle.setAttribute('aria-valuetext', width + ' px');
    }
    function setOpen(open, focus = true) {
        if (desktop.matches) {
            root.dataset.hagSidebar = open ? 'open' : 'closed';
            save('hag-sidebar-collapsed', !open);
        } else mobileOpen = open;
        sync();
        if (focus && !desktop.matches && open) sidebar.querySelector('a, button')?.focus();
        else if (focus && !open) toggle.focus();
    }
    window.openAdminSidebar = () => setOpen(true);
    window.closeAdminSidebar = () => setOpen(false);
    toggle.addEventListener('click', () => setOpen(!isOpen()));
    function setWidth(value, persist = false) {
        width = Math.round(Math.max(240, Math.min(380, value)));
        root.style.setProperty('--hag-sidebar-width', width + 'px');
        handle.setAttribute('aria-valuenow', String(width));
        handle.setAttribute('aria-valuetext', width + ' px');
        if (persist) save('hag-sidebar-width', width);
    }
    handle.addEventListener('pointerdown', event => {
        if (!desktop.matches || !isOpen() || event.button !== 0) return;
        event.preventDefault();
        drag = { x: event.clientX, width, id: event.pointerId };
        handle.setPointerCapture(event.pointerId);
        root.classList.add('hag-sidebar-dragging');
    });
    handle.addEventListener('pointermove', event => {
        if (!drag || drag.id !== event.pointerId) return;
        setWidth(drag.width + (event.clientX - drag.x) * (rtl() ? -1 : 1));
    });
    function endDrag(event) {
        if (!drag || (event && drag.id !== event.pointerId)) return;
        save('hag-sidebar-width', width);
        drag = null;
        root.classList.remove('hag-sidebar-dragging');
    }
    handle.addEventListener('pointerup', endDrag);
    handle.addEventListener('pointercancel', endDrag);
    handle.addEventListener('lostpointercapture', endDrag);
    handle.addEventListener('keydown', event => {
        let next = width;
        if (event.key === 'Home') next = 240;
        else if (event.key === 'End') next = 380;
        else if (event.key === 'ArrowRight') next += rtl() ? -16 : 16;
        else if (event.key === 'ArrowLeft') next += rtl() ? 16 : -16;
        else return;
        event.preventDefault(); setWidth(next, true);
    });
    handle.addEventListener('dblclick', () => setWidth(288, true));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !desktop.matches && mobileOpen) setOpen(false);
        if (event.key !== 'Tab' || desktop.matches || !mobileOpen) return;
        const items = Array.from(sidebar.querySelectorAll('a[href], button, [tabindex="0"]'))
            .filter(el => !el.disabled && el.getClientRects().length > 0);
        const first = items[0], last = items[items.length - 1];
        if (!first) return;
        if (event.shiftKey && (document.activeElement === first || !sidebar.contains(document.activeElement))) {
            event.preventDefault(); last.focus();
        } else if (!event.shiftKey && (document.activeElement === last || !sidebar.contains(document.activeElement))) {
            event.preventDefault(); first.focus();
        }
    });
    // Touch: start at the screen edge to open; swipe inside the drawer to close.
    document.addEventListener('pointerdown', event => {
        if (desktop.matches || event.pointerType !== 'touch' || !event.isPrimary) return;
        if (event.target.closest('input,select,textarea,button,[role="separator"]')) return;
        const edge = rtl() ? event.clientX >= window.innerWidth - 24 : event.clientX <= 24;
        if ((!mobileOpen && edge) || (mobileOpen && sidebar.contains(event.target))) {
            swipe = { x: event.clientX, y: event.clientY, open: mobileOpen, id: event.pointerId };
        }
    }, { passive: true });
    document.addEventListener('pointerup', event => {
        if (!swipe || swipe.id !== event.pointerId) return;
        const dx = (event.clientX - swipe.x) * (rtl() ? -1 : 1);
        const dy = Math.abs(event.clientY - swipe.y);
        if (Math.abs(dx) > 70 && Math.abs(dx) > dy * 1.5) {
            if (!swipe.open && dx > 0) setOpen(true);
            else if (swipe.open && dx < 0) setOpen(false);
        }
        swipe = null;
    }, { passive: true });
    document.addEventListener('pointercancel', () => { swipe = null; });
    const onBreakpoint = () => { mobileOpen = false; endDrag(); sync(); };
    if (desktop.addEventListener) desktop.addEventListener('change', onBreakpoint);
    else desktop.addListener(onBreakpoint);
    setWidth(width); sync();
})();

    </script>


</body>

</html>
