<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        $locale = app()->getLocale();
        $isArabic = $locale === 'ar';

        // Prepare optional contact data once.
        $phone = isset($setting) ? $setting->phone ?? null : null;
        $email = isset($setting) ? $setting->email ?? null : null;

        $t = $isArabic
            ? [
                'title' => 'صيانة المنصة',

                'status' => 'صيانة مجدولة',

                'headline' => 'نقوم بتحديث',

                'headline_highlight' => 'HAG Brokers',

                'description' =>
                    'نعمل حالياً على تطوير وتحسين منصة HAG Brokers لتقديم تجربة أكثر استقراراً ووضوحاً للشركات والتجار في مجال وساطة الشحن البحري.',

                'status_title' => 'حالة المنصة',

                'system_badge' => 'النظام',

                'system_status' => 'التحديثات قيد التنفيذ',

                'system_description' => 'يتم حالياً إجراء تحسينات على بعض خدمات المنصة والبنية التشغيلية.',

                'status_note' => 'ستعود خدمات المنصة تلقائياً بعد اكتمال أعمال التحديث والصيانة.',

                'services_title' => 'ما الذي نقوم بتحديثه؟',

                'service_1' => 'طلبات الشحن',

                'service_1_desc' => 'تحسين إدارة طلبات عروض الأسعار واحتياجات الشحن.',

                'service_2' => 'بيانات الناقلين',

                'service_2_desc' => 'تنظيم بيانات شركات الشحن والناقلين وخيارات النقل.',

                'service_3' => 'تتبع الشحنات',

                'service_3_desc' => 'تحسين تجربة متابعة حالة الشحنات والأحداث اللوجستية.',

                'service_4' => 'لوحة العمليات',

                'service_4_desc' => 'تطوير أدوات الإدارة والعمليات الداخلية للمنصة.',

                'business_title' => 'هل تحتاج إلى التواصل معنا؟',

                'contact' => 'تواصل معنا',

                'phone' => 'الهاتف',

                'email' => 'البريد الإلكتروني',

                'location' => 'الإمارات العربية المتحدة',

                'check_again' => 'إعادة المحاولة',

                'secure' => 'بياناتك محفوظة',

                'secure_desc' => 'أعمال الصيانة لا تؤثر على بيانات العملاء أو الشحنات المسجلة في النظام.',

                'footer' => 'HAG Brokers — شريكك في وساطة الشحن البحري',

                'switch_en' => 'English',

                'switch_ar' => 'العربية',
            ]
            : [
                'title' => 'Platform Maintenance',

                'status' => 'Scheduled Maintenance',

                'headline' => 'We Are Updating',

                'headline_highlight' => 'HAG Brokers',

                'description' =>
                    'We are currently improving the HAG Brokers platform to provide a more reliable and transparent experience for businesses and traders using sea freight brokerage services.',

                'status_title' => 'Platform Status',

                'system_badge' => 'SYSTEM',

                'system_status' => 'System Updates in Progress',

                'system_description' =>
                    'Some platform services and operational infrastructure are currently being improved.',

                'status_note' =>
                    'Platform services will become available again once the updates and maintenance are completed.',

                'services_title' => 'What Are We Updating?',

                'service_1' => 'Shipping Requests',

                'service_1_desc' => 'Improving quotation requests and shipping requirement management.',

                'service_2' => 'Carrier Data',

                'service_2_desc' => 'Organizing shipping carriers, partners and transport options.',

                'service_3' => 'Shipment Tracking',

                'service_3_desc' => 'Improving shipment status and logistics event tracking.',

                'service_4' => 'Operations Dashboard',

                'service_4_desc' => 'Enhancing internal management and operational tools.',

                'business_title' => 'Need to Reach Us?',

                'contact' => 'Contact Us',

                'phone' => 'Phone',

                'email' => 'Email',

                'location' => 'United Arab Emirates',

                'check_again' => 'Check Again',

                'secure' => 'Your Data Is Safe',

                'secure_desc' => 'Maintenance does not affect customer or shipment data stored in the system.',

                'footer' => 'HAG Brokers — Your Sea Freight Brokerage Partner',

                'switch_en' => 'English',

                'switch_ar' => 'العربية',
            ];

        // SEO / Social Sharing
        $siteName = config('app.name', 'HAG Brokers');
        $siteUrl = rtrim(config('app.url', url('/')), '/');
        $canonicalUrl = url()->current();
        $socialImage = asset('storage/favicon.png');
        $socialImageAlt = 'HAG Brokers logo';
        $seoTitle = $siteName . ' | ' . $t['title'];
        $seoDescription = $t['description'];
    @endphp


    <title>{{ $seoTitle }}</title>

    <!-- Basic SEO -->
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords"
        content="HAG Brokers, sea freight, maritime shipping, shipping brokerage, freight forwarding, UAE, Dubai, Abu Dhabi, logistics">
    <meta name="author" content="HAG Brokers">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="googlebot" content="index, follow, max-image-preview:large">
    <meta name="theme-color" content="#020c17">
    <meta name="application-name" content="{{ $siteName }}">
    <meta name="format-detection" content="telephone=no">

    <!-- Canonical -->
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="alternate" hreflang="ar-AE" href="{{ url()->current() }}?lang=ar">
    <link rel="alternate" hreflang="en-AE" href="{{ url()->current() }}?lang=en">
    <link rel="alternate" hreflang="x-default" href="{{ url('/') }}">

    <!-- Favicon / App Icons -->
    <link rel="icon" type="image/png" href="{{ $socialImage }}">
    <link rel="apple-touch-icon" href="{{ $socialImage }}">

    <!-- Open Graph / WhatsApp / Facebook / LinkedIn -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $socialImage }}">
    <meta property="og:image:secure_url" content="{{ $socialImage }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">
    <meta property="og:image:alt" content="{{ $socialImageAlt }}">
    <meta property="og:locale" content="{{ $isArabic ? 'ar_AE' : 'en_AE' }}">
    <meta property="og:locale:alternate" content="{{ $isArabic ? 'en_AE' : 'ar_AE' }}">

    <!-- X / Twitter -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $socialImage }}">
    <meta name="twitter:image:alt" content="{{ $socialImageAlt }}">

    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $siteName,
        'url' => $siteUrl,
        'logo' => $socialImage,
        'description' => $seoDescription,
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'United Arab Emirates',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $seoTitle,
        'url' => $canonicalUrl,
        'description' => $seoDescription,
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => $siteUrl,
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => $siteName,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $socialImage,
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])


    <style>
        :root {
            --navy: #020c17;
            --navy-2: #06192b;
            --cyan: #22d3ee;
            --cyan-light: #67e8f9;
            --blue: #38bdf8;
            --white: #f8fafc;
            --muted: #94a3b8;
        }


        * {
            box-sizing: border-box;
        }


        html {
            scroll-behavior: smooth;
        }


        body {
            margin: 0;
            min-height: 100vh;

            background:
                #020c17;

            color: white;

            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }


        a {
            color: inherit;
            text-decoration: none;
        }


        /* =====================================================
           MAIN PAGE
        ===================================================== */

        .maintenance-page {
            position: relative;

            min-height: 100vh;

            overflow: hidden;

            isolation: isolate;

            background:
                #020c17;
        }


        /* =====================================================
           REMOTE BACKGROUND IMAGE
        ===================================================== */

        .background-fallback {
            position: absolute;

            inset: 0;

            z-index: -5;

            background-image:
                url('https://images.pexels.com/photos/37113627/pexels-photo-37113627.jpeg');

            background-size: cover;

            background-position: center center;

            transform: scale(1.02);
        }


        /* =====================================================
           VIMEO BACKGROUND
        ===================================================== */

        .background-video {
            position: absolute;

            inset: 0;

            width: 100%;
            height: 100%;

            overflow: hidden;

            z-index: -4;

            pointer-events: none;

            background: transparent;
        }


        .background-video iframe {
            position: absolute;

            top: 50%;
            left: 50%;

            width: 100vw;
            height: 56.25vw;

            min-width: 177.78vh;
            min-height: 100vh;

            transform:
                translate(-50%, -50%) scale(1.08);

            border: 0;

            pointer-events: none;
        }


        /* =====================================================
           DARK OVERLAY
        ===================================================== */

        .background-overlay {
            position: absolute;

            inset: 0;

            z-index: -3;

            background:

                radial-gradient(circle at 78% 30%,
                    rgba(34, 211, 238, .13),
                    transparent 30%),

                linear-gradient(90deg,
                    rgba(2, 12, 23, .98) 0%,
                    rgba(2, 12, 23, .88) 35%,
                    rgba(2, 12, 23, .64) 68%,
                    rgba(2, 12, 23, .72) 100%);
        }


        .bottom-overlay {
            position: absolute;

            left: 0;
            right: 0;
            bottom: 0;

            height: 40%;

            z-index: -2;

            background:
                linear-gradient(to top,
                    rgba(2, 12, 23, .96),
                    transparent);

            pointer-events: none;
        }


        /* =====================================================
           CONTENT
        ===================================================== */

        .page-content {
            position: relative;

            z-index: 5;

            width:
                min(1180px, calc(100% - 36px));

            min-height: 100vh;

            margin: auto;

            display: flex;

            flex-direction: column;
        }


        /* =====================================================
           HEADER
        ===================================================== */

        .topbar {
            padding: 24px 0;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;
        }


        .brand {
            display: flex;

            align-items: center;

            gap: 13px;
        }


        .brand-icon {
            width: 48px;
            height: 48px;

            border-radius: 14px;

            display: grid;

            place-items: center;

            color: var(--cyan);

            background:
                rgba(34, 211, 238, .08);

            border:
                1px solid rgba(34, 211, 238, .30);

            box-shadow:
                0 0 35px rgba(34, 211, 238, .08);

            backdrop-filter:
                blur(12px);
        }


        .brand-name {
            font-size: 23px;

            font-weight: 900;

            letter-spacing: .12em;

            line-height: 1;
        }


        .brand-subtitle {
            margin-top: 6px;

            color: #a5f3fc;

            font-size: 9px;

            letter-spacing: .20em;

            font-weight: 700;
        }


        /* =====================================================
           LANGUAGE
        ===================================================== */

        .language-switcher {
            display: flex;

            align-items: center;

            gap: 4px;

            padding: 4px;

            border-radius: 999px;

            background:
                rgba(3, 15, 29, .65);

            border:
                1px solid rgba(255, 255, 255, .13);

            backdrop-filter:
                blur(14px);
        }


        .language-switcher a {
            padding:
                7px 12px;

            border-radius: 999px;

            font-size: 11px;

            font-weight: 800;

            color: #cbd5e1;

            transition: .2s ease;
        }


        .language-switcher a:hover {
            color: white;

            background:
                rgba(255, 255, 255, .07);
        }


        .language-switcher a.active {
            color: #03111f;

            background:
                var(--cyan);

            box-shadow:
                0 5px 20px rgba(34, 211, 238, .18);
        }


        /* =====================================================
           MAIN GRID
        ===================================================== */

        .main-content {
            flex: 1;

            display: grid;

            grid-template-columns:
                minmax(0, 1.1fr) minmax(330px, .75fr);

            align-items: center;

            gap: 70px;

            padding:
                55px 0 75px;
        }


        /* =====================================================
           LEFT SIDE
        ===================================================== */

        .status-pill {
            display: inline-flex;

            align-items: center;

            gap: 10px;

            padding:
                9px 15px;

            border-radius: 999px;

            background:
                rgba(34, 211, 238, .08);

            border:
                1px solid rgba(34, 211, 238, .38);

            color:
                var(--cyan-light);

            font-size: 12px;

            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: .08em;

            backdrop-filter:
                blur(12px);
        }


        .status-dot {
            width: 8px;
            height: 8px;

            border-radius: 50%;

            background:
                var(--cyan);

            box-shadow:
                0 0 15px var(--cyan);

            animation:
                pulse 2s infinite;
        }


        @keyframes pulse {

            0%,
            100% {
                opacity: 1;

                box-shadow:
                    0 0 10px var(--cyan);
            }

            50% {
                opacity: .4;

                box-shadow:
                    0 0 26px var(--cyan);
            }
        }


        .headline {
            max-width: 800px;

            font-size:
                clamp(42px, 6vw, 76px);

            line-height: 1.02;

            letter-spacing: -.045em;

            font-weight: 900;

            margin:
                25px 0 22px;
        }


        .headline-highlight {
            display: inline-block;

            color:
                var(--cyan);

            text-shadow:
                0 0 35px rgba(34, 211, 238, .15);
        }


        .description {
            max-width: 690px;

            color:
                #dbeafe;

            font-size: 18px;

            line-height: 1.8;

            margin:
                0 0 30px;
        }


        /* =====================================================
           BUTTONS
        ===================================================== */

        .action-row {
            display: flex;

            flex-wrap: wrap;

            gap: 12px;
        }


        .btn {
            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding:
                12px 19px;

            border-radius: 999px;

            font-weight: 800;

            font-size: 14px;

            transition:
                transform .2s ease,
                background .2s ease,
                box-shadow .2s ease;
        }


        .btn-primary {
            color:
                #032033;

            background:
                var(--cyan);

            box-shadow:
                0 10px 35px rgba(34, 211, 238, .20);
        }


        .btn-primary:hover {
            transform:
                translateY(-2px);

            background:
                var(--cyan-light);

            box-shadow:
                0 14px 40px rgba(34, 211, 238, .30);
        }


        .btn-secondary {
            color: white;

            border:
                1px solid rgba(255, 255, 255, .18);

            background:
                rgba(255, 255, 255, .06);

            backdrop-filter:
                blur(12px);
        }


        .btn-secondary:hover {
            transform:
                translateY(-2px);

            background:
                rgba(255, 255, 255, .11);
        }


        /* =====================================================
           STATUS CARD
        ===================================================== */

        .status-card {
            border-radius: 28px;

            padding: 30px;

            background:
                rgba(4, 20, 37, .76);

            border:
                1px solid rgba(34, 211, 238, .26);

            backdrop-filter:
                blur(18px);

            box-shadow:
                0 25px 70px rgba(0, 0, 0, .38);
        }


        .status-card-header {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            padding-bottom: 20px;

            border-bottom:
                1px solid rgba(255, 255, 255, .09);
        }


        .status-title {
            font-size: 12px;

            color:
                var(--cyan);

            font-weight: 900;

            text-transform: uppercase;

            letter-spacing: .14em;
        }


        .live-badge {
            padding:
                5px 9px;

            border-radius: 999px;

            color:
                #a5f3fc;

            background:
                rgba(34, 211, 238, .08);

            border:
                1px solid rgba(34, 211, 238, .22);

            font-size: 10px;

            font-weight: 800;
        }


        .status-main {
            display: flex;

            gap: 15px;

            align-items: flex-start;

            padding:
                25px 0;
        }


        .status-icon {
            flex:
                0 0 auto;

            width: 48px;
            height: 48px;

            border-radius: 15px;

            display: grid;

            place-items: center;

            color:
                var(--cyan);

            background:
                rgba(34, 211, 238, .08);

            border:
                1px solid rgba(34, 211, 238, .20);
        }


        .status-main h2 {
            margin:
                0 0 7px;

            font-size: 21px;

            line-height: 1.3;
        }


        .status-main p {
            margin: 0;

            color:
                #94a3b8;

            font-size: 13px;

            line-height: 1.7;
        }


        /* =====================================================
           PROGRESS
        ===================================================== */

        .status-line {
            height: 5px;

            overflow: hidden;

            border-radius: 999px;

            background:
                rgba(255, 255, 255, .08);
        }


        .status-line span {
            display: block;

            width: 68%;

            height: 100%;

            border-radius: inherit;

            background:
                linear-gradient(90deg,
                    #0891b2,
                    var(--cyan));

            animation:
                maintenanceProgress 3s ease-in-out infinite alternate;
        }


        @keyframes maintenanceProgress {

            from {
                width: 55%;
            }

            to {
                width: 78%;
            }
        }


        .status-note {
            margin-top: 14px;

            color:
                #64748b;

            font-size: 11px;

            line-height: 1.6;
        }


        /* =====================================================
           SERVICES
        ===================================================== */

        .services-heading {
            margin-top: 25px;

            color:
                #a5f3fc;

            font-size: 11px;

            font-weight: 900;

            letter-spacing: .10em;

            text-transform: uppercase;
        }


        .services {
            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 10px;

            margin-top: 14px;
        }


        .service {
            padding: 14px;

            border-radius: 15px;

            background:
                rgba(255, 255, 255, .035);

            border:
                1px solid rgba(255, 255, 255, .07);

            transition:
                .2s ease;
        }


        .service:hover {
            transform:
                translateY(-2px);

            background:
                rgba(34, 211, 238, .055);

            border-color:
                rgba(34, 211, 238, .16);
        }


        .service strong {
            display: block;

            font-size: 12px;

            margin-bottom: 5px;
        }


        .service span {
            display: block;

            color:
                #64748b;

            font-size: 10px;

            line-height: 1.5;
        }


        /* =====================================================
           CONTACT
        ===================================================== */

        .contact-box {
            margin-top: 20px;

            padding:
                15px 18px;

            border-radius: 17px;

            background:
                rgba(34, 211, 238, .055);

            border:
                1px solid rgba(34, 211, 238, .12);
        }


        .contact-box-title {
            font-size: 11px;

            font-weight: 900;

            color:
                var(--cyan-light);

            margin-bottom: 9px;
        }


        .contact-links {
            display: flex;

            flex-wrap: wrap;

            gap: 12px;
        }


        .contact-links a {
            color:
                #dbeafe;

            font-size: 11px;

            transition:
                color .2s ease;
        }


        .contact-links a:hover {
            color:
                var(--cyan);
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .footer {
            padding:
                22px 0;

            border-top:
                1px solid rgba(255, 255, 255, .10);

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            color:
                #64748b;

            font-size: 11px;
        }


        .footer-brand {
            color:
                #94a3b8;
        }


        /* =====================================================
           RTL
        ===================================================== */

        html[dir="rtl"] .brand {
            flex-direction: row;
        }


        html[dir="rtl"] .status-main {
            text-align: right;
        }


        html[dir="rtl"] .headline {
            letter-spacing: 0;
        }


        html[dir="rtl"] .brand-name,
        html[dir="rtl"] .brand-subtitle {
            letter-spacing: .05em;
        }


        /* =====================================================
           TABLET
        ===================================================== */

        @media (max-width: 900px) {

            .main-content {
                grid-template-columns: 1fr;

                gap: 35px;

                padding-top: 45px;
            }


            .status-card {
                max-width: 650px;

                width: 100%;

                margin:
                    0 auto;
            }


            .background-video iframe {
                transform:
                    translate(-50%, -50%) scale(1.18);
            }

        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 600px) {

            .page-content {
                width:
                    calc(100% - 26px);
            }


            .topbar {
                padding-top: 18px;
            }


            .brand-name {
                font-size: 18px;
            }


            .brand-subtitle {
                font-size: 7px;
            }


            .brand-icon {
                width: 43px;
                height: 43px;
            }


            .language-switcher a {
                padding:
                    6px 9px;
            }


            .main-content {
                padding:
                    35px 0 55px;
            }


            .headline {
                font-size:
                    43px;

                margin-top:
                    22px;
            }


            .description {
                font-size:
                    15px;

                line-height:
                    1.75;
            }


            .action-row {
                flex-direction:
                    column;
            }


            .action-row .btn {
                width: 100%;
            }


            .status-card {
                padding:
                    22px;

                border-radius:
                    22px;
            }


            .services {
                grid-template-columns:
                    1fr;
            }


            .footer {
                flex-direction:
                    column;

                align-items:
                    flex-start;

                line-height:
                    1.7;
            }


            .background-video iframe {
                transform:
                    translate(-50%, -50%) scale(1.45);
            }

        }


        /* =====================================================
           REDUCED MOTION
        ===================================================== */

        @media (prefers-reduced-motion: reduce) {

            html {
                scroll-behavior:
                    auto;
            }


            .status-dot,
            .status-line span {
                animation:
                    none;
            }

        }
    </style>

</head>


<body>


    <div class="maintenance-page">


        {{-- =====================================================
         REMOTE IMAGE FALLBACK
    ====================================================== --}}

        <div class="background-fallback"></div>


        {{-- =====================================================
         VIMEO BACKGROUND VIDEO

         IMPORTANT:
         Vimeo must use iframe.
         Do NOT use <video><source> for Vimeo player URLs.
    ====================================================== --}}

        <div class="background-video">

            <iframe src="https://player.vimeo.com/video/1153647382?h=0c069012da&background=1&autoplay=1&loop=1&muted=1"
                frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen
                title="Cargo Ship Container Ship">
            </iframe>

        </div>


        {{-- =====================================================
         OVERLAYS
    ====================================================== --}}

        <div class="background-overlay"></div>

        <div class="bottom-overlay"></div>


        {{-- =====================================================
         PAGE CONTENT
    ====================================================== --}}

        <div class="page-content">


            {{-- =====================================================
             HEADER
        ====================================================== --}}

            <header class="topbar">


                <a href="{{ url('/') }}" class="brand">


                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-400/10 ring-1 ring-cyan-300/20">
                        <span class="text-lg font-black text-cyan-300">HAG</span>
                    </div>


                    <div>

                        <div class="brand-name">
                            HAG BROKERS
                        </div>

                        <div class="brand-subtitle">
                            MARITIME SHIPPING SOLUTIONS
                        </div>

                    </div>


                </a>


                {{-- Language --}}

                <div class="language-switcher">


                    <a href="{{ route('language.switch', 'en') }}" class="{{ !$isArabic ? 'active' : '' }}">
                        EN
                    </a>


                    <a href="{{ route('language.switch', 'ar') }}" class="{{ $isArabic ? 'active' : '' }}">
                        AR
                    </a>


                </div>


            </header>



            {{-- =====================================================
             MAIN
        ====================================================== --}}

            <main class="main-content">


                {{-- =================================================
                 LEFT
            ================================================== --}}

                <section>


                    <div class="status-pill">

                        <span class="status-dot"></span>

                        {{ $t['status'] }}

                    </div>


                    <h1 class="headline">

                        {{ $t['headline'] }}

                        <br>

                        <span class="headline-highlight">
                            {{ $t['headline_highlight'] }}
                        </span>

                    </h1>


                    <p class="description">

                        {{ $t['description'] }}

                    </p>


                    <div class="action-row">


                        {{-- Check Again --}}

                        <a href="{{ url()->current() }}" class="btn btn-primary">

                            <span>
                                ↻
                            </span>

                            {{ $t['check_again'] }}

                        </a>


                        {{-- Contact --}}

                        @php
                            $contactButton = $email
                                ? '<a href="mailto:' .
                                    e($email) .
                                    '" class="btn btn-secondary"><span>✉</span> ' .
                                    e($t['contact']) .
                                    '</a>'
                                : '';
                        @endphp
                        {!! $contactButton !!}


                    </div>


                </section>



                {{-- =================================================
                 RIGHT STATUS PANEL
            ================================================== --}}

                <aside class="status-card">


                    <div class="status-card-header">


                        <div class="status-title">

                            {{ $t['status_title'] }}

                        </div>


                        <div class="live-badge">

                            ● {{ $t['system_badge'] }}

                        </div>


                    </div>



                    {{-- Status --}}

                    <div class="status-main">


                        <div class="status-icon">

                            <svg width="25" height="25" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.7">

                                <path d="M12 3v4" />

                                <path d="M5.6 7.4 8.4 10.2" />

                                <path d="M3 13h4" />

                                <path d="M5.6 18.6 8.4 15.8" />

                                <path d="M18.4 18.6 15.6 15.8" />

                                <path d="M21 13h-4" />

                                <path d="M18.4 7.4 15.6 10.2" />

                                <circle cx="12" cy="13" r="3" />

                            </svg>

                        </div>


                        <div>

                            <h2>
                                {{ $t['system_status'] }}
                            </h2>


                            <p>
                                {{ $t['system_description'] }}
                            </p>

                        </div>


                    </div>



                    {{-- Progress --}}

                    <div class="status-line">

                        <span></span>

                    </div>


                    <div class="status-note">

                        {{ $t['status_note'] }}

                    </div>



                    {{-- =================================================
                     SERVICES
                ================================================== --}}

                    <div class="services-heading">

                        {{ $t['services_title'] }}

                    </div>


                    <div class="services">


                        <div class="service">

                            <strong>
                                {{ $t['service_1'] }}
                            </strong>

                            <span>
                                {{ $t['service_1_desc'] }}
                            </span>

                        </div>


                        <div class="service">

                            <strong>
                                {{ $t['service_2'] }}
                            </strong>

                            <span>
                                {{ $t['service_2_desc'] }}
                            </span>

                        </div>


                        <div class="service">

                            <strong>
                                {{ $t['service_3'] }}
                            </strong>

                            <span>
                                {{ $t['service_3_desc'] }}
                            </span>

                        </div>


                        <div class="service">

                            <strong>
                                {{ $t['service_4'] }}
                            </strong>

                            <span>
                                {{ $t['service_4_desc'] }}
                            </span>

                        </div>


                    </div>



                    {{-- =================================================
                     CONTACT
                ================================================== --}}

                    @php
                        $contactBox = '';

                        if ($phone || $email) {
                            $contactBox .= '<div class="contact-box">';
                            $contactBox .= '<div class="contact-box-title">' . e($t['business_title']) . '</div>';
                            $contactBox .= '<div class="contact-links">';

                            if ($phone) {
                                $contactBox .= '<a href="tel:' . e($phone) . '">☎ ' . e($phone) . '</a>';
                            }

                            if ($email) {
                                $contactBox .= '<a href="mailto:' . e($email) . '">✉ ' . e($email) . '</a>';
                            }

                            $contactBox .= '</div></div>';
                        }
                    @endphp

                    {!! $contactBox !!}



                </aside>


            </main>



            {{-- =====================================================
             FOOTER
        ====================================================== --}}

            <footer class="footer">


                <div class="footer-brand">

                    © {{ date('Y') }}

                    {{ config('app.name', 'HAG Brokers') }}

                    ·

                    {{ $t['footer'] }}

                </div>


                <div>

                    {{ $t['secure'] }}

                    ·

                    {{ $t['location'] }}

                </div>


            </footer>


        </div>

    </div>


</body>

</html>
