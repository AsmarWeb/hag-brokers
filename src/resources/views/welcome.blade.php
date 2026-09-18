@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        {{ $isAr ? 'HAG Brokers | وسيطك للشحن البحري' : 'HAG Brokers | Your Sea Freight Brokerage Partner' }}
    </title>

    <meta name="description"
        content="{{ $isAr
            ? 'HAG Brokers يربط المستوردين والمصدرين بشركات النقل البحري والشركاء اللوجستيين وينسق عروض الشحن والحجز والمتابعة.'
            : 'HAG Brokers connects importers and exporters with shipping lines and logistics partners for freight quotes, booking coordination and shipment visibility.' }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            background: #07111f;
        }

        .glass {
            background: rgba(15, 31, 52, 0.68);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .glass-light {
            background: rgba(255, 255, 255, 0.045);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .hero-grid {
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
            background-size: 55px 55px;
        }

        .glow {
            box-shadow:
                0 0 80px rgba(34, 211, 238, 0.10),
                0 0 140px rgba(14, 165, 233, 0.07);
        }

        .cyan-glow {
            box-shadow: 0 0 35px rgba(34, 211, 238, 0.18);
        }

        .gradient-text {
            background: linear-gradient(90deg,
                    #67e8f9,
                    #22d3ee,
                    #38bdf8);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .network-line {
            background: linear-gradient(90deg,
                    transparent,
                    rgba(34, 211, 238, .55),
                    transparent);
        }

        [dir="rtl"] .ltr-number {
            direction: ltr;
            unicode-bidi: isolate;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        .float-animation {
            animation: float 5s ease-in-out infinite;
        }
    </style>
</head>

<body class="min-h-screen overflow-x-hidden bg-[#07111f] text-white">

    <!-- ========================================================= -->
    <!-- NAVBAR -->
    <!-- ========================================================= -->

    <header id="navbar" class="fixed inset-x-0 top-0 z-50 border-b border-white/5 bg-[#07111f]/80 backdrop-blur-xl">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 lg:px-8">

            <!-- Logo -->
            <a href="{{ url('/') }}" class="flex items-center gap-3">

                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-400/10 ring-1 ring-cyan-300/20">
                    <span class="text-lg font-black text-cyan-300">
                        HAG
                    </span>
                </div>

                <div>
                    <div class="text-lg font-black tracking-wide">
                        HAG <span class="text-cyan-300">BROKERS</span>
                    </div>

                    <div class="text-[10px] uppercase tracking-[0.25em] text-slate-500">
                        Sea Freight Brokerage
                    </div>
                </div>

            </a>

            <!-- Desktop Navigation -->
            <nav class="hidden items-center gap-8 lg:flex">

                <a href="#how-it-works" class="text-sm text-slate-300 transition hover:text-cyan-300">
                    {{ $isAr ? 'كيف نعمل' : 'How It Works' }}
                </a>

                <a href="#services" class="text-sm text-slate-300 transition hover:text-cyan-300">
                    {{ $isAr ? 'الخدمات' : 'Services' }}
                </a>

                <a href="#why-hag" class="text-sm text-slate-300 transition hover:text-cyan-300">
                    {{ $isAr ? 'لماذا HAG' : 'Why HAG' }}
                </a>

                <a href="#partners" class="text-sm text-slate-300 transition hover:text-cyan-300">
                    {{ $isAr ? 'للشركاء' : 'For Partners' }}
                </a>

                <a href="#contact" class="text-sm text-slate-300 transition hover:text-cyan-300">
                    {{ $isAr ? 'تواصل معنا' : 'Contact' }}
                </a>

            </nav>

            <!-- Desktop Actions -->
            <div class="hidden items-center gap-3 lg:flex">

                <!-- Language -->
                <div class="relative" id="language-menu">

                    <button type="button" onclick="toggleLanguageMenu()"
                        class="flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-slate-200 transition hover:bg-white/10">
                        <span>🌐</span>

                        <span>
                            {{ $isAr ? 'العربية' : 'English' }}
                        </span>

                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="language-dropdown"
                        class="absolute {{ $isAr ? 'left-0' : 'right-0' }} mt-2 hidden w-40 overflow-hidden rounded-2xl border border-white/10 bg-[#0b1b2e] shadow-2xl">

                        <a href="{{ route('language.switch', 'en') }}"
                            class="flex items-center gap-3 px-4 py-3 text-sm text-slate-200 transition hover:bg-white/10">
                            🇬🇧
                            <span>English</span>
                        </a>

                        <a href="{{ route('language.switch', 'ar') }}"
                            class="flex items-center gap-3 px-4 py-3 text-sm text-slate-200 transition hover:bg-white/10">
                            🇦🇪
                            <span>العربية</span>
                        </a>

                    </div>
                </div>

                @auth

                    <a href="{{ url('/dashboard') }}"
                        class="rounded-xl border border-white/10 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-white/5">
                        {{ $isAr ? 'لوحة التحكم' : 'Dashboard' }}
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="rounded-xl border border-white/10 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-white/5">
                        {{ $isAr ? 'تسجيل الدخول' : 'Login' }}
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-cyan-300">
                            {{ $isAr ? 'إنشاء حساب' : 'Create Account' }}
                        </a>
                    @endif

                @endauth

            </div>

            <!-- Mobile Button -->
            <button id="mobile-menu-button" type="button"
                class="flex h-11 w-11 items-center justify-center rounded-xl border border-white/10 bg-white/5 lg:hidden"
                aria-label="Open menu">

                <svg id="menu-open-icon" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>

                <svg id="menu-close-icon" class="hidden h-6 w-6" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>

            </button>

        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden border-t border-white/5 bg-[#07111f]">

            <div class="mx-auto max-w-7xl space-y-2 px-5 py-6">

                <a href="#how-it-works" class="mobile-link block rounded-xl px-4 py-3 text-slate-200 hover:bg-white/5">
                    {{ $isAr ? 'كيف نعمل' : 'How It Works' }}
                </a>

                <a href="#services" class="mobile-link block rounded-xl px-4 py-3 text-slate-200 hover:bg-white/5">
                    {{ $isAr ? 'الخدمات' : 'Services' }}
                </a>

                <a href="#why-hag" class="mobile-link block rounded-xl px-4 py-3 text-slate-200 hover:bg-white/5">
                    {{ $isAr ? 'لماذا HAG' : 'Why HAG' }}
                </a>

                <a href="#partners" class="mobile-link block rounded-xl px-4 py-3 text-slate-200 hover:bg-white/5">
                    {{ $isAr ? 'للشركاء' : 'For Partners' }}
                </a>

                <a href="#contact" class="mobile-link block rounded-xl px-4 py-3 text-slate-200 hover:bg-white/5">
                    {{ $isAr ? 'تواصل معنا' : 'Contact' }}
                </a>

                <div class="my-4 h-px bg-white/10"></div>

                <!-- Mobile Language -->
                <div class="grid grid-cols-2 gap-2">

                    <a href="{{ route('language.switch', 'en') }}"
                        class="rounded-xl border border-white/10 px-4 py-3 text-center text-sm text-slate-200">
                        🇬🇧 English
                    </a>

                    <a href="{{ route('language.switch', 'ar') }}"
                        class="rounded-xl border border-white/10 px-4 py-3 text-center text-sm text-slate-200">
                        🇦🇪 العربية
                    </a>

                </div>

                <div class="grid grid-cols-2 gap-2 pt-2">

                    @auth

                        <a href="{{ url('/dashboard') }}"
                            class="rounded-xl border border-white/10 px-4 py-3 text-center text-sm text-white">
                            {{ $isAr ? 'لوحة التحكم' : 'Dashboard' }}
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="rounded-xl border border-white/10 px-4 py-3 text-center text-sm text-white">
                            {{ $isAr ? 'دخول' : 'Login' }}
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="rounded-xl bg-cyan-400 px-4 py-3 text-center text-sm font-bold text-slate-950">
                                {{ $isAr ? 'حساب جديد' : 'Register' }}
                            </a>
                        @endif

                    @endauth

                </div>

            </div>

        </div>
    </header>


    <!-- ========================================================= -->
    <!-- HERO -->
    <!-- ========================================================= -->

    <main>

        <section class="relative overflow-hidden pt-32 lg:pt-40">

            <div class="hero-grid absolute inset-0 opacity-70"></div>

            <div class="absolute left-1/2 top-20 h-96 w-96 -translate-x-1/2 rounded-full bg-cyan-500/10 blur-3xl">
            </div>

            <div class="relative mx-auto max-w-7xl px-5 pb-20 lg:px-8 lg:pb-28">

                <div class="grid items-center gap-14 lg:grid-cols-2">

                    <!-- Hero Text -->
                    <div class="{{ $isAr ? 'lg:text-right' : 'lg:text-left' }}">

                        <div
                            class="mb-6 inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-cyan-300/5 px-4 py-2 text-xs font-semibold tracking-wider text-cyan-300">

                            <span class="h-2 w-2 rounded-full bg-cyan-300"></span>

                            SEA FREIGHT BROKERAGE

                        </div>

                        <h1 class="max-w-4xl text-4xl font-black leading-tight tracking-tight sm:text-5xl lg:text-6xl">

                            {{ $isAr ? 'وسيطك للشحن البحري' : 'Your Sea Freight Brokerage Partner' }}

                            <span class="mt-2 block gradient-text">

                                {{ $isAr ? 'نربط شحنتك بالناقل المناسب' : 'Connecting Your Cargo With the Right Carrier' }}

                            </span>

                        </h1>

                        <p class="mt-7 max-w-2xl text-base leading-8 text-slate-300 sm:text-lg">

                            {{ $isAr
                                ? 'أرسل تفاصيل شحنتك، ونحن ننسّق لك خيارات الشحن مع شركات النقل البحري والشركاء اللوجستيين، من طلب عرض السعر وحتى متابعة الشحنة.'
                                : 'Share your shipment details and we coordinate freight options with shipping lines and logistics partners — from quotation to shipment visibility.' }}

                        </p>

                        <div
                            class="mt-9 flex flex-col gap-3 sm:flex-row {{ $isAr ? 'sm:flex-row-reverse lg:justify-end' : '' }}">

                            <a href="#contact"
                                class="cyan-glow inline-flex items-center justify-center gap-2 rounded-2xl bg-cyan-400 px-7 py-4 font-bold text-slate-950 transition hover:-translate-y-0.5 hover:bg-cyan-300">
                                {{ $isAr ? 'اطلب عرض شحن' : 'Request a Freight Quote' }}

                                <span>
                                    {{ $isAr ? '←' : '→' }}
                                </span>
                            </a>

                            <a href="#how-it-works"
                                class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-7 py-4 font-semibold text-white transition hover:bg-white/10">
                                {{ $isAr ? 'كيف تعمل الوساطة؟' : 'How Brokerage Works' }}
                            </a>

                        </div>

                        <div class="mt-9 flex flex-wrap gap-x-6 gap-y-3 text-xs text-slate-400">

                            <span>✓ {{ $isAr ? 'ربط بالناقلين' : 'Carrier Matching' }}</span>

                            <span>✓ {{ $isAr ? 'مقارنة الخيارات' : 'Offer Comparison' }}</span>

                            <span>✓ {{ $isAr ? 'تنسيق الحجز' : 'Booking Coordination' }}</span>

                            <span>✓ {{ $isAr ? 'متابعة الشحنة' : 'Shipment Visibility' }}</span>

                        </div>

                    </div>


                    <!-- Hero Brokerage Visualization -->
                    <div class="relative">

                        <div class="absolute -inset-8 rounded-[3rem] bg-cyan-400/5 blur-3xl"></div>

                        <div class="glass glow relative rounded-[2rem] p-5 sm:p-7">

                            <!-- Top -->
                            <div class="flex items-center justify-between">

                                <div>
                                    <p class="text-xs uppercase tracking-widest text-slate-500">
                                        HAG BROKERS
                                    </p>

                                    <h2 class="mt-1 text-lg font-bold">
                                        {{ $isAr ? 'رحلة الوساطة' : 'Brokerage Journey' }}
                                    </h2>
                                </div>

                                <div
                                    class="rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1 text-xs text-cyan-300">
                                    {{ $isAr ? 'نشط' : 'Active' }}
                                </div>

                            </div>


                            <!-- Customer -->
                            <div class="mt-8 rounded-2xl border border-white/10 bg-white/[0.035] p-5">

                                <div class="flex items-center gap-4">

                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-400/10 text-2xl">
                                        📦
                                    </div>

                                    <div>
                                        <p class="text-xs text-slate-500">
                                            {{ $isAr ? 'العميل' : 'Customer' }}
                                        </p>

                                        <p class="font-bold">
                                            {{ $isAr ? 'مستورد / مصدر' : 'Importer / Exporter' }}
                                        </p>
                                    </div>

                                </div>

                            </div>


                            <!-- Connector -->
                            <div class="relative flex h-14 items-center justify-center">

                                <div class="absolute h-px w-3/4 network-line"></div>

                                <div
                                    class="relative z-10 flex h-9 w-9 items-center justify-center rounded-full bg-cyan-400 text-sm font-black text-slate-950">
                                    HAG
                                </div>

                            </div>


                            <!-- HAG -->
                            <div class="rounded-2xl border border-cyan-300/20 bg-cyan-300/5 p-5">

                                <div class="flex items-center gap-4">

                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-300 text-lg font-black text-slate-950">
                                        HAG
                                    </div>

                                    <div>

                                        <p class="text-xs text-cyan-300">
                                            HAG BROKERS
                                        </p>

                                        <p class="font-bold">
                                            {{ $isAr ? 'وساطة • مقارنة • تنسيق' : 'Brokerage • Sourcing • Coordination' }}
                                        </p>

                                    </div>

                                </div>

                            </div>


                            <!-- Connector -->
                            <div class="relative flex h-14 items-center justify-center">

                                <div class="absolute h-px w-3/4 network-line"></div>

                                <div
                                    class="relative z-10 rounded-full bg-slate-800 px-3 py-1 text-[10px] text-cyan-300">
                                    MATCH
                                </div>

                            </div>


                            <!-- Carriers -->
                            <div class="grid grid-cols-2 gap-3">

                                <div class="rounded-2xl border border-white/10 bg-white/[0.035] p-4">

                                    <div class="mb-3 text-xl">
                                        🚢
                                    </div>

                                    <p class="text-xs text-slate-500">
                                        {{ $isAr ? 'شركات النقل' : 'Shipping Lines' }}
                                    </p>

                                    <p class="mt-1 text-sm font-bold">
                                        {{ $isAr ? 'خيارات الناقل' : 'Carrier Options' }}
                                    </p>

                                </div>

                                <div class="rounded-2xl border border-white/10 bg-white/[0.035] p-4">

                                    <div class="mb-3 text-xl">
                                        🌐
                                    </div>

                                    <p class="text-xs text-slate-500">
                                        {{ $isAr ? 'الشركاء' : 'Partners' }}
                                    </p>

                                    <p class="mt-1 text-sm font-bold">
                                        {{ $isAr ? 'شركاء لوجستيون' : 'Logistics Partners' }}
                                    </p>

                                </div>

                            </div>


                            <!-- Status -->
                            <div class="mt-5 flex items-center justify-between rounded-xl bg-black/20 px-4 py-3">

                                <span class="text-xs text-slate-400">
                                    {{ $isAr ? 'الحالة' : 'Status' }}
                                </span>

                                <span class="flex items-center gap-2 text-xs text-emerald-300">

                                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>

                                    {{ $isAr ? 'بانتظار اختيار العرض' : 'Awaiting offer selection' }}

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- ========================================================= -->
        <!-- POSITIONING STRIP -->
        <!-- ========================================================= -->

        <section class="border-y border-white/5 bg-white/[0.02]">

            <div class="mx-auto grid max-w-7xl grid-cols-2 divide-x divide-white/5 px-5 lg:grid-cols-4 lg:px-8">

                <div class="px-4 py-7 text-center">
                    <p class="text-sm font-bold text-white">
                        {{ $isAr ? 'طلب عرض' : 'Quote Request' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $isAr ? 'أرسل متطلبات الشحنة' : 'Share shipment details' }}
                    </p>
                </div>

                <div class="px-4 py-7 text-center">
                    <p class="text-sm font-bold text-white">
                        {{ $isAr ? 'مطابقة الناقل' : 'Carrier Matching' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $isAr ? 'مصادر وخيارات متاحة' : 'Source available options' }}
                    </p>
                </div>

                <div class="px-4 py-7 text-center">
                    <p class="text-sm font-bold text-white">
                        {{ $isAr ? 'مقارنة العروض' : 'Offer Comparison' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $isAr ? 'قارن قبل الاختيار' : 'Compare before selecting' }}
                    </p>
                </div>

                <div class="px-4 py-7 text-center">
                    <p class="text-sm font-bold text-white">
                        {{ $isAr ? 'التنسيق والمتابعة' : 'Coordinate & Track' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $isAr ? 'نقطة اتصال واحدة' : 'One point of contact' }}
                    </p>
                </div>

            </div>

        </section>


        <!-- ========================================================= -->
        <!-- HOW IT WORKS -->
        <!-- ========================================================= -->

        <section id="how-it-works" class="scroll-mt-24 py-24 lg:py-32">

            <div class="mx-auto max-w-7xl px-5 lg:px-8">

                <div class="max-w-3xl">

                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-cyan-300">
                        01 — {{ $isAr ? 'كيف نعمل' : 'HOW IT WORKS' }}
                    </span>

                    <h2 class="mt-4 text-3xl font-black sm:text-4xl">
                        {{ $isAr ? 'وساطة شحن واضحة من البداية إلى الوصول' : 'A Clear Brokerage Process From Request to Arrival' }}
                    </h2>

                    <p class="mt-5 leading-8 text-slate-400">
                        {{ $isAr
                            ? 'نحوّل طلب الشحن إلى عملية منظمة: نفهم متطلباتك، نبحث عن الخيارات المناسبة، نساعدك في المقارنة ثم ننسق الخطوات التالية مع الأطراف المعنية.'
                            : 'We turn your freight request into an organized process: understand your requirements, source suitable options, help you compare them, and coordinate the next steps with the relevant parties.' }}
                    </p>

                </div>


                <div class="mt-14 grid gap-5 md:grid-cols-2 lg:grid-cols-4">

                    @php
                        $steps = $isAr
                            ? [
                                [
                                    '01',
                                    'أرسل التفاصيل',
                                    'شارك ميناء الانطلاق والوصول، نوع الحمولة، الحجم والمعدات المطلوبة.',
                                ],
                                [
                                    '02',
                                    'نبحث عن الخيارات',
                                    'ننسق مع شركات النقل البحري والشركاء المتاحين حسب متطلبات الشحنة.',
                                ],
                                [
                                    '03',
                                    'قارن واختر',
                                    'نساعدك على مراجعة الخيارات والعروض المتاحة قبل اتخاذ قرار الحجز.',
                                ],
                                ['04', 'ننسق ونتابع', 'ننسق الخطوات التشغيلية ونوفر لك رؤية أفضل لحالة الشحنة.'],
                            ]
                            : [
                                [
                                    '01',
                                    'Send Details',
                                    'Share origin, destination, cargo type, volume and equipment requirements.',
                                ],
                                [
                                    '02',
                                    'Source Options',
                                    'We coordinate with available shipping lines and logistics partners.',
                                ],
                                [
                                    '03',
                                    'Compare & Choose',
                                    'Review available options and quotations before selecting a service.',
                                ],
                                [
                                    '04',
                                    'Coordinate & Track',
                                    'We coordinate the next steps and provide shipment visibility.',
                                ],
                            ];
                    @endphp

                    @foreach ($steps as $step)
                        <div
                            class="glass-light group rounded-3xl p-6 transition hover:-translate-y-1 hover:border-cyan-300/20">

                            <div class="flex items-center justify-between">

                                <span class="text-4xl font-black text-white/10">
                                    {{ $step[0] }}
                                </span>

                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-300">
                                    {{ $loop->iteration == 1 ? '📋' : ($loop->iteration == 2 ? '🔎' : ($loop->iteration == 3 ? '⚖️' : '📍')) }}
                                </div>

                            </div>

                            <h3 class="mt-7 text-lg font-bold">
                                {{ $step[1] }}
                            </h3>

                            <p class="mt-3 text-sm leading-7 text-slate-400">
                                {{ $step[2] }}
                            </p>

                        </div>
                    @endforeach

                </div>

            </div>

        </section>


        <!-- ========================================================= -->
        <!-- SERVICES -->
        <!-- ========================================================= -->

        <section id="services" class="scroll-mt-24 border-y border-white/5 bg-white/[0.02] py-24 lg:py-32">

            <div class="mx-auto max-w-7xl px-5 lg:px-8">

                <div class="text-center">

                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-cyan-300">
                        02 — {{ $isAr ? 'الخدمات' : 'SERVICES' }}
                    </span>

                    <h2 class="mt-4 text-3xl font-black sm:text-4xl">
                        {{ $isAr ? 'خدمات مبنية حول دور الوسيط' : 'Services Built Around the Broker Role' }}
                    </h2>

                    <p class="mx-auto mt-5 max-w-2xl leading-8 text-slate-400">
                        {{ $isAr
                            ? 'نركز على الربط والتنسيق وإدارة خيارات الشحن بدلاً من تقديم أنفسنا كناقل بحري.'
                            : 'Our focus is connecting, coordinating and managing freight options — not presenting ourselves as a shipping line.' }}
                    </p>

                </div>


                <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

                    @php
                        $services = $isAr
                            ? [
                                [
                                    '💬',
                                    'وساطة عروض الشحن',
                                    'استقبال طلب الشحن وتنسيق الحصول على عروض مناسبة لمتطلباتك.',
                                ],
                                [
                                    '🚢',
                                    'مطابقة الناقلين',
                                    'ربط الشحنة بخيارات من شركات النقل البحري والشركاء المتاحين.',
                                ],
                                [
                                    '⚖️',
                                    'مقارنة الخيارات',
                                    'تنظيم المعلومات الأساسية لمساعدتك في مراجعة الخيارات المتاحة.',
                                ],
                                [
                                    '📦',
                                    'FCL و LCL',
                                    'تنسيق احتياجات الشحن بالحاويات الكاملة أو الشحن الجزئي حسب الخدمة المتاحة.',
                                ],
                                ['📝', 'تنسيق الحجز', 'متابعة خطوات الحجز والتواصل بين العميل والطرف المقدم للخدمة.'],
                                [
                                    '📍',
                                    'متابعة الشحنة',
                                    'تجميع وتحديث معلومات الشحنة ومراحلها التشغيلية حسب البيانات المتاحة.',
                                ],
                            ]
                            : [
                                [
                                    '💬',
                                    'Freight Quote Brokerage',
                                    'Receive shipment requests and coordinate suitable freight quotations.',
                                ],
                                [
                                    '🚢',
                                    'Carrier Matching',
                                    'Connect cargo requirements with available shipping lines and logistics partners.',
                                ],
                                [
                                    '⚖️',
                                    'Offer Comparison',
                                    'Organize key information to make available freight options easier to review.',
                                ],
                                [
                                    '📦',
                                    'FCL & LCL',
                                    'Coordinate full-container and less-than-container shipment requirements where available.',
                                ],
                                [
                                    '📝',
                                    'Booking Coordination',
                                    'Coordinate booking steps and communication between customers and service providers.',
                                ],
                                [
                                    '📍',
                                    'Shipment Visibility',
                                    'Centralize shipment updates and milestones based on available tracking information.',
                                ],
                            ];
                    @endphp

                    @foreach ($services as $service)
                        <div
                            class="glass-light rounded-3xl p-7 transition duration-300 hover:-translate-y-1 hover:bg-white/[0.06]">

                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/10 text-2xl">
                                {{ $service[0] }}
                            </div>

                            <h3 class="mt-6 text-lg font-bold">
                                {{ $service[1] }}
                            </h3>

                            <p class="mt-3 text-sm leading-7 text-slate-400">
                                {{ $service[2] }}
                            </p>

                        </div>
                    @endforeach

                </div>

            </div>

        </section>


        <!-- ========================================================= -->
        <!-- WHY HAG -->
        <!-- ========================================================= -->

        <section id="why-hag" class="scroll-mt-24 py-24 lg:py-32">

            <div class="mx-auto grid max-w-7xl items-center gap-14 px-5 lg:grid-cols-2 lg:px-8">

                <!-- Left -->
                <div>

                    <span class="text-xs font-bold uppercase tracking-[0.25em] text-cyan-300">
                        03 — {{ $isAr ? 'لماذا HAG' : 'WHY HAG' }}
                    </span>

                    <h2 class="mt-4 text-3xl font-black sm:text-4xl">
                        {{ $isAr
                            ? 'نقطة اتصال واحدة بدل التعامل مع عدة أطراف'
                            : 'One Coordination Point Instead of Managing Multiple Parties' }}
                    </h2>

                    <p class="mt-6 leading-8 text-slate-400">
                        {{ $isAr
                            ? 'دور HAG Brokers هو تبسيط عملية البحث والتواصل والتنسيق بين العميل والناقلين والشركاء اللوجستيين المشاركين في رحلة الشحن.'
                            : 'HAG Brokers helps simplify the process of sourcing, communicating and coordinating between customers, carriers and logistics partners involved in the shipment journey.' }}
                    </p>


                    <div class="mt-8 space-y-4">

                        @php
                            $benefits = $isAr
                                ? [
                                    'تقليل الوقت المطلوب للبحث عن خيارات الشحن',
                                    'تنظيم عروض وخيارات الناقلين في عملية واحدة',
                                    'تواصل مركزي مع الأطراف ذات العلاقة',
                                    'تنسيق أفضل للمستندات والحجز والمتابعة',
                                    'رؤية أوضح لحالة الشحنة والمراحل القادمة',
                                ]
                                : [
                                    'Reduce the time spent searching for freight options',
                                    'Organize carrier offers through one process',
                                    'Centralize communication with relevant parties',
                                    'Coordinate booking, documentation and follow-up',
                                    'Improve visibility across shipment milestones',
                                ];
                        @endphp

                        @foreach ($benefits as $benefit)
                            <div class="flex items-start gap-3">

                                <div
                                    class="mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-cyan-400/10 text-xs text-cyan-300">
                                    ✓
                                </div>

                                <p class="text-sm leading-6 text-slate-300">
                                    {{ $benefit }}
                                </p>

                            </div>
                        @endforeach

                    </div>

                </div>


                <!-- Right -->
                <div class="glass rounded-[2rem] p-5 glow sm:p-7">

                    <div class="rounded-2xl border border-white/10 bg-[#081522] p-5">

                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-xs text-slate-500">
                                    HAG BROKERS
                                </p>

                                <p class="mt-1 font-bold">
                                    {{ $isAr ? 'لوحة طلب الشحن' : 'Freight Request Dashboard' }}
                                </p>
                            </div>

                            <span class="rounded-full bg-cyan-300/10 px-3 py-1 text-xs text-cyan-300">
                                {{ $isAr ? 'طلب جديد' : 'New Request' }}
                            </span>

                        </div>


                        <div class="mt-6 space-y-3">

                            <div class="rounded-xl border border-white/5 bg-white/[0.03] p-4">

                                <div class="flex justify-between">

                                    <span class="text-xs text-slate-500">
                                        {{ $isAr ? 'المسار' : 'Route' }}
                                    </span>

                                    <span class="text-xs font-semibold text-white">
                                        {{ $isAr ? 'ميناء → ميناء' : 'Port → Port' }}
                                    </span>

                                </div>

                            </div>


                            <div class="rounded-xl border border-white/5 bg-white/[0.03] p-4">

                                <div class="flex justify-between">

                                    <span class="text-xs text-slate-500">
                                        {{ $isAr ? 'نوع الشحنة' : 'Cargo Type' }}
                                    </span>

                                    <span class="text-xs font-semibold text-white">
                                        {{ $isAr ? 'حمولة عامة' : 'General Cargo' }}
                                    </span>

                                </div>

                            </div>


                            <div class="rounded-xl border border-white/5 bg-white/[0.03] p-4">

                                <div class="flex justify-between">

                                    <span class="text-xs text-slate-500">
                                        {{ $isAr ? 'الخيارات' : 'Options' }}
                                    </span>

                                    <span class="text-xs font-semibold text-cyan-300">
                                        {{ $isAr ? 'بانتظار عروض' : 'Awaiting Quotes' }}
                                    </span>

                                </div>

                            </div>

                        </div>


                        <div class="mt-6 rounded-2xl bg-cyan-400/10 p-5">

                            <div class="flex items-center gap-3">

                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400 text-slate-950">
                                    ✓
                                </div>

                                <div>

                                    <p class="text-sm font-bold">
                                        {{ $isAr ? 'HAG كوسيط ومنسق' : 'HAG as Broker & Coordinator' }}
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-slate-400">
                                        {{ $isAr
                                            ? 'ننسق بين العميل ومقدمي خدمات النقل المتاحين.'
                                            : 'Coordinating between customers and available freight service providers.' }}
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- ========================================================= -->
        <!-- WHO WE SERVE -->
        <!-- ========================================================= -->

        <section class="border-y border-white/5 bg-white/[0.02] py-24">

            <div class="mx-auto max-w-7xl px-5 lg:px-8">

                <div class="grid gap-5 lg:grid-cols-2">

                    <div class="rounded-[2rem] border border-cyan-300/10 bg-cyan-300/[0.04] p-8 lg:p-10">

                        <div class="text-3xl">
                            📦
                        </div>

                        <h3 class="mt-6 text-2xl font-black">
                            {{ $isAr ? 'للمستوردين والمصدرين' : 'For Importers & Exporters' }}
                        </h3>

                        <p class="mt-4 leading-8 text-slate-400">
                            {{ $isAr
                                ? 'أرسل متطلبات الشحنة واحصل على عملية منظمة للبحث عن خيارات الشحن والتنسيق معها من نقطة اتصال واحدة.'
                                : 'Share your shipment requirements and get an organized process for sourcing and coordinating freight options through one point of contact.' }}
                        </p>

                        <a href="#contact" class="mt-7 inline-flex items-center gap-2 font-bold text-cyan-300">
                            {{ $isAr ? 'ابدأ طلب الشحن' : 'Start a Freight Request' }}
                            <span>{{ $isAr ? '←' : '→' }}</span>
                        </a>

                    </div>


                    <div class="rounded-[2rem] border border-white/10 bg-white/[0.03] p-8 lg:p-10">

                        <div class="text-3xl">
                            🚢
                        </div>

                        <h3 class="mt-6 text-2xl font-black">
                            {{ $isAr ? 'لشركات النقل والشركاء' : 'For Carriers & Logistics Partners' }}
                        </h3>

                        <p class="mt-4 leading-8 text-slate-400">
                            {{ $isAr
                                ? 'وسّع فرص الوصول إلى طلبات الشحن من خلال شبكة HAG من العملاء والشركاء، وفقاً للخدمات والمسارات المتاحة لديك.'
                                : 'Access freight opportunities through the HAG network of customers and partners, based on your available services and routes.' }}
                        </p>

                        <a href="#partners" class="mt-7 inline-flex items-center gap-2 font-bold text-cyan-300">
                            {{ $isAr ? 'انضم كشريك' : 'Become a Partner' }}
                            <span>{{ $isAr ? '←' : '→' }}</span>
                        </a>

                    </div>

                </div>

            </div>

        </section>


        <!-- ========================================================= -->
        <!-- PARTNERS -->
        <!-- ========================================================= -->

        <section id="partners" class="scroll-mt-24 py-24 lg:py-32">

            <div class="mx-auto max-w-5xl px-5 text-center lg:px-8">

                <span class="text-xs font-bold uppercase tracking-[0.25em] text-cyan-300">
                    04 — {{ $isAr ? 'شبكة الشركاء' : 'PARTNER NETWORK' }}
                </span>

                <h2 class="mt-4 text-3xl font-black sm:text-4xl">
                    {{ $isAr ? 'هل أنت شركة نقل بحري أو شريك لوجستي؟' : 'Are You a Shipping Line or Logistics Partner?' }}
                </h2>

                <p class="mx-auto mt-5 max-w-2xl leading-8 text-slate-400">
                    {{ $isAr
                        ? 'انضم إلى شبكة HAG لتلقي طلبات الشحن المناسبة لخدماتك ومساراتك المتاحة.'
                        : 'Join the HAG partner network and receive freight opportunities that match your available services and routes.' }}
                </p>


                <div class="mt-10 flex flex-col justify-center gap-3 sm:flex-row">

                    <a href="#contact"
                        class="rounded-2xl bg-cyan-400 px-7 py-4 font-bold text-slate-950 transition hover:bg-cyan-300">
                        {{ $isAr ? 'تواصل مع فريق الشراكات' : 'Contact Partnerships' }}
                    </a>

                </div>

            </div>

        </section>


        <!-- ========================================================= -->
        <!-- CONTACT / CTA -->
        <!-- ========================================================= -->

        <section id="contact" class="scroll-mt-24 pb-24 lg:pb-32">

            <div class="mx-auto max-w-7xl px-5 lg:px-8">

                <div
                    class="relative overflow-hidden rounded-[2rem] border border-cyan-300/10 bg-gradient-to-br from-cyan-400/10 via-slate-900 to-slate-950 p-8 lg:p-14">

                    <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl"></div>

                    <div class="relative grid items-center gap-10 lg:grid-cols-2">

                        <div>

                            <span class="text-xs font-bold uppercase tracking-[0.25em] text-cyan-300">
                                {{ $isAr ? 'ابدأ الآن' : 'GET STARTED' }}
                            </span>

                            <h2 class="mt-4 text-3xl font-black sm:text-4xl">
                                {{ $isAr ? 'لديك شحنة؟ دعنا نبدأ من متطلباتها.' : 'Have a shipment? Let’s start with your requirements.' }}
                            </h2>

                            <p class="mt-5 max-w-xl leading-8 text-slate-400">
                                {{ $isAr
                                    ? 'أرسل بيانات الشحنة، وسيتحول طلبك إلى عملية وساطة منظمة للبحث عن خيارات النقل والتنسيق معها.'
                                    : 'Share your shipment details and turn your request into an organized brokerage process for sourcing and coordinating freight options.' }}
                            </p>

                        </div>


                        <div class="flex flex-col gap-3 sm:flex-row lg:justify-end">

                            @auth

                                <a href="{{ url('/dashboard') }}"
                                    class="inline-flex items-center justify-center rounded-2xl bg-cyan-400 px-7 py-4 font-bold text-slate-950 transition hover:bg-cyan-300">
                                    {{ $isAr ? 'فتح لوحة التحكم' : 'Open Dashboard' }}
                                </a>
                            @else
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}"
                                        class="inline-flex items-center justify-center rounded-2xl bg-cyan-400 px-7 py-4 font-bold text-slate-950 transition hover:bg-cyan-300">
                                        {{ $isAr ? 'ابدأ طلبك' : 'Start Your Request' }}
                                    </a>
                                @endif

                                <a href="{{ route('login') }}"
                                    class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-7 py-4 font-semibold text-white transition hover:bg-white/10">
                                    {{ $isAr ? 'تسجيل الدخول' : 'Login' }}
                                </a>

                            @endauth

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </main>


    <!-- ========================================================= -->
    <!-- FOOTER -->
    <!-- ========================================================= -->

    <footer class="border-t border-white/5 bg-[#050d18]">

        <div class="mx-auto max-w-7xl px-5 py-12 lg:px-8">

            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">

                <!-- Brand -->
                <div>

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-300">
                            H
                        </div>

                        <div>
                            <p class="font-black">
                                HAG <span class="text-cyan-300">BROKERS</span>
                            </p>

                            <p class="text-[9px] uppercase tracking-widest text-slate-600">
                                Sea Freight Brokerage
                            </p>
                        </div>

                    </div>

                    <p class="mt-5 text-sm leading-7 text-slate-500">
                        {{ $isAr
                            ? 'وساطة وتنسيق للشحن البحري تربط العملاء بالناقلين والشركاء اللوجستيين.'
                            : 'Sea freight brokerage and coordination connecting customers with carriers and logistics partners.' }}
                    </p>

                </div>


                <!-- Navigation -->
                <div>

                    <h3 class="font-bold">
                        {{ $isAr ? 'المنصة' : 'Platform' }}
                    </h3>

                    <div class="mt-5 space-y-3 text-sm text-slate-500">

                        <a href="#how-it-works" class="block hover:text-cyan-300">
                            {{ $isAr ? 'كيف نعمل' : 'How It Works' }}
                        </a>

                        <a href="#services" class="block hover:text-cyan-300">
                            {{ $isAr ? 'الخدمات' : 'Services' }}
                        </a>

                        <a href="#why-hag" class="block hover:text-cyan-300">
                            {{ $isAr ? 'لماذا HAG' : 'Why HAG' }}
                        </a>

                    </div>

                </div>


                <!-- Partners -->
                <div>

                    <h3 class="font-bold">
                        {{ $isAr ? 'الشركاء' : 'Partners' }}
                    </h3>

                    <div class="mt-5 space-y-3 text-sm text-slate-500">

                        <a href="#partners" class="block hover:text-cyan-300">
                            {{ $isAr ? 'شركات النقل البحري' : 'Shipping Lines' }}
                        </a>

                        <a href="#partners" class="block hover:text-cyan-300">
                            {{ $isAr ? 'الشركاء اللوجستيون' : 'Logistics Partners' }}
                        </a>

                        <a href="#contact" class="block hover:text-cyan-300">
                            {{ $isAr ? 'انضم لشبكة HAG' : 'Join HAG Network' }}
                        </a>

                    </div>

                </div>


                <!-- Contact -->
                <div>

                    <h3 class="font-bold">
                        {{ $isAr ? 'تواصل' : 'Contact' }}
                    </h3>

                    <div class="mt-5 space-y-3 text-sm text-slate-500">

                        <a href="#contact" class="block hover:text-cyan-300">
                            {{ $isAr ? 'طلب عرض شحن' : 'Request a Quote' }}
                        </a>

                        @guest

                            <a href="{{ route('login') }}" class="block hover:text-cyan-300">
                                {{ $isAr ? 'تسجيل الدخول' : 'Login' }}
                            </a>

                        @endguest

                    </div>

                </div>

            </div>


            <div
                class="mt-12 flex flex-col gap-4 border-t border-white/5 pt-7 text-xs text-slate-600 sm:flex-row sm:items-center sm:justify-between">

                <p>
                    © {{ date('Y') }} HAG Brokers.
                    {{ $isAr ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}
                </p>

                <p>
                    {{ $isAr
                        ? 'وساطة وتنسيق الشحن البحري — حسب توفر الناقل والشريك.'
                        : 'Sea freight brokerage & coordination — subject to carrier and partner availability.' }}
                </p>

            </div>

        </div>

    </footer>


    <!-- ========================================================= -->
    <!-- JAVASCRIPT -->
    <!-- ========================================================= -->

    <script>
        /*
                                |--------------------------------------------------------------------------
                                | Language Menu
                                |--------------------------------------------------------------------------
                                */

        function toggleLanguageMenu() {

            const dropdown = document.getElementById('language-dropdown');

            if (!dropdown) {
                return;
            }

            dropdown.classList.toggle('hidden');
        }


        document.addEventListener('click', function(event) {

            const menu = document.getElementById('language-menu');
            const dropdown = document.getElementById('language-dropdown');

            if (
                menu &&
                dropdown &&
                !menu.contains(event.target)
            ) {
                dropdown.classList.add('hidden');
            }

        });


        /*
        |--------------------------------------------------------------------------
        | Mobile Menu
        |--------------------------------------------------------------------------
        */

        const mobileButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        const menuOpenIcon = document.getElementById('menu-open-icon');
        const menuCloseIcon = document.getElementById('menu-close-icon');

        if (mobileButton && mobileMenu) {

            mobileButton.addEventListener('click', function() {

                const isHidden = mobileMenu.classList.contains('hidden');

                if (isHidden) {

                    mobileMenu.classList.remove('hidden');

                    menuOpenIcon.classList.add('hidden');
                    menuCloseIcon.classList.remove('hidden');

                } else {

                    mobileMenu.classList.add('hidden');

                    menuOpenIcon.classList.remove('hidden');
                    menuCloseIcon.classList.add('hidden');

                }

            });

        }


        /*
        |--------------------------------------------------------------------------
        | Close Mobile Menu After Navigation
        |--------------------------------------------------------------------------
        */

        document.querySelectorAll('.mobile-link').forEach(function(link) {

            link.addEventListener('click', function() {

                if (!mobileMenu) {
                    return;
                }

                mobileMenu.classList.add('hidden');

                if (menuOpenIcon) {
                    menuOpenIcon.classList.remove('hidden');
                }

                if (menuCloseIcon) {
                    menuCloseIcon.classList.add('hidden');
                }

            });

        });


        /*
        |--------------------------------------------------------------------------
        | Navbar Background On Scroll
        |--------------------------------------------------------------------------
        */

        const navbar = document.getElementById('navbar');

        window.addEventListener('scroll', function() {

            if (!navbar) {
                return;
            }

            if (window.scrollY > 20) {

                navbar.classList.add('shadow-2xl');

            } else {

                navbar.classList.remove('shadow-2xl');

            }

        });
    </script>

</body>

</html>
