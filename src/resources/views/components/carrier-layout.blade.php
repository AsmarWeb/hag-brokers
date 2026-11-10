@props(['workspace' => false])

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('carrier_partner.portal') }} — HAG Brokers</title>
    <script>
        (() => {
            let theme;
            try { theme = localStorage.getItem('hag-carrier-theme'); } catch {}
            const dark = theme === 'dark' || (theme !== 'light' && matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
        })();
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900|noto-sans-arabic:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body id="carrier-portal" class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <a href="#carrier-content" class="hag-skip-link">{{ __('carrier_partner.skip') }}</a>
    <header class="border-b border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-6">
            <div class="flex items-center gap-3">
                <span aria-hidden="true" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-900 text-xs font-black text-cyan-200 dark:bg-cyan-950">HAG</span>
                <div>
                    <p class="font-black leading-tight">HAG Brokers</p>
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ __('carrier_partner.portal') }}</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-sm font-semibold">
                @foreach(['ar' => 'العربية', 'en' => 'English'] as $locale => $label)
                    <a href="{{ request()->fullUrlWithQuery(['lang' => $locale]) }}" lang="{{ $locale }}"
                        @if(app()->isLocale($locale)) aria-current="true" @endif
                        class="rounded-lg px-2 py-2 text-slate-700 hover:bg-slate-100 aria-[current=true]:bg-cyan-100 dark:text-slate-200 dark:hover:bg-slate-800 dark:aria-[current=true]:bg-cyan-950">{{ $label }}</a>
                @endforeach
                <button id="carrier-theme-toggle" type="button" hidden aria-pressed="false" class="rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-600">
                    {{ __('carrier_partner.dark_mode') }}
                </button>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg border border-slate-300 px-3 py-2 dark:border-slate-600">{{ __('carrier_partner.logout') }}</button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    @if($workspace)
        <div class="mx-auto grid max-w-7xl items-start gap-4 px-4 py-6 sm:px-6 lg:grid-cols-[14rem_minmax(0,1fr)]">
            <nav aria-label="{{ __('carrier_partner.portal') }}" class="hag-broker-card flex flex-wrap gap-2 lg:sticky lg:top-4 lg:flex-col">
                @foreach(['fleet' => __('carrier_partner.fleet'), 'vessel-requests' => __('carrier_vessels.history'), 'profile' => __('carrier_partner.profile'), 'api' => __('carrier_partner.api')] as $section => $label)
                    <a href="#carrier-{{ $section }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-cyan-50 dark:text-slate-200 dark:hover:bg-cyan-950">{{ $label }}</a>
                @endforeach
            </nav>
            <main id="carrier-content" tabindex="-1" class="min-w-0">{{ $slot }}</main>
        </div>
    @else
        <main id="carrier-content" tabindex="-1" class="mx-auto max-w-2xl px-4 py-10 sm:px-6">{{ $slot }}</main>
    @endif

    <script>
        (() => {
            const button = document.getElementById('carrier-theme-toggle');
            const root = document.documentElement;
            const sync = () => button.setAttribute('aria-pressed', String(root.classList.contains('dark')));
            button.addEventListener('click', () => {
                const dark = root.classList.toggle('dark');
                const theme = dark ? 'dark' : 'light';
                root.style.colorScheme = theme;
                try { localStorage.setItem('hag-carrier-theme', theme); } catch {}
                sync();
            });
            sync();
            button.hidden = false;
        })();
    </script>
</body>
</html>
