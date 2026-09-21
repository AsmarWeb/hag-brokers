@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
@endphp

<!DOCTYPE html>
<html lang="{{ $isAr ? 'ar' : 'en' }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        $siteName = 'HAG Brokers';
        $siteUrl = rtrim(config('app.url', url('/')), '/');
        $canonicalUrl = url()->current();
        $faviconUrl = asset('storage/favicon.png');
        $logoUrl = asset('storage/favicon.png');
        $ogImage = asset('storage/hag-brokers-og.png');

        $seoTitle = $isAr ? 'HAG Brokers | وسيط الشحن البحري في الخليج' : 'HAG Brokers | Gulf Sea Freight Brokerage';

        $seoDescription = $isAr
            ? 'HAG Brokers منصة لوساطة وتنسيق الشحن البحري في الخليج، تربط المستوردين والمصدرين بخيارات الناقلين والشركاء اللوجستيين المتاحة.'
            : 'HAG Brokers is a Gulf-focused sea freight brokerage platform connecting importers and exporters with available carriers and logistics partners.';

        $seoKeywords = $isAr
            ? 'HAG Brokers, الشحن البحري, وساطة الشحن, شحن الخليج, شحن الإمارات, دبي, جبل علي, عروض الشحن, تتبع الشحنات, شركات الشحن'
            : 'HAG Brokers, sea freight, freight brokerage, Gulf shipping, UAE shipping, Dubai, Jebel Ali, freight quotes, shipment tracking, shipping carriers';
    @endphp

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="{{ $seoKeywords }}">
    <meta name="author" content="HAG Brokers">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="googlebot" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="theme-color" content="#050d18">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <link rel="alternate" hreflang="en-AE" href="{{ route('language.switch', 'en') }}">
    <link rel="alternate" hreflang="ar-AE" href="{{ route('language.switch', 'ar') }}">
    <link rel="alternate" hreflang="x-default" href="{{ url('/') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="64x64" href="{{ $faviconUrl }}?v=2">
    <link rel="shortcut icon" href="{{ $faviconUrl }}?v=2">
    <link rel="apple-touch-icon" sizes="64x64" href="{{ $faviconUrl }}?v=2">

    <!-- Open Graph / WhatsApp / Facebook / LinkedIn -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:locale" content="{{ $isAr ? 'ar_AE' : 'en_AE' }}">

    <!-- Social Sharing Image -->
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:secure_url" content="{{ $ogImage }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="HAG Brokers - Gulf Sea Freight Brokerage">

    <!-- Twitter / X -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <meta name="twitter:image:alt" content="HAG Brokers - Gulf Sea Freight Brokerage">

    <!-- Structured Data -->
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $siteName,
        'url' => $siteUrl,
        'logo' => $logoUrl,
        'description' => $seoDescription,
        'areaServed' => [
            '@type' => 'Place',
            'name' => 'Gulf Cooperation Council'
        ],
        'knowsAbout' => [
            'Sea freight',
            'Freight brokerage',
            'Shipment tracking',
            'Gulf ports'
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => $siteUrl,
        'description' => $seoDescription,
        'inLanguage' => $isAr ? 'ar-AE' : 'en-AE'
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Leaflet --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            background: #050d18;
        }

        .glass {
            background: rgba(12, 28, 47, .72);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, .08);
        }

        .glass-light {
            background: rgba(255, 255, 255, .035);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, .07);
        }

        .hero-grid {
            background-image:
                linear-gradient(rgba(255, 255, 255, .035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .035) 1px, transparent 1px);
            background-size: 55px 55px;
        }

        .gradient-text {
            background: linear-gradient(90deg, #67e8f9, #22d3ee, #38bdf8);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .cyan-glow {
            box-shadow: 0 0 40px rgba(34, 211, 238, .18);
        }

        .network-line {
            background: linear-gradient(90deg, transparent, rgba(34, 211, 238, .65), transparent);
        }

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT: Leaflet map sizing
        |--------------------------------------------------------------------------
        */
        #gulf-map {
            width: 100%;
            height: 450px;
            min-height: 450px;
            position: relative;
            z-index: 1;
        }

        @media (min-width: 640px) {
            #gulf-map {
                height: 550px;
                min-height: 550px;
            }
        }

        @media (min-width: 1024px) {
            #gulf-map {
                height: 600px;
                min-height: 600px;
            }
        }

        #gulf-map .leaflet-container {
            width: 100%;
            height: 100%;
            background: #07111f;
            font-family: inherit;
        }

        .leaflet-popup-content-wrapper,
        .leaflet-popup-tip {
            background: #0b1b2e;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        #gulf-map .leaflet-control-zoom {
            border: none !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .30) !important;
        }

        #gulf-map .leaflet-control-zoom a {
            width: 38px;
            height: 38px;
            line-height: 38px;
            border: none !important;
            background: rgba(7, 17, 31, .95) !important;
            color: #fff !important;
        }

        #gulf-map .leaflet-control-zoom a:hover {
            background: #22d3ee !important;
            color: #06101d !important;
        }

        #gulf-map .leaflet-control-attribution {
            font-size: 10px;
            background: rgba(7, 17, 31, .85);
            color: #94a3b8;
            padding: 3px 7px;
        }

        #gulf-map .leaflet-control-attribution a {
            color: #67e8f9;
        }

        .port-marker {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #22d3ee;
            border: 3px solid rgba(255, 255, 255, .90);
            box-shadow: 0 0 20px rgba(34, 211, 238, .9);
        }

        .vessel-marker {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(34, 211, 238, .15);
            border: 1px solid rgba(34, 211, 238, .45);
            box-shadow: 0 0 30px rgba(34, 211, 238, .35);
            font-size: 18px;
        }

        .pulse-dot {
            animation: pulseDot 2s infinite;
        }

        @keyframes pulseDot {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, .5);
            }

            50% {
                box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
            }
        }

        [dir="rtl"] .ltr-number {
            direction: ltr;
            unicode-bidi: isolate;
        }
    </style>
</head>

