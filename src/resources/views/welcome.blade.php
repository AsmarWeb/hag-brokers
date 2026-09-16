@php
    $isAr = app()->getLocale() === 'ar';
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#07111f">

    <title>{{ $isAr ? 'حلول الشحن البحري | HAG Brokers' : 'Sea Freight Solutions | HAG Brokers' }}</title>
    <meta name="description"
        content="{{ $isAr ? 'حلول شحن بحري احترافية لإدارة الشحنات والتنسيق اللوجستي عبر الموانئ والوجهات.' : 'Professional sea freight solutions for shipment coordination, cargo visibility and logistics management across ports and destinations.' }}">

    <link rel="icon" type="image/png" href="{{ asset('storage/favicon.png') }}">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .hero-grid {
            background-image: linear-gradient(rgba(255, 255, 255, .035) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, .035) 1px, transparent 1px);
            background-size: 56px 56px;
        }

        .hero-glow {
            box-shadow: 0 0 100px rgba(34, 211, 238, .12);
        }

        .glass {
            background: rgba(255, 255, 255, .045);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .nav-link {
            position: relative;
        }

        .nav-link::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -8px;
            margin: auto;
            width: 0;
            height: 2px;
            border-radius: 999px;
            background: #22d3ee;
            transition: .25s;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .mobile-menu {
            transform: translateX(100%);
            transition: transform .3s ease;
        }

        [dir="ltr"] .mobile-menu {
            transform: translateX(100%);
        }

        [dir="rtl"] .mobile-menu {
            transform: translateX(-100%);
        }

        .mobile-menu.open {
            transform: translateX(0);
        }

        .fade-up {
            animation: fadeUp .7s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .ship-line {
            animation: drift 7s ease-in-out infinite;
        }

        @keyframes drift {

            0%,
            100% {
                transform: translateX(0);
            }

            50% {
                transform: translateX(10px);
            }
        }
    </style>
</head>

<body class="min-h-screen bg-[#06101d] text-white antialiased selection:bg-cyan-400 selection:text-slate-950">

    <header id="navbar" class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-[#06101d]/80 backdrop-blur-xl">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 sm:px-8 lg:px-10">
            <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="HAG Brokers">
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-400 text-lg font-black text-slate-950 shadow-lg shadow-cyan-400/20">
                    HAG</div>
                <div class="leading-none">
                    <div class="text-[15px] font-bold tracking-wide">HAG Brokers</div>
                    <div class="mt-1 text-[9px] font-semibold uppercase tracking-[.2em] text-slate-400">
                        {{ $isAr ? 'حلول الشحن البحري' : 'Sea Freight Solutions' }}</div>
                </div>
            </a>

            <nav class="hidden items-center gap-8 md:flex" aria-label="Primary navigation">
                <a href="#services"
                    class="nav-link text-sm font-medium text-slate-300 transition hover:text-white">{{ $isAr ? 'الخدمات' : 'Services' }}</a>
                <a href="#solutions"
                    class="nav-link text-sm font-medium text-slate-300 transition hover:text-white">{{ $isAr ? 'الحلول' : 'Solutions' }}</a>
                <a href="#about"
                    class="nav-link text-sm font-medium text-slate-300 transition hover:text-white">{{ $isAr ? 'عن HAG' : 'About' }}</a>
                <a href="#contact"
                    class="nav-link text-sm font-medium text-slate-300 transition hover:text-white">{{ $isAr ? 'تواصل معنا' : 'Contact' }}</a>
            </nav>

            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="hidden rounded-xl bg-cyan-400 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-cyan-300 sm:inline-flex">
                        {{ $isAr ? 'لوحة التحكم' : 'Dashboard' }}
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="hidden rounded-xl border border-white/10 px-4 py-2.5 text-sm font-semibold text-slate-200 transition hover:border-cyan-400/50 hover:text-white sm:inline-flex">
                        {{ $isAr ? 'تسجيل الدخول' : 'Log in' }}
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="hidden rounded-xl bg-cyan-400 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-cyan-300 sm:inline-flex">
                            {{ $isAr ? 'إنشاء حساب' : 'Create account' }}
                        </a>
                    @endif
                @endauth

                <div class="relative hidden sm:block">
                    <button type="button" onclick="document.getElementById('language-menu').classList.toggle('hidden')"
                        class="flex h-11 items-center gap-2 rounded-xl border border-white/10 px-3 text-sm font-semibold text-slate-200 hover:border-cyan-400/40"
                        aria-label="Language">
                        <span>🌐</span><span>{{ $isAr ? 'العربية' : 'English' }}</span>
                    </button>
                    <div id="language-menu"
                        class="absolute end-0 top-full mt-2 hidden w-40 rounded-xl border border-white/10 bg-[#0b1727] p-1.5 shadow-2xl">
                        <a href="{{ route('language.switch', 'en') }}"
                            class="block rounded-lg px-3 py-2.5 text-sm text-slate-200 hover:bg-white/5">🇬🇧
                            English</a>
                        <a href="{{ route('language.switch', 'ar') }}"
                            class="block rounded-lg px-3 py-2.5 text-sm text-slate-200 hover:bg-white/5">🇦🇪
                            العربية</a>
                    </div>
                </div>

                <button id="mobileToggle" type="button"
                    class="rounded-xl border border-white/10 p-2.5 text-slate-200 md:hidden"
                    aria-label="Open navigation" aria-expanded="false">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <div id="mobileMenu"
        class="mobile-menu fixed inset-y-0 end-0 z-[60] w-[min(86vw,360px)] border-s border-white/10 bg-[#06101d]/98 p-6 shadow-2xl md:hidden"
        aria-hidden="true">
        <div class="flex items-center justify-between border-b border-white/10 pb-5">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-400 text-sm font-black text-slate-950">
                    HAG</div><span class="font-bold">HAG Brokers</span>
            </div>
            <button id="mobileClose" type="button" class="rounded-lg p-2 text-slate-400 hover:text-white"
                aria-label="Close navigation"><svg class="h-5 w-5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg></button>
        </div>
        <nav class="mt-7 grid gap-2" aria-label="Mobile navigation">
            <a href="#services"
                class="rounded-xl px-4 py-3.5 font-medium text-slate-300 hover:bg-white/5 hover:text-white">{{ $isAr ? 'الخدمات' : 'Services' }}</a>
            <a href="#solutions"
                class="rounded-xl px-4 py-3.5 font-medium text-slate-300 hover:bg-white/5 hover:text-white">{{ $isAr ? 'الحلول' : 'Solutions' }}</a>
            <a href="#about"
                class="rounded-xl px-4 py-3.5 font-medium text-slate-300 hover:bg-white/5 hover:text-white">{{ $isAr ? 'عن HAG' : 'About' }}</a>
            <a href="#contact"
                class="rounded-xl px-4 py-3.5 font-medium text-slate-300 hover:bg-white/5 hover:text-white">{{ $isAr ? 'تواصل معنا' : 'Contact' }}</a>
        </nav>
        <div class="mt-7 grid gap-3">
            <a href="{{ route('language.switch', 'en') }}"
                class="rounded-xl border border-white/10 px-4 py-3 text-center text-sm">🇬🇧 English</a>
            <a href="{{ route('language.switch', 'ar') }}"
                class="rounded-xl border border-white/10 px-4 py-3 text-center text-sm">🇦🇪 العربية</a>
            @auth
                <a href="{{ url('/dashboard') }}"
                    class="rounded-xl bg-cyan-400 px-4 py-3 text-center text-sm font-bold text-slate-950">{{ $isAr ? 'لوحة التحكم' : 'Dashboard' }}</a>
            @else
                <a href="{{ route('login') }}"
                    class="rounded-xl border border-white/10 px-4 py-3 text-center text-sm font-semibold">{{ $isAr ? 'تسجيل الدخول' : 'Log in' }}</a>
            @endauth
        </div>
    </div>
    <div id="mobileOverlay" class="fixed inset-0 z-[55] hidden bg-black/60 md:hidden"></div>

    <main>
        <section class="hero-grid relative overflow-hidden pt-32 sm:pt-36" aria-labelledby="hero-title">
            <div class="absolute -start-32 top-20 h-96 w-96 rounded-full bg-cyan-500/10 blur-3xl"></div>
            <div class="absolute -end-32 top-40 h-96 w-96 rounded-full bg-blue-500/10 blur-3xl"></div>

            <div class="mx-auto max-w-7xl px-5 pb-20 sm:px-8 sm:pb-28 lg:px-10">
                <div class="grid items-center gap-14 lg:grid-cols-[1.08fr_.92fr] lg:gap-20">
                    <div class="fade-up">
                        <div
                            class="mb-6 inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/5 px-3.5 py-2 text-xs font-bold uppercase tracking-[.16em] text-cyan-300">
                            <span class="relative flex h-2 w-2"><span
                                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-60"></span><span
                                    class="relative inline-flex h-2 w-2 rounded-full bg-cyan-400"></span></span>
                            {{ $isAr ? 'حلول الشحن البحري' : 'Sea Freight Solutions' }}
                        </div>
                        <h1 id="hero-title"
                            class="max-w-4xl text-5xl font-black leading-[1.02] tracking-[-.035em] sm:text-6xl lg:text-7xl">
                            {{ $isAr ? 'حرّك شحنتك بثقة' : 'Move your cargo with confidence' }}
                            <span
                                class="mt-2 block text-cyan-400">{{ $isAr ? 'عبر البحر والعالم' : 'across sea & beyond' }}</span>
                        </h1>
                        <p class="mt-7 max-w-2xl text-base leading-8 text-slate-400 sm:text-lg">
                            {{ $isAr ? 'من تنسيق الشحنات إلى متابعة الرحلة، تجمع HAG Brokers بين العمليات البحرية والرؤية اللوجستية في تجربة رقمية واضحة.' : 'From shipment coordination to journey visibility, HAG Brokers brings sea freight operations and logistics management together in one clear digital experience.' }}
                        </p>

                        <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                            <a href="#contact"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-400 px-7 py-4 text-base font-bold text-slate-950 shadow-xl shadow-cyan-400/10 transition hover:-translate-y-0.5 hover:bg-cyan-300">
                                {{ $isAr ? 'ابدأ طلبك' : 'Start a shipment request' }}
                                <svg class="h-5 w-5 {{ $isAr ? 'rotate-180' : '' }}" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 12h14m-6-6 6 6-6 6" />
                                </svg>
                            </a>
                            <a href="#services"
                                class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/[.03] px-7 py-4 text-base font-semibold text-white transition hover:border-cyan-400/30 hover:bg-white/[.06]">{{ $isAr ? 'استكشف الخدمات' : 'Explore services' }}</a>
                        </div>

                        <div class="mt-10 flex flex-wrap gap-x-8 gap-y-3 text-sm text-slate-500">
                            <span>✓ {{ $isAr ? 'تنسيق الشحنات' : 'Shipment coordination' }}</span>
                            <span>✓ {{ $isAr ? 'رؤية أوضح للشحن' : 'Clearer shipment visibility' }}</span>
                            <span>✓ {{ $isAr ? 'منصة آمنة' : 'Secure platform' }}</span>
                        </div>
                    </div>

                    <div class="relative fade-up" style="animation-delay:.15s">
                        <div
                            class="hero-glow relative overflow-hidden rounded-[2rem] border border-white/10 bg-gradient-to-br from-white/[.07] to-white/[.02] p-5 sm:p-7">
                            <div
                                class="absolute inset-0 bg-[radial-gradient(circle_at_70%_15%,rgba(34,211,238,.15),transparent_35%)]">
                            </div>
                            <div class="relative">
                                <div class="flex items-center justify-between border-b border-white/10 pb-5">
                                    <div>
                                        <p class="text-xs uppercase tracking-[.16em] text-slate-500">
                                            {{ $isAr ? 'لوحة الشحنة' : 'Shipment overview' }}</p>
                                        <p class="mt-1 font-bold">HAG / SEA-2048</p>
                                    </div>
                                    <span
                                        class="rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-bold text-cyan-300">{{ $isAr ? 'قيد المتابعة' : 'In transit' }}</span>
                                </div>
                                <div class="py-8">
                                    <div class="flex items-center justify-between text-xs text-slate-500">
                                        <span>{{ $isAr ? 'ميناء الانطلاق' : 'Origin port' }}</span><span>{{ $isAr ? 'ميناء الوصول' : 'Destination port' }}</span>
                                    </div>
                                    <div class="relative my-7 flex items-center justify-between">
                                        <div class="h-4 w-4 rounded-full border-4 border-cyan-400 bg-[#06101d]"></div>
                                        <div
                                            class="ship-line absolute start-5 end-5 h-px bg-gradient-to-r from-cyan-400/80 via-cyan-400/20 to-cyan-400/80">
                                        </div>
                                        <div
                                            class="relative z-10 flex h-12 w-12 items-center justify-center rounded-2xl border border-cyan-400/20 bg-cyan-400/10 text-xl">
                                            🚢</div>
                                        <div class="h-4 w-4 rounded-full border-4 border-cyan-400 bg-[#06101d]"></div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-bold">{{ $isAr ? 'دبي' : 'Dubai' }}</p>
                                            <p class="mt-1 text-xs text-slate-500">{{ $isAr ? 'الإمارات' : 'UAE' }}
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <p class="font-bold">{{ $isAr ? 'الوجهة' : 'Destination' }}</p>
                                            <p class="mt-1 text-xs text-slate-500">
                                                {{ $isAr ? 'ميناء الوصول' : 'Arrival port' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 border-t border-white/10 pt-5 text-center">
                                    <div>
                                        <p class="text-lg font-black">FCL</p>
                                        <p class="mt-1 text-[10px] uppercase tracking-wider text-slate-500">
                                            {{ $isAr ? 'نوع الشحنة' : 'Shipment' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-lg font-black">SEA</p>
                                        <p class="mt-1 text-[10px] uppercase tracking-wider text-slate-500">
                                            {{ $isAr ? 'الوسيلة' : 'Mode' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-lg font-black text-cyan-300">24/7</p>
                                        <p class="mt-1 text-[10px] uppercase tracking-wider text-slate-500">
                                            {{ $isAr ? 'دعم' : 'Support' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="absolute -bottom-5 -start-5 hidden rounded-2xl border border-white/10 bg-[#0b1727] px-5 py-4 shadow-2xl sm:block">
                            <p class="text-xs text-slate-500">{{ $isAr ? 'تنسيق لوجستي' : 'Logistics coordination' }}
                            </p>
                            <p class="mt-1 font-bold text-cyan-300">{{ $isAr ? 'منظّم وواضح' : 'Structured & clear' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-y border-white/10 bg-white/[.025]" aria-label="Key capabilities">
            <div class="mx-auto grid max-w-7xl grid-cols-2 divide-x divide-white/10 sm:grid-cols-4 sm:px-8 lg:px-10">
                <div class="px-5 py-8 text-center sm:py-10">
                    <p class="text-2xl font-black text-cyan-300 sm:text-3xl">SEA</p>
                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">{{ $isAr ? 'شحن بحري' : 'Sea freight' }}</p>
                </div>
                <div class="px-5 py-8 text-center sm:py-10">
                    <p class="text-2xl font-black sm:text-3xl">FCL / LCL</p>
                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">{{ $isAr ? 'خيارات شحن' : 'Freight options' }}
                    </p>
                </div>
                <div class="border-t border-white/10 px-5 py-8 text-center sm:border-t-0 sm:py-10">
                    <p class="text-2xl font-black sm:text-3xl">24/7</p>
                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">{{ $isAr ? 'دعم المنصة' : 'Platform support' }}
                    </p>
                </div>
                <div class="border-t border-white/10 px-5 py-8 text-center sm:border-t-0 sm:py-10">
                    <p class="text-2xl font-black text-cyan-300 sm:text-3xl">HAG</p>
                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                        {{ $isAr ? 'حلول لوجستية' : 'Logistics solutions' }}</p>
                </div>
            </div>
        </section>

        <section id="services" class="py-24 sm:py-28">
            <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[.2em] text-cyan-300">
                        {{ $isAr ? 'الخدمات' : 'Services' }}</p>
                    <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-5xl">
                        {{ $isAr ? 'كل ما تحتاجه لإدارة الشحن البحري' : 'Everything you need to manage sea freight' }}
                    </h2>
                    <p class="mt-5 leading-8 text-slate-400">
                        {{ $isAr ? 'واجهة عملية مصممة لتجعل رحلة الشحنة والمعلومات المرتبطة بها أسهل في المتابعة.' : 'A practical experience designed to make shipment activity and the information around it easier to manage.' }}
                    </p>
                </div>

                <div class="mt-12 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
        ['icon' => '⚓', 'title' => $isAr ? 'الشحن البحري' : 'Sea Freight', 'text' => $isAr ? 'تنسيق عمليات الشحن البحري وربط تفاصيل الشحنة في مكان واضح.' : 'Coordinate sea freight activity and keep shipment details organized in one clear place.'],
        ['icon' => '📦', 'title' => $isAr ? 'إدارة الشحنات' : 'Shipment Management', 'text' => $isAr ? 'أنشئ وراجع وتابع معلومات الشحنات من خلال تجربة رقمية موحّدة.' : 'Create, review and follow shipment information through a unified digital experience.'],
        ['icon' => '🔎', 'title' => $isAr ? 'تتبع الشحنة' : 'Shipment Tracking', 'text' => $isAr ? 'الوصول السريع إلى حالة الشحنة والمعلومات التشغيلية المتاحة.' : 'Quick access to shipment status and the operational information available to you.'],
        ['icon' => '💬', 'title' => $isAr ? 'طلب عرض سعر' : 'Quote Requests', 'text' => $isAr ? 'أرسل تفاصيل الشحن المطلوبة لبدء عملية طلب عرض السعر.' : 'Submit the freight details you need to start a quote request.'],
        ['icon' => '🌐', 'title' => $isAr ? 'تنسيق دولي' : 'International Coordination', 'text' => $isAr ? 'تنظيم المعلومات المرتبطة بالموانئ والوجهات ضمن سير عمل واحد.' : 'Organize information connected to ports and destinations within one workflow.'],
        ['icon' => '🛡️', 'title' => $isAr ? 'منصة آمنة' : 'Secure Platform', 'text' => $isAr ? 'إدارة حسابك وعملياتك عبر منصة مصممة لتجربة استخدام واضحة وآمنة.' : 'Manage your account and operations through a platform built for a clear, secure experience.'],
    ] as $service)
                        <article
                            class="group rounded-2xl border border-white/10 bg-white/[.025] p-7 transition duration-300 hover:-translate-y-1 hover:border-cyan-400/30 hover:bg-white/[.04]">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-400/10 text-2xl ring-1 ring-cyan-400/10">
                                {{ $service['icon'] }}</div>
                            <h3 class="mt-6 text-xl font-bold">{{ $service['title'] }}</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-400">{{ $service['text'] }}</p>
                            <div class="mt-6 h-px w-12 bg-cyan-400/60 transition-all group-hover:w-20"></div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="solutions" class="border-y border-white/10 bg-[#081625] py-24 sm:py-28">
            <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">
                <div class="grid items-center gap-14 lg:grid-cols-2">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[.2em] text-cyan-300">
                            {{ $isAr ? 'الحلول الرقمية' : 'Digital solutions' }}</p>
                        <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-5xl">
                            {{ $isAr ? 'واجهة واحدة لعملياتك البحرية' : 'One interface for your sea freight operations' }}
                        </h2>
                        <p class="mt-5 max-w-xl leading-8 text-slate-400">
                            {{ $isAr ? 'بدلاً من تشتيت معلومات الشحن بين الأدوات المختلفة، تجمع المنصة الإجراءات الأساسية في تجربة منظمة وسهلة الاستخدام.' : 'Instead of scattering freight information across different tools, the platform brings core actions together in an organized, easy-to-use experience.' }}
                        </p>
                        <div class="mt-8 space-y-4">
                            @foreach ([$isAr ? 'متابعة حالة الشحنة من لوحة واضحة' : 'Follow shipment status from a clear dashboard', $isAr ? 'الوصول إلى الخدمات والإجراءات السريعة' : 'Access common services and quick actions', $isAr ? 'إدارة الحساب والعمليات من مكان واحد' : 'Manage account and operations from one place'] as $point)
                                <div class="flex items-start gap-3"><span
                                        class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-cyan-400 text-xs font-black text-slate-950">✓</span><span
                                        class="text-sm leading-7 text-slate-300">{{ $point }}</span></div>
                            @endforeach
                        </div>
                    </div>
                    <div class="relative">
                        <div class="rounded-[2rem] border border-white/10 bg-[#06101d] p-5 shadow-2xl sm:p-7">
                            <div class="flex items-center gap-2 border-b border-white/10 pb-5"><span
                                    class="h-2.5 w-2.5 rounded-full bg-cyan-400"></span><span
                                    class="h-2.5 w-2.5 rounded-full bg-white/10"></span><span
                                    class="h-2.5 w-2.5 rounded-full bg-white/10"></span><span
                                    class="ms-auto text-xs text-slate-500">HAG Brokers</span></div>
                            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-white/10 bg-white/[.03] p-5 sm:col-span-2">
                                    <p class="text-xs text-slate-500">
                                        {{ $isAr ? 'الشحنة الحالية' : 'Current shipment' }}</p>
                                    <div class="mt-3 flex items-center justify-between"><span
                                            class="font-bold">SEA-2048</span><span
                                            class="rounded-full bg-cyan-400/10 px-2.5 py-1 text-xs font-semibold text-cyan-300">{{ $isAr ? 'قيد الشحن' : 'In transit' }}</span>
                                    </div>
                                    <div class="mt-5 h-2 overflow-hidden rounded-full bg-white/5">
                                        <div class="h-full w-[72%] rounded-full bg-cyan-400"></div>
                                    </div>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-white/[.03] p-5">
                                    <p class="text-xs text-slate-500">{{ $isAr ? 'الشحنات' : 'Shipments' }}</p>
                                    <p class="mt-2 text-2xl font-black">12</p>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-white/[.03] p-5">
                                    <p class="text-xs text-slate-500">{{ $isAr ? 'قيد المتابعة' : 'In transit' }}</p>
                                    <p class="mt-2 text-2xl font-black text-cyan-300">04</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="py-24 sm:py-28">
            <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">
                <div
                    class="rounded-[2rem] border border-white/10 bg-gradient-to-br from-cyan-400/[.08] via-white/[.02] to-transparent p-8 sm:p-12 lg:p-16">
                    <div class="grid gap-12 lg:grid-cols-[1.1fr_.9fr] lg:items-end">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[.2em] text-cyan-300">HAG Brokers</p>
                            <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-5xl">
                                {{ $isAr ? 'Sea Freight Solutions مصممة للأعمال الحديثة' : 'Sea Freight Solutions for modern business' }}
                            </h2>
                            <p class="mt-5 max-w-2xl leading-8 text-slate-400">
                                {{ $isAr ? 'تجمع HAG Brokers بين احتياجات الشحن البحري وتجربة رقمية تساعدك على تنظيم المعلومات واتخاذ الخطوات التشغيلية بوضوح.' : 'HAG Brokers connects sea freight needs with a digital experience that helps organize information and keep operational steps clear.' }}
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-white/10 bg-black/10 p-5">
                                <p class="text-2xl font-black text-cyan-300">FCL</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $isAr ? 'شحن كامل' : 'Full container' }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-black/10 p-5">
                                <p class="text-2xl font-black">LCL</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $isAr ? 'شحن جزئي' : 'Consolidated freight' }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-black/10 p-5">
                                <p class="text-2xl font-black">SEA</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $isAr ? 'النقل البحري' : 'Ocean mode' }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-black/10 p-5">
                                <p class="text-2xl font-black text-cyan-300">HAG</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $isAr ? 'منصة واحدة' : 'One platform' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="contact" class="pb-24 sm:pb-28">
            <div class="mx-auto max-w-5xl px-5 sm:px-8 lg:px-10">
                <div
                    class="relative overflow-hidden rounded-[2rem] border border-cyan-400/20 bg-cyan-400/[.07] px-7 py-14 text-center sm:px-12">
                    <div
                        class="absolute -top-28 start-1/2 h-64 w-64 -translate-x-1/2 rounded-full bg-cyan-400/10 blur-3xl">
                    </div>
                    <div class="relative">
                        <p class="text-xs font-bold uppercase tracking-[.2em] text-cyan-300">
                            {{ $isAr ? 'ابدأ الآن' : 'Get started' }}</p>
                        <h2 class="mt-4 text-3xl font-black sm:text-5xl">
                            {{ $isAr ? 'جاهز لتنظيم شحنتك البحرية؟' : 'Ready to organize your sea freight?' }}</h2>
                        <p class="mx-auto mt-5 max-w-2xl leading-8 text-slate-400">
                            {{ $isAr ? 'سجّل الدخول لإدارة شحناتك، أو أنشئ حساباً جديداً للوصول إلى منصة HAG Brokers.' : 'Log in to manage your shipments, or create an account to access the HAG Brokers platform.' }}
                        </p>
                        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                            @auth
                                <a href="{{ url('/dashboard') }}"
                                    class="rounded-xl bg-cyan-400 px-7 py-4 font-bold text-slate-950 transition hover:bg-cyan-300">{{ $isAr ? 'الذهاب إلى لوحة التحكم' : 'Go to dashboard' }}</a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="rounded-xl bg-cyan-400 px-7 py-4 font-bold text-slate-950 transition hover:bg-cyan-300">{{ $isAr ? 'تسجيل الدخول' : 'Log in' }}</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}"
                                        class="rounded-xl border border-white/10 bg-white/[.04] px-7 py-4 font-semibold text-white transition hover:bg-white/[.08]">{{ $isAr ? 'إنشاء حساب' : 'Create account' }}</a>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-white/10 bg-[#040b14]">
        <div class="mx-auto max-w-7xl px-5 py-12 sm:px-8 lg:px-10">
            <div class="grid gap-10 md:grid-cols-[1.5fr_1fr_1fr]">
                <div>
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400 text-sm font-black text-slate-950">
                            HAG</div>
                        <div>
                            <div class="font-bold">HAG Brokers</div>
                            <div class="text-[9px] uppercase tracking-[.18em] text-slate-500">
                                {{ $isAr ? 'حلول الشحن البحري' : 'Sea Freight Solutions' }}</div>
                        </div>
                    </div>
                    <p class="mt-5 max-w-md text-sm leading-7 text-slate-500">
                        {{ $isAr ? 'حلول رقمية لإدارة الشحن البحري والشحنات والخدمات اللوجستية.' : 'Digital solutions for sea freight, shipment management and logistics operations.' }}
                    </p>
                </div>
                <div>
                    <p class="font-semibold">{{ $isAr ? 'روابط' : 'Explore' }}</p>
                    <div class="mt-4 grid gap-3 text-sm text-slate-500"><a href="#services"
                            class="hover:text-cyan-300">{{ $isAr ? 'الخدمات' : 'Services' }}</a><a href="#solutions"
                            class="hover:text-cyan-300">{{ $isAr ? 'الحلول' : 'Solutions' }}</a><a href="#about"
                            class="hover:text-cyan-300">{{ $isAr ? 'عن HAG' : 'About' }}</a></div>
                </div>
                <div>
                    <p class="font-semibold">{{ $isAr ? 'الحساب' : 'Account' }}</p>
                    <div class="mt-4 grid gap-3 text-sm text-slate-500"><a href="{{ route('login') }}"
                            class="hover:text-cyan-300">{{ $isAr ? 'تسجيل الدخول' : 'Log in' }}</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="hover:text-cyan-300">{{ $isAr ? 'إنشاء حساب' : 'Create account' }}</a>
                        @endif
                    </div>
                </div>
            </div>
            <div
                class="mt-10 flex flex-col gap-3 border-t border-white/10 pt-6 text-xs text-slate-600 sm:flex-row sm:items-center sm:justify-between">
                <span>© {{ date('Y') }} HAG Brokers.
                    {{ $isAr ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}</span><span>{{ $isAr ? 'Sea Freight Solutions' : 'Sea Freight Solutions' }}</span>
            </div>
        </div>
    </footer>

    <script>
        (() => {
            const toggle = document.getElementById('mobileToggle');
            const close = document.getElementById('mobileClose');
            const menu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('mobileOverlay');
            const languageMenu = document.getElementById('language-menu');

            function openMenu() {
                menu.classList.add('open');
                overlay.classList.remove('hidden');
                menu.setAttribute('aria-hidden', 'false');
                toggle?.setAttribute('aria-expanded', 'true');
                document.body.classList.add('overflow-hidden');
            }

            function closeMenu() {
                menu.classList.remove('open');
                overlay.classList.add('hidden');
                menu.setAttribute('aria-hidden', 'true');
                toggle?.setAttribute('aria-expanded', 'false');
                document.body.classList.remove('overflow-hidden');
            }
            toggle?.addEventListener('click', openMenu);
            close?.addEventListener('click', closeMenu);
            overlay?.addEventListener('click', closeMenu);
            menu?.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMenu));
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') {
                    closeMenu();
                    languageMenu?.classList.add('hidden');
                }
            });
            document.addEventListener('click', e => {
                if (languageMenu && !e.target.closest('[onclick*="language-menu"]') && !e.target.closest(
                        '#language-menu')) languageMenu.classList.add('hidden');
            });
        })();
    </script>

</body>

</html>
