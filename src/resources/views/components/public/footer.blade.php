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
                                {{ (app()->getLocale() === 'ar') ? 'وساطة الشحن البحري' : 'Sea Freight Brokerage' }}
                            </p>
                        </div>

                    </div>

                    <p class="mt-5 text-sm leading-7 text-slate-500">
                        {{ (app()->getLocale() === 'ar') ? 'وساطة وتنسيق للشحن البحري تربط العملاء بخيارات الناقلين والشركاء اللوجستيين المتاحة.' : 'Sea freight brokerage and coordination connecting customers with available carrier and logistics partner options.' }}
                    </p>

                </div>

                <div>

                    <h3 class="font-bold">
                        {{ (app()->getLocale() === 'ar') ? 'المنصة' : 'Platform' }}
                    </h3>

                    <div class="mt-5 space-y-3 text-sm text-slate-500">

                        <a href="{{ route('opportunities.index', ['category' => 'marine']) }}"
                            class="block hover:text-cyan-300">
                            {{ (app()->getLocale() === 'ar') ? 'الخريطة البحرية' : 'Maritime Map' }}
                        </a>

                        <a href="{{ url('/#tracking') }}" class="block hover:text-cyan-300">
                            {{ (app()->getLocale() === 'ar') ? 'تتبع الشحنات' : 'Tracking' }}
                        </a>

                        <a href="{{ url('/#services') }}" class="block hover:text-cyan-300">
                            {{ (app()->getLocale() === 'ar') ? 'الخدمات' : 'Services' }}
                        </a>

                    </div>

                </div>

                <div>

                    <h3 class="font-bold">
                        {{ (app()->getLocale() === 'ar') ? 'الخليج' : 'Gulf' }}
                    </h3>

                    <div class="mt-5 space-y-3 text-sm text-slate-500">

                        <a href="{{ url('/#ports') }}" class="block hover:text-cyan-300">
                            {{ (app()->getLocale() === 'ar') ? 'الموانئ الخليجية' : 'Gulf Ports' }}
                        </a>

                        <a href="{{ route('opportunities.index', ['category' => 'marine']) }}"
                            class="block hover:text-cyan-300">
                            {{ (app()->getLocale() === 'ar') ? 'شبكة السفن' : 'Vessel Network' }}
                        </a>

                        <a href="{{ url('/#partners') }}" class="block hover:text-cyan-300">
                            {{ (app()->getLocale() === 'ar') ? 'الشركاء' : 'Partners' }}
                        </a>

                    </div>

                </div>

                <div>

                    <h3 class="font-bold">
                        {{ (app()->getLocale() === 'ar') ? 'تواصل' : 'Contact' }}
                    </h3>

                    <div class="mt-5 space-y-3 text-sm text-slate-500">

                        <a href="{{ route('freight.sailings') }}"
                            class="block hover:text-cyan-300">{{ __('sailings.title') }}</a>
                        <a href="{{ route('quotations.create') }}" class="block hover:text-cyan-300">
                            {{ (app()->getLocale() === 'ar') ? 'طلب عرض شحن' : 'Request a Quote' }}
                        </a>

                        @guest

                            <a href="{{ route('login') }}" class="block hover:text-cyan-300">
                                {{ (app()->getLocale() === 'ar') ? 'تسجيل الدخول' : 'Login' }}
                            </a>

                        @endguest

                    </div>

                </div>

            </div>

            <div
                class="mt-12 flex flex-col gap-4 border-t border-white/5 pt-7 text-xs text-slate-600 sm:flex-row sm:items-center sm:justify-between">

                <p>
                    © {{ date('Y') }} HAG Brokers.
                    {{ (app()->getLocale() === 'ar') ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}
                </p>

                <p>
                    {{ (app()->getLocale() === 'ar') ? 'وساطة وتنسيق الشحن البحري — حسب توفر الناقل والشريك والبيانات.' : 'Sea freight brokerage & coordination — subject to carrier, partner and data availability.' }}
                </p>

            </div>

        </div>
        @if (!empty($androidRelease))
            <div class="mx-auto max-w-7xl px-5 py-5"><a
                    href="{{ route('app.download', ['lang' => (app()->getLocale() === 'ar') ? 'ar' : 'en']) }}"
                    class="text-cyan-300 underline">{{ __('public_experience.download_android') }} ↓</a></div>
        @endif
    </footer>
