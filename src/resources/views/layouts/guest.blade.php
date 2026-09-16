<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <title>{{ config('app.name', 'HAG Brokers') }} — Sea Freight Solutions</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <link rel="icon" type="image/png" href="{{ asset('storage/favicon.png') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-950 font-sans text-slate-900 antialiased">
    <div class="relative flex min-h-screen overflow-hidden">

        <!-- Background -->
        <div class="absolute inset-0 bg-slate-950"></div>

        <div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-cyan-500/10 blur-3xl"></div>
        <div class="absolute -bottom-40 -right-32 h-96 w-96 rounded-full bg-cyan-400/10 blur-3xl"></div>

        <!-- Main -->
        <div class="relative z-10 flex min-h-screen w-full items-center justify-center px-4 py-8 sm:px-6 lg:px-8">

            <div class="w-full max-w-md">

                <!-- Brand -->
                <div class="mb-8 text-center">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-3 transition hover:opacity-90">
                        <span
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-500 text-lg font-extrabold text-slate-950 shadow-lg shadow-cyan-500/20">
                            HAG
                        </span>

                        <span class="text-left rtl:text-right">
                            <span class="block text-lg font-extrabold leading-tight text-white">
                                HAG Brokers
                            </span>

                            <span class="block text-xs font-medium text-slate-400">
                                {{ __('messages.sea_freight_solutions') }}
                            </span>
                        </span>
                    </a>
                </div>

                <!-- Card -->
                <div
                    class="overflow-hidden rounded-3xl border border-slate-800 bg-white/95 shadow-2xl shadow-black/30 backdrop-blur dark:bg-slate-900/95">

                    <div class="px-6 py-7 sm:px-8 sm:py-8">
                        {{ $slot }}
                    </div>

                </div>

                <!-- Footer -->
                <div class="mt-6 text-center">
                    <div class="flex items-center justify-center gap-2 text-xs text-slate-500">
                        <span class="inline-flex h-2 w-2 rounded-full bg-cyan-500"></span>

                        <span>
                            {{ __('messages.secure') }}
                            {{ __('messages.platform') }}
                        </span>
                    </div>

                    <!-- Language -->
                    <div class="mt-4 flex items-center justify-center gap-2 text-xs">
                        <span class="text-slate-600">
                            {{ __('messages.language') }}:
                        </span>

                        <a href="{{ route('language.switch', 'en') }}"
                            class="font-semibold transition {{ app()->getLocale() === 'en' ? 'text-cyan-400' : 'text-slate-500 hover:text-white' }}">
                            English
                        </a>

                        <span class="text-slate-700">|</span>

                        <a href="{{ route('language.switch', 'ar') }}"
                            class="font-semibold transition {{ app()->getLocale() === 'ar' ? 'text-cyan-400' : 'text-slate-500 hover:text-white' }}">
                            العربية
                        </a>
                    </div>

                    <p class="mt-3 text-xs text-slate-600">
                        © {{ date('Y') }} HAG Brokers. {{ __('messages.sea_freight_solutions') }}.
                    </p>
                </div>

            </div>
        </div>
    </div>
</body>

</html>
