<x-app-layout>

    @php
        $isAr = app()->getLocale() === 'ar';
        $dir = $isAr ? 'rtl' : 'ltr';

        /*
        |--------------------------------------------------------------------------
        | Opportunity Types
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Active Filters
        |--------------------------------------------------------------------------
        */

        $hasFilters =
            request()->filled('search') ||
            request()->filled('type') ||
            request()->filled('category') ||
            request()->filled('country');

        /*
        |--------------------------------------------------------------------------
        | Helper for Media URLs
        |--------------------------------------------------------------------------
        */

        $mediaUrl = function ($path) {
            if (!$path) {
                return null;
            }

            if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '//'])) {
                return $path;
            }

            return asset('storage/' . ltrim($path, '/'));
        };
    @endphp


    <div dir="{{ $dir }}" class="min-h-screen overflow-hidden bg-[#06101d] text-white">

        {{-- =========================================================
        BACKGROUND
        ========================================================== --}}

        <div class="pointer-events-none fixed inset-0 overflow-hidden">

            <div class="absolute -left-32 top-20 h-96 w-96 rounded-full bg-cyan-500/10 blur-3xl">
            </div>

            <div class="absolute -right-32 bottom-20 h-96 w-96 rounded-full bg-blue-500/10 blur-3xl">
            </div>

            <div
                class="absolute left-1/2 top-1/2 h-80 w-80 -translate-x-1/2 -translate-y-1/2 rounded-full bg-cyan-400/[0.03] blur-3xl">
            </div>

        </div>


        <main class="relative mx-auto max-w-7xl px-4 pb-24 pt-28 sm:px-6 lg:px-8">

            {{-- =====================================================
            HERO
            ====================================================== --}}

            <section class="mb-10">

                <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">

                    <div>

                        {{-- Badge --}}

                        <div
                            class="mb-5 inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3.5 py-2 text-xs font-bold text-cyan-300">

                            <span
                                class="h-1.5 w-1.5 rounded-full bg-cyan-300 shadow-[0_0_10px_rgba(34,211,238,0.8)]"></span>

                            {{ $isAr ? 'سوق الفرص' : 'Opportunity Marketplace' }}

                        </div>


                        {{-- Heading --}}

                        <h1 class="max-w-4xl text-3xl font-black leading-tight tracking-tight sm:text-4xl lg:text-5xl">
                            {{ $isAr ? 'اكتشف فرصاً يمكن أن تتحول إلى صفقات' : 'Discover Opportunities That Can Become Deals' }}
                        </h1>


                        {{-- Description --}}

                        <p class="mt-5 max-w-3xl text-sm leading-7 text-slate-400 sm:text-base">
                            {{ $isAr
                                ? 'تصفح فرصاً تجارية واستثمارية ولوجستية، تعرّف على التفاصيل، واستكشف الفرص التي يمكن أن تتطور إلى صفقات عبر شبكة HAG Brokers.'
                                : 'Explore business, investment and logistics opportunities, review the details, and discover opportunities that can develop into deals through the HAG Brokers network.' }}
                        </p>


                        {{-- Quick Stats --}}

                        <div class="mt-7 flex flex-wrap gap-3">

                            <div class="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 backdrop-blur-xl">

                                <div class="text-lg font-black text-white">
                                    {{ number_format($opportunities->total()) }}
                                </div>

                                <div class="mt-0.5 text-[11px] text-slate-500">
                                    {{ $isAr ? 'فرصة متاحة' : 'Available Opportunities' }}
                                </div>

                            </div>


                            <div class="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 backdrop-blur-xl">

                                <div class="text-lg font-black text-cyan-300">
                                    {{ count($categories) }}
                                </div>

                                <div class="mt-0.5 text-[11px] text-slate-500">
                                    {{ $isAr ? 'فئات للفرص' : 'Opportunity Categories' }}
                                </div>

                            </div>


                            <div
                                class="rounded-2xl border border-emerald-400/10 bg-emerald-400/[0.04] px-4 py-3 backdrop-blur-xl">

                                <div class="flex items-center gap-2 text-sm font-black text-emerald-300">

                                    <span
                                        class="h-2 w-2 rounded-full bg-emerald-300 shadow-[0_0_10px_rgba(52,211,153,0.8)]"></span>

                                    {{ $isAr ? 'موثقة' : 'Verified' }}

                                </div>

                                <div class="mt-0.5 text-[11px] text-slate-500">
                                    {{ $isAr ? 'فرص تمت مراجعتها' : 'Reviewed Opportunities' }}
                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Submit Opportunity --}}

                    @auth

                        <a href="{{ route('opportunities.create') }}"
                            class="group inline-flex items-center justify-center gap-3 rounded-2xl bg-cyan-400 px-6 py-4 text-sm font-black text-slate-950 shadow-xl shadow-cyan-500/10 transition duration-300 hover:-translate-y-1 hover:bg-cyan-300">

                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-950/10">

                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>

                            </span>

                            <span>
                                {{ $isAr ? 'أضف فرصة جديدة' : 'Submit an Opportunity' }}
                            </span>

                        </a>

                    @endauth

                </div>

            </section>


            {{-- =====================================================
            FILTERS
            ====================================================== --}}

            <section
                class="mb-10 rounded-3xl border border-white/10 bg-white/[0.03] p-5 shadow-2xl shadow-black/10 backdrop-blur-xl sm:p-6">

                <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                    <div>

                        <div class="flex items-center gap-2">

                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-cyan-400/10">

                                <svg class="h-4 w-4 text-cyan-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z" />
                                </svg>

                            </span>

                            <h2 class="text-base font-bold">
                                {{ $isAr ? 'ابحث عن الفرصة المناسبة' : 'Find the Right Opportunity' }}
                            </h2>

                        </div>

                        <p class="mt-2 text-xs leading-6 text-slate-500">
                            {{ $isAr
                                ? 'استخدم البحث والتصفية للوصول إلى الفرص المناسبة بسرعة.'
                                : 'Use search and filters to quickly find relevant opportunities.' }}
                        </p>

                    </div>


                    @if ($hasFilters)
                        <a href="{{ route('opportunities.index') }}"
                            class="inline-flex items-center gap-2 self-start rounded-xl border border-white/10 px-3.5 py-2 text-xs font-bold text-slate-400 transition hover:border-cyan-400/20 hover:bg-cyan-400/5 hover:text-cyan-300">

                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>

                            {{ $isAr ? 'مسح الفلاتر' : 'Clear filters' }}

                        </a>
                    @endif

                </div>


                <form method="GET" action="{{ route('opportunities.index') }}">

                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-6">

                        {{-- Search --}}

                        <div class="lg:col-span-2">

                            <label class="mb-2 block text-xs font-semibold text-slate-400">
                                {{ $isAr ? 'البحث' : 'Search' }}
                            </label>

                            <div class="relative">

                                <svg class="pointer-events-none absolute start-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-600"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z" />
                                </svg>

                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="{{ $isAr ? 'اسم الفرصة، المنتج، الخدمة...' : 'Opportunity, product, service...' }}"
                                    class="w-full rounded-xl border border-white/10 bg-[#07111f] py-3.5 ps-11 pe-4 text-sm text-white outline-none placeholder:text-slate-600 transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/10">

                            </div>

                        </div>


                        {{-- Type --}}

                        <div>

                            <label class="mb-2 block text-xs font-semibold text-slate-400">
                                {{ $isAr ? 'نوع الفرصة' : 'Opportunity Type' }}
                            </label>

                            <select name="type"
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/10">

                                <option value="">
                                    {{ $isAr ? 'كل الأنواع' : 'All Types' }}
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
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3.5 text-sm text-white outline-none transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/10">

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


                        {{-- Country --}}

                        <div>

                            <label class="mb-2 block text-xs font-semibold text-slate-400">
                                {{ $isAr ? 'الدولة' : 'Country' }}
                            </label>

                            <input type="text" name="country" value="{{ request('country') }}"
                                placeholder="{{ $isAr ? 'مثال: الإمارات' : 'e.g. UAE' }}"
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3.5 text-sm text-white outline-none placeholder:text-slate-600 transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/10">

                        </div>


                        {{-- Search Button --}}

                        <div class="flex items-end">

                            <button type="submit"
                                class="group flex w-full items-center justify-center gap-2 rounded-xl bg-white/10 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-cyan-400 hover:text-slate-950">

                                <svg class="h-4 w-4 transition group-hover:scale-110" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z" />
                                </svg>

                                {{ $isAr ? 'بحث' : 'Search' }}

                            </button>

                        </div>

                    </div>

                </form>

            </section>


            {{-- =====================================================
            RESULTS HEADER
            ====================================================== --}}

            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <h2 class="text-xl font-black">
                        {{ $isAr ? 'الفرص المتاحة' : 'Available Opportunities' }}
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">

                        @if ($opportunities->total())
                            {{ $isAr
                                ? 'عرض ' . $opportunities->firstItem() . '–' . $opportunities->lastItem() . ' من ' . $opportunities->total()
                                : 'Showing ' . $opportunities->firstItem() . '–' . $opportunities->lastItem() . ' of ' . $opportunities->total() }}
                        @else
                            {{ $isAr ? 'لا توجد نتائج' : 'No results' }}
                        @endif

                    </p>

                </div>


                @if ($opportunities->count())
                    <div
                        class="inline-flex items-center gap-2 self-start rounded-full border border-emerald-400/10 bg-emerald-400/5 px-3.5 py-2 text-[11px] font-semibold text-emerald-300">

                        <span
                            class="h-1.5 w-1.5 rounded-full bg-emerald-300 shadow-[0_0_8px_rgba(52,211,153,0.7)]"></span>

                        {{ $isAr ? 'فرص تمت مراجعتها' : 'Reviewed opportunities' }}

                    </div>
                @endif

            </div>


            {{-- =====================================================
            RESULTS
            ====================================================== --}}

            @if ($opportunities->count())

                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">

                    @foreach ($opportunities as $opportunity)
                        @php
                            /*
                            |--------------------------------------------------------------------------
                            | Labels
                            |--------------------------------------------------------------------------
                            */

                            $typeLabel =
                                $opportunityTypes[$opportunity->opportunity_type] ?? $opportunity->opportunity_type;

                            $categoryLabel = $categories[$opportunity->category] ?? $opportunity->category;

                            /*
                            |--------------------------------------------------------------------------
                            | First Media
                            |--------------------------------------------------------------------------
                            */

                            $media = null;

                            if (
                                isset($opportunity->media) &&
                                $opportunity->media instanceof \Illuminate\Support\Collection
                            ) {
                                $media = $opportunity->media->first();
                            }

                            $mediaPath = $media?->path;

                            $mediaType = $media?->mime_type ?? ($media?->type ?? null);

                            $mediaSrc = $mediaUrl($mediaPath);

                            $isVideo = $mediaType && \Illuminate\Support\Str::startsWith($mediaType, 'video/');

                            $isImage = $mediaType && \Illuminate\Support\Str::startsWith($mediaType, 'image/');

                            /*
                            |--------------------------------------------------------------------------
                            | Location
                            |--------------------------------------------------------------------------
                            */

                            $locationParts = collect([$opportunity->city, $opportunity->country])->filter();

                            /*
                            |--------------------------------------------------------------------------
                            | Port Route
                            |--------------------------------------------------------------------------
                            */

                            $originPort =
                                $opportunity->originPort?->name ?? ($opportunity->originPort?->port_name ?? null);

                            $destinationPort =
                                $opportunity->destinationPort?->name ??
                                ($opportunity->destinationPort?->port_name ?? null);

                            /*
                            |--------------------------------------------------------------------------
                            | Financial Values
                            |--------------------------------------------------------------------------
                            */

                            $requiredCapital =
                                $opportunity->required_capital !== null
                                    ? number_format((float) $opportunity->required_capital, 2)
                                    : null;

                            $minInvestment =
                                $opportunity->min_investment !== null
                                    ? number_format((float) $opportunity->min_investment, 2)
                                    : null;
                        @endphp


                        <article
                            class="group relative flex h-full flex-col overflow-hidden rounded-3xl border border-white/10 bg-white/[0.03] shadow-xl shadow-black/10 backdrop-blur-xl transition duration-300 hover:-translate-y-1 hover:border-cyan-400/30 hover:bg-white/[0.05]">

                            {{-- =================================================
                            MEDIA
                            ================================================== --}}

                            <div class="relative h-56 shrink-0 overflow-hidden bg-[#07111f]">

                                @if ($isImage && $mediaSrc)
                                    <img src="{{ $mediaSrc }}" alt="{{ $opportunity->title }}" loading="lazy"
                                        class="h-full w-full object-cover transition duration-700 group-hover:scale-105">

                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-[#06101d] via-transparent to-transparent opacity-80">
                                    </div>
                                @elseif ($isVideo && $mediaSrc)
                                    <video
                                        class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                        muted loop autoplay playsinline preload="metadata">

                                        <source src="{{ $mediaSrc }}" type="{{ $mediaType }}">

                                    </video>

                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-[#06101d] via-transparent to-transparent opacity-80">
                                    </div>
                                @else
                                    {{-- Placeholder --}}

                                    <div class="absolute inset-0">

                                        <div
                                            class="absolute -left-10 top-8 h-40 w-40 rounded-full bg-cyan-400/10 blur-3xl">
                                        </div>

                                        <div
                                            class="absolute -right-10 bottom-0 h-48 w-48 rounded-full bg-blue-500/10 blur-3xl">
                                        </div>

                                    </div>

                                    <div class="relative flex h-full items-center justify-center">

                                        <div
                                            class="flex h-20 w-20 items-center justify-center rounded-3xl border border-cyan-400/15 bg-cyan-400/5">

                                            <svg class="h-9 w-9 text-cyan-300/80" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.4" d="M12 6v12m6-6H6" />
                                            </svg>

                                        </div>

                                    </div>
                                @endif


                                {{-- Type Badge --}}

                                <div class="absolute start-4 top-4">

                                    <span
                                        class="inline-flex rounded-full border border-cyan-300/20 bg-slate-950/75 px-3 py-1.5 text-[11px] font-bold text-cyan-300 shadow-lg backdrop-blur-md">
                                        {{ $typeLabel }}
                                    </span>

                                </div>


                                {{-- Verified Badge --}}

                                <div class="absolute end-4 top-4">

                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full border border-emerald-400/20 bg-slate-950/75 px-3 py-1.5 text-[11px] font-semibold text-emerald-300 shadow-lg backdrop-blur-md">

                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m5.5 2a8.5 8.5 0 11-17 0 8.5 8.5 0 0117 0z" />
                                        </svg>

                                        {{ $isAr ? 'موثقة' : 'Verified' }}

                                    </span>

                                </div>


                                {{-- Video Badge --}}

                                @if ($isVideo)
                                    <div class="absolute bottom-4 start-4">

                                        <span
                                            class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-slate-950/80 px-3 py-1.5 text-[11px] font-bold text-white backdrop-blur-md">

                                            <svg class="h-3.5 w-3.5 text-cyan-300" fill="currentColor"
                                                viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z" />
                                            </svg>

                                            {{ $isAr ? 'فيديو' : 'Video' }}

                                        </span>

                                    </div>
                                @endif

                            </div>


                            {{-- =================================================
                            CARD CONTENT
                            ================================================== --}}

                            <div class="flex flex-1 flex-col p-5 sm:p-6">

                                {{-- Category --}}

                                @if ($categoryLabel)
                                    <div class="mb-3">

                                        <span
                                            class="inline-flex rounded-full border border-white/5 bg-white/[0.03] px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                            {{ $categoryLabel }}
                                        </span>

                                    </div>
                                @endif


                                {{-- Title --}}

                                <h2
                                    class="line-clamp-2 min-h-[4rem] text-xl font-black leading-8 text-white transition group-hover:text-cyan-300">
                                    {{ $opportunity->title }}
                                </h2>


                                {{-- Description --}}

                                <p class="mt-3 line-clamp-3 min-h-[5.25rem] text-sm leading-7 text-slate-400">
                                    {{ $opportunity->description }}
                                </p>


                                {{-- Location --}}

                                @if ($locationParts->isNotEmpty())
                                    <div class="mt-5 flex items-center gap-2 text-xs text-slate-400">

                                        <span
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10">

                                            <svg class="h-4 w-4 text-cyan-300" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 21a2 2 0 01-2.828 0l-4.243-4.343a8 8 0 1111.314 0z" />

                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>

                                        </span>

                                        <span class="truncate">
                                            {{ $locationParts->implode(', ') }}
                                        </span>

                                    </div>
                                @endif


                                {{-- Ports Route --}}

                                @if ($originPort || $destinationPort)
                                    <div class="mt-3 flex items-center gap-2 text-xs text-slate-500">

                                        <span
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-blue-400/10">

                                            <svg class="h-4 w-4 text-blue-300" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5" d="M3 17h18M5 17V9h14v8M8 9V6h8v3M7 21h10" />
                                            </svg>

                                        </span>

                                        <div class="min-w-0 truncate">

                                            @if ($originPort && $destinationPort)
                                                <span class="text-slate-300">
                                                    {{ $originPort }}
                                                </span>

                                                <span class="mx-1 text-cyan-400">
                                                    →
                                                </span>

                                                <span class="text-slate-300">
                                                    {{ $destinationPort }}
                                                </span>
                                            @elseif ($originPort)
                                                {{ $originPort }}
                                            @else
                                                {{ $destinationPort }}
                                            @endif

                                        </div>

                                    </div>
                                @endif


                                {{-- Financial Cards --}}

                                <div class="mt-5 grid grid-cols-2 gap-3">

                                    {{-- Required Capital --}}

                                    <div class="rounded-2xl border border-white/5 bg-black/10 p-4">

                                        <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">
                                            {{ $isAr ? 'رأس المال' : 'Required Capital' }}
                                        </div>

                                        <div class="mt-2 truncate text-sm font-black text-white">

                                            @if ($requiredCapital !== null)
                                                {{ $requiredCapital }}

                                                <span class="text-[10px] font-semibold text-cyan-300">
                                                    {{ $opportunity->currency }}
                                                </span>
                                            @else
                                                <span class="text-slate-600">
                                                    —
                                                </span>
                                            @endif

                                        </div>

                                    </div>


                                    {{-- Minimum Investment --}}

                                    <div class="rounded-2xl border border-white/5 bg-black/10 p-4">

                                        <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">
                                            {{ $isAr ? 'الحد الأدنى' : 'Minimum' }}
                                        </div>

                                        <div class="mt-2 truncate text-sm font-black text-white">

                                            @if ($minInvestment !== null)
                                                {{ $minInvestment }}

                                                <span class="text-[10px] font-semibold text-cyan-300">
                                                    {{ $opportunity->currency }}
                                                </span>
                                            @else
                                                <span class="text-slate-600">
                                                    —
                                                </span>
                                            @endif

                                        </div>

                                    </div>

                                </div>


                                {{-- Spacer --}}

                                <div class="flex-1"></div>


                                {{-- Footer --}}

                                <div
                                    class="mt-5 flex items-center justify-between gap-3 border-t border-white/10 pt-5">

                                    <div class="min-w-0">

                                        <div class="text-[10px] text-slate-600">
                                            {{ $isAr ? 'تاريخ النشر' : 'Published' }}
                                        </div>

                                        <div class="mt-1 text-xs font-medium text-slate-400">

                                            @if ($opportunity->published_at)
                                                {{ $opportunity->published_at->format('M d, Y') }}
                                            @else
                                                —
                                            @endif

                                        </div>

                                    </div>


                                    <a href="{{ route('opportunities.show', $opportunity) }}"
                                        class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-cyan-400/20 bg-cyan-400/5 px-4 py-2.5 text-xs font-bold text-cyan-300 transition hover:border-cyan-400/30 hover:bg-cyan-400/10">

                                        {{ $isAr ? 'التفاصيل' : 'View Details' }}

                                        <svg class="h-4 w-4 {{ $isAr ? 'rotate-180' : '' }}" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>

                                    </a>

                                </div>

                            </div>

                        </article>
                    @endforeach

                </div>


                {{-- =====================================================
                PAGINATION
                ====================================================== --}}

                @if ($opportunities->hasPages())
                    <div class="mt-10">
                        {{ $opportunities->withQueryString()->links() }}
                    </div>
                @endif
            @else
                {{-- =====================================================
                EMPTY STATE
                ====================================================== --}}

                <section
                    class="relative overflow-hidden rounded-3xl border border-dashed border-white/10 bg-white/[0.02] px-6 py-20 text-center">

                    <div
                        class="pointer-events-none absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-cyan-400/5 blur-3xl">
                    </div>


                    <div
                        class="relative mx-auto flex h-20 w-20 items-center justify-center rounded-3xl border border-cyan-400/10 bg-cyan-400/5">

                        <svg class="h-9 w-9 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 6v12m6-6H6" />
                        </svg>

                    </div>


                    <h2 class="relative mt-6 text-2xl font-black">
                        {{ $isAr ? 'لم نجد فرصاً مطابقة' : 'No Matching Opportunities' }}
                    </h2>


                    <p class="relative mx-auto mt-3 max-w-lg text-sm leading-7 text-slate-500">
                        {{ $isAr
                            ? 'جرّب تغيير كلمات البحث أو الفلاتر، أو أضف فرصة جديدة إلى شبكة HAG Brokers.'
                            : 'Try changing your search or filters, or submit a new opportunity to the HAG Brokers network.' }}
                    </p>


                    <div class="relative mt-7 flex flex-col justify-center gap-3 sm:flex-row">

                        <a href="{{ route('opportunities.index') }}"
                            class="rounded-xl border border-white/10 px-6 py-3 text-sm font-semibold text-slate-300 transition hover:bg-white/5">
                            {{ $isAr ? 'عرض جميع الفرص' : 'View All Opportunities' }}
                        </a>


                        @auth

                            <a href="{{ route('opportunities.create') }}"
                                class="rounded-xl bg-cyan-400 px-6 py-3 text-sm font-bold text-slate-950 transition hover:bg-cyan-300">
                                {{ $isAr ? 'إضافة فرصة' : 'Submit Opportunity' }}
                            </a>

                        @endauth

                    </div>

                </section>

            @endif


            {{-- =====================================================
            BOTTOM CTA
            ====================================================== --}}

            @auth

                <section
                    class="relative mt-14 overflow-hidden rounded-3xl border border-cyan-400/15 bg-cyan-400/[0.04] p-6 sm:p-8">

                    <div
                        class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-cyan-400/10 blur-3xl">
                    </div>

                    <div
                        class="pointer-events-none absolute -bottom-20 -left-20 h-56 w-56 rounded-full bg-blue-500/10 blur-3xl">
                    </div>


                    <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">

                        <div>

                            <div class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-300">
                                {{ $isAr ? 'لديك فرصة؟' : 'Have an Opportunity?' }}
                            </div>

                            <h2 class="mt-2 text-2xl font-black">
                                {{ $isAr ? 'أضفها إلى شبكة HAG Brokers' : 'Submit it to the HAG Brokers Network' }}
                            </h2>

                            <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-500">
                                {{ $isAr
                                    ? 'أرسل تفاصيل الفرصة والصور أو الفيديو. سيقوم فريق HAG Brokers بمراجعتها والتحقق منها قبل نشرها.'
                                    : 'Submit the opportunity details, images or video. The HAG Brokers team will review and verify it before publication.' }}
                            </p>

                        </div>


                        <a href="{{ route('opportunities.create') }}"
                            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-cyan-400 px-6 py-3.5 text-sm font-black text-slate-950 transition hover:bg-cyan-300">

                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>

                            {{ $isAr ? 'أضف فرصة الآن' : 'Submit Now' }}

                        </a>

                    </div>

                </section>

            @endauth

        </main>

    </div>

</x-app-layout>
