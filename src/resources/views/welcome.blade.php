@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
@endphp

<!DOCTYPE html>
<html lang="{{ $isAr ? 'ar' : 'en' }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php $faviconUrl = asset('storage/favicon.png'); @endphp

    @include('seo.head')
    <meta name="theme-color" content="#050d18">
    <meta name="color-scheme" content="dark light">
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">

    @include('components.public-theme')

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
            background: var(--hag-public-surface, rgba(12, 28, 47, .72));
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid var(--hag-public-border, rgba(255, 255, 255, .08));
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

        /* HAG light theme: preserve existing Tailwind dark styling without removing sections. */
        html.theme-light {
            color-scheme: light;
        }

        html.theme-light body {
            background: #f3f7fb !important;
            color: #10243a;
        }

        html.theme-light #navbar,
        html.theme-light #mobile-menu {
            background: rgba(255, 255, 255, .95) !important;
            border-color: #dbe5ef !important;
        }

        html.theme-light .glass {
            background: var(--hag-public-surface);
            border-color: var(--hag-public-border);
            box-shadow: 0 12px 36px rgba(15, 35, 60, .07);
        }

        html.theme-light .glass-light {
            background: rgba(255, 255, 255, .78);
            border-color: #dbe5ef;
        }

        html.theme-light .gradient-text {
            background-image: linear-gradient(90deg, #0369a1, #0891b2);
        }

        html.theme-light .hero-grid {
            background-image: linear-gradient(rgba(10, 70, 100, .05) 1px, transparent 1px), linear-gradient(90deg, rgba(10, 70, 100, .05) 1px, transparent 1px);
        }

        html.theme-light .bg-\[\#06101d\],
        html.theme-light .bg-\[\#050d18\],
        html.theme-light .bg-\[\#040a12\] {
            background-color: #edf4fa !important;
        }

        html.theme-light .bg-\[\#07111f\] {
            background-color: #fff !important;
        }

        html.theme-light .bg-\[\#0b1b2e\] {
            background-color: #fff !important;
        }

        html.theme-light .text-white,
        html.theme-light .text-slate-200,
        html.theme-light .text-slate-300 {
            color: #18334b !important;
        }

        html.theme-light .bg-cyan-400,
        html.theme-light .bg-cyan-300 {
            color: #082334 !important;
        }

        html.theme-light .text-slate-400,
        html.theme-light .text-slate-500,
        html.theme-light .text-slate-600 {
            color: #526a80 !important;
        }

        html.theme-light .text-cyan-300,
        html.theme-light .text-cyan-200 {
            color: #087f9b !important;
        }

        html.theme-light .bg-white\/5,
        html.theme-light .bg-white\/10,
        html.theme-light .bg-white\/\[\.03\],
        html.theme-light .bg-white\/\[0\.035\],
        html.theme-light .bg-white\/\[0\.025\] {
            background-color: rgba(255, 255, 255, .83) !important;
        }

        html.theme-light .border-white\/10,
        html.theme-light .border-white\/5 {
            border-color: #d5e1eb !important;
        }

        html.theme-light .opportunity-card {
            background: #fff !important;
            border-color: #dce7f0 !important;
        }

        html.theme-light .opportunity-card .bg-slate-950\/70,
        html.theme-light .opportunity-card .bg-slate-950\/75 {
            background: rgba(7, 20, 36, .8) !important;
        }

        html.theme-light .opportunity-card .bg-slate-950\/70 *,
        html.theme-light .opportunity-card .bg-slate-950\/75 * {
            color: #fff !important;
        }

        html.theme-light .opportunity-card .bg-gradient-to-t {
            pointer-events: none;
        }

        html.theme-light input,
        html.theme-light select,
        html.theme-light textarea {
            color: #18334b !important;
            background-color: #fff !important;
            border-color: #cbdce9 !important;
        }

        html.theme-light input::placeholder,
        html.theme-light textarea::placeholder {
            color: #64748b !important;
        }

        html.theme-light .leaflet-popup-content-wrapper,
        html.theme-light .leaflet-popup-tip {
            background: #fff;
            color: #10243a;
        }

        html.theme-light #gulf-map .leaflet-control-zoom a {
            background: #fff !important;
            color: #12334b !important;
        }

        html.theme-light #gulf-map .leaflet-control-attribution {
            background: rgba(255, 255, 255, .88);
            color: #425466;
        }

        html.theme-light #language-dropdown {
            background: #fff !important;
        }

        html.theme-light #language-dropdown a {
            color: #18334b !important;
        }

        html.theme-light .hag-theme-toggle {
            background: #fff;
            color: #17334b;
            border-color: #cbdce9;
        }

        html:not(.theme-light) .hag-theme-toggle {
            background: rgba(255, 255, 255, .05);
            color: #e2e8f0;
            border-color: rgba(255, 255, 255, .12);
        }

        .hag-theme-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            min-height: 42px;
            padding: .6rem .8rem;
            border: 1px solid;
            border-radius: .8rem;
            cursor: pointer;
        }

        .hag-theme-toggle:focus-visible {
            outline: 3px solid #06b6d4;
            outline-offset: 3px;
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            .pulse-dot {
                animation: none;
            }
        }
    </style>
