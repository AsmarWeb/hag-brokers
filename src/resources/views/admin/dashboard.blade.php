<x-admin-layout>

    @php
        $isAr = app()->getLocale() === 'ar';
        $dir = $isAr ? 'rtl' : 'ltr';

        $authUser = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Laratrust Permissions
        |--------------------------------------------------------------------------
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


    <div dir="{{ $dir }}" class="min-h-screen bg-slate-50 text-slate-900 dark:bg-[#06101d] dark:text-slate-100">

        {{-- ================================================================
            HEADER
        ================================================================= --}}
        <div class="mb-8">

            <div
                class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-[#0a1728]">

                {{-- Decorative background --}}
                <div
                    class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-cyan-500/10 blur-3xl dark:bg-cyan-400/10">
                </div>

                <div
                    class="pointer-events-none absolute -bottom-24 -left-20 h-56 w-56 rounded-full bg-blue-500/10 blur-3xl dark:bg-blue-500/10">
                </div>


                <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                    {{-- Title --}}
                    <div>

                        <div class="mb-2 flex items-center gap-2">

                            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-cyan-400 shadow-lg shadow-cyan-400/50">
                            </span>

                            <span
                                class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-600 dark:text-cyan-400">
                                HAG Brokers
                            </span>

                        </div>

                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ $isAr ? 'لوحة التحكم' : 'Admin Dashboard' }}
                        </h1>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ $isAr ? 'مرحباً بك في لوحة تحكم HAG Brokers' : 'Welcome to the HAG Brokers administration dashboard.' }}
                        </p>

                    </div>


                    {{-- Header Actions --}}
                    <div class="relative flex flex-wrap gap-3">

                        @if ($canCreateShipments)
                            <a href="{{ route('admin.shipments.create') }}"
                                class="inline-flex items-center gap-2 rounded-xl bg-cyan-500 px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-lg shadow-cyan-500/20 transition duration-200 hover:-translate-y-0.5 hover:bg-cyan-400 hover:shadow-cyan-500/30">

                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>

                                {{ $isAr ? 'شحنة جديدة' : 'New Shipment' }}

                            </a>
                        @endif


                        @if ($canViewQuotations)
                            <a href="{{ route('admin.quotations.index') }}"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-cyan-400 hover:text-cyan-600 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-200 dark:hover:border-cyan-400 dark:hover:text-cyan-400">

                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 14l6-6m2-5H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2z" />
                                </svg>

                                {{ $isAr ? 'عروض الأسعار' : 'Quotations' }}

                            </a>
                        @endif

                    </div>

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
                        class="group relative overflow-hidden rounded-2xl border border-cyan-100 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-cyan-300 hover:shadow-xl hover:shadow-cyan-500/10 dark:border-cyan-500/10 dark:bg-[#0a1728] dark:hover:border-cyan-500/30">

                        <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-cyan-500/10 blur-2xl"></div>

                        <div class="relative flex items-center justify-between">

                            <div>

                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'إجمالي الشحنات' : 'Total Shipments' }}
                                </p>

                                <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                                    {{ $stats['total_shipments'] ?? 0 }}
                                </p>

                            </div>

                            <div
                                class="rounded-2xl border border-cyan-200 bg-cyan-50 p-3 text-cyan-600 dark:border-cyan-500/20 dark:bg-cyan-500/10 dark:text-cyan-400">

                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 7l9-4 9 4-9 4-9-4zm0 0v10l9 4 9-4V7M12 11v10" />
                                </svg>

                            </div>

                        </div>

                        <a href="{{ route('admin.shipments.index') }}"
                            class="relative mt-4 inline-flex items-center gap-1 text-xs font-semibold text-cyan-600 transition hover:text-cyan-500 dark:text-cyan-400">

                            {{ $isAr ? 'عرض الشحنات' : 'View shipments' }}

                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>

                        </a>

                    </div>
                @endif


                {{-- In Transit --}}
                @if ($canViewShipments)
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-blue-100 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-blue-300 hover:shadow-xl hover:shadow-blue-500/10 dark:border-blue-500/10 dark:bg-[#0a1728] dark:hover:border-blue-500/30">

                        <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-blue-500/10 blur-2xl"></div>

                        <div class="relative flex items-center justify-between">

                            <div>

                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'قيد الشحن' : 'In Transit' }}
                                </p>

                                <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                                    {{ $stats['in_transit'] ?? 0 }}
                                </p>

                            </div>

                            <div
                                class="rounded-2xl border border-blue-200 bg-blue-50 p-3 text-blue-600 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-400">

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
                        class="group relative overflow-hidden rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-xl hover:shadow-emerald-500/10 dark:border-emerald-500/10 dark:bg-[#0a1728] dark:hover:border-emerald-500/30">

                        <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-emerald-500/10 blur-2xl"></div>

                        <div class="relative flex items-center justify-between">

                            <div>

                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'تم التسليم' : 'Delivered' }}
                                </p>

                                <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                                    {{ $stats['delivered'] ?? 0 }}
                                </p>

                            </div>

                            <div
                                class="rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-emerald-600 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400">

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
                        class="group relative overflow-hidden rounded-2xl border border-amber-100 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-amber-300 hover:shadow-xl hover:shadow-amber-500/10 dark:border-amber-500/10 dark:bg-[#0a1728] dark:hover:border-amber-500/30">

                        <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-amber-500/10 blur-2xl"></div>

                        <div class="relative flex items-center justify-between">

                            <div>

                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'عروض أسعار معلقة' : 'Pending Quotations' }}
                                </p>

                                <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                                    {{ $stats['pending_quotations'] ?? 0 }}
                                </p>

                            </div>

                            <div
                                class="rounded-2xl border border-amber-200 bg-amber-50 p-3 text-amber-600 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-400">

                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h8l4 4v12a2 2 0 01-2 2z" />
                                </svg>

                            </div>

                        </div>

                        <a href="{{ route('admin.quotations.index') }}"
                            class="relative mt-4 inline-flex items-center gap-1 text-xs font-semibold text-amber-600 transition hover:text-amber-500 dark:text-amber-400">

                            {{ $isAr ? 'عرض العروض' : 'View quotations' }}

                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>

                        </a>

                    </div>
                @endif

            </div>


            {{-- ============================================================
                SECONDARY STATS
            ============================================================= --}}

            <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-3">

                {{-- Pending Shipments --}}
                @if ($canViewShipments)
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-cyan-200 dark:border-slate-800 dark:bg-[#0a1728] dark:hover:border-cyan-500/20">

                        <div class="flex items-center justify-between">

                            <div>

                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'شحنات معلقة' : 'Pending Shipments' }}
                                </p>

                                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">
                                    {{ $stats['pending_shipments'] ?? 0 }}
                                </p>

                            </div>

                            <span class="h-2.5 w-2.5 rounded-full bg-cyan-400 shadow-lg shadow-cyan-400/30">
                            </span>

                        </div>

                    </div>
                @endif


                {{-- Total Quotations --}}
                @if ($canViewQuotations)
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-amber-200 dark:border-slate-800 dark:bg-[#0a1728] dark:hover:border-amber-500/20">

                        <div class="flex items-center justify-between">

                            <div>

                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'إجمالي عروض الأسعار' : 'Total Quotations' }}
                                </p>

                                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">
                                    {{ $stats['total_quotations'] ?? 0 }}
                                </p>

                            </div>

                            <span class="h-2.5 w-2.5 rounded-full bg-amber-400 shadow-lg shadow-amber-400/30">
                            </span>

                        </div>

                    </div>
                @endif


                {{-- Customers --}}
                @if ($canViewCustomers)
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-emerald-200 dark:border-slate-800 dark:bg-[#0a1728] dark:hover:border-emerald-500/20">

                        <div class="flex items-center justify-between">

                            <div>

                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'العملاء' : 'Customers' }}
                                </p>

                                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">
                                    {{ $stats['customers'] ?? 0 }}
                                </p>

                            </div>

                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 shadow-lg shadow-emerald-400/30">
                            </span>

                        </div>

                        <a href="{{ route('admin.customers.index') }}"
                            class="mt-3 inline-block text-xs font-semibold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400">

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
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-[#0a1728]">

                        <div
                            class="flex items-center justify-between border-b border-slate-200 bg-slate-50/70 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/40">

                            <div>

                                <div class="flex items-center gap-2">

                                    <span class="h-2 w-2 rounded-full bg-cyan-400 shadow-lg shadow-cyan-400/40">
                                    </span>

                                    <h2 class="font-semibold text-slate-900 dark:text-white">
                                        {{ $isAr ? 'أحدث الشحنات' : 'Recent Shipments' }}
                                    </h2>

                                </div>

                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'آخر الشحنات المسجلة في النظام' : 'Latest shipments in the system' }}
                                </p>

                            </div>

                            <a href="{{ route('admin.shipments.index') }}"
                                class="text-xs font-semibold text-cyan-600 transition hover:text-cyan-500 dark:text-cyan-400">

                                {{ $isAr ? 'عرض الكل' : 'View all' }}

                            </a>

                        </div>


                        <div class="divide-y divide-slate-100 dark:divide-slate-800">

                            @forelse ($recentShipments ?? [] as $shipment)
                                <a href="{{ route('admin.shipments.show', $shipment) }}"
                                    class="block px-5 py-4 transition duration-200 hover:bg-cyan-50/40 dark:hover:bg-cyan-500/[0.04]">

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
                                                class="inline-flex rounded-full border border-cyan-200 bg-cyan-50 px-2.5 py-1 text-xs font-medium text-cyan-700 dark:border-cyan-500/20 dark:bg-cyan-500/10 dark:text-cyan-400">

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

                                    <div
                                        class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">

                                        <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 7l9-4 9 4-9 4-9-4zm0 0v10l9 4 9-4V7M12 11v10" />
                                        </svg>

                                    </div>

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
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-[#0a1728]">

                        <div
                            class="flex items-center justify-between border-b border-slate-200 bg-slate-50/70 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/40">

                            <div>

                                <div class="flex items-center gap-2">

                                    <span class="h-2 w-2 rounded-full bg-amber-400 shadow-lg shadow-amber-400/40">
                                    </span>

                                    <h2 class="font-semibold text-slate-900 dark:text-white">
                                        {{ $isAr ? 'أحدث عروض الأسعار' : 'Recent Quotations' }}
                                    </h2>

                                </div>

                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'آخر عروض الأسعار في النظام' : 'Latest quotations in the system' }}
                                </p>

                            </div>

                            <a href="{{ route('admin.quotations.index') }}"
                                class="text-xs font-semibold text-amber-600 transition hover:text-amber-500 dark:text-amber-400">

                                {{ $isAr ? 'عرض الكل' : 'View all' }}

                            </a>

                        </div>


                        <div class="divide-y divide-slate-100 dark:divide-slate-800">

                            @forelse ($recentQuotations ?? [] as $quotation)
                                <a href="{{ route('admin.quotations.show', $quotation) }}"
                                    class="block px-5 py-4 transition duration-200 hover:bg-amber-50/40 dark:hover:bg-amber-500/[0.04]">

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
                                                class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-400">

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

                                    <div
                                        class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">

                                        <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 14l6-6m2-5H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2z" />
                                        </svg>

                                    </div>

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
                            class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-cyan-300 hover:shadow-xl hover:shadow-cyan-500/10 dark:border-slate-800 dark:bg-[#0a1728] dark:hover:border-cyan-500/30">

                            <div
                                class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl border border-cyan-200 bg-cyan-50 text-cyan-600 dark:border-cyan-500/20 dark:bg-cyan-500/10 dark:text-cyan-400">

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
                            class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-amber-300 hover:shadow-xl hover:shadow-amber-500/10 dark:border-slate-800 dark:bg-[#0a1728] dark:hover:border-amber-500/30">

                            <div
                                class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl border border-amber-200 bg-amber-50 text-amber-600 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-400">

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
                            class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-xl hover:shadow-emerald-500/10 dark:border-slate-800 dark:bg-[#0a1728] dark:hover:border-emerald-500/30">

                            <div
                                class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-600 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400">

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
                            class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-purple-300 hover:shadow-xl hover:shadow-purple-500/10 dark:border-slate-800 dark:bg-[#0a1728] dark:hover:border-purple-500/30">

                            <div
                                class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl border border-purple-200 bg-purple-50 text-purple-600 dark:border-purple-500/20 dark:bg-purple-500/10 dark:text-purple-400">

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

                <div
                    class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-800 dark:bg-[#0a1728]">

                    <div
                        class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl border border-slate-200 bg-slate-100 text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">

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
