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

        {{-- Background --}}
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute -left-32 top-20 h-96 w-96 rounded-full bg-cyan-500/10 blur-3xl"></div>
            <div class="absolute -right-32 bottom-20 h-96 w-96 rounded-full bg-blue-500/10 blur-3xl"></div>
        </div>

        <main class="relative mx-auto max-w-7xl px-5 pb-20 pt-32 lg:px-8">

            {{-- Header --}}
            <div class="mb-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">

                <div>
                    <div
                        class="mb-3 inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1.5 text-xs font-semibold text-cyan-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-cyan-300"></span>

                        {{ $isAr ? 'سوق الفرص' : 'Opportunity Marketplace' }}
                    </div>

                    <h1 class="text-3xl font-black tracking-tight md:text-4xl">
                        {{ $isAr ? 'اكتشف الفرص التجارية' : 'Discover Business Opportunities' }}
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-400">
                        {{ $isAr
                            ? 'اكتشف فرصاً تجارية واستثمارية وشحنية تم التحقق منها ومراجعتها من فريق HAG Brokers.'
                            : 'Discover verified business, investment and logistics opportunities reviewed by HAG Brokers.' }}
                    </p>
                </div>

                @auth
                    <a href="{{ route('opportunities.create') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-400 px-5 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-cyan-500/10 transition hover:bg-cyan-300">

                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>

                        {{ $isAr ? 'إضافة فرصة' : 'Add Opportunity' }}
                    </a>
                @endauth

            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('opportunities.index') }}"
                class="mb-10 rounded-3xl border border-white/10 bg-white/[0.03] p-5 backdrop-blur-xl">

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">

                    {{-- Search --}}
                    <div class="lg:col-span-2">
                        <label class="mb-2 block text-xs font-semibold text-slate-400">
                            {{ $isAr ? 'بحث' : 'Search' }}
                        </label>

                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="{{ $isAr ? 'ابحث عن فرصة...' : 'Search opportunities...' }}"
                            class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/30">
                    </div>

                    {{-- Type --}}
                    <div>
                        <label class="mb-2 block text-xs font-semibold text-slate-400">
                            {{ $isAr ? 'نوع الفرصة' : 'Opportunity Type' }}
                        </label>

                        <select name="type"
                            class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none focus:border-cyan-400/50">

                            <option value="">
                                {{ $isAr ? 'الكل' : 'All Types' }}
                            </option>

                            @foreach ($opportunityTypes as $value => $label)
                                <option value="{{ $value }}" @selected(request('type') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="mb-2 block text-xs font-semibold text-slate-400">
                            {{ $isAr ? 'الفئة' : 'Category' }}
                        </label>

                        <select name="category"
                            class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none focus:border-cyan-400/50">

                            <option value="">
                                {{ $isAr ? 'كل الفئات' : 'All Categories' }}
                            </option>

                            @foreach ($categories as $value => $label)
                                <option value="{{ $value }}" @selected(request('category') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                </div>

                <div class="mt-4 flex justify-end gap-3">

                    <a href="{{ route('opportunities.index') }}"
                        class="rounded-xl border border-white/10 px-5 py-3 text-sm text-slate-300 transition hover:bg-white/5">
                        {{ $isAr ? 'إعادة تعيين' : 'Reset' }}
                    </a>

                    <button type="submit"
                        class="rounded-xl bg-white/10 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/15">
                        {{ $isAr ? 'بحث' : 'Search' }}
                    </button>

                </div>

            </form>

            {{-- Results --}}
            @if ($opportunities->count())

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">

                    @foreach ($opportunities as $opportunity)
                        <article
                            class="group overflow-hidden rounded-3xl border border-white/10 bg-white/[0.03] backdrop-blur-xl transition hover:-translate-y-1 hover:border-cyan-400/30 hover:bg-white/[0.05]">

                            <div class="p-6">

                                {{-- Badges --}}
                                <div class="mb-5 flex flex-wrap items-center gap-2">

                                    <span
                                        class="rounded-full bg-cyan-400/10 px-3 py-1 text-[11px] font-semibold text-cyan-300">
                                        {{ $opportunityTypes[$opportunity->opportunity_type] ?? $opportunity->opportunity_type }}
                                    </span>

                                    @if ($opportunity->category)
                                        <span class="rounded-full bg-white/5 px-3 py-1 text-[11px] text-slate-400">
                                            {{ $categories[$opportunity->category] ?? $opportunity->category }}
                                        </span>
                                    @endif

                                    <span
                                        class="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-[11px] text-emerald-300">
                                        {{ $isAr ? 'تم التحقق' : 'Verified' }}
                                    </span>

                                </div>

                                {{-- Title --}}
                                <h2 class="text-xl font-bold leading-8 text-white transition group-hover:text-cyan-300">
                                    {{ $opportunity->title }}
                                </h2>

                                {{-- Description --}}
                                <p class="mt-3 line-clamp-3 text-sm leading-7 text-slate-400">
                                    {{ $opportunity->description }}
                                </p>

                                {{-- Location --}}
                                @if ($opportunity->country || $opportunity->city)
                                    <div class="mt-5 flex items-center gap-2 text-sm text-slate-400">

                                        <svg class="h-4 w-4 text-cyan-300" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 21a2 2 0 01-2.828 0l-4.243-4.343a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>

                                        {{ collect([$opportunity->city, $opportunity->country])->filter()->implode(', ') }}

                                    </div>
                                @endif

                                {{-- Financial --}}
                                <div class="mt-6 grid grid-cols-2 gap-3">

                                    <div class="rounded-2xl border border-white/5 bg-black/10 p-4">
                                        <div class="text-[11px] text-slate-500">
                                            {{ $isAr ? 'رأس المال المطلوب' : 'Required Capital' }}
                                        </div>

                                        <div class="mt-1 text-sm font-bold text-white">
                                            @if ($opportunity->required_capital)
                                                {{ number_format((float) $opportunity->required_capital, 2) }}
                                                {{ $opportunity->currency }}
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border border-white/5 bg-black/10 p-4">
                                        <div class="text-[11px] text-slate-500">
                                            {{ $isAr ? 'الحد الأدنى' : 'Minimum Investment' }}
                                        </div>

                                        <div class="mt-1 text-sm font-bold text-white">
                                            @if ($opportunity->min_investment)
                                                {{ number_format((float) $opportunity->min_investment, 2) }}
                                                {{ $opportunity->currency }}
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </div>

                                </div>

                                {{-- Details --}}
                                <a href="{{ route('opportunities.show', $opportunity) }}"
                                    class="mt-6 flex items-center justify-center gap-2 rounded-xl border border-cyan-400/20 bg-cyan-400/5 px-4 py-3 text-sm font-semibold text-cyan-300 transition hover:bg-cyan-400/10">

                                    {{ $isAr ? 'عرض التفاصيل' : 'View Details' }}

                                    <svg class="h-4 w-4 {{ $isAr ? 'rotate-180' : '' }}" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>

                                </a>

                            </div>

                        </article>
                    @endforeach

                </div>

                {{-- Pagination --}}
                <div class="mt-10">
                    {{ $opportunities->links() }}
                </div>
            @else
                <div class="rounded-3xl border border-dashed border-white/10 bg-white/[0.02] px-6 py-20 text-center">

                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-cyan-400/10">
                        <svg class="h-8 w-8 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 6v12m6-6H6" />
                        </svg>
                    </div>

                    <h2 class="mt-5 text-xl font-bold">
                        {{ $isAr ? 'لا توجد فرص حالياً' : 'No Opportunities Found' }}
                    </h2>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-slate-500">
                        {{ $isAr
                            ? 'لا توجد فرص منشورة تطابق البحث الحالي.'
                            : 'There are no published opportunities matching your search.' }}
                    </p>

                </div>

            @endif

        </main>

    </div>

</x-app-layout>
