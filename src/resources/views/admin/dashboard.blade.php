@php
    $isAr = app()->getLocale() === 'ar';
    $dir = $isAr ? 'rtl' : 'ltr';

    $t = [
        'dashboard' => $isAr ? 'لوحة التحكم' : 'Admin Dashboard',
        'manage_intro' => $isAr
            ? 'إدارة الشحنات وعروض الأسعار والعملاء وعمليات الشحن والخدمات اللوجستية.'
            : 'Manage shipments, quotations, customers and logistics operations.',
        'new_shipment' => $isAr ? 'شحنة جديدة' : 'New Shipment',
        'quotations' => $isAr ? 'عروض الأسعار' : 'Quotations',
        'total_shipments' => $isAr ? 'إجمالي الشحنات' : 'Total Shipments',
        'all_shipments' => $isAr ? 'جميع الشحنات المسجلة' : 'All registered shipments',
        'view_shipments' => $isAr ? 'عرض الشحنات' : 'View shipments',
        'in_transit' => $isAr ? 'قيد النقل' : 'In Transit',
        'currently_transit' => $isAr ? 'الشحنات الموجودة حالياً في الطريق' : 'Currently in transit',
        'view_transit' => $isAr ? 'عرض الشحنات قيد النقل' : 'View in-transit',
        'delivered' => $isAr ? 'تم التسليم' : 'Delivered',
        'successfully_delivered' => $isAr ? 'الشحنات التي تم تسليمها بنجاح' : 'Successfully delivered',
        'view_delivered' => $isAr ? 'عرض الشحنات المسلّمة' : 'View delivered',
        'pending_quotations' => $isAr ? 'عروض الأسعار المعلقة' : 'Pending Quotations',
        'awaiting_admin' => $isAr ? 'بانتظار إجراء الإدارة' : 'Awaiting admin action',
        'review_quotations' => $isAr ? 'مراجعة عروض الأسعار' : 'Review quotations',
        'pending_shipments' => $isAr ? 'الشحنات المعلقة' : 'Pending Shipments',
        'total_quotations' => $isAr ? 'إجمالي عروض الأسعار' : 'Total Quotations',
        'customers' => $isAr ? 'العملاء' : 'Customers',
        'recent_shipments' => $isAr ? 'أحدث الشحنات' : 'Recent Shipments',
        'latest_shipment_activity' => $isAr
            ? 'أحدث أنشطة الشحن في المنصة.'
            : 'Latest shipment activity across the platform.',
        'view_all' => $isAr ? 'عرض الكل' : 'View all',
        'unknown' => $isAr ? 'غير معروف' : 'Unknown',
        'view' => $isAr ? 'عرض' : 'View',
        'no_shipments' => $isAr ? 'لا توجد شحنات بعد' : 'No shipments yet',
        'shipment_activity_here' => $isAr ? 'ستظهر أنشطة الشحن هنا.' : 'Shipment activity will appear here.',
        'recent_quotations' => $isAr ? 'أحدث عروض الأسعار' : 'Recent Quotations',
        'latest_customer_requests' => $isAr ? 'أحدث طلبات العملاء.' : 'Latest customer requests.',
        'cargo_request' => $isAr ? 'طلب شحن' : 'Cargo request',
        'no_quotations' => $isAr ? 'لا توجد عروض أسعار بعد' : 'No quotations yet',
        'quotation_requests_here' => $isAr
            ? 'ستظهر طلبات عروض الأسعار من العملاء هنا.'
            : 'Customer quotation requests will appear here.',
        'quick_actions' => $isAr ? 'إجراءات سريعة' : 'Quick Actions',
        'frequently_used' => $isAr ? 'أدوات الإدارة المستخدمة بشكل متكرر.' : 'Frequently used administration tools.',
        'manage_shipments' => $isAr ? 'إدارة الشحنات' : 'Manage Shipments',
        'create_edit_shipments' => $isAr ? 'إنشاء وتعديل وإدارة الشحنات.' : 'Create, edit and manage shipments.',
        'review_quotations_action' => $isAr ? 'مراجعة عروض الأسعار' : 'Review Quotations',
        'review_process_requests' => $isAr ? 'مراجعة ومعالجة طلبات العملاء.' : 'Review and process customer requests.',
        'manage_customer_accounts' => $isAr
            ? 'إدارة حسابات وبيانات العملاء.'
            : 'Manage customer accounts and information.',
        'users' => $isAr ? 'المستخدمون' : 'Users',
        'manage_users' => $isAr ? 'إدارة المشرفين ومستخدمي المنصة.' : 'Manage administrators and platform users.',
        'footer' => $isAr ? 'HAG Brokers — حلول الشحن البحري' : 'HAG Brokers — Sea Freight Solutions',
        'control_center' => $isAr ? 'مركز التحكم الإداري' : 'Admin Control Center',
    ];
