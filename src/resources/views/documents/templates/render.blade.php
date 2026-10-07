@php
    $isAr = app()->getLocale() === 'ar';

    $companyName = $companyName ?? ($data['company']['name'] ?? 'HAG Brokers');

    $companyNameAr = $companyNameAr ?? ($data['company']['name_ar'] ?? '');

    $logo = $data['company']['logo'] ?? null;
    $signature = $data['company']['signature'] ?? null;
    $stamp = $data['company']['stamp'] ?? null;

    $companyEmail = $data['company']['email'] ?? '';
    $companyPhone = $data['company']['phone'] ?? '';
    $companyWebsite = $data['company']['website'] ?? '';
    $companyAddress = $data['company']['address'] ?? '';
    $companyCity = $data['company']['city'] ?? '';
    $companyCountry = $data['company']['country'] ?? '';
    $companyPoBox = $data['company']['po_box'] ?? '';

    $headerColor = $template->metadata['header_color'] ?? '#06101d';

    $footerText = $template->footer ?: $data['company']['footer_text'] ?? '';

    $direction = $template->metadata['direction'] ?? ($isAr ? 'rtl' : 'ltr');

    $fontFamily = $template->metadata['font_family'] ?? 'DejaVu Sans';
@endphp

<!DOCTYPE html>

<html lang="{{ $isAr ? 'ar' : 'en' }}" dir="{{ $direction }}">