<body class="min-h-screen overflow-x-hidden bg-[#050d18] text-white">

    <header id="navbar" class="fixed inset-x-0 top-0 z-50 border-b border-white/5 bg-[#07111f]/85 backdrop-blur-xl">

        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 lg:px-8">

            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-400/10 ring-1 ring-cyan-300/20">
                    <span class="text-lg font-black text-cyan-300">HAG</span>
                </div>

                <div>
                    <div class="text-lg font-black tracking-wide">
                        HAG <span class="text-cyan-300">BROKERS</span>
                    </div>

                    <div class="text-[9px] uppercase tracking-[.24em] text-slate-500">
                        {{ $isAr ? 'وساطة الشحن البحري' : 'Sea Freight Brokerage' }}
                    </div>
                </div>
            </a>

            <nav class="hidden items-center gap-7 lg:flex">
                <a href="#network" class="text-sm text-slate-300 transition hover:text-cyan-300">
                    {{ $isAr ? 'الخريطة البحرية' : 'Maritime Map' }}
                </a>
                <a href="#tracking" class="text-sm text-slate-300 transition hover:text-cyan-300">
                    {{ $isAr ? 'تتبع الشحنات' : 'Tracking' }}
                </a>
                <a href="#services" class="text-sm text-slate-300 transition hover:text-cyan-300">
                    {{ $isAr ? 'الخدمات' : 'Services' }}
                </a>
                <a href="#ports" class="text-sm text-slate-300 transition hover:text-cyan-300">
                    {{ $isAr ? 'الموانئ' : 'Ports' }}
                </a>
                <a href="#partners" class="text-sm text-slate-300 transition hover:text-cyan-300">
                    {{ $isAr ? 'الشركاء' : 'Partners' }}
                </a>
            </nav>

            <div class="hidden items-center gap-3 lg:flex">

                <div class="relative" id="language-menu">
                    <button type="button" onclick="toggleLanguageMenu()"
                        class="flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-slate-200 transition hover:bg-white/10">
                        <span>🌐</span>
                        <span>{{ $isAr ? 'العربية' : 'English' }}</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="language-dropdown"
                        class="absolute {{ $isAr ? 'left-0' : 'right-0' }} mt-2 hidden w-40 overflow-hidden rounded-2xl border border-white/10 bg-[#0b1b2e] shadow-2xl">

                        <a href="{{ route('language.switch', 'en') }}"
                            class="flex items-center gap-3 px-4 py-3 text-sm text-slate-200 hover:bg-white/10">
                            🇬🇧 <span>English</span>
                        </a>

                        <a href="{{ route('language.switch', 'ar') }}"
                            class="flex items-center gap-3 px-4 py-3 text-sm text-slate-200 hover:bg-white/10">
                            🇦🇪 <span>العربية</span>
                        </a>
                    </div>
                </div>

                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="rounded-xl border border-white/10 px-4 py-2.5 text-sm font-medium text-white hover:bg-white/5">
                        {{ $isAr ? 'لوحة التحكم' : 'Dashboard' }}
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="rounded-xl border border-white/10 px-4 py-2.5 text-sm font-medium text-white hover:bg-white/5">
                        {{ $isAr ? 'تسجيل الدخول' : 'Login' }}
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-bold text-slate-950 hover:bg-cyan-300">
                            {{ $isAr ? 'إنشاء حساب' : 'Create Account' }}
                        </a>
                    @endif
                @endauth

            </div>

            <button id="mobile-menu-button" type="button"
                class="flex h-11 w-11 items-center justify-center rounded-xl border border-white/10 bg-white/5 lg:hidden"
                aria-label="{{ $isAr ? 'فتح القائمة' : 'Open menu' }}">

                <svg id="menu-open-icon" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>

                <svg id="menu-close-icon" class="hidden h-6 w-6" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div id="mobile-menu" class="hidden border-t border-white/5 bg-[#07111f]">
            <div class="mx-auto max-w-7xl space-y-2 px-5 py-6">

                <a href="#network" class="mobile-link block rounded-xl px-4 py-3 text-slate-200 hover:bg-white/5">
                    {{ $isAr ? 'الخريطة البحرية' : 'Maritime Map' }}
                </a>

                <a href="#tracking" class="mobile-link block rounded-xl px-4 py-3 text-slate-200 hover:bg-white/5">
                    {{ $isAr ? 'تتبع الشحنات' : 'Tracking' }}
                </a>

                <a href="#services" class="mobile-link block rounded-xl px-4 py-3 text-slate-200 hover:bg-white/5">
                    {{ $isAr ? 'الخدمات' : 'Services' }}
                </a>

                <a href="#ports" class="mobile-link block rounded-xl px-4 py-3 text-slate-200 hover:bg-white/5">
                    {{ $isAr ? 'الموانئ' : 'Ports' }}
                </a>

                <a href="#partners" class="mobile-link block rounded-xl px-4 py-3 text-slate-200 hover:bg-white/5">
                    {{ $isAr ? 'الشركاء' : 'Partners' }}
                </a>

                <div class="my-4 h-px bg-white/10"></div>

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

    <main>

        {{-- HERO --}}
        <section class="relative overflow-hidden pt-32 lg:pt-40">

            <div class="hero-grid absolute inset-0 opacity-70"></div>

            <div class="absolute left-1/2 top-20 h-96 w-96 -translate-x-1/2 rounded-full bg-cyan-500/10 blur-3xl">
            </div>

            <div class="relative mx-auto max-w-7xl px-5 pb-20 lg:px-8 lg:pb-28">

                <div class="grid items-center gap-14 lg:grid-cols-[.9fr_1.1fr]">

                    <div class="{{ $isAr ? 'lg:text-right' : 'lg:text-left' }}">

                        <div
                            class="mb-6 inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-cyan-300/5 px-4 py-2 text-xs font-semibold tracking-wider text-cyan-300">
                            <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
                            {{ $isAr ? 'شبكة الشحن البحري الخليجية' : 'GULF MARITIME FREIGHT NETWORK' }}
                        </div>

                        <h1 class="max-w-4xl text-4xl font-black leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                            {{ $isAr ? 'وسيطك للشحن البحري في الخليج' : 'Your Sea Freight Brokerage Partner in the Gulf' }}

                            <span class="mt-3 block gradient-text">
                                {{ $isAr ? 'نربط شحنتك بالخيارات والناقل المناسب' : 'Connecting Your Cargo With the Right Carrier' }}
                            </span>
                        </h1>

                        <p class="mt-7 max-w-2xl text-base leading-8 text-slate-300 sm:text-lg">
                            {{ $isAr
                                ? 'أرسل تفاصيل شحنتك، وننسق لك خيارات الشحن والعروض والحجز والمتابعة مع شركات النقل البحري والشركاء اللوجستيين المتاحين.'
                                : 'Share your shipment details and we coordinate freight options, quotations, booking steps and shipment visibility with available carriers and logistics partners.' }}
                        </p>

                        <div
                            class="mt-9 flex flex-col gap-3 sm:flex-row {{ $isAr ? 'sm:flex-row-reverse lg:justify-end' : '' }}">

                            <a href="#tracking"
                                class="cyan-glow inline-flex items-center justify-center gap-2 rounded-2xl bg-cyan-400 px-7 py-4 font-bold text-slate-950 hover:bg-cyan-300">
                                {{ $isAr ? 'تتبع شحنة' : 'Track Shipment' }}
                                <span>{{ $isAr ? '←' : '→' }}</span>
                            </a>

                            <a href="#contact"
                                class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-7 py-4 font-semibold text-white hover:bg-white/10">
                                {{ $isAr ? 'اطلب عرض شحن' : 'Request Freight Quote' }}
                            </a>
                        </div>

                        <div class="mt-9 grid grid-cols-2 gap-3 sm:grid-cols-4">

                            <div class="glass-light rounded-2xl p-4">
                                <p class="text-2xl font-black ltr-number">138</p>
                                <p class="mt-1 text-[10px] text-slate-500">
                                    {{ $isAr ? 'شحنة نشطة' : 'Active Shipments' }}
                                </p>
                            </div>

                            <div class="glass-light rounded-2xl p-4">
                                <p class="text-2xl font-black ltr-number">24</p>
                                <p class="mt-1 text-[10px] text-slate-500">
                                    {{ $isAr ? 'سفينة' : 'Vessels' }}
                                </p>
                            </div>

                            <div class="glass-light rounded-2xl p-4">
                                <p class="text-2xl font-black ltr-number">18</p>
                                <p class="mt-1 text-[10px] text-slate-500">
                                    {{ $isAr ? 'ميناء' : 'Ports' }}
                                </p>
                            </div>

                            <div class="glass-light rounded-2xl p-4">
                                <p class="text-2xl font-black ltr-number">67</p>
                                <p class="mt-1 text-[10px] text-slate-500">
                                    {{ $isAr ? 'عرض شحن' : 'Freight Offers' }}
                                </p>
                            </div>

                        </div>
                    </div>

                    <div class="relative">

                        <div class="absolute -inset-8 rounded-[3rem] bg-cyan-400/5 blur-3xl"></div>

                        <div class="glass relative rounded-[2rem] p-5 sm:p-7">

                            <div class="flex items-center justify-between">

                                <div>
                                    <p class="text-xs uppercase tracking-widest text-slate-500">
                                        HAG BROKERS
                                    </p>

                                    <h2 class="mt-1 text-lg font-bold">
                                        {{ $isAr ? 'مركز العمليات البحرية' : 'Maritime Operations Center' }}
                                    </h2>
                                </div>

                                <span
                                    class="flex items-center gap-2 rounded-full border border-emerald-300/20 bg-emerald-300/10 px-3 py-1 text-xs text-emerald-300">
                                    <span class="pulse-dot h-2 w-2 rounded-full bg-emerald-400"></span>
                                    {{ $isAr ? 'الشبكة نشطة' : 'Network Active' }}
                                </span>

                            </div>

                            <div class="mt-7 rounded-2xl border border-white/10 bg-black/20 p-4">

                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500">
                                        {{ $isAr ? 'رحلة نموذجية' : 'Sample Route' }}
                                    </span>

                                    <span class="text-cyan-300">
                                        {{ $isAr ? 'الخليج' : 'Gulf' }}
                                    </span>
                                </div>

                                <div class="mt-5 flex items-center justify-between gap-2">

                                    <div>
                                        <div class="text-2xl">🇦🇪</div>
                                        <p class="mt-2 text-sm font-bold">
                                            {{ $isAr ? 'جبل علي' : 'Jebel Ali' }}
                                        </p>
                                        <p class="text-[10px] text-slate-500">
                                            {{ $isAr ? 'الإمارات' : 'UAE' }}
                                        </p>
                                    </div>

                                    <div class="relative flex-1 px-3">
                                        <div class="h-px network-line"></div>
                                        <div
                                            class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 rounded-full bg-slate-900 px-3 py-1 text-lg">
                                            🚢
                                        </div>
                                    </div>

                                    <div class="{{ $isAr ? 'text-left' : 'text-right' }}">
                                        <div class="text-2xl">🇸🇦</div>
                                        <p class="mt-2 text-sm font-bold">
                                            {{ $isAr ? 'جدة' : 'Jeddah' }}
                                        </p>
                                        <p class="text-[10px] text-slate-500">
                                            {{ $isAr ? 'السعودية' : 'Saudi Arabia' }}
                                        </p>
                                    </div>

                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-3">

                                <div class="rounded-2xl border border-white/10 bg-white/[.03] p-4">
                                    <p class="text-[10px] text-slate-500">
                                        {{ $isAr ? 'آخر تحديث' : 'Last Update' }}
                                    </p>
                                    <p class="mt-2 text-sm font-bold">
                                        {{ $isAr ? 'الآن' : 'Just now' }}
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-white/10 bg-white/[.03] p-4">
                                    <p class="text-[10px] text-slate-500">
                                        {{ $isAr ? 'الحالة' : 'Status' }}
                                    </p>
                                    <p class="mt-2 text-sm font-bold text-emerald-300">
                                        {{ $isAr ? 'قيد النقل' : 'In Transit' }}
                                    </p>
                                </div>

                            </div>

                            <div class="mt-4 rounded-2xl bg-cyan-400/10 p-4">

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
                                            {{ $isAr ? 'نربط العميل بخيارات الناقلين والشركاء المتاحة.' : 'Connecting customers with available carrier and partner options.' }}
                                        </p>
                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- NETWORK MAP --}}
        <section id="network" class="scroll-mt-24 border-y border-white/5 bg-[#06101d] py-20 lg:py-28">

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <div class="mb-8 text-center">

                    <div
                        class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/10 text-cyan-300 shadow-sm">
                        ⚓
                    </div>

                    <span class="text-xs font-bold uppercase tracking-[.25em] text-cyan-300">
                        01 — {{ $isAr ? 'الخريطة البحرية' : 'MARITIME MAP' }}
                    </span>

                    <h2 class="mt-4 text-3xl font-black sm:text-4xl">
                        {{ $isAr ? 'شبكة HAG البحرية في الخليج' : 'HAG Gulf Maritime Network' }}
                    </h2>

                    <p class="mx-auto mt-3 max-w-2xl text-slate-400">
                        {{ $isAr ? 'استعرض الموانئ ومسارات الشحن ومواقع السفن ضمن شبكة الخليج.' : 'Explore ports, shipping routes and vessel positions across the Gulf network.' }}
                    </p>

                </div>

                {{-- MAP CARD --}}
                <div
                    class="group relative overflow-hidden rounded-[2rem] border border-white/10 bg-[#07111f] shadow-2xl shadow-black/30">

                    {{-- The map MUST have an explicit height --}}
                    <div id="gulf-map" class="h-[450px] w-full sm:h-[550px] lg:h-[600px]"></div>

                    {{-- Top badge --}}
                    <div
                        class="pointer-events-none absolute left-4 top-4 z-[500] flex items-center gap-2 rounded-full border border-white/10 bg-[#07111f]/90 px-4 py-2 text-sm font-semibold text-white shadow-xl backdrop-blur">

                        <span class="relative flex h-3 w-3">
                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75">
                            </span>

                            <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-400"></span>
                        </span>

                        {{ $isAr ? 'شبكة الخليج نشطة' : 'Gulf Network Active' }}

                    </div>

                    {{-- Bottom information --}}
                    <div
                        class="pointer-events-none absolute bottom-4 left-4 right-4 z-[500] flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                        <div
                            class="rounded-2xl border border-white/10 bg-[#07111f]/90 px-4 py-3 shadow-xl backdrop-blur">

                            <div class="flex items-center gap-3">

                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-300">
                                    🚢
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500">
                                        {{ $isAr ? 'السفن في الشبكة' : 'Vessels in network' }}
                                    </p>

                                    <p class="font-bold text-white">
                                        24
                                        <span class="text-xs font-normal text-emerald-300">
                                            {{ $isAr ? 'للتتبع' : 'Trackable' }}
                                        </span>
                                    </p>
                                </div>

                            </div>

                        </div>

                        <div
                            class="rounded-2xl border border-white/10 bg-[#07111f]/90 px-4 py-3 shadow-xl backdrop-blur">

                            <div class="flex items-center gap-3">

                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-300">
                                    ⚓
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500">
                                        {{ $isAr ? 'موانئ الشبكة' : 'Network ports' }}
                                    </p>

                                    <p class="font-bold text-white">
                                        13
                                    </p>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- Map side information --}}
                <div class="mt-6 grid gap-4 sm:grid-cols-3">

                    <div class="glass rounded-3xl p-5">
                        <p class="text-xs text-slate-500">
                            {{ $isAr ? 'السفن في الشبكة' : 'VESSELS IN NETWORK' }}
                        </p>
                        <p class="mt-1 text-3xl font-black ltr-number">24</p>
                        <p class="mt-2 text-xs text-emerald-300">
                            {{ $isAr ? 'حسب مصادر التتبع المتاحة' : 'Based on available tracking sources' }}
                        </p>
                    </div>

                    <div class="glass rounded-3xl p-5">
                        <p class="text-xs text-slate-500">
                            {{ $isAr ? 'الشحنات النشطة' : 'ACTIVE SHIPMENTS' }}
                        </p>
                        <p class="mt-1 text-3xl font-black ltr-number">138</p>
                        <p class="mt-2 text-xs text-cyan-300">
                            {{ $isAr ? 'قيد المتابعة' : 'Currently monitored' }}
                        </p>
                    </div>

                    <div class="glass rounded-3xl p-5">
                        <p class="text-xs text-slate-500">
                            {{ $isAr ? 'منطقة الخريطة' : 'MAP REGION' }}
                        </p>
                        <p class="mt-1 text-lg font-black">
                            {{ $isAr ? 'الخليج العربي' : 'Arabian Gulf' }}
                        </p>
                        <p class="mt-2 text-xs text-slate-500">
                            {{ $isAr ? 'الإمارات ودول الخليج والممرات البحرية المرتبطة.' : 'UAE, Gulf states and connected maritime corridors.' }}
                        </p>
                    </div>

                </div>

            </div>
        </section>

        {{-- TRACKING --}}
        <section id="tracking" class="scroll-mt-24 py-24 lg:py-32">

            <div class="mx-auto max-w-7xl px-5 lg:px-8">

                <div class="grid gap-12 lg:grid-cols-[.8fr_1.2fr]">

                    <div>

                        <span class="text-xs font-bold uppercase tracking-[.25em] text-cyan-300">
                            02 — {{ $isAr ? 'تتبع الشحنات' : 'SHIPMENT TRACKING' }}
                        </span>

                        <h2 class="mt-4 text-3xl font-black sm:text-4xl">
                            {{ $isAr ? 'تتبع الشحنة أو الحاوية' : 'Track a Shipment or Container' }}
                        </h2>

                        <p class="mt-5 leading-8 text-slate-400">
                            {{ $isAr ? 'أدخل مرجع الشحنة أو الحاوية للوصول إلى آخر معلومات التتبع المتاحة.' : 'Enter a shipment or container reference to access the latest available tracking information.' }}
                        </p>

                        <form
                            action="{{ Route::has('shipments.index') ? route('shipments.index') : url('/shipments') }}"
                            method="GET" class="mt-8 flex flex-col gap-3 sm:flex-row">

                            <input type="text" name="tracking"
                                placeholder="{{ $isAr ? 'رقم الشحنة أو الحاوية' : 'Shipment or container number' }}"
                                class="w-full rounded-2xl border border-white/10 bg-white/5 px-5 py-4 text-white outline-none placeholder:text-slate-600 focus:border-cyan-300/40">

                            <button type="submit"
                                class="rounded-2xl bg-cyan-400 px-7 py-4 font-bold text-slate-950 hover:bg-cyan-300">
                                {{ $isAr ? 'تتبع' : 'Track' }}
                            </button>

                        </form>

                        <p class="mt-4 text-xs text-slate-600">
                            {{ $isAr ? 'التحديثات تعتمد على البيانات المتاحة من مصادر التتبع وشركاء الشحن.' : 'Updates depend on available tracking data from maritime sources and shipping partners.' }}
                        </p>

                    </div>

                    <div class="glass rounded-[2rem] p-6 sm:p-8">

                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-xs text-slate-500">HAG-2026-001</p>
                                <p class="mt-1 font-bold">
                                    {{ $isAr ? 'جبل علي → جدة' : 'Jebel Ali → Jeddah' }}
                                </p>
                            </div>

                            <span class="rounded-full bg-emerald-400/10 px-3 py-1 text-xs text-emerald-300">
                                {{ $isAr ? 'قيد النقل' : 'In Transit' }}
                            </span>

                        </div>

                        <div class="mt-10 grid grid-cols-4 gap-2">

                            @foreach ([[$isAr ? 'تم الطلب' : 'Requested', '✓'], [$isAr ? 'تم الحجز' : 'Booked', '✓'], [$isAr ? 'في البحر' : 'At Sea', '🚢'], [$isAr ? 'الوصول' : 'Arrival', '⚓']] as $trackingStep)
                                <div class="text-center">

                                    <div
                                        class="mx-auto flex h-11 w-11 items-center justify-center rounded-full {{ $loop->index < 3 ? 'bg-cyan-400 text-slate-950' : 'bg-white/10 text-slate-500' }}">
                                        {{ $trackingStep[1] }}
                                    </div>

                                    <p
                                        class="mt-3 text-[10px] font-bold {{ $loop->index < 3 ? 'text-white' : 'text-slate-500' }}">
                                        {{ $trackingStep[0] }}
                                    </p>

                                </div>
                            @endforeach

                        </div>

                        <div class="mt-8 grid gap-3 sm:grid-cols-3">

                            <div class="rounded-2xl border border-white/5 bg-white/[.03] p-4">
                                <p class="text-[10px] text-slate-500">
                                    {{ $isAr ? 'الناقل' : 'Carrier' }}
                                </p>
                                <p class="mt-2 text-sm font-bold">
                                    {{ $isAr ? 'خيار متاح' : 'Available Option' }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/5 bg-white/[.03] p-4">
                                <p class="text-[10px] text-slate-500">
                                    {{ $isAr ? 'الحاويات' : 'Containers' }}
                                </p>
                                <p class="mt-2 text-sm font-bold ltr-number">2 × 40HC</p>
                            </div>

                            <div class="rounded-2xl border border-white/5 bg-white/[.03] p-4">
                                <p class="text-[10px] text-slate-500">
                                    {{ $isAr ? 'آخر تحديث' : 'Last Update' }}
                                </p>
                                <p class="mt-2 text-sm font-bold">
                                    {{ $isAr ? 'الآن' : 'Just now' }}
                                </p>
                            </div>

                        </div>

                    </div>

                </div>
            </div>
        </section>

        {{-- SERVICES --}}
        <section id="services" class="scroll-mt-24 border-y border-white/5 bg-white/[.02] py-24 lg:py-32">

            <div class="mx-auto max-w-7xl px-5 lg:px-8">

                <div class="text-center">

                    <span class="text-xs font-bold uppercase tracking-[.25em] text-cyan-300">
                        03 — {{ $isAr ? 'الخدمات' : 'SERVICES' }}
                    </span>

                    <h2 class="mt-4 text-3xl font-black sm:text-4xl">
                        {{ $isAr ? 'منصة مبنية حول الوساطة البحرية' : 'A Platform Built Around Maritime Brokerage' }}
                    </h2>

                    <p class="mx-auto mt-5 max-w-2xl leading-8 text-slate-400">
                        {{ $isAr ? 'نربط وننسق وننظم خيارات الشحن دون تقديم HAG كناقل بحري.' : 'We connect, coordinate and organize freight options without presenting HAG as a shipping line.' }}
                    </p>

                </div>

                <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

                    @foreach ([
        ['💬', $isAr ? 'وساطة عروض الشحن' : 'Freight Quote Brokerage', $isAr ? 'استقبال طلب الشحن وتنسيق الحصول على عروض مناسبة.' : 'Receive shipment requests and coordinate suitable freight quotations.'],
        ['🚢', $isAr ? 'مطابقة الناقلين' : 'Carrier Matching', $isAr ? 'ربط متطلبات الشحنة بخيارات الناقلين المتاحة.' : 'Match shipment requirements with available carrier options.'],
        ['⚖️', $isAr ? 'مقارنة العروض' : 'Offer Comparison', $isAr ? 'تنظيم المعلومات الأساسية لمراجعة خيارات الشحن.' : 'Organize key information to review available freight options.'],
        ['📦', 'FCL & LCL', $isAr ? 'تنسيق احتياجات الحاويات الكاملة أو الجزئية حسب الخدمة المتاحة.' : 'Coordinate full-container and less-than-container requirements where available.'],
        ['📝', $isAr ? 'تنسيق الحجز' : 'Booking Coordination', $isAr ? 'تنسيق خطوات الحجز والتواصل بين العميل ومقدم الخدمة.' : 'Coordinate booking steps between customers and service providers.'],
        ['📍', $isAr ? 'رؤية الشحنة' : 'Shipment Visibility', $isAr ? 'تجميع تحديثات الشحنة ومراحلها التشغيلية حسب البيانات المتاحة.' : 'Centralize shipment updates and milestones based on available data.'],
    ] as $service)
                        <div class="glass-light rounded-3xl p-7 transition hover:-translate-y-1 hover:bg-white/[.06]">

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

        {{-- PORTS --}}
        <section id="ports" class="scroll-mt-24 py-24 lg:py-32">

            <div class="mx-auto max-w-7xl px-5 lg:px-8">

                <div class="max-w-3xl">

                    <span class="text-xs font-bold uppercase tracking-[.25em] text-cyan-300">
                        04 — {{ $isAr ? 'الموانئ الخليجية' : 'GULF PORTS' }}
                    </span>

                    <h2 class="mt-4 text-3xl font-black sm:text-4xl">
                        {{ $isAr ? 'شبكة الموانئ والمسارات' : 'Ports & Shipping Corridors' }}
                    </h2>

                    <p class="mt-5 leading-8 text-slate-400">
                        {{ $isAr ? 'واجهة موحدة لاستعراض الموانئ المهمة لعمليات الشحن في الخليج.' : 'A unified view of ports relevant to freight operations across the Gulf.' }}
                    </p>

                </div>

                @php
                    $gulfPorts = [
                        [
                            '🇦🇪',
                            $isAr ? 'الإمارات العربية المتحدة' : 'United Arab Emirates',
                            [
                                $isAr ? 'ميناء جبل علي' : 'Jebel Ali Port',
                                $isAr ? 'ميناء خليفة' : 'Khalifa Port',
                                $isAr ? 'ميناء راشد' : 'Port Rashid',
                            ],
                        ],
                        [
                            '🇸🇦',
                            $isAr ? 'المملكة العربية السعودية' : 'Saudi Arabia',
                            [
                                $isAr ? 'ميناء الدمام' : 'Dammam Port',
                                $isAr ? 'ميناء الملك عبدالله' : 'King Abdullah Port',
                                $isAr ? 'ميناء جدة' : 'Jeddah Port',
                            ],
                        ],
                        [
                            '🇴🇲',
                            $isAr ? 'سلطنة عُمان' : 'Oman',
                            [
                                $isAr ? 'ميناء صحار' : 'Sohar Port',
                                $isAr ? 'ميناء صلالة' : 'Salalah Port',
                                $isAr ? 'ميناء الدقم' : 'Duqm Port',
                            ],
                        ],
                        ['🇶🇦', $isAr ? 'دولة قطر' : 'Qatar', [$isAr ? 'ميناء حمد' : 'Hamad Port']],
                        [
                            '🇧🇭',
                            $isAr ? 'مملكة البحرين' : 'Bahrain',
                            [$isAr ? 'ميناء خليفة بن سلمان' : 'Khalifa Bin Salman Port'],
                        ],
                        [
                            '🇰🇼',
                            $isAr ? 'دولة الكويت' : 'Kuwait',
                            [$isAr ? 'ميناء الشويخ' : 'Shuwaikh Port', $isAr ? 'ميناء الشعيبة' : 'Shuaiba Port'],
                        ],
                    ];
                @endphp

                <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

                    @foreach ($gulfPorts as $country)
                        <div class="glass-light rounded-3xl p-6">

                            <div class="flex items-center gap-3">
                                <span class="text-3xl">{{ $country[0] }}</span>

                                <div>
                                    <h3 class="font-bold">{{ $country[1] }}</h3>

                                    <p class="text-xs text-slate-500">
                                        {{ $isAr ? 'موانئ ضمن شبكة الخليج' : 'Ports in Gulf network' }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 space-y-2">

                                @foreach ($country[2] as $port)
                                    <div
                                        class="flex items-center gap-2 rounded-xl bg-white/[.03] px-3 py-2 text-sm text-slate-300">

                                        <span class="h-1.5 w-1.5 rounded-full bg-cyan-300"></span>

                                        {{ $port }}

                                    </div>
                                @endforeach

                            </div>

                        </div>
                    @endforeach

                </div>

            </div>
        </section>

        {{-- HOW IT WORKS --}}
        <section id="how-it-works" class="scroll-mt-24 border-y border-white/5 bg-white/[.02] py-24 lg:py-32">

            <div class="mx-auto max-w-7xl px-5 lg:px-8">

                <div class="text-center">

                    <span class="text-xs font-bold uppercase tracking-[.25em] text-cyan-300">
                        05 — {{ $isAr ? 'كيف نعمل' : 'HOW IT WORKS' }}
                    </span>

                    <h2 class="mt-4 text-3xl font-black sm:text-4xl">
                        {{ $isAr ? 'من طلب الشحن إلى التتبع' : 'From Freight Request to Tracking' }}
                    </h2>

                </div>

                <div class="mt-14 grid gap-5 md:grid-cols-2 lg:grid-cols-5">

                    @foreach ([['📋', $isAr ? 'طلب الشحن' : 'Shipment Request', $isAr ? 'أدخل تفاصيل البضاعة والموانئ والمعدات.' : 'Submit cargo, port and equipment requirements.'], ['🔎', $isAr ? 'البحث عن الخيارات' : 'Source Options', $isAr ? 'ننسق مع الناقلين والشركاء المتاحين.' : 'Source options from available carriers and partners.'], ['⚖️', $isAr ? 'مقارنة العروض' : 'Compare Offers', $isAr ? 'راجع الخيارات قبل اتخاذ قرار الحجز.' : 'Review available options before booking.'], ['📝', $isAr ? 'الحجز' : 'Booking', $isAr ? 'ننسق خطوات الحجز مع الطرف المقدم للخدمة.' : 'Coordinate booking steps with the service provider.'], ['📍', $isAr ? 'التتبع' : 'Tracking', $isAr ? 'تابع الشحنة ومعلومات السفينة حسب البيانات المتاحة.' : 'Follow shipment and vessel information based on available data.']] as $step)
                        <div class="glass-light rounded-3xl p-6">

                            <div class="text-3xl">{{ $step[0] }}</div>

                            <h3 class="mt-5 font-bold">
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

        {{-- PARTNERS --}}
        <section id="partners" class="scroll-mt-24 py-24 lg:py-32">

            <div class="mx-auto max-w-5xl px-5 text-center lg:px-8">

                <span class="text-xs font-bold uppercase tracking-[.25em] text-cyan-300">
                    06 — {{ $isAr ? 'شبكة الشركاء' : 'PARTNER NETWORK' }}
                </span>

                <h2 class="mt-4 text-3xl font-black sm:text-4xl">
                    {{ $isAr ? 'لشركات النقل البحري والشركاء اللوجستيين' : 'For Carriers & Logistics Partners' }}
                </h2>

                <p class="mx-auto mt-5 max-w-2xl leading-8 text-slate-400">
                    {{ $isAr ? 'انضم إلى شبكة HAG لتلقي طلبات الشحن التي تتوافق مع خدماتك ومساراتك المتاحة.' : 'Join the HAG network to receive freight opportunities matching your available services and routes.' }}
                </p>

                <div class="mt-10 grid gap-4 sm:grid-cols-3">

                    <div class="glass rounded-3xl p-6">
                        <div class="text-2xl">🚢</div>
                        <p class="mt-4 font-bold">{{ $isAr ? 'شركات النقل' : 'Carriers' }}</p>
                        <p class="mt-2 text-xs leading-6 text-slate-500">
                            {{ $isAr ? 'استقبل طلبات مناسبة للخدمات المتاحة.' : 'Receive opportunities matching available services.' }}
                        </p>
                    </div>

                    <div class="glass rounded-3xl p-6">
                        <div class="text-2xl">🌐</div>
                        <p class="mt-4 font-bold">{{ $isAr ? 'شركاء لوجستيون' : 'Logistics Partners' }}</p>
                        <p class="mt-2 text-xs leading-6 text-slate-500">
                            {{ $isAr ? 'تواصل مركزي مع شبكة HAG.' : 'Centralized communication through HAG.' }}
                        </p>
                    </div>

                    <div class="glass rounded-3xl p-6">
                        <div class="text-2xl">📊</div>
                        <p class="mt-4 font-bold">{{ $isAr ? 'طلبات منظمة' : 'Structured Requests' }}</p>
                        <p class="mt-2 text-xs leading-6 text-slate-500">
                            {{ $isAr ? 'بيانات أوضح لاتخاذ خطوات تشغيلية مناسبة.' : 'Clearer requests for operational follow-up.' }}
                        </p>
                    </div>

                </div>

                <a href="#contact"
                    class="mt-10 inline-flex rounded-2xl bg-cyan-400 px-7 py-4 font-bold text-slate-950 hover:bg-cyan-300">
                    {{ $isAr ? 'تواصل مع فريق الشراكات' : 'Contact Partnerships' }}
                </a>

            </div>
        </section>

        {{-- CONTACT --}}
        <section id="contact" class="scroll-mt-24 pb-24 lg:pb-32">

            <div class="mx-auto max-w-7xl px-5 lg:px-8">

                <div
                    class="relative overflow-hidden rounded-[2rem] border border-cyan-300/10 bg-gradient-to-br from-cyan-400/10 via-slate-900 to-slate-950 p-8 lg:p-14">

                    <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl"></div>

                    <div class="relative grid items-center gap-10 lg:grid-cols-2">

                        <div>

                            <span class="text-xs font-bold uppercase tracking-[.25em] text-cyan-300">
                                {{ $isAr ? 'ابدأ الآن' : 'GET STARTED' }}
                            </span>

                            <h2 class="mt-4 text-3xl font-black sm:text-4xl">
                                {{ $isAr ? 'لديك شحنة؟ ابدأ من متطلباتها.' : 'Have a shipment? Start with your requirements.' }}
                            </h2>

                            <p class="mt-5 max-w-xl leading-8 text-slate-400">
                                {{ $isAr ? 'أرسل تفاصيل الشحنة لتحويلها إلى طلب منظم يمكن تنسيقه مع خيارات الناقلين والشركاء المتاحة.' : 'Share your shipment details and turn them into a structured request for coordination with available carriers and partners.' }}
                            </p>

                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row lg:justify-end">

                            @auth

                                <a href="{{ url('/dashboard') }}"
                                    class="inline-flex items-center justify-center rounded-2xl bg-cyan-400 px-7 py-4 font-bold text-slate-950 hover:bg-cyan-300">
                                    {{ $isAr ? 'فتح لوحة التحكم' : 'Open Dashboard' }}
                                </a>
                            @else
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}"
                                        class="inline-flex items-center justify-center rounded-2xl bg-cyan-400 px-7 py-4 font-bold text-slate-950 hover:bg-cyan-300">
                                        {{ $isAr ? 'إنشاء حساب والبدء' : 'Create Account & Start' }}
                                    </a>
                                @endif

                                <a href="{{ route('login') }}"
                                    class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-7 py-4 font-semibold text-white hover:bg-white/10">
                                    {{ $isAr ? 'تسجيل الدخول' : 'Login' }}
                                </a>

                            @endauth

                        </div>

                    </div>
                </div>
            </div>
        </section>

    </main>

    <footer class="border-t border-white/5 bg-[#040a12]">

        <div class="mx-auto max-w-7xl px-5 py-12 lg:px-8">

            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">

                <div>

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-300">
                            HAG
                        </div>

                        <div>
                            <p class="font-black">
                                HAG <span class="text-cyan-300">BROKERS</span>
                            </p>

                            <p class="text-[9px] uppercase tracking-widest text-slate-600">
                                {{ $isAr ? 'وساطة الشحن البحري' : 'Sea Freight Brokerage' }}
                            </p>
                        </div>

                    </div>

                    <p class="mt-5 text-sm leading-7 text-slate-500">
                        {{ $isAr ? 'وساطة وتنسيق للشحن البحري تربط العملاء بخيارات الناقلين والشركاء اللوجستيين المتاحة.' : 'Sea freight brokerage and coordination connecting customers with available carrier and logistics partner options.' }}
                    </p>

                </div>

                <div>

                    <h3 class="font-bold">
                        {{ $isAr ? 'المنصة' : 'Platform' }}
                    </h3>

                    <div class="mt-5 space-y-3 text-sm text-slate-500">

                        <a href="#network" class="block hover:text-cyan-300">
                            {{ $isAr ? 'الخريطة البحرية' : 'Maritime Map' }}
                        </a>

                        <a href="#tracking" class="block hover:text-cyan-300">
                            {{ $isAr ? 'تتبع الشحنات' : 'Tracking' }}
                        </a>

                        <a href="#services" class="block hover:text-cyan-300">
                            {{ $isAr ? 'الخدمات' : 'Services' }}
                        </a>

                    </div>

                </div>

                <div>

                    <h3 class="font-bold">
                        {{ $isAr ? 'الخليج' : 'Gulf' }}
                    </h3>

                    <div class="mt-5 space-y-3 text-sm text-slate-500">

                        <a href="#ports" class="block hover:text-cyan-300">
                            {{ $isAr ? 'الموانئ الخليجية' : 'Gulf Ports' }}
                        </a>

                        <a href="#network" class="block hover:text-cyan-300">
                            {{ $isAr ? 'شبكة السفن' : 'Vessel Network' }}
                        </a>

                        <a href="#partners" class="block hover:text-cyan-300">
                            {{ $isAr ? 'الشركاء' : 'Partners' }}
                        </a>

                    </div>

                </div>

                <div>

                    <h3 class="font-bold">
                        {{ $isAr ? 'تواصل' : 'Contact' }}
                    </h3>

                    <div class="mt-5 space-y-3 text-sm text-slate-500">

                        <a href="{{ route('quotations.create') }}" class="block hover:text-cyan-300">
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
                    {{ $isAr ? 'وساطة وتنسيق الشحن البحري — حسب توفر الناقل والشريك والبيانات.' : 'Sea freight brokerage & coordination — subject to carrier, partner and data availability.' }}
                </p>

            </div>

        </div>
    </footer>

    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        /*
                                                            |--------------------------------------------------------------------------
                                                            | Language menu
                                                            |--------------------------------------------------------------------------
                                                            */

        function toggleLanguageMenu() {
            const dropdown = document.getElementById('language-dropdown');

            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
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
        | Mobile menu
        |--------------------------------------------------------------------------
        */

        const mobileButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuOpenIcon = document.getElementById('menu-open-icon');
        const menuCloseIcon = document.getElementById('menu-close-icon');

        if (mobileButton && mobileMenu) {

            mobileButton.addEventListener('click', function() {

                const hidden = mobileMenu.classList.contains('hidden');

                mobileMenu.classList.toggle('hidden', !hidden);

                menuOpenIcon?.classList.toggle('hidden', hidden);

                menuCloseIcon?.classList.toggle('hidden', !hidden);

            });

        }

        document.querySelectorAll('.mobile-link').forEach(function(link) {

            link.addEventListener('click', function() {

                mobileMenu?.classList.add('hidden');

                menuOpenIcon?.classList.remove('hidden');

                menuCloseIcon?.classList.add('hidden');

            });

        });

        /*
        |--------------------------------------------------------------------------
        | Navbar shadow
        |--------------------------------------------------------------------------
        */

        const navbar = document.getElementById('navbar');

        window.addEventListener('scroll', function() {

            if (!navbar) {
                return;
            }

            navbar.classList.toggle(
                'shadow-2xl',
                window.scrollY > 20
            );

        });

        /*
        |--------------------------------------------------------------------------
        | HAG Gulf Map
        |--------------------------------------------------------------------------
        */

        (function() {

            function initializeGulfMap() {

                /*
                |--------------------------------------------------------------------------
                | Check Leaflet
                |--------------------------------------------------------------------------
                */

                if (typeof L === 'undefined') {

                    console.error(
                        'Leaflet.js is not loaded.'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Find map element
                |--------------------------------------------------------------------------
                */

                const mapElement =
                    document.getElementById('gulf-map');

                if (!mapElement) {

                    console.error(
                        'Gulf map element not found.'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Prevent duplicate initialization
                |--------------------------------------------------------------------------
                */

                if (mapElement._leaflet_id) {
                    return;
                }

                const isAr = @json($isAr);

                /*
                |--------------------------------------------------------------------------
                | Gulf bounds
                |--------------------------------------------------------------------------
                |
                | The map is restricted to the Gulf region.
                |
                */

                const gulfBounds = L.latLngBounds(
                    [23.0, 47.0],
                    [30.5, 60.5]
                );

                /*
                |--------------------------------------------------------------------------
                | Create map
                |--------------------------------------------------------------------------
                */

                const map = L.map(
                    'gulf-map', {
                        zoomControl: false,

                        scrollWheelZoom: true,

                        dragging: true,

                        doubleClickZoom: true,

                        touchZoom: true,

                        boxZoom: true,

                        keyboard: true,

                        minZoom: 5,

                        maxZoom: 10,

                        maxBounds: gulfBounds,

                        maxBoundsViscosity: 1.0
                    }
                );

                /*
                |--------------------------------------------------------------------------
                | Zoom control
                |--------------------------------------------------------------------------
                */

                L.control.zoom({
                    position: 'bottomright'
                }).addTo(map);

                /*
                |--------------------------------------------------------------------------
                | OpenStreetMap
                |--------------------------------------------------------------------------
                */

                L.tileLayer(
                    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        minZoom: 4,

                        maxZoom: 19,

                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors'
                    }
                ).addTo(map);

                /*
                |--------------------------------------------------------------------------
                | Initial Gulf view
                |--------------------------------------------------------------------------
                */

                map.fitBounds(
                    gulfBounds, {
                        padding: [20, 20]
                    }
                );

                /*
                |--------------------------------------------------------------------------
                | Port data
                |--------------------------------------------------------------------------
                */

                const ports = [

                    {
                        name: isAr ?
                            'ميناء جبل علي' : 'Jebel Ali Port',
                        country: '🇦🇪',
                        lat: 24.9857,
                        lng: 55.0273
                    },

                    {
                        name: isAr ?
                            'ميناء خليفة' : 'Khalifa Port',
                        country: '🇦🇪',
                        lat: 24.7991,
                        lng: 54.6703
                    },

                    {
                        name: isAr ?
                            'ميناء راشد' : 'Port Rashid',
                        country: '🇦🇪',
                        lat: 25.2700,
                        lng: 55.2900
                    },

                    {
                        name: isAr ?
                            'ميناء الدمام' : 'Dammam Port',
                        country: '🇸🇦',
                        lat: 26.4300,
                        lng: 50.1000
                    },

                    {
                        name: isAr ?
                            'ميناء الملك عبدالله' : 'King Abdullah Port',
                        country: '🇸🇦',
                        lat: 22.7500,
                        lng: 39.1000
                    },

                    {
                        name: isAr ?
                            'ميناء صحار' : 'Sohar Port',
                        country: '🇴🇲',
                        lat: 24.5000,
                        lng: 56.6200
                    },

                    {
                        name: isAr ?
                            'ميناء حمد' : 'Hamad Port',
                        country: '🇶🇦',
                        lat: 24.8500,
                        lng: 51.5500
                    },

                    {
                        name: isAr ?
                            'ميناء خليفة بن سلمان' : 'Khalifa Bin Salman Port',
                        country: '🇧🇭',
                        lat: 26.0500,
                        lng: 50.6000
                    },

                    {
                        name: isAr ?
                            'ميناء الشويخ' : 'Shuwaikh Port',
                        country: '🇰🇼',
                        lat: 29.3500,
                        lng: 47.9000
                    },

                    {
                        name: isAr ?
                            'ميناء الشعيبة' : 'Shuaiba Port',
                        country: '🇰🇼',
                        lat: 28.9800,
                        lng: 48.1700
                    },

                    {
                        name: isAr ?
                            'ميناء خصب' : 'Khasab Port',
                        country: '🇴🇲',
                        lat: 26.1800,
                        lng: 56.2500
                    }

                ];

                /*
                |--------------------------------------------------------------------------
                | Port icon
                |--------------------------------------------------------------------------
                */

                const portIcon = L.divIcon({

                    className: '',

                    html: '<div class="port-marker"></div>',

                    iconSize: [14, 14],

                    iconAnchor: [7, 7]

                });

                /*
                |--------------------------------------------------------------------------
                | Add ports
                |--------------------------------------------------------------------------
                */

                ports.forEach(function(port) {

                    L.marker(
                            [port.lat, port.lng], {
                                icon: portIcon
                            }
                        )
                        .addTo(map)
                        .bindPopup(`
                        <div style="
                            min-width:210px;
                            font-family:Arial,Helvetica,sans-serif;
                        ">

                            <div style="
                                font-size:16px;
                                font-weight:800;
                                color:#ffffff;
                            ">
                                ${port.country} ${port.name}
                            </div>

                            <div style="
                                margin-top:7px;
                                color:#94a3b8;
                                font-size:12px;
                            ">
                                ${
                                    isAr
                                    ? 'ميناء ضمن شبكة HAG البحرية'
                                    : 'Port within the HAG maritime network'
                                }
                            </div>

                        </div>
                    `);

                });

                /*
                |--------------------------------------------------------------------------
                | Demo vessels
                |--------------------------------------------------------------------------
                */

                const vessels = [

                    {
                        name: 'HAG DEMO 001',
                        lat: 24.45,
                        lng: 54.85,
                        destination: isAr ?
                            'جبل علي' : 'Jebel Ali',
                        speed: '14 kn'
                    },

                    {
                        name: 'HAG DEMO 002',
                        lat: 25.05,
                        lng: 52.15,
                        destination: isAr ?
                            'ميناء حمد' : 'Hamad Port',
                        speed: '12 kn'
                    },

                    {
                        name: 'HAG DEMO 003',
                        lat: 25.70,
                        lng: 49.80,
                        destination: isAr ?
                            'الدمام' : 'Dammam',
                        speed: '11 kn'
                    },

                    {
                        name: 'HAG DEMO 004',
                        lat: 24.10,
                        lng: 56.00,
                        destination: isAr ?
                            'صحار' : 'Sohar',
                        speed: '15 kn'
                    }

                ];

                /*
                |--------------------------------------------------------------------------
                | Vessel icon
                |--------------------------------------------------------------------------
                */

                const vesselIcon = L.divIcon({

                    className: '',

                    html: '<div class="vessel-marker">🚢</div>',

                    iconSize: [38, 38],

                    iconAnchor: [19, 19],

                    popupAnchor: [0, -19]

                });

                /*
                |--------------------------------------------------------------------------
                | Add vessels
                |--------------------------------------------------------------------------
                */

                vessels.forEach(function(vessel) {

                    L.marker(
                            [vessel.lat, vessel.lng], {
                                icon: vesselIcon
                            }
                        )
                        .addTo(map)
                        .bindPopup(`

                        <div style="
                            min-width:220px;
                            font-family:Arial,Helvetica,sans-serif;
                        ">

                            <div style="
                                font-size:16px;
                                font-weight:800;
                                color:#ffffff;
                            ">
                                🚢 ${vessel.name}
                            </div>

                            <div style="
                                margin-top:8px;
                                color:#22d3ee;
                                font-size:13px;
                            ">
                                ${
                                    isAr
                                    ? 'سفينة قيد الحركة'
                                    : 'Vessel in transit'
                                }
                            </div>

                            <div style="
                                margin-top:6px;
                                color:#94a3b8;
                                font-size:12px;
                            ">
                                ${
                                    isAr
                                    ? 'الوجهة:'
                                    : 'Destination:'
                                }
                                ${vessel.destination}
                            </div>

                            <div style="
                                margin-top:5px;
                                color:#94a3b8;
                                font-size:12px;
                            ">
                                ${
                                    isAr
                                    ? 'السرعة:'
                                    : 'Speed:'
                                }
                                ${vessel.speed}
                            </div>

                            <div style="
                                margin-top:9px;
                                color:#64748b;
                                font-size:10px;
                            ">
                                ${
                                    isAr
                                    ? 'بيانات تجريبية للواجهة'
                                    : 'Demo data for interface'
                                }
                            </div>

                        </div>

                    `);

                });

                /*
                |--------------------------------------------------------------------------
                | Shipping routes
                |--------------------------------------------------------------------------
                */

                const routes = [

                    [
                        [24.9857, 55.0273],
                        [24.8500, 51.5500]
                    ],

                    [
                        [24.9857, 55.0273],
                        [26.4300, 50.1000]
                    ],

                    [
                        [24.5000, 56.6200],
                        [24.8500, 51.5500]
                    ],

                    [
                        [24.9857, 55.0273],
                        [25.70, 49.80]
                    ]

                ];

                routes.forEach(function(route) {

                    L.polyline(
                        route, {
                            color: '#22d3ee',

                            weight: 2,

                            opacity: .55,

                            dashArray: '8 10'
                        }
                    ).addTo(map);

                });

                /*
                |--------------------------------------------------------------------------
                | Final rendering fix
                |--------------------------------------------------------------------------
                */

                function refreshMap() {

                    map.invalidateSize({
                        pan: false
                    });

                }

                setTimeout(
                    refreshMap,
                    100
                );

                setTimeout(
                    refreshMap,
                    300
                );

                setTimeout(
                    refreshMap,
                    800
                );

                window.addEventListener(
                    'resize',
                    refreshMap
                );

            }

            /*
            |--------------------------------------------------------------------------
            | Start after DOM is ready
            |--------------------------------------------------------------------------
            */

            if (
                document.readyState === 'loading'
            ) {

                document.addEventListener(
                    'DOMContentLoaded',
                    initializeGulfMap
                );

            } else {

                initializeGulfMap();

            }

        })();
    </script>

</body>

</html>
