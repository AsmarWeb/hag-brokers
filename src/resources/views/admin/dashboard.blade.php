<x-admin-layout>

    @php
        $isAr = app()->getLocale() === 'ar';
        $dir = $isAr ? 'rtl' : 'ltr';

        $authUser = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Laratrust Permissions
        |--------------------------------------------------------------------------
        |
        | Admin لديه صلاحية كاملة.
        | باقي المستخدمين يعتمدون على Permissions الخاصة بهم.
        |
        */

        $isAdmin = $authUser?->hasRole('admin|super-admin') ?? false;

        $can = function (string $permission) use ($authUser, $isAdmin): bool {
            if (!$authUser) {
                return false;
            }

            return $isAdmin || $authUser->hasPermission($permission);
        };

        // Shipments
        $canViewShipments = $can('shipments.view');
        $canCreateShipments = $can('shipments.create');
        $canEditShipments = $can('shipments.edit');
        $canDeleteShipments = $can('shipments.delete');

        // Quotations
        $canViewQuotations = $can('quotations.view');
        $canCreateQuotations = $can('quotations.create');
        $canEditQuotations = $can('quotations.edit');
        $canDeleteQuotations = $can('quotations.delete');

        // Customers
        $canViewCustomers = $can('customers.view');
        $canCreateCustomers = $can('customers.create');
        $canEditCustomers = $can('customers.edit');
        $canDeleteCustomers = $can('customers.delete');

        // Users
        $canViewUsers = $can('users.view');
        $canCreateUsers = $can('users.create');
        $canEditUsers = $can('users.edit');
        $canDeleteUsers = $can('users.delete');

        /*
        |--------------------------------------------------------------------------
        | Dashboard Access
        |--------------------------------------------------------------------------
        */

        $hasDashboardAccess = $canViewShipments || $canViewQuotations || $canViewCustomers || $canViewUsers;
    @endphp


    <div dir="{{ $dir }}" class="min-h-screen bg-slate-50 dark:bg-[#06101d]">

        {{-- ================================================================
            HEADER
        ================================================================= --}}
        <div class="mb-8">

            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                        {{ $isAr ? 'لوحة التحكم' : 'Admin Dashboard' }}
                    </h1>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $isAr ? 'مرحباً بك في لوحة تحكم HAG Brokers' : 'Welcome to the HAG Brokers administration dashboard.' }}
                    </p>
                </div>


                {{-- Header Actions --}}
                <div class="flex flex-wrap gap-3">

                    @if ($canCreateShipments)
                        <a href="{{ route('admin.shipments.create') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-cyan-500 px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>

                            {{ $isAr ? 'شحنة جديدة' : 'New Shipment' }}
                        </a>
                    @endif


                    @if ($canViewQuotations)
                        <a href="{{ route('admin.quotations.index') }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-cyan-400 hover:text-cyan-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-cyan-400 dark:hover:text-cyan-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 14l6-6m2-5H7a2 2 0 00-2 2v14a2 2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2z" />
                            </svg>

                            {{ $isAr ? 'عروض الأسعار' : 'Quotations' }}
                        </a>
                    @endif

                </div>

            </div>

        </div>


        @if ($hasDashboardAccess)

            {{-- ============================================================
                MAIN KPI CARDS
            ============================================================= --}}

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

                {{-- Total Shipments --}}
                @if ($canViewShipments)
                    <div
                        class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-400/50 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900">

                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'إجمالي الشحنات' : 'Total Shipments' }}
                                </p>

                                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                                    {{ $stats['total_shipments'] ?? 0 }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-cyan-500/10 p-3 text-cyan-500">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 7l9-4 9 4-9 4-9-4zm0 0v10l9 4 9-4V7M12 11v10" />
                                </svg>
                            </div>

                        </div>

                        <a href="{{ route('admin.shipments.index') }}"
                            class="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-cyan-500 hover:text-cyan-400">
                            {{ $isAr ? 'عرض الشحنات' : 'View shipments' }}
                        </a>

                    </div>
                @endif


                {{-- In Transit --}}
                @if ($canViewShipments)
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">

                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'قيد الشحن' : 'In Transit' }}
                                </p>

                                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                                    {{ $stats['in_transit'] ?? 0 }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-blue-500/10 p-3 text-blue-500">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 12h14M13 6l6 6-6 6" />
                                </svg>
                            </div>

                        </div>

                    </div>
                @endif


                {{-- Delivered --}}
                @if ($canViewShipments)
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">

                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'تم التسليم' : 'Delivered' }}
                                </p>

                                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                                    {{ $stats['delivered'] ?? 0 }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-emerald-500/10 p-3 text-emerald-500">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>

                        </div>

                    </div>
                @endif


                {{-- Pending Quotations --}}
                @if ($canViewQuotations)
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">

                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'عروض أسعار معلقة' : 'Pending Quotations' }}
                                </p>

                                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                                    {{ $stats['pending_quotations'] ?? 0 }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-amber-500/10 p-3 text-amber-500">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h8l4 4v12a2 2 0 01-2 2z" />
                                </svg>
                            </div>

                        </div>

                        <a href="{{ route('admin.quotations.index') }}"
                            class="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-amber-500 hover:text-amber-400">
                            {{ $isAr ? 'عرض العروض' : 'View quotations' }}
                        </a>

                    </div>
                @endif

            </div>


            {{-- ============================================================
                SECONDARY STATS
            ============================================================= --}}

            <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-3">

                @if ($canViewShipments)
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">

                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ $isAr ? 'شحنات معلقة' : 'Pending Shipments' }}
                        </p>

                        <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">
                            {{ $stats['pending_shipments'] ?? 0 }}
                        </p>

                    </div>
                @endif


                @if ($canViewQuotations)
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">

                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ $isAr ? 'إجمالي عروض الأسعار' : 'Total Quotations' }}
                        </p>

                        <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">
                            {{ $stats['total_quotations'] ?? 0 }}
                        </p>

                    </div>
                @endif


                @if ($canViewCustomers)
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">

                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ $isAr ? 'العملاء' : 'Customers' }}
                        </p>

                        <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">
                            {{ $stats['customers'] ?? 0 }}
                        </p>

                        <a href="{{ route('admin.customers.index') }}"
                            class="mt-3 inline-block text-xs font-semibold text-cyan-500 hover:text-cyan-400">
                            {{ $isAr ? 'عرض العملاء' : 'View customers' }}
                        </a>

                    </div>
                @endif

            </div>


            {{-- ============================================================
                RECENT DATA
            ============================================================= --}}

            <div class="mt-8 grid grid-cols-1 gap-6 xl:grid-cols-2">

                {{-- Recent Shipments --}}
                @if ($canViewShipments)

                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                        <div
                            class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">

                            <div>
                                <h2 class="font-semibold text-slate-900 dark:text-white">
                                    {{ $isAr ? 'أحدث الشحنات' : 'Recent Shipments' }}
                                </h2>

                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'آخر الشحنات المسجلة في النظام' : 'Latest shipments in the system' }}
                                </p>
                            </div>

                            <a href="{{ route('admin.shipments.index') }}"
                                class="text-xs font-semibold text-cyan-500 hover:text-cyan-400">
                                {{ $isAr ? 'عرض الكل' : 'View all' }}
                            </a>

                        </div>


                        <div class="divide-y divide-slate-100 dark:divide-slate-800">

                            @forelse ($recentShipments ?? [] as $shipment)
                                <a href="{{ route('admin.shipments.show', $shipment) }}"
                                    class="block px-5 py-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/50">

                                    <div class="flex items-center justify-between gap-4">

                                        <div class="min-w-0">

                                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                                                {{ $shipment->reference ?? ($shipment->shipment_number ?? '#' . $shipment->id) }}
                                            </p>

                                            <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">
                                                {{ $shipment->customer->company_name ?? ($shipment->customer->name ?? '—') }}
                                            </p>

                                        </div>

                                        <div class="text-end">

                                            <span
                                                class="inline-flex rounded-full bg-cyan-500/10 px-2.5 py-1 text-xs font-medium text-cyan-500">
                                                {{ $shipment->status ?? '—' }}
                                            </span>

                                            @if ($shipment->created_at)
                                                <p class="mt-1 text-[11px] text-slate-400">
                                                    {{ $shipment->created_at->format('Y-m-d') }}
                                                </p>
                                            @endif

                                        </div>

                                    </div>

                                </a>

                            @empty

                                <div class="px-5 py-10 text-center">

                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ $isAr ? 'لا توجد شحنات حالياً.' : 'No shipments found.' }}
                                    </p>

                                </div>
                            @endforelse

                        </div>

                    </section>

                @endif


                {{-- Recent Quotations --}}
                @if ($canViewQuotations)

                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                        <div
                            class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">

                            <div>
                                <h2 class="font-semibold text-slate-900 dark:text-white">
                                    {{ $isAr ? 'أحدث عروض الأسعار' : 'Recent Quotations' }}
                                </h2>

                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'آخر عروض الأسعار في النظام' : 'Latest quotations in the system' }}
                                </p>
                            </div>

                            <a href="{{ route('admin.quotations.index') }}"
                                class="text-xs font-semibold text-cyan-500 hover:text-cyan-400">
                                {{ $isAr ? 'عرض الكل' : 'View all' }}
                            </a>

                        </div>


                        <div class="divide-y divide-slate-100 dark:divide-slate-800">

                            @forelse ($recentQuotations ?? [] as $quotation)
                                <a href="{{ route('admin.quotations.show', $quotation) }}"
                                    class="block px-5 py-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/50">

                                    <div class="flex items-center justify-between gap-4">

                                        <div class="min-w-0">

                                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                                                {{ $quotation->reference ?? ($quotation->quotation_number ?? '#' . $quotation->id) }}
                                            </p>

                                            <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">
                                                {{ $quotation->customer->company_name ?? ($quotation->customer->name ?? '—') }}
                                            </p>

                                        </div>

                                        <div class="text-end">

                                            <span
                                                class="inline-flex rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-500">
                                                {{ $quotation->status ?? '—' }}
                                            </span>

                                            @if ($quotation->created_at)
                                                <p class="mt-1 text-[11px] text-slate-400">
                                                    {{ $quotation->created_at->format('Y-m-d') }}
                                                </p>
                                            @endif

                                        </div>

                                    </div>

                                </a>

                            @empty

                                <div class="px-5 py-10 text-center">

                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ $isAr ? 'لا توجد عروض أسعار حالياً.' : 'No quotations found.' }}
                                    </p>

                                </div>
                            @endforelse

                        </div>

                    </section>

                @endif

            </div>


            {{-- ============================================================
                QUICK ACTIONS
            ============================================================= --}}

            <section class="mt-8">

                <div class="mb-4">

                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                        {{ $isAr ? 'إجراءات سريعة' : 'Quick Actions' }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $isAr
                            ? 'الوصول السريع إلى الأقسام التي لديك صلاحية استخدامها.'
                            : 'Quick access to the sections you are allowed to use.' }}
                    </p>

                </div>


                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                    {{-- Shipments --}}
                    @if ($canViewShipments)
                        <a href="{{ route('admin.shipments.index') }}"
                            class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-cyan-400/50 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900">

                            <div
                                class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-500">

                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 7l9-4 9 4-9 4-9-4zm0 0v10l9 4 9-4V7M12 11v10" />
                                </svg>

                            </div>

                            <h3 class="font-semibold text-slate-900 dark:text-white">
                                {{ $isAr ? 'الشحنات' : 'Shipments' }}
                            </h3>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ $isAr ? 'إدارة ومتابعة الشحنات.' : 'Manage and track shipments.' }}
                            </p>

                        </a>
                    @endif


                    {{-- Quotations --}}
                    @if ($canViewQuotations)
                        <a href="{{ route('admin.quotations.index') }}"
                            class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-cyan-400/50 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900">

                            <div
                                class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500">

                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 14l6-6m2-5H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2z" />
                                </svg>

                            </div>

                            <h3 class="font-semibold text-slate-900 dark:text-white">
                                {{ $isAr ? 'عروض الأسعار' : 'Quotations' }}
                            </h3>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ $isAr ? 'إنشاء وإدارة عروض الأسعار.' : 'Create and manage quotations.' }}
                            </p>

                        </a>
                    @endif


                    {{-- Customers --}}
                    @if ($canViewCustomers)
                        <a href="{{ route('admin.customers.index') }}"
                            class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-cyan-400/50 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900">

                            <div
                                class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500">

                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5V4H2v16h5m10 0v-5H7v5m5-9a3 3 0 100-6 3 3 0 000 6z" />
                                </svg>

                            </div>

                            <h3 class="font-semibold text-slate-900 dark:text-white">
                                {{ $isAr ? 'العملاء' : 'Customers' }}
                            </h3>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ $isAr ? 'إدارة بيانات العملاء.' : 'Manage customer information.' }}
                            </p>

                        </a>
                    @endif


                    {{-- Users --}}
                    @if ($canViewUsers)
                        <a href="{{ route('admin.users.index') }}"
                            class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-cyan-400/50 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900">

                            <div
                                class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-purple-500/10 text-purple-500">

                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5V4H2v16h5m10 0v-5H7v5m5-9a3 3 0 100-6 3 3 0 000 6z" />
                                </svg>

                            </div>

                            <h3 class="font-semibold text-slate-900 dark:text-white">
                                {{ $isAr ? 'المستخدمون' : 'Users' }}
                            </h3>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ $isAr ? 'إدارة المستخدمين والصلاحيات.' : 'Manage users and permissions.' }}
                            </p>

                        </a>
                    @endif

                </div>

            </section>
        @else
            {{-- ============================================================
                NO ACCESS
            ============================================================= --}}

            <div class="flex min-h-[500px] items-center justify-center">

                <div class="max-w-md text-center">

                    <div
                        class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-200 text-slate-500 dark:bg-slate-800 dark:text-slate-400">

                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-7a2 2 0 00-2-2H6a2 2 0 00-2 2v7a2 2 0 002 2zm10-11V7a4 4 0 00-8 0v1" />
                        </svg>

                    </div>

                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                        {{ $isAr ? 'لا توجد صلاحيات' : 'No Access' }}
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        {{ $isAr
                            ? 'ليس لديك صلاحيات للوصول إلى أقسام لوحة التحكم. يرجى التواصل مع مسؤول النظام.'
                            : 'You do not have permission to access the dashboard sections. Please contact the system administrator.' }}
                    </p>

                </div>

            </div>

        @endif


        {{-- ================================================================
            FOOTER
        ================================================================= --}}
        <div class="mt-10 border-t border-slate-200 pt-6 dark:border-slate-800">

            <div class="flex flex-col gap-2 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between">

                <p>
                    © {{ date('Y') }} HAG Brokers.
                    {{ $isAr ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}
                </p>

                <p>
                    {{ $isAr ? 'منصة حلول الشحن البحري' : 'Sea Freight Solutions' }}
                </p>

            </div>

        </div>

    </div>

</x-admin-layout>
