<x-app-layout>

    @php
        $isAr = app()->getLocale() === 'ar';
        $dir = $isAr ? 'rtl' : 'ltr';

        /*
        |--------------------------------------------------------------------------
        | Labels
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
        | Media
        |--------------------------------------------------------------------------
        */

        $mediaItems = collect();

        if (isset($opportunity->media) && $opportunity->media instanceof \Illuminate\Support\Collection) {
            $mediaItems = $opportunity->media;
        }

        $mediaUrl = function ($path) {
            if (!$path) {
                return null;
            }

            if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '//'])) {
                return $path;
            }

            return asset('storage/' . ltrim($path, '/'));
        };

        $images = $mediaItems
            ->filter(function ($media) {
                $type = $media->mime_type ?? ($media->type ?? '');

                return \Illuminate\Support\Str::startsWith($type, 'image/');
            })
            ->values();

        $videos = $mediaItems
            ->filter(function ($media) {
                $type = $media->mime_type ?? ($media->type ?? '');

                return \Illuminate\Support\Str::startsWith($type, 'video/');
            })
            ->values();

        $primaryMedia = $mediaItems->firstWhere('is_primary', true);

        if (!$primaryMedia) {
            $primaryMedia = $images->first() ?? $mediaItems->first();
        }

        $primaryPath = $primaryMedia?->path ?? ($primaryMedia?->file_path ?? ($primaryMedia?->url ?? null));

        $primaryType = $primaryMedia?->mime_type ?? ($primaryMedia?->type ?? '');

        $primaryUrl = $mediaUrl($primaryPath);

        $primaryIsVideo = \Illuminate\Support\Str::startsWith($primaryType, 'video/');

        /*
        |--------------------------------------------------------------------------
        | Location
        |--------------------------------------------------------------------------
        */

        $locationParts = collect([$opportunity->city, $opportunity->country])->filter();

        /*
        |--------------------------------------------------------------------------
        | Ports
        |--------------------------------------------------------------------------
        */

        $originPort = $opportunity->originPort;

        $destinationPort = $opportunity->destinationPort;

        $originPortName = $originPort?->name ?? ($originPort?->port_name ?? null);

        $destinationPortName = $destinationPort?->name ?? ($destinationPort?->port_name ?? null);

        /*
        |--------------------------------------------------------------------------
        | Financials
        |--------------------------------------------------------------------------
        */

        $currency = $opportunity->currency ?: 'USD';

        $requiredCapital =
            $opportunity->required_capital !== null ? number_format((float) $opportunity->required_capital, 2) : null;

        $minInvestment =
            $opportunity->min_investment !== null ? number_format((float) $opportunity->min_investment, 2) : null;

        $estimatedValue =
            $opportunity->estimated_value !== null ? number_format((float) $opportunity->estimated_value, 2) : null;

        $commissionRate =
            $opportunity->commission_rate !== null ? number_format((float) $opportunity->commission_rate, 2) : null;

        /*
        |--------------------------------------------------------------------------
        | Dates
        |--------------------------------------------------------------------------
        */

        $publishedDate = $opportunity->published_at ? \Carbon\Carbon::parse($opportunity->published_at) : null;

        $expectedCloseDate = $opportunity->expected_close_date
            ? \Carbon\Carbon::parse($opportunity->expected_close_date)
            : null;

        $expiresAt = $opportunity->expires_at ? \Carbon\Carbon::parse($opportunity->expires_at) : null;

        /*
        |--------------------------------------------------------------------------
        | Meta
        |--------------------------------------------------------------------------
        */

        $typeLabel = $opportunityTypes[$opportunity->opportunity_type] ?? $opportunity->opportunity_type;

        $categoryLabel = $categories[$opportunity->category] ?? $opportunity->category;
    @endphp


    <div dir="{{ $dir }}" class="min-h-screen overflow-hidden bg-[#06101d] text-white">

        {{-- =========================================================
        BACKGROUND
        ========================================================== --}}

        <div class="pointer-events-none fixed inset-0 overflow-hidden">

            <div class="absolute -left-40 top-20 h-96 w-96 rounded-full bg-cyan-500/10 blur-3xl"></div>

            <div class="absolute -right-40 top-[45%] h-96 w-96 rounded-full bg-blue-500/10 blur-3xl"></div>

            <div
                class="absolute left-1/2 top-[65%] h-80 w-80 -translate-x-1/2 rounded-full bg-cyan-400/[0.025] blur-3xl">
            </div>

        </div>


        <main class="relative mx-auto max-w-7xl px-4 pb-24 pt-28 sm:px-6 lg:px-8">

            {{-- =========================================================
            ALERTS
            ========================================================== --}}

            @if (session('success'))
                <div
                    class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 p-4 text-sm text-emerald-300">

                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>

                    <span>
                        {{ session('success') }}
                    </span>

                </div>
            @endif


            @if (session('info'))
                <div
                    class="mb-6 flex items-start gap-3 rounded-2xl border border-cyan-400/20 bg-cyan-400/10 p-4 text-sm text-cyan-300">

                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z" />
                    </svg>

                    <span>
                        {{ session('info') }}
                    </span>

                </div>
            @endif


            {{-- =========================================================
            BACK
            ========================================================== --}}

            <a href="{{ route('opportunities.index') }}"
                class="mb-7 inline-flex items-center gap-2 text-sm font-semibold text-slate-400 transition hover:text-cyan-300">

                <svg class="h-4 w-4 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>

                {{ $isAr ? 'العودة إلى الفرص' : 'Back to Opportunities' }}

            </a>


            {{-- =========================================================
            HERO
            ========================================================== --}}

            <section
                class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.035] shadow-2xl shadow-black/20 backdrop-blur-xl">

                <div class="grid lg:grid-cols-5">

                    {{-- =================================================
                    PRIMARY MEDIA
                    ================================================== --}}

                    <div class="relative min-h-[320px] overflow-hidden bg-[#07111f] lg:col-span-2 lg:min-h-[500px]">

                        @if ($primaryUrl && $primaryIsVideo)
                            <video class="absolute inset-0 h-full w-full object-cover" controls playsinline
                                preload="metadata">

                                <source src="{{ $primaryUrl }}" type="{{ $primaryType }}">

                            </video>
                        @elseif ($primaryUrl)
                            <img src="{{ $primaryUrl }}" alt="{{ $opportunity->title }}"
                                class="absolute inset-0 h-full w-full object-cover">
                        @else
                            {{-- Visual Placeholder --}}

                            <div class="absolute inset-0">

                                <div class="absolute -left-20 top-10 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl">
                                </div>

                                <div class="absolute -right-20 bottom-0 h-72 w-72 rounded-full bg-blue-500/10 blur-3xl">
                                </div>

                            </div>

                            <div class="relative flex h-full items-center justify-center">

                                <div
                                    class="flex h-24 w-24 items-center justify-center rounded-[2rem] border border-cyan-400/15 bg-cyan-400/5 shadow-2xl shadow-cyan-500/10">

                                    <svg class="h-12 w-12 text-cyan-300/80" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M3 7.5A2.5 2.5 0 015.5 5h3l1.5 2h8A2.5 2.5 0 0120.5 9.5v7A2.5 2.5 0 0118 19H6a3 3 0 01-3-3V7.5z" />
                                    </svg>

                                </div>

                            </div>
                        @endif


                        {{-- Gradient --}}

                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-t from-[#06101d] via-transparent to-transparent opacity-90">
                        </div>


                        {{-- Top badges --}}

                        <div class="absolute start-5 end-5 top-5 flex items-start justify-between gap-3">

                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-emerald-400/20 bg-slate-950/75 px-3 py-1.5 text-xs font-bold text-emerald-300 backdrop-blur-md">

                                <span
                                    class="h-1.5 w-1.5 rounded-full bg-emerald-300 shadow-[0_0_8px_rgba(52,211,153,0.8)]"></span>

                                {{ $isAr ? 'تمت المراجعة بواسطة HAG' : 'HAG Reviewed' }}

                            </span>


                            @if ($mediaItems->count())
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-slate-950/75 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur-md">

                                    <svg class="h-3.5 w-3.5 text-cyan-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M4 5h16v14H4z" />
                                        <circle cx="8" cy="9" r="1" />
                                    </svg>

                                    {{ $mediaItems->count() }}

                                    {{ $isAr ? 'ملف' : 'media' }}

                                </span>
                            @endif

                        </div>


                        {{-- Media counter --}}

                        @if ($mediaItems->count() > 1)
                            <div
                                class="absolute bottom-5 start-5 rounded-full border border-white/10 bg-slate-950/75 px-3 py-1.5 text-[11px] font-semibold text-slate-300 backdrop-blur-md">

                                {{ $isAr
                                    ? $images->count() . ' صور · ' . $videos->count() . ' فيديو'
                                    : $images->count() . ' images · ' . $videos->count() . ' videos' }}

                            </div>
                        @endif

                    </div>


                    {{-- =================================================
                    HERO CONTENT
                    ================================================== --}}

                    <div class="flex flex-col justify-center p-7 sm:p-9 lg:col-span-3 lg:p-12">

                        {{-- Badges --}}

                        <div class="flex flex-wrap gap-2">

                            <span
                                class="inline-flex rounded-full border border-cyan-400/15 bg-cyan-400/10 px-3 py-1.5 text-xs font-bold text-cyan-300">
                                {{ $typeLabel }}
                            </span>


                            @if ($categoryLabel)
                                <span
                                    class="inline-flex rounded-full border border-white/5 bg-white/[0.04] px-3 py-1.5 text-xs font-semibold text-slate-400">
                                    {{ $categoryLabel }}
                                </span>
                            @endif

                        </div>


                        {{-- Title --}}

                        <h1 class="mt-6 text-3xl font-black leading-tight tracking-tight sm:text-4xl lg:text-5xl">
                            {{ $opportunity->title }}
                        </h1>


                        {{-- Location --}}

                        @if ($locationParts->isNotEmpty())
                            <div class="mt-6 flex items-center gap-2 text-sm text-slate-400">

                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10">

                                    <svg class="h-4 w-4 text-cyan-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 21a8 8 0 10-11.314 0L10.586 21a2 2 0 002.828 0l4.243-4.343z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>

                                </span>

                                <span>
                                    {{ $locationParts->implode(', ') }}
                                </span>

                            </div>
                        @endif


                        {{-- Route --}}

                        @if ($originPortName || $destinationPortName)

                            <div class="mt-3 flex items-center gap-2 text-sm text-slate-400">

                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-400/10">

                                    <svg class="h-4 w-4 text-blue-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M3 17h18M5 17V9h14v8M8 9V6h8v3M7 21h10" />
                                    </svg>

                                </span>

                                <div class="min-w-0">

                                    @if ($originPortName)
                                        <span class="font-semibold text-slate-300">
                                            {{ $originPortName }}
                                        </span>
                                    @endif

                                    @if ($originPortName && $destinationPortName)
                                        <span class="mx-2 text-cyan-400">
                                            →
                                        </span>
                                    @endif

                                    @if ($destinationPortName)
                                        <span class="font-semibold text-slate-300">
                                            {{ $destinationPortName }}
                                        </span>
                                    @endif

                                </div>

                            </div>

                        @endif


                        <div class="my-8 h-px bg-white/10"></div>


                        {{-- Financial summary --}}

                        <div class="grid gap-3 sm:grid-cols-3">

                            {{-- Capital --}}

                            <div class="rounded-2xl border border-cyan-400/10 bg-cyan-400/[0.05] p-4">

                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                    {{ $isAr ? 'رأس المال' : 'Required Capital' }}
                                </div>

                                <div class="mt-2 truncate text-lg font-black text-cyan-300">

                                    @if ($requiredCapital !== null)
                                        {{ $requiredCapital }}

                                        <span class="text-xs">
                                            {{ $currency }}
                                        </span>
                                    @else
                                        —
                                    @endif

                                </div>

                            </div>


                            {{-- Minimum --}}

                            <div class="rounded-2xl border border-white/5 bg-white/[0.025] p-4">

                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                    {{ $isAr ? 'الحد الأدنى' : 'Minimum' }}
                                </div>

                                <div class="mt-2 truncate text-lg font-black">

                                    @if ($minInvestment !== null)
                                        {{ $minInvestment }}

                                        <span class="text-xs text-slate-400">
                                            {{ $currency }}
                                        </span>
                                    @else
                                        —
                                    @endif

                                </div>

                            </div>


                            {{-- Commission --}}

                            <div class="rounded-2xl border border-white/5 bg-white/[0.025] p-4">

                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                    {{ $isAr ? 'العمولة' : 'Commission' }}
                                </div>

                                <div class="mt-2 text-lg font-black">

                                    @if ($commissionRate !== null)

                                        {{ $commissionRate }}

                                        @if ($opportunity->commission_type === 'percentage')
                                            <span class="text-cyan-300">
                                                %
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400">
                                                {{ $currency }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-sm text-slate-400">
                                            {{ $isAr ? 'قابلة للتفاوض' : 'Negotiable' }}
                                        </span>

                                    @endif

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </section>


            {{-- =========================================================
            MAIN CONTENT
            ========================================================== --}}

            <div class="mt-6 grid gap-6 lg:grid-cols-3">

                {{-- =====================================================
                LEFT / MAIN
                ====================================================== --}}

                <div class="space-y-6 lg:col-span-2">


                    {{-- =================================================
                    ABOUT
                    ================================================== --}}

                    <section class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl md:p-8">

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-300">

                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M12 20h9" />
                                    <path stroke-linecap="round" stroke-width="1.7"
                                        d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z" />
                                </svg>

                            </div>

                            <div>

                                <h2 class="text-lg font-bold">
                                    {{ $isAr ? 'عن الفرصة' : 'About This Opportunity' }}
                                </h2>

                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $isAr ? 'تفاصيل الفرصة' : 'Opportunity details' }}
                                </p>

                            </div>

                        </div>


                        <div class="mt-6 whitespace-pre-line text-sm leading-8 text-slate-300">
                            {{ $opportunity->description }}
                        </div>

                    </section>


                    {{-- =================================================
                    LOGISTICS
                    ================================================== --}}

                    @if ($opportunity->cargo_type || $opportunity->container_type || $originPortName || $destinationPortName)

                        <section
                            class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl md:p-8">

                            <div class="flex items-center gap-3">

                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-400/10 text-blue-300">

                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                            d="M3 7h11v10H3zM14 10h4l3 3v4h-7z" />
                                        <circle cx="7" cy="19" r="2" stroke-width="1.7" />
                                        <circle cx="17" cy="19" r="2" stroke-width="1.7" />
                                    </svg>

                                </div>

                                <div>

                                    <h2 class="text-lg font-bold">
                                        {{ $isAr ? 'الشحن واللوجستيات' : 'Shipping & Logistics' }}
                                    </h2>

                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $isAr ? 'تفاصيل الحركة والشحن' : 'Shipping and logistics details' }}
                                    </p>

                                </div>

                            </div>


                            <div class="mt-6 grid gap-4 sm:grid-cols-2">

                                @if ($opportunity->cargo_type)
                                    <div class="rounded-2xl border border-white/5 bg-black/10 p-5">

                                        <div class="text-xs text-slate-500">
                                            {{ $isAr ? 'نوع البضاعة' : 'Cargo Type' }}
                                        </div>

                                        <div class="mt-2 font-semibold text-slate-200">
                                            {{ $opportunity->cargo_type }}
                                        </div>

                                    </div>
                                @endif


                                @if ($opportunity->container_type)
                                    <div class="rounded-2xl border border-white/5 bg-black/10 p-5">

                                        <div class="text-xs text-slate-500">
                                            {{ $isAr ? 'نوع الحاوية' : 'Container Type' }}
                                        </div>

                                        <div class="mt-2 font-semibold text-slate-200">
                                            {{ $opportunity->container_type }}
                                        </div>

                                    </div>
                                @endif


                                @if ($originPortName)
                                    <div class="rounded-2xl border border-white/5 bg-black/10 p-5">

                                        <div class="text-xs text-slate-500">
                                            {{ $isAr ? 'ميناء المغادرة' : 'Origin Port' }}
                                        </div>

                                        <div class="mt-2 font-semibold text-slate-200">
                                            {{ $originPortName }}
                                        </div>

                                    </div>
                                @endif


                                @if ($destinationPortName)
                                    <div class="rounded-2xl border border-white/5 bg-black/10 p-5">

                                        <div class="text-xs text-slate-500">
                                            {{ $isAr ? 'ميناء الوصول' : 'Destination Port' }}
                                        </div>

                                        <div class="mt-2 font-semibold text-slate-200">
                                            {{ $destinationPortName }}
                                        </div>

                                    </div>
                                @endif

                            </div>

                        </section>

                    @endif


                    {{-- =================================================
                    MEDIA GALLERY
                    ================================================== --}}

                    @if ($mediaItems->count())

                        <section
                            class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl md:p-8">

                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

                                <div>

                                    <h2 class="text-lg font-bold">
                                        {{ $isAr ? 'الصور والفيديو' : 'Photos & Videos' }}
                                    </h2>

                                    <p class="mt-1 text-xs leading-6 text-slate-500">
                                        {{ $isAr
                                            ? 'استكشف المزيد من تفاصيل الفرصة بصرياً.'
                                            : 'Explore additional visual details about this opportunity.' }}
                                    </p>

                                </div>


                                <div
                                    class="inline-flex self-start rounded-full border border-white/10 bg-white/[0.03] px-3 py-1.5 text-[11px] font-semibold text-slate-400">
                                    {{ $mediaItems->count() }}
                                    {{ $isAr ? 'ملف' : 'files' }}
                                </div>

                            </div>


                            <div class="mt-6 grid gap-4 sm:grid-cols-2">

                                @foreach ($mediaItems as $media)
                                    @php
                                        $path = $media->path ?? ($media->file_path ?? ($media->url ?? null));

                                        $type = $media->mime_type ?? ($media->type ?? '');

                                        $url = $mediaUrl($path);

                                        $isMediaImage = \Illuminate\Support\Str::startsWith($type, 'image/');

                                        $isMediaVideo = \Illuminate\Support\Str::startsWith($type, 'video/');
                                    @endphp


                                    @if ($url && $isMediaImage)
                                        <a href="{{ $url }}" target="_blank" rel="noopener"
                                            class="group relative aspect-video overflow-hidden rounded-2xl border border-white/10 bg-black/20">

                                            <img src="{{ $url }}" alt="{{ $opportunity->title }}"
                                                loading="lazy"
                                                class="h-full w-full object-cover transition duration-700 group-hover:scale-105">

                                            <div
                                                class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 transition group-hover:opacity-100">
                                            </div>

                                            <div
                                                class="absolute bottom-3 end-3 flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-slate-950/70 text-white opacity-0 backdrop-blur-md transition group-hover:opacity-100">

                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.7"
                                                        d="M15 3h6v6M10 14L21 3M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" />
                                                </svg>

                                            </div>

                                        </a>
                                    @elseif ($url && $isMediaVideo)
                                        <div
                                            class="relative overflow-hidden rounded-2xl border border-white/10 bg-black/30">

                                            <video controls playsinline preload="metadata"
                                                class="aspect-video h-full w-full object-cover">

                                                <source src="{{ $url }}" type="{{ $type }}">

                                            </video>

                                            <div
                                                class="pointer-events-none absolute start-3 top-3 rounded-full border border-white/10 bg-slate-950/75 px-3 py-1.5 text-[10px] font-bold text-white backdrop-blur-md">

                                                <span class="text-cyan-300">
                                                    ▶
                                                </span>

                                                {{ $isAr ? 'فيديو' : 'Video' }}

                                            </div>

                                        </div>
                                    @endif
                                @endforeach

                            </div>

                        </section>

                    @endif


                    {{-- =================================================
                    NOTES
                    ================================================== --}}

                    @if ($opportunity->notes)
                        <section
                            class="rounded-3xl border border-amber-400/10 bg-amber-400/[0.025] p-6 backdrop-blur-xl md:p-8">

                            <div class="flex items-start gap-3">

                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-400/10 text-amber-300">

                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                            d="M12 9v4m0 4h.01M10.3 3.8L2.7 17a2 2 0 001.7 3h15.2a2 2 0 001.7-3L13.7 3.8a2 2 0 00-3.4 0z" />
                                    </svg>

                                </div>

                                <div>

                                    <h2 class="font-bold text-amber-200">
                                        {{ $isAr ? 'ملاحظات إضافية' : 'Additional Notes' }}
                                    </h2>

                                    <div class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-400">
                                        {{ $opportunity->notes }}
                                    </div>

                                </div>

                            </div>

                        </section>
                    @endif

                </div>


                {{-- =====================================================
                SIDEBAR
                ====================================================== --}}

                <aside class="space-y-6">


                    {{-- =================================================
                    FINANCIAL INFORMATION
                    ================================================== --}}

                    <section class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl">

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-300">

                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M12 8c-3 0-5 1.2-5 3s2 3 5 3 5 1.2 5 3-2 3-5 3-5-1.2-5-3" />
                                    <path stroke-linecap="round" stroke-width="1.7" d="M12 5v14" />
                                </svg>

                            </div>

                            <h2 class="font-bold">
                                {{ $isAr ? 'المعلومات المالية' : 'Financial Information' }}
                            </h2>

                        </div>


                        <div class="mt-6 space-y-5">

                            {{-- Required Capital --}}

                            <div>

                                <div class="text-xs text-slate-500">
                                    {{ $isAr ? 'رأس المال المطلوب' : 'Required Capital' }}
                                </div>

                                <div class="mt-2 text-2xl font-black text-cyan-300">

                                    @if ($requiredCapital !== null)
                                        {{ $requiredCapital }}

                                        <span class="text-sm">
                                            {{ $currency }}
                                        </span>
                                    @else
                                        —
                                    @endif

                                </div>

                            </div>


                            {{-- Minimum --}}

                            <div class="h-px bg-white/10"></div>

                            <div>

                                <div class="text-xs text-slate-500">
                                    {{ $isAr ? 'الحد الأدنى للاستثمار' : 'Minimum Investment' }}
                                </div>

                                <div class="mt-2 text-lg font-bold">

                                    @if ($minInvestment !== null)
                                        {{ $minInvestment }}
                                        {{ $currency }}
                                    @else
                                        —
                                    @endif

                                </div>

                            </div>


                            {{-- Estimated Value --}}

                            @if ($estimatedValue !== null)
                                <div class="h-px bg-white/10"></div>

                                <div>

                                    <div class="text-xs text-slate-500">
                                        {{ $isAr ? 'القيمة التقديرية' : 'Estimated Value' }}
                                    </div>

                                    <div class="mt-2 text-lg font-bold">

                                        {{ $estimatedValue }}
                                        {{ $currency }}

                                    </div>

                                </div>
                            @endif

                        </div>

                    </section>


                    {{-- =================================================
                    COMMISSION
                    ================================================== --}}

                    <section class="rounded-3xl border border-cyan-400/15 bg-cyan-400/[0.035] p-6">

                        <div class="flex items-center justify-between gap-3">

                            <h2 class="font-bold">
                                {{ $isAr ? 'نموذج العمولة' : 'Commission Model' }}
                            </h2>

                            <span
                                class="rounded-full border border-cyan-400/10 bg-cyan-400/5 px-2.5 py-1 text-[10px] font-bold text-cyan-300">
                                {{ $opportunity->commission_type === 'percentage'
                                    ? ($isAr
                                        ? 'نسبة'
                                        : 'Percentage')
                                    : ($isAr
                                        ? 'مبلغ ثابت'
                                        : 'Fixed') }}
                            </span>

                        </div>


                        <div class="mt-5">

                            @if ($commissionRate !== null)

                                <div class="text-3xl font-black text-cyan-300">

                                    {{ $commissionRate }}

                                    @if ($opportunity->commission_type === 'percentage')
                                        %
                                    @else
                                        <span class="text-sm">
                                            {{ $currency }}
                                        </span>
                                    @endif

                                </div>
                            @else
                                <div class="text-lg font-bold text-slate-300">
                                    {{ $isAr ? 'قابلة للتفاوض' : 'To be agreed' }}
                                </div>

                            @endif


                            <p class="mt-3 text-xs leading-6 text-slate-500">
                                {{ $isAr
                                    ? 'تفاصيل العمولة تخضع لشروط الفرصة والاتفاق النهائي بين الأطراف.'
                                    : 'Commission details remain subject to the opportunity terms and final agreement between the parties.' }}
                            </p>

                        </div>

                    </section>


                    {{-- =================================================
                    TIMELINE
                    ================================================== --}}

                    @if ($expectedCloseDate || $expiresAt)

                        <section class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl">

                            <div class="flex items-center gap-3">

                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-400/10 text-blue-300">

                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                            d="M8 7V3m8 4V3M4 11h16M5 21h14a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v14a1 1 0 001 1z" />
                                    </svg>

                                </div>

                                <h2 class="font-bold">
                                    {{ $isAr ? 'الجدول الزمني' : 'Timeline' }}
                                </h2>

                            </div>


                            <div class="mt-6 space-y-5">

                                @if ($expectedCloseDate)
                                    <div class="flex items-start gap-3">

                                        <div
                                            class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-300">

                                            <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.7"
                                                    d="M8 7V3m8 4V3M4 11h16M5 21h14a1 1 0 001-1V6a1 1 0 00-1-1h14a1 1 0 001 1v14a1 1 0 001 1z" />
                                            </svg>

                                        </div>

                                        <div>

                                            <div class="text-xs text-slate-500">
                                                {{ $isAr ? 'الإغلاق المتوقع' : 'Expected Close' }}
                                            </div>

                                            <div class="mt-1 text-sm font-semibold text-slate-200">
                                                {{ $expectedCloseDate->format('M d, Y') }}
                                            </div>

                                        </div>

                                    </div>
                                @endif


                                @if ($expiresAt)
                                    <div class="flex items-start gap-3">

                                        <div
                                            class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-400/10 text-amber-300">

                                            <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.7"
                                                    d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>

                                        </div>

                                        <div>

                                            <div class="text-xs text-slate-500">
                                                {{ $isAr ? 'تنتهي في' : 'Expires' }}
                                            </div>

                                            <div class="mt-1 text-sm font-semibold text-slate-200">
                                                {{ $expiresAt->format('M d, Y H:i') }}
                                            </div>

                                        </div>

                                    </div>
                                @endif

                            </div>

                        </section>

                    @endif


                    {{-- =================================================
                    INTEREST CTA
                    ================================================== --}}

                    @auth

                        <section
                            class="relative overflow-hidden rounded-3xl border border-cyan-400/20 bg-gradient-to-br from-cyan-400/10 to-blue-500/5 p-6">

                            <div
                                class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-cyan-400/10 blur-3xl">
                            </div>

                            <div class="relative">

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-400/10 text-cyan-300">

                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>

                                </div>


                                <h2 class="mt-5 text-lg font-bold text-cyan-300">
                                    {{ $isAr ? 'مهتم بهذه الفرصة؟' : 'Interested in this opportunity?' }}
                                </h2>


                                <p class="mt-3 text-sm leading-7 text-slate-400">
                                    {{ $isAr
                                        ? 'أرسل اهتمامك وسيتواصل معك فريق HAG Brokers لمناقشة التفاصيل والخطوات التالية.'
                                        : 'Send your interest and the HAG Brokers team can follow up with you about the opportunity and next steps.' }}
                                </p>


                                <a href="{{ route('opportunities.interest', $opportunity) }}"
                                    class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-cyan-400 px-5 py-3.5 text-sm font-black text-slate-950 shadow-lg shadow-cyan-500/10 transition hover:bg-cyan-300">

                                    {{ $isAr ? 'أنا مهتم بهذه الفرصة' : "I'm Interested" }}

                                    <svg class="h-4 w-4 {{ $isAr ? 'rotate-180' : '' }}" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>

                                </a>

                            </div>

                        </section>
                    @else
                        <section class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl">

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-cyan-400/10 text-cyan-300">

                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M16 11c1.657 0 3-1.567 3-3.5S17.657 4 16 4s-3 1.567-3 3.5 1.343 3.5 3 3.5zM8 11c1.657 0 3-1.567 3-3.5S9.657 4 8 4 5 5.567 5 7.5 6.343 11 8 11zM3 20c0-2.761 2.239-5 5-5h0c2.761 0 5 2.239 5 5M13 15h3c2.761 0 5 2.239 5 5" />
                                </svg>

                            </div>


                            <h2 class="mt-5 font-bold">
                                {{ $isAr ? 'هل أنت مهتم؟' : 'Interested?' }}
                            </h2>


                            <p class="mt-3 text-sm leading-7 text-slate-400">
                                {{ $isAr
                                    ? 'سجل الدخول أولاً للتعبير عن اهتمامك بهذه الفرصة.'
                                    : 'Log in first to express your interest in this opportunity.' }}
                            </p>


                            <a href="{{ route('login') }}"
                                class="mt-5 block w-full rounded-xl bg-cyan-400 px-5 py-3.5 text-center text-sm font-black text-slate-950 transition hover:bg-cyan-300">
                                {{ $isAr ? 'تسجيل الدخول' : 'Login' }}
                            </a>

                        </section>

                    @endauth


                    {{-- =================================================
                    HAG INFORMATION
                    ================================================== --}}

                    <section class="rounded-3xl border border-white/5 bg-white/[0.02] p-5">

                        <div class="flex items-start gap-3">

                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-300">

                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z" />
                                </svg>

                            </div>


                            <p class="text-xs leading-6 text-slate-500">
                                {{ $isAr
                                    ? 'تقوم HAG Brokers بمراجعة الفرص قبل نشرها في السوق. أي اتفاق تجاري نهائي يخضع للتحقق والتفاوض بين الأطراف المعنية.'
                                    : 'HAG Brokers reviews opportunities before publishing them in the marketplace. Any final commercial agreement remains subject to verification and negotiation between the relevant parties.' }}
                            </p>

                        </div>

                    </section>

                </aside>

            </div>


            {{-- =========================================================
            FOOTER META
            ========================================================== --}}

            <div
                class="mt-8 flex flex-col gap-3 border-t border-white/10 pt-5 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    {{ $isAr ? 'تاريخ النشر:' : 'Published:' }}

                    <span class="font-medium text-slate-400">

                        @if ($publishedDate)
                            {{ $publishedDate->format('M d, Y') }}
                        @else
                            —
                        @endif

                    </span>

                </div>


                <div>

                    {{ $isAr ? 'رقم الفرصة:' : 'Opportunity ID:' }}

                    <span class="font-mono text-slate-400">
                        #{{ $opportunity->id }}
                    </span>

                </div>

            </div>

        </main>

    </div>

</x-app-layout>