</head>

<body class="min-h-screen overflow-x-hidden bg-[#050d18] text-white">

    @include('components.public.header')

    <main>

        {{-- HERO --}}
        <section id="hero" class="relative overflow-hidden pt-32 lg:pt-40">

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
                            class="mt-9 flex flex-col gap-3 sm:flex-row sm:flex-wrap">

                            <a href="#services"
                                class="cyan-glow inline-flex items-center justify-center gap-2 rounded-2xl bg-cyan-400 px-7 py-4 font-bold text-slate-950 transition hover:bg-cyan-300">
                                {{ $isAr ? 'استكشف الخدمات البحرية' : 'Explore Maritime Services' }}
                                <span>{{ $isAr ? '←' : '→' }}</span>
                            </a>

                            <a href="#quote-request"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-cyan-300/30 px-6 py-4 font-bold text-cyan-300 hover:bg-cyan-300/10">
                                {{ $isAr ? 'اطلب عرض شحن' : 'Request Freight Quote' }}
                            </a>

                            <a href="#partners"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-cyan-400/30 bg-white/5 px-7 py-4 font-semibold text-white transition hover:border-cyan-400/60 hover:bg-cyan-400/10">
                                {{ $isAr ? 'تعاون مع HAG' : 'Partner With HAG' }}
                                <span class="text-cyan-300">{{ $isAr ? '←' : '→' }}</span>
                            </a>

                        </div>


                        <p class="mt-7 text-xs text-slate-400">
                            {{ $isAr ? 'المؤشرات التشغيلية ستظهر بعد ربط البيانات الفعلية المعتمدة.' : 'Operational indicators will appear once verified live data is connected.' }}
                        </p>
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">

                            <div class="glass-light rounded-2xl p-4">
                                <p class="text-2xl font-black ltr-number">—</p>
                                <p class="mt-1 text-[10px] text-slate-500">
                                    {{ $isAr ? 'شحنة نشطة' : 'Active Shipments' }}
                                </p>
                            </div>

                            <div class="glass-light rounded-2xl p-4">
                                <p class="text-2xl font-black ltr-number">—</p>
                                <p class="mt-1 text-[10px] text-slate-500">
                                    {{ $isAr ? 'سفينة' : 'Vessels' }}
                                </p>
                            </div>

                            <div class="glass-light rounded-2xl p-4">
                                <p class="text-2xl font-black ltr-number">—</p>
                                <p class="mt-1 text-[10px] text-slate-500">
                                    {{ $isAr ? 'ميناء' : 'Ports' }}
                                </p>
                            </div>

                            <div class="glass-light rounded-2xl p-4">
                                <p class="text-2xl font-black ltr-number">—</p>
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
                                    {{ $isAr ? 'عرض توضيحي' : 'Illustrative Preview' }}
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
                                        {{ $isAr ? 'مثال' : 'Example' }}
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-white/10 bg-white/[.03] p-4">
                                    <p class="text-[10px] text-slate-500">
                                        {{ $isAr ? 'الحالة' : 'Status' }}
                                    </p>
                                    <p class="mt-2 text-sm font-bold text-emerald-300">
                                        {{ $isAr ? 'حالة توضيحية' : 'Sample Status' }}
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
        @include('maritime-board.home-link')

        @include('public-experience.map')

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

        {{-- QUOTE REQUEST & BUSINESS OPPORTUNITY CONVERSION --}}
        <section id="quote-request" class="scroll-mt-24 py-20 lg:py-24">
            <div class="mx-auto max-w-7xl px-5 lg:px-8">
                <div class="mx-auto max-w-4xl">
                    <div class="glass rounded-[2rem] p-7 sm:p-10">
                        <p class="text-xs font-bold uppercase tracking-widest text-cyan-300">{{ $isAr ? 'وساطة الشحن البحري' : 'SEA FREIGHT BROKERAGE' }}</p>
                        <h2 class="mt-4 text-2xl font-black sm:text-3xl">{{ $isAr ? 'اطلب عرض سعر لشحنتك' : 'Request a Freight Quotation' }}</h2>
                        <p class="mt-4 leading-7 text-slate-400">{{ $isAr ? 'شاركنا ميناء الانطلاق والوصول ونوع البضاعة والحاوية. سنساعدك في تنسيق خيارات الناقلين المتاحة.' : 'Share your origin, destination, cargo and container requirements. We can coordinate available carrier options.' }}</p>
                        <a href="{{ route('quotations.create') }}"
                            class="mt-7 inline-flex rounded-xl bg-cyan-400 px-6 py-3 font-bold text-slate-950 hover:bg-cyan-300">
                            {{ $isAr ? 'ابدأ طلب الشحن' : 'Start Freight Request' }}
                        </a>
                        <p class="mt-3 text-xs text-slate-500">{{ $isAr ? 'سجّل الدخول إلى حساب شركتك لإرسال الطلب ومتابعته.' : 'Sign in to your company account to submit and follow your request.' }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- FREQUENTLY ASKED QUESTIONS --}}
        <section id="faq" class="scroll-mt-24 py-16">
            <div class="mx-auto max-w-4xl px-5 lg:px-8">
                <h2 class="mb-7 text-3xl font-black">{{ $isAr ? 'الأسئلة الشائعة' : 'Frequently Asked Questions' }}
                </h2>
                <div class="space-y-3">
                    <details class="glass rounded-2xl p-5">
                        <summary class="cursor-pointer font-bold">
                            {{ $isAr ? 'هل HAG Brokers شركة ملاحة بحرية؟' : 'Is HAG Brokers a shipping line?' }}
                        </summary>
                        <p class="mt-3 leading-7 text-slate-400">
                            {{ $isAr ? 'لا. HAG Brokers وسيط تجاري ينسق خيارات الشحن مع الناقلين والشركاء المتاحين.' : 'No. HAG Brokers is a commercial freight broker coordinating options with available carriers and partners.' }}
                        </p>
                    </details>
                    <details class="glass rounded-2xl p-5">
                        <summary class="cursor-pointer font-bold">
                            {{ $isAr ? 'كيف أطلب عرض سعر؟' : 'How do I request a quote?' }}</summary>
                        <p class="mt-3 leading-7 text-slate-400">
                            {{ $isAr ? 'ابدأ من قسم طلب عرض الشحن وشارك تفاصيل البضاعة والمسار المطلوب.' : 'Use the freight request section and share your cargo and route requirements.' }}
                        </p>
                    </details>
                    <details class="glass rounded-2xl p-5">
                        <summary class="cursor-pointer font-bold">
                            {{ $isAr ? 'هل تتوفر بيانات تتبع مباشرة؟' : 'Is real-time tracking available?' }}
                        </summary>
                        <p class="mt-3 leading-7 text-slate-400">
                            {{ $isAr ? 'تعتمد التحديثات على البيانات التي توفرها أنظمة المنصة والشركاء، ولا تُعرض الخريطة التوضيحية كتتبع مباشر.' : 'Tracking updates depend on available platform and partner data. The illustrative map is not live vessel tracking.' }}
                        </p>
                    </details>
                </div>
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

    @include('public-experience.download-card')

    @include('components.public.footer')

{{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


    @include('public-experience.map-script')

</body>

</html>
