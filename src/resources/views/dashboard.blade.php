@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';

    /*
    |--------------------------------------------------------------------------
    | Safe dashboard data
    |--------------------------------------------------------------------------
    */
    $dashboardStats = [
        'shipments' => $stats['total_shipments'] ?? 0,
        'in_transit' => $stats['in_transit'] ?? 0,
        'delivered' => $stats['delivered'] ?? 0,
        'pending' => $stats['pending'] ?? 0,
    ];

    $dashboardOpportunities = collect($opportunities ?? []);

    $dashboardShipments = collect($shipments ?? []);

    $dashboardQuotations = collect($quotations ?? []);

    $dashboardNotifications = collect($notifications ?? []);

    $userName = auth()->user()->name ?? ($isRtl ? 'عميلنا' : 'Customer');
@endphp

<x-app-layout>

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <x-slot name="header">

        <div dir="{{ $isRtl ? 'rtl' : 'ltr' }}" class="flex items-center justify-between gap-4">

            <div class="min-w-0">

                <h2 class="text-xl font-semibold leading-tight text-slate-800">
                    {{ __('dashboard.dashboard') }}
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    HAG Brokers
                    <span class="mx-1">•</span>
                    {{ __('dashboard.sea_freight_solutions') }}
                </p>

            </div>

            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center
                       rounded-xl bg-cyan-400 text-sm font-black text-slate-950
                       shadow-lg shadow-cyan-400/20">
                HAG
            </div>

        </div>

    </x-slot>


    {{-- =========================================================
         PAGE
    ========================================================== --}}
    <div dir="{{ $isRtl ? 'rtl' : 'ltr' }}" class="min-h-screen bg-slate-50 py-8 sm:py-10">

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">


            {{-- =================================================
                 SUCCESS / ERROR MESSAGES
            ================================================== --}}

            @if (session('success'))
                <div
                    class="mb-6 flex items-start gap-3 rounded-2xl
                           border border-emerald-200 bg-emerald-50
                           px-5 py-4 text-sm text-emerald-800">
                    <span class="text-lg">✓</span>

                    <div>
                        {{ session('success') }}
                    </div>
                </div>
            @endif


            @if (session('error'))
                <div
                    class="mb-6 flex items-start gap-3 rounded-2xl
                           border border-red-200 bg-red-50
                           px-5 py-4 text-sm text-red-800">
                    <span class="text-lg">!</span>

                    <div>
                        {{ session('error') }}
                    </div>
                </div>
            @endif


            {{-- =================================================
                 HERO
            ================================================== --}}

            <section class="relative mb-8 overflow-hidden rounded-3xl
                       bg-slate-950 shadow-xl">

                <div
                    class="absolute inset-x-0 top-0 h-1
                           bg-gradient-to-r from-red-500
                           via-white/90 to-emerald-500">
                </div>

                <div
                    class="pointer-events-none absolute -end-24 -top-24
                           h-80 w-80 rounded-full bg-cyan-500/10 blur-3xl">
                </div>

                <div
                    class="pointer-events-none absolute -start-24 -bottom-24
                           h-80 w-80 rounded-full bg-emerald-500/10 blur-3xl">
                </div>

                <div class="relative p-7 sm:p-10">

                    <div
                        class="flex flex-col gap-6
                               lg:flex-row lg:items-center
                               lg:justify-between">

                        {{-- Welcome --}}

                        <div class="min-w-0">

                            <div class="flex items-center gap-3">

                                <div
                                    class="flex h-12 w-12 shrink-0
                                           items-center justify-center
                                           rounded-xl bg-cyan-400
                                           text-sm font-black
                                           text-slate-950">
                                    HAG
                                </div>

                                <div>

                                    <p
                                        class="text-xs font-semibold
                                               uppercase tracking-[0.2em]
                                               text-cyan-400">
                                        HAG Brokers
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ __('dashboard.sea_freight_solutions') }}
                                    </p>

                                </div>

                            </div>


                            <div
                                class="mt-7 inline-flex items-center gap-2
                                       rounded-full border border-white/10
                                       bg-white/5 px-3 py-1.5">

                                <span>🇦🇪</span>

                                <span class="text-xs font-medium text-slate-300">
                                    {{ __('dashboard.based_in_uae') }}
                                </span>

                            </div>


                            <h1
                                class="mt-5 max-w-3xl text-3xl font-bold
                                       tracking-tight text-white
                                       sm:text-4xl">
                                {{ $isRtl ? 'مرحباً، ' : 'Welcome, ' }}
                                {{ $userName }}
                            </h1>


                            <p
                                class="mt-4 max-w-2xl text-sm leading-7
                                       text-slate-300 sm:text-base">
                                {{ $isRtl
                                    ? 'أدر شحناتك، اطلب عروض الأسعار، واكتشف الفرص التجارية من مكان واحد.'
                                    : 'Manage your shipments, request freight quotes, and discover business opportunities from one place.' }}
                            </p>

                        </div>


                        {{-- Main actions --}}

                        <div class="flex shrink-0 flex-col gap-3 sm:flex-row lg:flex-col">

                            <a href="{{ route('quotations.create') }}"
                                class="inline-flex items-center justify-center
                                       gap-2 rounded-xl bg-cyan-400
                                       px-5 py-3 text-sm font-bold
                                       text-slate-950 shadow-lg
                                       shadow-cyan-400/20 transition
                                       hover:-translate-y-0.5
                                       hover:bg-cyan-300">
                                <span class="text-lg">+</span>

                                {{ $isRtl ? 'طلب عرض سعر' : 'Request a Quote' }}
                            </a>


                            <a href="{{ route('opportunities.index') }}"
                                class="inline-flex items-center justify-center
                                       gap-2 rounded-xl border border-slate-700
                                       bg-white/5 px-5 py-3 text-sm
                                       font-semibold text-white transition
                                       hover:border-cyan-400
                                       hover:bg-slate-800">
                                <span>💼</span>

                                {{ $isRtl ? 'استكشف الفرص' : 'Explore Opportunities' }}
                            </a>

                        </div>

                    </div>


                    {{-- GCC quick navigation --}}

                    <div class="mt-8 flex flex-wrap gap-2">

                        @foreach ([['🇦🇪', 'dashboard.uae'], ['🇸🇦', 'dashboard.saudi_arabia'], ['🇴🇲', 'dashboard.oman'], ['🇶🇦', 'dashboard.qatar'], ['🇧🇭', 'dashboard.bahrain'], ['🇰🇼', 'dashboard.kuwait']] as [$flag, $key])
                            <span
                                class="rounded-full border border-white/10
                                       bg-white/5 px-3 py-1.5 text-xs
                                       font-medium text-slate-300">
                                {{ $flag }}
                                {{ __($key) }}
                            </span>
                        @endforeach

                    </div>

                </div>

            </section>


            {{-- =================================================
                 STATISTICS
            ================================================== --}}

            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                {{-- Shipments --}}

                <a href="{{ route('shipments.index') }}"
                    class="group rounded-2xl bg-white p-5 shadow-sm
                           ring-1 ring-slate-200 transition
                           hover:-translate-y-0.5 hover:shadow-md
                           hover:ring-cyan-300">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm text-slate-500">
                                {{ $isRtl ? 'إجمالي الشحنات' : 'Total Shipments' }}
                            </p>

                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                {{ $dashboardStats['shipments'] }}
                            </p>

                        </div>

                        <div
                            class="flex h-12 w-12 items-center
                                   justify-center rounded-xl
                                   bg-cyan-50 text-xl">
                            🚢
                        </div>

                    </div>

                    <p
                        class="mt-4 text-xs font-medium text-cyan-600
                               group-hover:text-cyan-700">
                        {{ $isRtl ? 'عرض الشحنات ←' : 'View shipments →' }}
                    </p>

                </a>


                {{-- Transit --}}

                <a href="{{ route('shipments.index') }}"
                    class="group rounded-2xl bg-white p-5 shadow-sm
                           ring-1 ring-slate-200 transition
                           hover:-translate-y-0.5 hover:shadow-md
                           hover:ring-blue-300">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm text-slate-500">
                                {{ $isRtl ? 'قيد النقل' : 'In Transit' }}
                            </p>

                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                {{ $dashboardStats['in_transit'] }}
                            </p>

                        </div>

                        <div
                            class="flex h-12 w-12 items-center
                                   justify-center rounded-xl
                                   bg-blue-50 text-xl">
                            🌊
                        </div>

                    </div>

                    <p class="mt-4 text-xs text-slate-400">
                        {{ $isRtl ? 'الشحنات الحالية' : 'Currently moving' }}
                    </p>

                </a>


                {{-- Delivered --}}

                <a href="{{ route('shipments.index') }}"
                    class="group rounded-2xl bg-white p-5 shadow-sm
                           ring-1 ring-slate-200 transition
                           hover:-translate-y-0.5 hover:shadow-md
                           hover:ring-emerald-300">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm text-slate-500">
                                {{ $isRtl ? 'تم التسليم' : 'Delivered' }}
                            </p>

                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                {{ $dashboardStats['delivered'] }}
                            </p>

                        </div>

                        <div
                            class="flex h-12 w-12 items-center
                                   justify-center rounded-xl
                                   bg-emerald-50 text-xl">
                            ✓
                        </div>

                    </div>

                    <p class="mt-4 text-xs text-slate-400">
                        {{ $isRtl ? 'شحنات مكتملة' : 'Completed shipments' }}
                    </p>

                </a>


                {{-- Pending --}}

                <a href="{{ route('quotations.create') }}"
                    class="group rounded-2xl bg-white p-5 shadow-sm
                           ring-1 ring-slate-200 transition
                           hover:-translate-y-0.5 hover:shadow-md
                           hover:ring-amber-300">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm text-slate-500">
                                {{ $isRtl ? 'قيد المتابعة' : 'Pending' }}
                            </p>

                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                {{ $dashboardStats['pending'] }}
                            </p>

                        </div>

                        <div
                            class="flex h-12 w-12 items-center
                                   justify-center rounded-xl
                                   bg-amber-50 text-xl">
                            ⏱
                        </div>

                    </div>

                    <p class="mt-4 text-xs text-slate-400">
                        {{ $isRtl ? 'طلبات تحتاج متابعة' : 'Requests needing attention' }}
                    </p>

                </a>

            </section>


            {{-- =================================================
                 OPPORTUNITIES
            ================================================== --}}

            <section
                class="mt-8 overflow-hidden rounded-2xl
                       bg-white shadow-sm ring-1 ring-slate-200">

                {{-- Header --}}

                <div
                    class="flex flex-col gap-4 border-b border-slate-200
                           px-6 py-5 sm:flex-row sm:items-center
                           sm:justify-between">

                    <div>

                        <div class="flex items-center gap-2">

                            <span class="text-xl">💼</span>

                            <h3 class="text-lg font-semibold text-slate-900">
                                {{ $isRtl ? 'الفرص المتاحة' : 'Available Opportunities' }}
                            </h3>

                        </div>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $isRtl
                                ? 'اكتشف فرصاً تجارية واستثمارية متاحة عبر شبكة HAG Brokers.'
                                : 'Discover business and investment opportunities available through HAG Brokers.' }}
                        </p>

                    </div>


                    <div class="flex gap-2">

                        <a href="{{ route('opportunities.index') }}"
                            class="inline-flex items-center justify-center
                                   rounded-xl border border-slate-200
                                   px-4 py-2.5 text-xs font-semibold
                                   text-slate-700 transition
                                   hover:border-cyan-400
                                   hover:text-cyan-600">
                            {{ $isRtl ? 'كل الفرص' : 'All Opportunities' }}
                        </a>


                        @auth

                            <a href="{{ route('opportunities.create') }}"
                                class="inline-flex items-center justify-center
                                       gap-2 rounded-xl bg-slate-900
                                       px-4 py-2.5 text-xs font-bold
                                       text-white transition
                                       hover:bg-cyan-400
                                       hover:text-slate-950">
                                <span>+</span>

                                {{ $isRtl ? 'إضافة فرصة' : 'Add Opportunity' }}
                            </a>

                        @endauth

                    </div>

                </div>


                {{-- Opportunities --}}

                @if ($dashboardOpportunities->isNotEmpty())

                    <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-3">

                        @foreach ($dashboardOpportunities->take(6) as $opportunity)
                            <a href="{{ route('opportunities.show', $opportunity) }}"
                                class="group overflow-hidden rounded-2xl
                                       border border-slate-200
                                       bg-white transition
                                       hover:-translate-y-1
                                       hover:border-cyan-300
                                       hover:shadow-lg">

                                {{-- Media --}}

                                <div
                                    class="relative h-40 overflow-hidden
                                           bg-slate-100">

                                    @php
                                        $media = $opportunity->media->first();
                                    @endphp

                                    @if ($media && $media->type === 'video')
                                        <video class="h-full w-full object-cover" muted playsinline preload="metadata">
                                            <source src="{{ asset('storage/' . $media->path) }}"
                                                type="{{ $media->mime_type }}">
                                        </video>
                                    @elseif($media)
                                        <img src="{{ asset('storage/' . $media->path) }}"
                                            alt="{{ $opportunity->title }}"
                                            class="h-full w-full object-cover
                                                   transition duration-500
                                                   group-hover:scale-105">
                                    @else
                                        <div
                                            class="flex h-full items-center
                                                   justify-center
                                                   bg-gradient-to-br
                                                   from-slate-900
                                                   to-slate-800">
                                            <span class="text-4xl">💼</span>
                                        </div>
                                    @endif


                                    {{-- Badge --}}

                                    <div
                                        class="absolute start-3 top-3
                                               rounded-full bg-white/90
                                               px-2.5 py-1 text-[10px]
                                               font-bold text-slate-700
                                               shadow-sm backdrop-blur">
                                        HAG Reviewed
                                    </div>

                                </div>


                                <div class="p-5">

                                    <div
                                        class="flex items-center justify-between
                                               gap-3">

                                        <span
                                            class="rounded-full bg-cyan-50
                                                   px-2.5 py-1 text-[10px]
                                                   font-semibold text-cyan-700">
                                            {{ $opportunity->category ?? ($isRtl ? 'فرصة' : 'Opportunity') }}
                                        </span>

                                        @if ($opportunity->expires_at)
                                            <span class="text-[10px] text-slate-400">
                                                {{ $isRtl ? 'متاحة حتى' : 'Until' }}
                                                {{ optional($opportunity->expires_at)->format('M d') }}
                                            </span>
                                        @endif

                                    </div>


                                    <h4
                                        class="mt-3 line-clamp-2
                                               font-semibold text-slate-900
                                               transition
                                               group-hover:text-cyan-700">
                                        {{ $opportunity->title }}
                                    </h4>


                                    <p
                                        class="mt-2 line-clamp-2
                                               text-xs leading-5 text-slate-500">
                                        {{ $opportunity->description }}
                                    </p>


                                    <div
                                        class="mt-4 flex items-center
                                               justify-between border-t
                                               border-slate-100 pt-4">

                                        <div>

                                            <p class="text-[10px] text-slate-400">
                                                {{ $isRtl ? 'الحد الأدنى للاستثمار' : 'Minimum Investment' }}
                                            </p>

                                            <p class="mt-1 text-sm font-bold text-slate-900">
                                                {{ $opportunity->currency ?? 'AED' }}
                                                {{ number_format($opportunity->min_investment ?? 0) }}
                                            </p>

                                        </div>


                                        <span
                                            class="text-xs font-semibold
                                                   text-cyan-600
                                                   group-hover:text-cyan-700">
                                            {{ $isRtl ? 'عرض →' : 'View →' }}
                                        </span>

                                    </div>

                                </div>

                            </a>
                        @endforeach

                    </div>
                @else
                    {{-- Empty state --}}

                    <div class="px-6 py-12">

                        <div class="mx-auto max-w-xl text-center">

                            <div
                                class="mx-auto flex h-20 w-20
                                       items-center justify-center
                                       rounded-3xl bg-cyan-50 text-4xl">
                                💼
                            </div>


                            <h4 class="mt-5 text-xl font-bold text-slate-900">
                                {{ $isRtl ? 'لا توجد فرص متاحة حالياً' : 'No opportunities available yet' }}
                            </h4>


                            <p
                                class="mx-auto mt-2 max-w-md
                                       text-sm leading-6 text-slate-500">
                                {{ $isRtl
                                    ? 'يمكنك إنشاء فرصة تجارية أو استثمارية وإرسالها إلى فريق HAG Brokers للمراجعة.'
                                    : 'You can create a business or investment opportunity and submit it to HAG Brokers for review.' }}
                            </p>


                            @auth

                                <a href="{{ route('opportunities.create') }}"
                                    class="mt-6 inline-flex items-center
                                           justify-center gap-2 rounded-xl
                                           bg-cyan-400 px-6 py-3
                                           text-sm font-bold
                                           text-slate-950 transition
                                           hover:-translate-y-0.5
                                           hover:bg-cyan-300">
                                    <span class="text-lg">+</span>

                                    {{ $isRtl ? 'إضافة فرصة جديدة' : 'Add New Opportunity' }}
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="mt-6 inline-flex items-center
                                           justify-center gap-2 rounded-xl
                                           bg-slate-900 px-6 py-3
                                           text-sm font-bold text-white
                                           transition hover:bg-slate-800">
                                    {{ $isRtl ? 'تسجيل الدخول لإضافة فرصة' : 'Login to Add an Opportunity' }}
                                </a>

                            @endauth

                        </div>

                    </div>

                @endif

            </section>


            {{-- =================================================
                 RECENT SHIPMENTS + QUICK ACTIONS
            ================================================== --}}

            <section class="mt-8 grid gap-6 lg:grid-cols-3">


                {{-- RECENT SHIPMENTS --}}

                <div
                    class="overflow-hidden rounded-2xl bg-white
                           shadow-sm ring-1 ring-slate-200
                           lg:col-span-2">

                    <div
                        class="flex items-center justify-between
                               border-b border-slate-200 px-6 py-5">

                        <div>

                            <h3 class="text-lg font-semibold text-slate-900">
                                {{ $isRtl ? 'آخر الشحنات' : 'Recent Shipments' }}
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ $isRtl ? 'متابعة أحدث الشحنات الخاصة بك.' : 'Track your latest shipments.' }}
                            </p>

                        </div>


                        <a href="{{ route('shipments.index') }}"
                            class="text-xs font-semibold text-cyan-600
                                   hover:text-cyan-700">
                            {{ $isRtl ? 'عرض الكل' : 'View All' }}
                        </a>

                    </div>


                    @if ($dashboardShipments->isNotEmpty())

                        <div class="divide-y divide-slate-100">

                            @foreach ($dashboardShipments->take(5) as $shipment)
                                <a href="{{ route('shipments.index') }}"
                                    class="flex items-center gap-4 px-6 py-4
                                           transition hover:bg-slate-50">

                                    <div
                                        class="flex h-11 w-11 shrink-0
                                               items-center justify-center
                                               rounded-xl bg-slate-100">
                                        🚢
                                    </div>


                                    <div class="min-w-0 flex-1">

                                        <div
                                            class="flex flex-wrap items-center
                                                   gap-2">

                                            <p class="font-semibold text-slate-900">
                                                {{ $shipment->reference ?? ($shipment->shipment_number ?? '#' . $shipment->id) }}
                                            </p>

                                            @if (isset($shipment->status))
                                                <span
                                                    class="rounded-full
                                                           bg-cyan-50 px-2
                                                           py-1 text-[10px]
                                                           font-semibold
                                                           text-cyan-700">
                                                    {{ $shipment->status }}
                                                </span>
                                            @endif

                                        </div>


                                        <p class="mt-1 truncate text-xs text-slate-500">

                                            {{ $shipment->origin ?? ($shipment->originPort->name ?? '—') }}

                                            <span class="mx-1">→</span>

                                            {{ $shipment->destination ?? ($shipment->destinationPort->name ?? '—') }}

                                        </p>

                                    </div>


                                    <span class="text-slate-400">
                                        {{ $isRtl ? '←' : '→' }}
                                    </span>

                                </a>
                            @endforeach

                        </div>
                    @else
                        <div class="px-6 py-12 text-center">

                            <div
                                class="mx-auto flex h-14 w-14
                                       items-center justify-center
                                       rounded-full bg-slate-100">
                                🚢
                            </div>

                            <p class="mt-4 font-semibold text-slate-800">
                                {{ $isRtl ? 'لا توجد شحنات حتى الآن' : 'No shipments yet' }}
                            </p>

                            <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                                {{ $isRtl
                                    ? 'ابدأ بطلب عرض سعر لإنشاء أول شحنة لك.'
                                    : 'Start by requesting a freight quote to create your first shipment.' }}
                            </p>

                            <a href="{{ route('quotations.create') }}"
                                class="mt-5 inline-flex items-center gap-2
                                       rounded-xl bg-cyan-400 px-5 py-2.5
                                       text-xs font-bold text-slate-950
                                       transition hover:bg-cyan-300">
                                <span>+</span>
                                {{ $isRtl ? 'طلب عرض سعر' : 'Request a Quote' }}
                            </a>

                        </div>

                    @endif

                </div>


                {{-- QUICK ACTIONS --}}

                <div class="rounded-2xl bg-white p-6 shadow-sm
                           ring-1 ring-slate-200">

                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ $isRtl ? 'إجراءات سريعة' : 'Quick Actions' }}
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ $isRtl ? 'الوصول السريع إلى أهم الخدمات.' : 'Quick access to your most important actions.' }}
                    </p>


                    <div class="mt-5 space-y-3">

                        <a href="{{ route('quotations.create') }}"
                            class="group flex items-center gap-3
                                   rounded-xl border border-slate-200
                                   p-3 transition hover:border-cyan-300
                                   hover:bg-cyan-50">

                            <span
                                class="flex h-10 w-10 items-center
                                       justify-center rounded-xl
                                       bg-cyan-50 text-lg">
                                💰
                            </span>

                            <span class="flex-1">

                                <span
                                    class="block text-sm font-semibold
                                           text-slate-800">
                                    {{ $isRtl ? 'طلب عرض سعر' : 'Request Quote' }}
                                </span>

                                <span class="block text-[11px] text-slate-500">
                                    {{ $isRtl ? 'احصل على سعر الشحن' : 'Get a freight quote' }}
                                </span>

                            </span>

                            <span class="text-slate-400">
                                {{ $isRtl ? '←' : '→' }}
                            </span>

                        </a>


                        <a href="{{ route('opportunities.create') }}"
                            class="group flex items-center gap-3
                                   rounded-xl border border-slate-200
                                   p-3 transition hover:border-cyan-300
                                   hover:bg-cyan-50">

                            <span
                                class="flex h-10 w-10 items-center
                                       justify-center rounded-xl
                                       bg-emerald-50 text-lg">
                                💼
                            </span>

                            <span class="flex-1">

                                <span
                                    class="block text-sm font-semibold
                                           text-slate-800">
                                    {{ $isRtl ? 'إضافة فرصة' : 'Add Opportunity' }}
                                </span>

                                <span class="block text-[11px] text-slate-500">
                                    {{ $isRtl ? 'أضف فرصة تجارية' : 'Submit a business opportunity' }}
                                </span>

                            </span>

                            <span class="text-slate-400">
                                {{ $isRtl ? '←' : '→' }}
                            </span>

                        </a>


                        <a href="{{ route('shipments.index') }}"
                            class="group flex items-center gap-3
                                   rounded-xl border border-slate-200
                                   p-3 transition hover:border-cyan-300
                                   hover:bg-cyan-50">

                            <span
                                class="flex h-10 w-10 items-center
                                       justify-center rounded-xl
                                       bg-blue-50 text-lg">
                                📍
                            </span>

                            <span class="flex-1">

                                <span
                                    class="block text-sm font-semibold
                                           text-slate-800">
                                    {{ $isRtl ? 'تتبع الشحنات' : 'Track Shipments' }}
                                </span>

                                <span class="block text-[11px] text-slate-500">
                                    {{ $isRtl ? 'راجع حالة شحناتك' : 'Check shipment status' }}
                                </span>

                            </span>

                            <span class="text-slate-400">
                                {{ $isRtl ? '←' : '→' }}
                            </span>

                        </a>


                        <a href="{{ route('notifications.index') }}"
                            class="group flex items-center gap-3
                                   rounded-xl border border-slate-200
                                   p-3 transition hover:border-cyan-300
                                   hover:bg-cyan-50">

                            <span
                                class="flex h-10 w-10 items-center
                                       justify-center rounded-xl
                                       bg-amber-50 text-lg">
                                🔔
                            </span>

                            <span class="flex-1">

                                <span
                                    class="block text-sm font-semibold
                                           text-slate-800">
                                    {{ $isRtl ? 'الإشعارات' : 'Notifications' }}
                                </span>

                                <span class="block text-[11px] text-slate-500">
                                    {{ $isRtl ? 'آخر التحديثات' : 'Latest updates' }}
                                </span>

                            </span>

                            <span class="text-slate-400">
                                {{ $isRtl ? '←' : '→' }}
                            </span>

                        </a>

                    </div>

                </div>

            </section>


            {{-- =================================================
                 QUOTATIONS
            ================================================== --}}

            <section
                class="mt-8 overflow-hidden rounded-2xl bg-white
                       shadow-sm ring-1 ring-slate-200">

                <div
                    class="flex items-center justify-between
                           border-b border-slate-200 px-6 py-5">

                    <div>

                        <h3 class="text-lg font-semibold text-slate-900">
                            {{ $isRtl ? 'طلبات عروض الأسعار' : 'Quotation Requests' }}
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $isRtl ? 'آخر طلبات عروض الأسعار الخاصة بك.' : 'Your latest quotation requests.' }}
                        </p>

                    </div>


                    <a href="{{ route('quotations.create') }}"
                        class="inline-flex items-center gap-2
                               rounded-xl bg-slate-900 px-4 py-2.5
                               text-xs font-bold text-white
                               transition hover:bg-cyan-400
                               hover:text-slate-950">
                        <span>+</span>
                        {{ $isRtl ? 'طلب جديد' : 'New Request' }}
                    </a>

                </div>


                @if ($dashboardQuotations->isNotEmpty())

                    <div class="overflow-x-auto">

                        <table class="min-w-full">

                            <thead class="bg-slate-50">

                                <tr>

                                    <th
                                        class="px-6 py-3 text-start text-[11px]
                                               font-semibold uppercase
                                               tracking-wider text-slate-500">
                                        {{ $isRtl ? 'المرجع' : 'Reference' }}
                                    </th>

                                    <th
                                        class="px-6 py-3 text-start text-[11px]
                                               font-semibold uppercase
                                               tracking-wider text-slate-500">
                                        {{ $isRtl ? 'المسار' : 'Route' }}
                                    </th>

                                    <th
                                        class="px-6 py-3 text-start text-[11px]
                                               font-semibold uppercase
                                               tracking-wider text-slate-500">
                                        {{ $isRtl ? 'الحالة' : 'Status' }}
                                    </th>

                                    <th
                                        class="px-6 py-3 text-end text-[11px]
                                               font-semibold uppercase
                                               tracking-wider text-slate-500">
                                        {{ $isRtl ? 'التاريخ' : 'Date' }}
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-slate-100">

                                @foreach ($dashboardQuotations->take(5) as $quotation)
                                    <tr class="transition hover:bg-slate-50">

                                        <td class="whitespace-nowrap px-6 py-4">

                                            <span class="text-sm font-semibold text-slate-800">
                                                {{ $quotation->reference ?? '#' . $quotation->id }}
                                            </span>

                                        </td>


                                        <td class="px-6 py-4">

                                            <span class="text-xs text-slate-500">
                                                {{ $quotation->origin ?? '—' }}

                                                <span class="mx-1">→</span>

                                                {{ $quotation->destination ?? '—' }}
                                            </span>

                                        </td>


                                        <td class="px-6 py-4">

                                            <span
                                                class="rounded-full bg-amber-50
                                                       px-2.5 py-1 text-[10px]
                                                       font-semibold text-amber-700">
                                                {{ $quotation->status ?? ($isRtl ? 'قيد المراجعة' : 'Pending') }}
                                            </span>

                                        </td>


                                        <td
                                            class="whitespace-nowrap px-6 py-4
                                                   text-end text-xs text-slate-400">
                                            {{ optional($quotation->created_at)->format('M d, Y') }}
                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>

                        </table>

                    </div>
                @else
                    <div class="px-6 py-10 text-center">

                        <div
                            class="mx-auto flex h-14 w-14 items-center
                                   justify-center rounded-full
                                   bg-slate-100 text-xl">
                            💰
                        </div>

                        <p class="mt-4 font-semibold text-slate-800">
                            {{ $isRtl ? 'لا توجد طلبات عروض أسعار' : 'No quotation requests yet' }}
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $isRtl ? 'ابدأ بطلب عرض سعر لشحنتك.' : 'Start by requesting a quote for your shipment.' }}
                        </p>

                    </div>

                @endif

            </section>


            {{-- =================================================
                 NOTIFICATIONS / NEXT STEP
            ================================================== --}}

            <section class="mt-8 grid gap-6 lg:grid-cols-2">


                {{-- Notifications --}}

                <div
                    class="overflow-hidden rounded-2xl bg-white
                           shadow-sm ring-1 ring-slate-200">

                    <div
                        class="flex items-center justify-between
                               border-b border-slate-200 px-6 py-5">

                        <div>

                            <h3 class="text-lg font-semibold text-slate-900">
                                {{ $isRtl ? 'آخر التحديثات' : 'Latest Updates' }}
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ $isRtl ? 'الإشعارات والتحديثات المهمة.' : 'Important notifications and updates.' }}
                            </p>

                        </div>

                        <span class="text-xl">🔔</span>

                    </div>


                    @if ($dashboardNotifications->isNotEmpty())

                        <div class="divide-y divide-slate-100">

                            @foreach ($dashboardNotifications->take(4) as $notification)
                                <div class="flex gap-3 px-6 py-4">

                                    <div
                                        class="flex h-9 w-9 shrink-0
                                               items-center justify-center
                                               rounded-full bg-cyan-50">
                                        🔔
                                    </div>

                                    <div class="min-w-0">

                                        <p class="text-sm font-medium text-slate-800">
                                            {{ $notification->title ?? 'HAG Brokers' }}
                                        </p>

                                        <p class="mt-1 text-xs leading-5 text-slate-500">
                                            {{ $notification->message ?? '' }}
                                        </p>

                                    </div>

                                </div>
                            @endforeach

                        </div>
                    @else
                        <div class="px-6 py-10 text-center">

                            <div class="text-3xl">
                                🔔
                            </div>

                            <p class="mt-3 text-sm font-semibold text-slate-700">
                                {{ $isRtl ? 'لا توجد إشعارات جديدة' : 'No new notifications' }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                {{ $isRtl ? 'سنخبرك هنا عند وجود تحديثات مهمة.' : 'Important updates will appear here.' }}
                            </p>

                        </div>

                    @endif

                </div>


                {{-- Next step --}}

                <div
                    class="relative overflow-hidden rounded-2xl
                           bg-slate-950 p-7 text-white shadow-sm">

                    <div
                        class="pointer-events-none absolute -end-16 -top-16
                               h-48 w-48 rounded-full
                               bg-cyan-400/10 blur-3xl">
                    </div>


                    <div class="relative">

                        <div
                            class="flex h-12 w-12 items-center justify-center
                                   rounded-xl bg-cyan-400
                                   text-xl text-slate-950">
                            ✨
                        </div>


                        <p
                            class="mt-6 text-xs font-semibold uppercase
                                   tracking-[0.2em] text-cyan-400">
                            HAG BROKERS
                        </p>


                        <h3 class="mt-2 text-2xl font-bold">
                            {{ $isRtl ? 'ما هي خطوتك التالية؟' : 'What is your next step?' }}
                        </h3>


                        <p class="mt-3 max-w-lg text-sm leading-6 text-slate-400">
                            {{ $isRtl
                                ? 'ابدأ بطلب عرض سعر، تتبع شحنتك، أو أضف فرصة تجارية ليقوم فريق HAG Brokers بمراجعتها.'
                                : 'Request a quote, track your shipment, or submit a business opportunity for HAG Brokers to review.' }}
                        </p>


                        <div class="mt-6 flex flex-wrap gap-3">

                            <a href="{{ route('quotations.create') }}"
                                class="inline-flex items-center gap-2
                                       rounded-xl bg-cyan-400 px-4 py-3
                                       text-xs font-bold text-slate-950
                                       transition hover:bg-cyan-300">
                                <span>+</span>
                                {{ $isRtl ? 'طلب عرض سعر' : 'Request Quote' }}
                            </a>


                            <a href="{{ route('opportunities.create') }}"
                                class="inline-flex items-center gap-2
                                       rounded-xl border border-slate-700
                                       bg-white/5 px-4 py-3 text-xs
                                       font-semibold text-white transition
                                       hover:border-cyan-400">
                                💼
                                {{ $isRtl ? 'إضافة فرصة' : 'Add Opportunity' }}
                            </a>

                        </div>

                    </div>

                </div>

            </section>


            {{-- =================================================
                 GCC NETWORK
            ================================================== --}}

            <section class="mt-8 rounded-2xl bg-white shadow-sm
                       ring-1 ring-slate-200">

                <div class="border-b border-slate-200 px-6 py-5">

                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ __('dashboard.gulf_network') }}
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ __('dashboard.gulf_network_description') }}
                    </p>

                </div>


                @php

                    $countries = [
                        [
                            'flag' => '🇦🇪',
                            'name' => 'dashboard.united_arab_emirates',
                            'short' => 'dashboard.uae',
                            'ports' => ['dashboard.jebel_ali', 'dashboard.khalifa_port', 'dashboard.port_rashid'],
                            'uae' => true,
                        ],

                        [
                            'flag' => '🇸🇦',
                            'name' => 'dashboard.saudi_arabia',
                            'short' => 'dashboard.kingdom_of_saudi_arabia',
                            'ports' => ['dashboard.jeddah', 'dashboard.dammam', 'dashboard.king_abdullah_port'],
                        ],

                        [
                            'flag' => '🇴🇲',
                            'name' => 'dashboard.oman',
                            'short' => 'dashboard.sultanate_of_oman',
                            'ports' => ['dashboard.sohar', 'dashboard.salalah', 'dashboard.duqm'],
                        ],

                        [
                            'flag' => '🇶🇦',
                            'name' => 'dashboard.qatar',
                            'short' => 'dashboard.state_of_qatar',
                            'ports' => ['dashboard.hamad_port'],
                        ],

                        [
                            'flag' => '🇧🇭',
                            'name' => 'dashboard.bahrain',
                            'short' => 'dashboard.kingdom_of_bahrain',
                            'ports' => ['dashboard.khalifa_bin_salman'],
                        ],

                        [
                            'flag' => '🇰🇼',
                            'name' => 'dashboard.kuwait',
                            'short' => 'dashboard.state_of_kuwait',
                            'ports' => ['dashboard.shuwaikh', 'dashboard.shuaiba'],
                        ],
                    ];

                @endphp


                <div class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-3">

                    @foreach ($countries as $country)
                        <div
                            class="rounded-xl border p-5 transition
                                   hover:border-cyan-400
                                   hover:shadow-sm
                                   {{ $country['uae'] ?? false
                                       ? 'border-red-100 bg-gradient-to-br from-red-50 via-white to-emerald-50'
                                       : 'border-slate-200 bg-white' }}">

                            <div class="flex items-center gap-3">

                                <span class="text-2xl">
                                    {{ $country['flag'] }}
                                </span>

                                <div>

                                    <h4 class="font-semibold text-slate-900">
                                        {{ __($country['name']) }}
                                    </h4>

                                    <p class="text-xs text-slate-500">
                                        {{ __($country['short']) }}
                                    </p>

                                </div>

                            </div>


                            <div class="mt-4 flex flex-wrap gap-2">

                                @foreach ($country['ports'] as $port)
                                    <span
                                        class="rounded-lg bg-slate-50
                                               px-2.5 py-1 text-xs
                                               text-slate-600">
                                        {{ __($port) }}
                                    </span>
                                @endforeach

                            </div>

                        </div>
                    @endforeach

                </div>

            </section>


            {{-- =================================================
                 HELP / FOOTER
            ================================================== --}}

            <section
                class="mt-8 rounded-2xl border border-cyan-100
                       bg-gradient-to-r from-cyan-50 via-white
                       to-emerald-50 p-6 sm:p-8">

                <div
                    class="flex flex-col gap-5
                           sm:flex-row sm:items-center
                           sm:justify-between">

                    <div>

                        <p
                            class="text-xs font-semibold uppercase
                                   tracking-[0.2em] text-cyan-600">
                            HAG BROKERS
                        </p>

                        <h3 class="mt-2 text-xl font-bold text-slate-900">
                            {{ $isRtl ? 'هل تحتاج إلى مساعدة؟' : 'Need help?' }}
                        </h3>

                        <p class="mt-1 max-w-2xl text-sm text-slate-500">
                            {{ $isRtl
                                ? 'فريق HAG Brokers جاهز لمساعدتك في الشحن البحري والفرص التجارية.'
                                : 'The HAG Brokers team is ready to help with sea freight and business opportunities.' }}
                        </p>

                    </div>


                    <div class="flex flex-wrap gap-3">

                        <a href="{{ route('notifications.index') }}"
                            class="inline-flex items-center gap-2
                                   rounded-xl bg-slate-900 px-5 py-3
                                   text-xs font-bold text-white
                                   transition hover:bg-slate-800">
                            🔔
                            {{ $isRtl ? 'التحديثات' : 'Updates' }}
                        </a>


                        <a href="{{ route('quotations.create') }}"
                            class="inline-flex items-center gap-2
                                   rounded-xl bg-cyan-400 px-5 py-3
                                   text-xs font-bold text-slate-950
                                   transition hover:bg-cyan-300">
                            💬
                            {{ $isRtl ? 'ابدأ الآن' : 'Get Started' }}
                        </a>

                    </div>

                </div>

            </section>


            {{-- FOOTER --}}

            <div class="mt-8 pb-4 text-center">

                <p class="text-xs text-slate-400">

                    HAG Brokers

                    <span class="mx-1">•</span>

                    {{ __('dashboard.sea_freight_brokerage') }}

                    <span class="mx-1">•</span>

                    {{ __('dashboard.uae_and_gcc') }}

                </p>

            </div>

        </div>

    </div>

</x-app-layout>
