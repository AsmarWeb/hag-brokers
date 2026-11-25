    <div class="market-header">
        <header id="navbar"
            class="fixed inset-x-0 top-0 z-50 border-b border-white/5 bg-[#07111f]/85 backdrop-blur-xl">

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
                            {{ app()->getLocale() === 'ar' ? 'وساطة الشحن البحري' : 'Sea Freight Brokerage' }}
                        </div>
                    </div>
                </a>

                <nav class="hidden items-center gap-7 lg:flex">
                    <a href="{{ url('/#tracking') }}" class="text-sm text-slate-300 transition hover:text-cyan-300">
                        {{ app()->getLocale() === 'ar' ? 'تتبع الشحنات' : 'Tracking' }}
                    </a>
                    <a href="{{ url('/#services') }}" class="text-sm text-slate-300 transition hover:text-cyan-300">
                        {{ app()->getLocale() === 'ar' ? 'الخدمات' : 'Services' }}
                    </a>
                    <a href="{{ url('/#ports') }}" class="text-sm text-slate-300 transition hover:text-cyan-300">
                        {{ app()->getLocale() === 'ar' ? 'الموانئ' : 'Ports' }}
                    </a>
                    <a href="{{ url('/#partners') }}" class="text-sm text-slate-300 transition hover:text-cyan-300">
                        {{ app()->getLocale() === 'ar' ? 'الشركاء' : 'Partners' }}
                    </a>
                </nav>

                <div class="hidden items-center gap-3 lg:flex">

                    <button id="theme-toggle" type="button" class="hag-theme-toggle"
                        aria-label="{{ app()->getLocale() === 'ar' ? 'تغيير المظهر' : 'Toggle color theme' }}"
                        aria-pressed="false"
                        title="{{ app()->getLocale() === 'ar' ? 'الوضع الليلي والنهاري' : 'Dark / light mode' }}">
                        <svg class="hag-icon-sun h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" aria-hidden="true">
                            <circle cx="12" cy="12" r="4" />
                            <path stroke-linecap="round"
                                d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" />
                        </svg>
                        <svg class="hag-icon-moon hidden h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5 8.5 8.5 0 1 0 20.5 14.2Z" />
                        </svg>
                    </button>

                    <div class="relative" id="language-menu">
                        <button id="language-menu-button" type="button" aria-controls="language-dropdown"
                            aria-expanded="false"
                            class="flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-slate-200 transition hover:bg-white/10">
                            <span>🌐</span>
                            <span>{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="language-dropdown"
                            class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 hidden w-40 overflow-hidden rounded-2xl border border-white/10 bg-[#0b1b2e] shadow-2xl">

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

                        <a href="{{ route('quotations.create') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-cyan-400 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-cyan-300">
                            <span>{{ app()->getLocale() === 'ar' ? 'اطلب عرض شحن' : 'Request Freight Quote' }}</span>
                        </a>

                        {{-- Dashboard --}}
                        <a href="{{ url('/dashboard') }}"
                            class="rounded-xl border border-white/10 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-white/5">
                            {{ app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard' }}
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="rounded-xl border border-white/10 px-4 py-2.5 text-sm font-medium text-white hover:bg-white/5">
                            {{ app()->getLocale() === 'ar' ? 'تسجيل الدخول' : 'Login' }}
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-bold text-slate-950 hover:bg-cyan-300">
                                {{ app()->getLocale() === 'ar' ? 'إنشاء حساب' : 'Create Account' }}
                            </a>
                        @endif

                    @endauth

                </div>

                <button id="mobile-menu-button" type="button"
                    class="flex h-11 w-11 items-center justify-center rounded-xl border border-white/10 bg-white/5 lg:hidden"
                    aria-controls="mobile-menu" aria-expanded="false"
                    aria-label="{{ app()->getLocale() === 'ar' ? 'القائمة الرئيسية' : 'Main menu' }}">

                    <svg id="menu-open-icon" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>

                    <svg id="menu-close-icon" class="hidden h-6 w-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div id="mobile-menu" class="hidden border-t border-white/5 bg-[#07111f]">
                <div class="mx-auto max-w-7xl space-y-2 px-5 py-6">

                    <a href="{{ url('/#quote-request') }}"
                        class="mobile-link block rounded-xl px-4 py-3 text-slate-200 hover:bg-white/5">
                        {{ app()->getLocale() === 'ar' ? 'طلبات الشحن' : 'Cargo Requests' }}
                    </a>

                    <a href="{{ url('/#tracking') }}"
                        class="mobile-link block rounded-xl px-4 py-3 text-slate-200 hover:bg-white/5">
                        {{ app()->getLocale() === 'ar' ? 'تتبع الشحنات' : 'Tracking' }}
                    </a>

                    <a href="{{ url('/#services') }}"
                        class="mobile-link block rounded-xl px-4 py-3 text-slate-200 hover:bg-white/5">
                        {{ app()->getLocale() === 'ar' ? 'الخدمات' : 'Services' }}
                    </a>

                    <a href="{{ url('/#ports') }}"
                        class="mobile-link block rounded-xl px-4 py-3 text-slate-200 hover:bg-white/5">
                        {{ app()->getLocale() === 'ar' ? 'الموانئ' : 'Ports' }}
                    </a>

                    <a href="{{ url('/#partners') }}"
                        class="mobile-link block rounded-xl px-4 py-3 text-slate-200 hover:bg-white/5">
                        {{ app()->getLocale() === 'ar' ? 'الشركاء' : 'Partners' }}
                    </a>

                    <button id="theme-toggle-mobile" type="button" class="hag-theme-toggle w-full"
                        aria-pressed="false">
                        <svg class="hag-icon-sun h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" aria-hidden="true">
                            <circle cx="12" cy="12" r="4" />
                            <path stroke-linecap="round" d="M12 2v2m0 16v2M2 12h2m16 0h2" />
                        </svg>
                        <svg class="hag-icon-moon hidden h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5 8.5 8.5 0 1 0 20.5 14.2Z" />
                        </svg>
                        <span
                            class="hag-theme-label">{{ app()->getLocale() === 'ar' ? 'تغيير المظهر' : 'Toggle theme' }}</span>
                    </button>

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

                    <div class="space-y-2 pt-2">

                        @auth

                            <a href="{{ route('quotations.create') }}"
                                class="flex items-center justify-center rounded-xl bg-cyan-400 px-4 py-3 text-center text-sm font-bold text-slate-950">
                                {{ app()->getLocale() === 'ar' ? 'اطلب عرض شحن' : 'Request Freight Quote' }}
                            </a>

                            {{-- Dashboard --}}
                            <a href="{{ url('/dashboard') }}"
                                class="block rounded-xl border border-white/10 px-4 py-3 text-center text-sm text-white">
                                {{ app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard' }}
                            </a>
                        @else
                            <div class="grid grid-cols-2 gap-2">

                                <a href="{{ route('login') }}"
                                    class="rounded-xl border border-white/10 px-4 py-3 text-center text-sm text-white">
                                    {{ app()->getLocale() === 'ar' ? 'دخول' : 'Login' }}
                                </a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}"
                                        class="rounded-xl bg-cyan-400 px-4 py-3 text-center text-sm font-bold text-slate-950">
                                        {{ app()->getLocale() === 'ar' ? 'حساب جديد' : 'Register' }}
                                    </a>
                                @endif

                            </div>

                        @endauth

                    </div>

                </div>
            </div>
        </header>
    </div>
