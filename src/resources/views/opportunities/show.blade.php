<x-app-layout>

    @php
        $isAr = app()->getLocale() === 'ar';
        $dir = $isAr ? 'rtl' : 'ltr';

        $opportunityTypes = [
            'business' => $isAr ? 'أعمال' : 'Business',
            'investment' => $isAr ? 'استثمار' : 'Investment',
            'trade' => $isAr ? 'تجارة' : 'Trade',
            'procurement' => $isAr ? 'مشتريات' : 'Procurement',
            'distribution' => $isAr ? 'توزيع' : 'Distribution',
            'logistics' => $isAr ? 'لوجستيات' : 'Logistics',
            'partnership' => $isAr ? 'شراكة' : 'Partnership',
            'other' => $isAr ? 'أخرى' : 'Other',
        ];

        $categories = [
            'import' => $isAr ? 'استيراد' : 'Import',
            'export' => $isAr ? 'تصدير' : 'Export',
            'sea_freight' => $isAr ? 'شحن بحري' : 'Sea Freight',
            'distribution' => $isAr ? 'توزيع' : 'Distribution',
            'manufacturing' => $isAr ? 'تصنيع' : 'Manufacturing',
            'real_estate' => $isAr ? 'عقارات' : 'Real Estate',
            'technology' => $isAr ? 'تقنية' : 'Technology',
            'services' => $isAr ? 'خدمات' : 'Services',
            'other' => $isAr ? 'أخرى' : 'Other',
        ];
    @endphp

    <div dir="{{ $dir }}" class="min-h-screen bg-[#06101d] text-white">

        <main class="mx-auto max-w-6xl px-5 pb-20 pt-32 lg:px-8">

            {{-- Success --}}
            @if (session('success'))
                <div
                    class="mb-6 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 p-4 text-sm text-emerald-300">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('info'))
                <div class="mb-6 rounded-2xl border border-cyan-400/20 bg-cyan-400/10 p-4 text-sm text-cyan-300">
                    {{ session('info') }}
                </div>
            @endif

            {{-- Back --}}
            <a href="{{ route('opportunities.index') }}"
                class="mb-6 inline-flex items-center gap-2 text-sm text-slate-400 transition hover:text-cyan-300">

                <svg class="h-4 w-4 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linecap="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>

                {{ $isAr ? 'العودة إلى الفرص' : 'Back to Opportunities' }}

            </a>

            <div class="grid gap-6 lg:grid-cols-3">

                {{-- Main --}}
                <div class="lg:col-span-2">

                    <section class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl md:p-8">

                        <div class="flex flex-wrap gap-2">

                            <span class="rounded-full bg-cyan-400/10 px-3 py-1.5 text-xs font-semibold text-cyan-300">
                                {{ $opportunityTypes[$opportunity->opportunity_type] ?? $opportunity->opportunity_type }}
                            </span>

                            @if ($opportunity->category)
                                <span class="rounded-full bg-white/5 px-3 py-1.5 text-xs text-slate-400">
                                    {{ $categories[$opportunity->category] ?? $opportunity->category }}
                                </span>
                            @endif

                            <span
                                class="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1.5 text-xs text-emerald-300">
                                ✓ {{ $isAr ? 'تم التحقق' : 'Verified' }}
                            </span>

                        </div>

                        <h1 class="mt-6 text-3xl font-black leading-tight md:text-4xl">
                            {{ $opportunity->title }}
                        </h1>

                        @if ($opportunity->country || $opportunity->city)
                            <div class="mt-5 flex items-center gap-2 text-sm text-slate-400">

                                <svg class="h-5 w-5 text-cyan-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 21a2 2 0 01-2.828 0l-4.243-4.343a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>

                                {{ collect([$opportunity->city, $opportunity->country])->filter()->implode(', ') }}

                            </div>
                        @endif

                        <div class="my-8 h-px bg-white/10"></div>

                        <h2 class="text-lg font-bold">
                            {{ $isAr ? 'عن الفرصة' : 'About This Opportunity' }}
                        </h2>

                        <div class="mt-4 whitespace-pre-line text-sm leading-8 text-slate-300">
                            {{ $opportunity->description }}
                        </div>

                    </section>

                    {{-- Logistics --}}
                    @if (
                        $opportunity->cargo_type ||
                            $opportunity->container_type ||
                            $opportunity->originPort ||
                            $opportunity->destinationPort)

                        <section
                            class="mt-6 rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl md:p-8">

                            <h2 class="text-lg font-bold">
                                {{ $isAr ? 'تفاصيل الشحن واللوجستيات' : 'Shipping & Logistics' }}
                            </h2>

                            <div class="mt-6 grid gap-4 sm:grid-cols-2">

                                @if ($opportunity->cargo_type)
                                    <div class="rounded-2xl border border-white/5 bg-black/10 p-4">
                                        <div class="text-xs text-slate-500">
                                            {{ $isAr ? 'نوع البضاعة' : 'Cargo Type' }}
                                        </div>

                                        <div class="mt-1 font-semibold">
                                            {{ $opportunity->cargo_type }}
                                        </div>
                                    </div>
                                @endif

                                @if ($opportunity->container_type)
                                    <div class="rounded-2xl border border-white/5 bg-black/10 p-4">
                                        <div class="text-xs text-slate-500">
                                            {{ $isAr ? 'نوع الحاوية' : 'Container Type' }}
                                        </div>

                                        <div class="mt-1 font-semibold">
                                            {{ $opportunity->container_type }}
                                        </div>
                                    </div>
                                @endif

                                @if ($opportunity->originPort)
                                    <div class="rounded-2xl border border-white/5 bg-black/10 p-4">
                                        <div class="text-xs text-slate-500">
                                            {{ $isAr ? 'ميناء المغادرة' : 'Origin Port' }}
                                        </div>

                                        <div class="mt-1 font-semibold">
                                            {{ $opportunity->originPort->name }}
                                        </div>
                                    </div>
                                @endif

                                @if ($opportunity->destinationPort)
                                    <div class="rounded-2xl border border-white/5 bg-black/10 p-4">
                                        <div class="text-xs text-slate-500">
                                            {{ $isAr ? 'ميناء الوصول' : 'Destination Port' }}
                                        </div>

                                        <div class="mt-1 font-semibold">
                                            {{ $opportunity->destinationPort->name }}
                                        </div>
                                    </div>
                                @endif

                            </div>

                        </section>

                    @endif

                </div>

                {{-- Sidebar --}}
                <aside class="space-y-6">

                    {{-- Financial --}}
                    <section class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl">

                        <h2 class="font-bold">
                            {{ $isAr ? 'المعلومات المالية' : 'Financial Information' }}
                        </h2>

                        <div class="mt-5 space-y-4">

                            <div>
                                <div class="text-xs text-slate-500">
                                    {{ $isAr ? 'رأس المال المطلوب' : 'Required Capital' }}
                                </div>

                                <div class="mt-1 text-xl font-black text-cyan-300">
                                    @if ($opportunity->required_capital)
                                        {{ number_format((float) $opportunity->required_capital, 2) }}
                                        {{ $opportunity->currency }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>

                            <div class="h-px bg-white/10"></div>

                            <div>
                                <div class="text-xs text-slate-500">
                                    {{ $isAr ? 'الحد الأدنى للاستثمار' : 'Minimum Investment' }}
                                </div>

                                <div class="mt-1 font-bold text-white">
                                    @if ($opportunity->min_investment)
                                        {{ number_format((float) $opportunity->min_investment, 2) }}
                                        {{ $opportunity->currency }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>

                            @if ($opportunity->estimated_value)
                                <div class="h-px bg-white/10"></div>

                                <div>
                                    <div class="text-xs text-slate-500">
                                        {{ $isAr ? 'القيمة التقديرية' : 'Estimated Value' }}
                                    </div>

                                    <div class="mt-1 font-bold">
                                        {{ number_format((float) $opportunity->estimated_value, 2) }}
                                        {{ $opportunity->currency }}
                                    </div>
                                </div>
                            @endif

                        </div>

                    </section>

                    {{-- Commission --}}
                    <section class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl">

                        <h2 class="font-bold">
                            {{ $isAr ? 'نموذج العمولة' : 'Commission Model' }}
                        </h2>

                        <div class="mt-4 rounded-2xl bg-cyan-400/5 p-4">

                            @if ($opportunity->commission_rate !== null)

                                <div class="text-2xl font-black text-cyan-300">
                                    {{ number_format((float) $opportunity->commission_rate, 2) }}

                                    @if ($opportunity->commission_type === 'percentage')
                                        %
                                    @else
                                        {{ $opportunity->currency }}
                                    @endif
                                </div>
                            @else
                                <div class="text-slate-400">
                                    {{ $isAr ? 'يتم الاتفاق عليها' : 'To be agreed' }}
                                </div>
                            @endif

                        </div>

                    </section>

                    {{-- Interest --}}
                    @auth
                        <section class="rounded-3xl border border-cyan-400/20 bg-cyan-400/5 p-6">

                            <h2 class="font-bold text-cyan-300">
                                {{ $isAr ? 'مهتم بهذه الفرصة؟' : 'Interested in this opportunity?' }}
                            </h2>

                            <p class="mt-3 text-sm leading-7 text-slate-400">
                                {{ $isAr
                                    ? 'يمكنك الآن إبداء اهتمامك وسيقوم فريق HAG Brokers بالتواصل معك.'
                                    : 'Express your interest and the HAG Brokers team can follow up with you.' }}
                            </p>

                            <a href="{{ route('opportunities.interest', $opportunity) }}"
                                class="mt-5 block w-full rounded-xl bg-cyan-400 px-5 py-3 text-center text-sm font-bold text-slate-950 transition hover:bg-cyan-300">

                                {{ $isAr ? 'أنا مهتم' : "I'm Interested" }}

                            </a>

                        </section>
                    @else
                        <section class="rounded-3xl border border-white/10 bg-white/[0.03] p-6">

                            <h2 class="font-bold">
                                {{ $isAr ? 'هل أنت مهتم؟' : 'Interested?' }}
                            </h2>

                            <p class="mt-3 text-sm leading-7 text-slate-400">
                                {{ $isAr
                                    ? 'سجل الدخول للتعبير عن اهتمامك بهذه الفرصة.'
                                    : 'Log in to express your interest in this opportunity.' }}
                            </p>

                            <a href="{{ route('login') }}"
                                class="mt-5 block w-full rounded-xl bg-cyan-400 px-5 py-3 text-center text-sm font-bold text-slate-950 transition hover:bg-cyan-300">
                                {{ $isAr ? 'تسجيل الدخول' : 'Login' }}
                            </a>

                        </section>

                    @endauth

                </aside>

            </div>

        </main>

    </div>

</x-app-layout>