@endphp

<x-admin-layout>

    <x-slot name="title">
        {{ $t['dashboard'] }}
    </x-slot>

    <x-slot name="header">

        <div dir="{{ $dir }}" class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-cyan-500">
                    <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                    HAG Brokers
                </div>

                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900 dark:text-white sm:text-3xl">
                    {{ $t['dashboard'] }}
                </h1>

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {{ $t['manage_intro'] }}
                </p>
            </div>

            <div class="flex flex-wrap gap-3">

                <a href="{{ route('admin.shipments.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-500 px-4 py-2.5 text-sm font-extrabold text-slate-950 shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4" />
                    </svg>

                    {{ $t['new_shipment'] }}
                </a>

                <a href="{{ route('admin.quotations.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-cyan-300 hover:text-cyan-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-cyan-500 dark:hover:text-cyan-400">
                    {{ $t['quotations'] }}
                </a>

            </div>

        </div>

    </x-slot>


    <div dir="{{ $dir }}" class="min-h-screen bg-slate-100 dark:bg-slate-950">

        <div class="mx-auto max-w-[1600px] space-y-6 px-4 py-8 sm:px-6 lg:px-8">


            {{-- =====================================================
                KPI CARDS
            ====================================================== --}}

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                {{-- {{ $t['total_shipments'] }} --}}
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="flex items-start justify-between">

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ $t['total_shipments'] }}
                            </p>

                            <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">
                                {{ $stats['total_shipments'] ?? 0 }}
                            </p>

                            <p class="mt-2 text-xs text-slate-500">
                                {{ $t['all_shipments'] }}
                            </p>
                        </div>

                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-400">

                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M3 7h11v10H3zM14 10h4l3 3v4h-7z" />

                                <circle cx="7" cy="17" r="2" stroke-width="1.7" />

                                <circle cx="18" cy="17" r="2" stroke-width="1.7" />
                            </svg>

                        </div>

                    </div>

                    <div class="mt-5">

                        <a href="{{ route('admin.shipments.index') }}"
                            class="text-xs font-bold text-cyan-600 hover:text-cyan-500 dark:text-cyan-400">
                            {{ $t['view_shipments'] }} {{ $isAr ? '←' : '→' }}
                        </a>

                    </div>

                </div>


                {{-- {{ $t['in_transit'] }} --}}
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="flex items-start justify-between">

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ $t['in_transit'] }}
                            </p>

                            <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">
                                {{ $stats['in_transit'] ?? 0 }}
                            </p>

                            <p class="mt-2 text-xs text-slate-500">
                                {{ $t['currently_transit'] }}
                            </p>
                        </div>

                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600 dark:bg-blue-400/10 dark:text-blue-400">

                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M3 12h18M5 12l2-5h10l2 5M6 17h12M8 17v2M16 17v2" />
                            </svg>

                        </div>

                    </div>

                    <div class="mt-5">

                        <a href="{{ route('admin.shipments.index', ['status' => 'in_transit']) }}"
                            class="text-xs font-bold text-cyan-600 hover:text-cyan-500 dark:text-cyan-400">
                            {{ $t['view_transit'] }} {{ $isAr ? '←' : '→' }}
                        </a>

                    </div>

                </div>


                {{-- {{ $t['delivered'] }} --}}
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="flex items-start justify-between">

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ $t['delivered'] }}
                            </p>

                            <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">
                                {{ $stats['delivered'] ?? 0 }}
                            </p>

                            <p class="mt-2 text-xs text-slate-500">
                                {{ $t['successfully_delivered'] }}
                            </p>
                        </div>

                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400">

                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M5 13l4 4L19 7" />
                            </svg>

                        </div>

                    </div>

                    <div class="mt-5">

                        <a href="{{ route('admin.shipments.index', ['status' => 'delivered']) }}"
                            class="text-xs font-bold text-cyan-600 hover:text-cyan-500 dark:text-cyan-400">
                            {{ $t['view_delivered'] }} {{ $isAr ? '←' : '→' }}
                        </a>

                    </div>

                </div>


                {{-- Quotations --}}
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="flex items-start justify-between">

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ $t['pending_quotations'] }}
                            </p>

                            <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">
                                {{ $stats['pending_quotations'] ?? 0 }}
                            </p>

                            <p class="mt-2 text-xs text-slate-500">
                                {{ $t['awaiting_admin'] }}
                            </p>
                        </div>

                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">

                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M12 8v4l3 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>

                        </div>

                    </div>

                    <div class="mt-5">

                        <a href="{{ route('admin.quotations.index', ['status' => 'pending']) }}"
                            class="text-xs font-bold text-cyan-600 hover:text-cyan-500 dark:text-cyan-400">
                            {{ $t['review_quotations'] }} {{ $isAr ? '←' : '→' }}
                        </a>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                SECONDARY STATS
            ====================================================== --}}

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

                <div
                    class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="flex items-center justify-between">

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ $t['pending_shipments'] }}
                            </p>

                            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">
                                {{ $stats['pending_shipments'] ?? 0 }}
                            </p>
                        </div>

                        <div
                            class="rounded-xl bg-amber-100 p-3 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="8" stroke-width="1.7" />
                                <path stroke-width="1.7" d="M12 8v4l2.5 1.5" />
                            </svg>
                        </div>

                    </div>

                </div>


                <div
                    class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="flex items-center justify-between">

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ $t['total_quotations'] }}
                            </p>

                            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">
                                {{ $stats['total_quotations'] ?? 0 }}
                            </p>
                        </div>

                        <div
                            class="rounded-xl bg-violet-100 p-3 text-violet-600 dark:bg-violet-400/10 dark:text-violet-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="1.7" d="M7 3h8l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                                <path stroke-width="1.7" d="M15 3v5h5M9 13h6M9 17h4" />
                            </svg>
                        </div>

                    </div>

                </div>


                <div
                    class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="flex items-center justify-between">

                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ $t['customers'] }}
                            </p>

                            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">
                                {{ $stats['customers'] ?? ($stats['total_customers'] ?? 0) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-cyan-100 p-3 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="1.7" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2" />
                                <circle cx="9" cy="7" r="4" stroke-width="1.7" />
                                <path stroke-width="1.7" d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
                            </svg>
                        </div>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                MAIN CONTENT
            ====================================================== --}}

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">


                {{-- =================================================
                    RECENT SHIPMENTS
                ================================================== --}}

                <section
                    class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-2 dark:border-slate-800 dark:bg-slate-900">

                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-800">

                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
                                {{ $t['recent_shipments'] }}
                            </h2>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ $t['latest_shipment_activity'] }}
                            </p>
                        </div>

                        <a href="{{ route('admin.shipments.index') }}"
                            class="text-xs font-bold text-cyan-600 hover:text-cyan-500 dark:text-cyan-400">
                            {{ $t['view_all'] }}
                        </a>

                    </div>


                    @forelse ($recentShipments as $shipment)
                        @php
                            $reference = $shipment->reference_no ?? ($shipment->shipment_no ?? 'SHIP-' . $shipment->id);

                            $status = $shipment->status ?? 'pending';

                            $statusClasses = match ($status) {
                                'delivered'
                                    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-400',

                                'in_transit' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-400/10 dark:text-cyan-400',

                                default => 'bg-amber-100 text-amber-700 dark:bg-amber-400/10 dark:text-amber-400',
                            };
                        @endphp

                        <div class="border-b border-slate-100 px-6 py-5 last:border-b-0 dark:border-slate-800">

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                                <div class="flex min-w-0 items-center gap-4">

                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-width="1.7" d="M3 7h11v10H3zM14 10h4l3 3v4h-7z" />
                                            <circle cx="7" cy="17" r="2" stroke-width="1.7" />
                                            <circle cx="18" cy="17" r="2" stroke-width="1.7" />
                                        </svg>
                                    </div>

                                    <div class="min-w-0">

                                        <a href="{{ route('admin.shipments.show', $shipment) }}"
                                            class="truncate text-sm font-extrabold text-slate-900 hover:text-cyan-600 dark:text-white dark:hover:text-cyan-400">
                                            {{ $reference }}
                                        </a>

                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-400">

                                            <span>
                                                {{ $shipment->originPort->name ?? $t['unknown'] }}
                                            </span>

                                            <span>
                                                {{ $isAr ? '←' : '→' }}
                                            </span>

                                            <span>
                                                {{ $shipment->destinationPort->name ?? $t['unknown'] }}
                                            </span>

                                            @if ($shipment->vessel)
                                                <span class="hidden sm:inline">
                                                    •
                                                </span>

                                                <span class="hidden sm:inline">
                                                    {{ $shipment->vessel->name }}
                                                </span>
                                            @endif

                                        </div>

                                    </div>

                                </div>


                                <div class="flex items-center gap-3 sm:shrink-0">

                                    <span
                                        class="inline-flex rounded-full px-3 py-1.5 text-[11px] font-extrabold capitalize {{ $statusClasses }}">
                                        {{ $isAr? match ($status) {'pending' => 'معلق','booked' => 'تم الحجز','confirmed' => 'مؤكد','in_transit' => 'قيد النقل','arrived' => 'وصل','delivered' => 'تم التسليم','cancelled' => 'ملغى',default => str_replace('_', ' ', $status)}: str_replace('_', ' ', $status) }}
                                    </span>

                                    <a href="{{ route('admin.shipments.show', $shipment) }}"
                                        class="text-xs font-bold text-slate-500 hover:text-cyan-600 dark:text-slate-400 dark:hover:text-cyan-400">
                                        {{ $t['view'] }}
                                    </a>

                                </div>

                            </div>

                        </div>

                    @empty

                        <div class="px-6 py-14 text-center">

                            <div
                                class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">

                                <svg class="h-7 w-7 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-width="1.7" d="M3 7h11v10H3zM14 10h4l3 3v4h-7z" />
                                </svg>

                            </div>

                            <h3 class="mt-4 font-extrabold text-slate-900 dark:text-white">
                                {{ $t['no_shipments'] }}
                            </h3>

                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                {{ $t['shipment_activity_here'] }}
                            </p>

                        </div>
                    @endforelse

                </section>


                {{-- =================================================
                    QUOTATIONS
                ================================================== --}}

                <section
                    class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-800">

                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
                                {{ $t['recent_quotations'] }}
                            </h2>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ $t['latest_customer_requests'] }}
                            </p>
                        </div>

                        <a href="{{ route('admin.quotations.index') }}"
                            class="text-xs font-bold text-cyan-600 hover:text-cyan-500 dark:text-cyan-400">
                            {{ $t['view_all'] }}
                        </a>

                    </div>


                    @forelse ($recentQuotations as $quotation)
                        @php
                            $quotationReference = $quotation->reference_no ?? 'QT-' . $quotation->id;

                            $quotationStatus = $quotation->status ?? 'pending';

                            $quotationStatusClasses = match ($quotationStatus) {
                                'approved'
                                    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-400',

                                'rejected' => 'bg-red-100 text-red-700 dark:bg-red-400/10 dark:text-red-400',

                                default => 'bg-amber-100 text-amber-700 dark:bg-amber-400/10 dark:text-amber-400',
                            };
                        @endphp

                        <div class="border-b border-slate-100 px-6 py-5 last:border-b-0 dark:border-slate-800">

                            <div class="flex items-start justify-between gap-4">

                                <div class="min-w-0">

                                    <a href="{{ route('admin.quotations.show', $quotation) }}"
                                        class="truncate text-sm font-extrabold text-slate-900 hover:text-cyan-600 dark:text-white dark:hover:text-cyan-400">
                                        {{ $quotationReference }}
                                    </a>

                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $quotation->cargo_type ?? $t['cargo_request'] }}
                                    </p>

                                    @if ($quotation->created_at)
                                        <p class="mt-2 text-[11px] text-slate-400">
                                            {{ $quotation->created_at->format('M d, Y · H:i') }}
                                        </p>
                                    @endif

                                </div>

                                <span
                                    class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-extrabold capitalize {{ $quotationStatusClasses }}">
                                    {{ $isAr? match ($quotationStatus) {'pending' => 'معلق','approved' => 'معتمد','rejected' => 'مرفوض','quoted' => 'تم التسعير','accepted' => 'مقبول',default => str_replace('_', ' ', $quotationStatus)}: str_replace('_', ' ', $quotationStatus) }}
                                </span>

                            </div>

                        </div>

                    @empty

                        <div class="px-6 py-14 text-center">

                            <div
                                class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">

                                <svg class="h-7 w-7 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-width="1.7" d="M7 3h8l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                                    <path stroke-width="1.7" d="M15 3v5h5" />
                                </svg>

                            </div>

                            <h3 class="mt-4 font-extrabold text-slate-900 dark:text-white">
                                {{ $t['no_quotations'] }}
                            </h3>

                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                {{ $t['quotation_requests_here'] }}
                            </p>

                        </div>
                    @endforelse

                </section>

            </div>


            {{-- =====================================================
                QUICK ACTIONS
            ====================================================== --}}

            <section>

                <div class="mb-4">
                    <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
                        {{ $t['quick_actions'] }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $t['frequently_used'] }}
                    </p>
                </div>


                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                    <a href="{{ route('admin.shipments.index') }}"
                        class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-cyan-500">

                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-100 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="1.7" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>

                        <h3 class="mt-4 text-sm font-extrabold text-slate-900 dark:text-white">
                            {{ $t['manage_shipments'] }}
                        </h3>

                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                            {{ $t['create_edit_shipments'] }}
                        </p>

                    </a>


                    <a href="{{ route('admin.quotations.index') }}"
                        class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-cyan-500">

                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="1.7" d="M7 3h8l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                            </svg>
                        </div>

                        <h3 class="mt-4 text-sm font-extrabold text-slate-900 dark:text-white">
                            {{ $t['review_quotations_action'] }}
                        </h3>

                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                            {{ $t['review_process_requests'] }}
                        </p>

                    </a>


                    <a href="{{ route('admin.customers.index') }}"
                        class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-cyan-500">

                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-400/10 dark:text-violet-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="9" cy="7" r="4" stroke-width="1.7" />
                                <path stroke-width="1.7" d="M2 21a7 7 0 0114 0" />
                            </svg>
                        </div>

                        <h3 class="mt-4 text-sm font-extrabold text-slate-900 dark:text-white">
                            {{ $t['customers'] }}
                        </h3>

                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                            {{ $t['manage_customer_accounts'] }}
                        </p>

                    </a>


                    <a href="{{ route('admin.users.index') }}"
                        class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-cyan-500">

                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="1.7" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1" />
                                <circle cx="12" cy="8" r="4" stroke-width="1.7" />
                            </svg>
                        </div>

                        <h3 class="mt-4 text-sm font-extrabold text-slate-900 dark:text-white">
                            {{ $t['users'] }}
                        </h3>

                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                            {{ $t['manage_users'] }}
                        </p>

                    </a>

                </div>

            </section>


            {{-- Footer --}}
            <div
                class="flex flex-col items-center justify-between gap-3 border-t border-slate-200 pt-6 text-xs text-slate-400 sm:flex-row dark:border-slate-800">

                <p>
                    {{ $t['footer'] }}
                </p>

                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                    <span>
                        {{ $t['control_center'] }}
                    </span>
                </div>

            </div>

        </div>

    </div>

</x-admin-layout>
