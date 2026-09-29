<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO -->
    <meta name="description"
        content="{{ __('Sea Freight Solutions in UAE. Reliable shipping, freight forwarding and logistics services from Dubai and the UAE.') }}">
    <meta name="keywords"
        content="Sea Freight UAE, Sea Freight Dubai, Shipping UAE, Freight Forwarding Dubai, Logistics UAE">
    <meta name="author" content="Sea Freight Solutions">

    <!-- Title -->
    <title>
        @hasSection('title')
            @yield('title') | Sea Freight Solutions
        @else
            {{ config('app.name', 'Sea Freight Solutions') }}
        @endif
    </title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('storage/favicon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    <!-- RTL / LTR specific styling -->
    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Figtree', sans-serif;
        }

        html[dir="rtl"] body {
            text-align: right;
        }

        html[dir="ltr"] body {
            text-align: left;
        }

        html[dir="rtl"] input,
        html[dir="rtl"] textarea,
        html[dir="rtl"] select {
            text-align: right;
        }

        html[dir="ltr"] input,
        html[dir="ltr"] textarea,
        html[dir="ltr"] select {
            text-align: left;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen bg-gray-100">

        <!-- Navigation -->
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Main Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
</body>

</html>
