<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('seo.head')
    @include('components.public-theme')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="hag-public-app market-body">
    <a class="market-skip" href="#market-main">{{ __('marketplace.skip') }}</a>

    @include('components.public.header')

    <main id="market-main" class="trade-page market-main">
        {{ $slot }}
    </main>

    @include('components.public.footer')
</body>
</html>