<head>

    <meta charset="UTF-8">

    <title>
        {{ $template->name }}
    </title>

    <style>
        @page {
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        body {
            font-family: "{{ $fontFamily }}", DejaVu Sans, sans-serif;
            color: #172033;
            font-size: 11px;
            line-height: 1.65;
            direction: {{ $direction }};
        }

        .page {
            position: relative;
            width: 100%;
            min-height: 297mm;
            padding: 18mm 18mm 25mm 18mm;
        }

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        .company-header {
            width: 100%;
            padding-bottom: 12px;
            margin-bottom: 20px;
            border-bottom: 2px solid {{ $headerColor }};
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-logo {
            width: 25%;
            vertical-align: middle;
        }

        .header-logo img {
            max-width: 170px;
            max-height: 65px;
        }

        .header-company {
            width: 75%;
            vertical-align: middle;
            text-align: right;
        }

        [dir="ltr"] .header-company {
            text-align: right;
        }

        [dir="rtl"] .header-company {
            text-align: left;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #06101d;
        }

        .company-name-ar {
            margin-top: 2px;
            font-size: 12px;
            color: #475569;
        }

        .company-contact {
            margin-top: 5px;
            font-size: 8.5px;
            color: #64748b;
        }

        /*
        |--------------------------------------------------------------------------
        | DOCUMENT TITLE
        |--------------------------------------------------------------------------
        */

        .document-heading {
            margin: 20px 0 18px;
        }

        .document-title {
            font-size: 21px;
            font-weight: bold;
            color: #06101d;
        }

        .document-subtitle {
            margin-top: 4px;
            color: #64748b;
            font-size: 9px;
        }

        /*
        |--------------------------------------------------------------------------
        | CONTENT
        |--------------------------------------------------------------------------
        */

        .document-content {
            width: 100%;
            color: #172033;
        }

        .document-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
        }

        .document-content th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            padding: 7px;
            border: 1px solid #cbd5e1;
        }

        .document-content td {
            padding: 7px;
            border: 1px solid #cbd5e1;
        }

        .document-content h1 {
            font-size: 20px;
            margin: 15px 0 8px;
            color: #06101d;
        }

        .document-content h2 {
            font-size: 16px;
            margin: 14px 0 7px;
            color: #06101d;
        }

        .document-content h3 {
            font-size: 13px;
            margin: 12px 0 6px;
            color: #06101d;
        }

        .document-content p {
            margin: 5px 0;
        }

        .document-content ul,
        .document-content ol {
            margin-top: 5px;
            margin-bottom: 8px;
        }

        .document-content blockquote {
            margin: 10px 0;
            padding: 8px 12px;
            border-left: 3px solid {{ $headerColor }};
            background: #f8fafc;
            color: #475569;
        }

        /*
        |--------------------------------------------------------------------------
        | INFORMATION BOX
        |--------------------------------------------------------------------------
        */

        .info-box {
            border: 1px solid #dbe3ec;
            background: #f8fafc;
            border-radius: 6px;
            padding: 10px;
            margin: 10px 0;
        }

        .info-title {
            font-weight: bold;
            color: #06101d;
            margin-bottom: 5px;
        }

        /*
        |--------------------------------------------------------------------------
        | SIGNATURE AREA
        |--------------------------------------------------------------------------
        */

        .signature-area {
            margin-top: 35px;
            width: 100%;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-column {
            width: 50%;
            vertical-align: bottom;
            text-align: center;
        }

        .signature-image {
            max-width: 130px;
            max-height: 65px;
            margin-bottom: 5px;
        }

        .stamp-image {
            max-width: 90px;
            max-height: 90px;
            margin-bottom: 5px;
        }

        .signature-line {
            width: 150px;
            margin: 8px auto 5px;
            border-top: 1px solid #94a3b8;
        }

        .signature-label {
            font-size: 9px;
            color: #64748b;
        }

        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        .document-footer {
            position: absolute;
            bottom: 8mm;
            left: 18mm;
            right: 18mm;
            padding-top: 7px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 8px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-left {
            text-align: left;
        }

        .footer-right {
            text-align: right;
        }

        .footer-center {
            text-align: center;
        }

        /*
        |--------------------------------------------------------------------------
        | BRAND ACCENT
        |--------------------------------------------------------------------------
        */

        .accent {
            color: {{ $headerColor }};
        }

        .accent-line {
            width: 55px;
            height: 3px;
            background: {{ $headerColor }};
            margin-top: 6px;
        }

        /*
        |--------------------------------------------------------------------------
        | PRINT HELPERS
        |--------------------------------------------------------------------------
        */

        .avoid-break {
            page-break-inside: avoid;
        }

        .page-break {
            page-break-before: always;
        }
    </style>

</head>


<body>

    <div class="page">

        {{-- ============================================================= --}}
        {{-- COMPANY HEADER --}}
        {{-- ============================================================= --}}

        <div class="company-header">

            <table class="header-table">

                <tr>

                    <td class="header-logo">

                        @if ($logo)
                            <img src="{{ $logo }}" alt="{{ $companyName }}">
                        @endif

                    </td>


                    <td class="header-company">

                        <div class="company-name">
                            {{ $companyName }}
                        </div>

                        @if ($companyNameAr)
                            <div class="company-name-ar">
                                {{ $companyNameAr }}
                            </div>
                        @endif

                        <div class="company-contact">

                            @if ($companyEmail)
                                {{ $companyEmail }}
                            @endif

                            @if ($companyPhone)
                                &nbsp; | &nbsp; {{ $companyPhone }}
                            @endif

                            @if ($companyWebsite)
                                &nbsp; | &nbsp; {{ $companyWebsite }}
                            @endif

                            @if ($companyCity || $companyCountry)
                                <br>

                                {{ $companyAddress }}

                                @if ($companyCity)
                                    , {{ $companyCity }}
                                @endif

                                @if ($companyCountry)
                                    , {{ $companyCountry }}
                                @endif

                                @if ($companyPoBox)
                                    &nbsp; | &nbsp;
                                    P.O. Box {{ $companyPoBox }}
                                @endif

                            @endif

                        </div>

                    </td>

                </tr>

            </table>

        </div>


        {{-- ============================================================= --}}
        {{-- DOCUMENT TITLE --}}
        {{-- ============================================================= --}}

        <div class="document-heading">

            <div class="document-title">
                {{ $template->name }}
            </div>

            <div class="accent-line"></div>

            @if ($template->document_type)
                <div class="document-subtitle">
                    {{ $template->document_type }}
                </div>
            @endif

        </div>


        {{-- ============================================================= --}}
        {{-- CUSTOM HEADER --}}
        {{-- ============================================================= --}}

        @if (!empty($header))
            <div class="document-custom-header">

                {!! $header !!}

            </div>
        @endif


        {{-- ============================================================= --}}
        {{-- MAIN CONTENT --}}
        {{-- ============================================================= --}}

        <div class="document-content">

            {!! $content !!}

        </div>


        {{-- ============================================================= --}}
        {{-- SIGNATURE + STAMP --}}
        {{-- ============================================================= --}}

        @if ($signature || $stamp)

            <div class="signature-area">

                <table class="signature-table">

                    <tr>

                        <td class="signature-column">

                            @if ($signature)
                                <img src="{{ $signature }}" alt="Signature" class="signature-image">
                            @endif

                            <div class="signature-line"></div>

                            <div class="signature-label">
                                Authorized Signature
                            </div>

                        </td>


                        <td class="signature-column">

                            @if ($stamp)
                                <img src="{{ $stamp }}" alt="Company Stamp" class="stamp-image">
                            @endif

                            <div class="signature-label">
                                Company Stamp
                            </div>

                        </td>

                    </tr>

                </table>

            </div>

        @endif


        {{-- ============================================================= --}}
        {{-- FOOTER --}}
        {{-- ============================================================= --}}

        <div class="document-footer">

            <table class="footer-table">

                <tr>

                    <td class="footer-left">

                        {{ $companyName }}

                    </td>


                    <td class="footer-center">

                        {{ $footerText }}

                    </td>


                    <td class="footer-right">

                        {{ now()->format('Y-m-d') }}

                    </td>

                </tr>

            </table>

        </div>

    </div>

</body>

</html>
