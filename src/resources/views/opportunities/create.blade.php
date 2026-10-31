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
            'vessel_brokerage' => $isAr ? 'وساطة السفن واليخوت' : 'Vessel & Yacht Brokerage',
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

        $currencies = [
            'AED' => 'AED — درهم إماراتي',
            'USD' => 'USD — US Dollar',
            'EUR' => 'EUR — Euro',
            'GBP' => 'GBP — British Pound',
            'SAR' => 'SAR — Saudi Riyal',
        ];
    @endphp

    <div dir="{{ $dir }}" class="min-h-screen bg-[#06101d] text-white" x-data="opportunityWizard()" x-cloak>

        <main class="mx-auto max-w-6xl px-4 pb-28 pt-24 sm:px-6 lg:px-8">

            {{-- =========================================================
                HEADER
            ========================================================== --}}

            <div class="mb-8">

                <a href="{{ route('opportunities.index') }}"
                    class="mb-5 inline-flex items-center gap-2 text-sm text-slate-400 transition hover:text-cyan-300">
                    <svg class="h-4 w-4 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>

                    {{ $isAr ? 'العودة إلى الفرص' : 'Back to Opportunities' }}
                </a>

                <div class="flex flex-wrap items-center gap-3">

                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1.5 text-xs font-semibold text-cyan-300">

                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-cyan-300"></span>

                        {{ $isAr ? 'إضافة فرصة تجارية' : 'New Business Opportunity' }}

                    </span>

                    <span class="text-xs text-slate-600">
                        HAG Brokers
                    </span>

                </div>

                <h1 class="mt-4 max-w-3xl text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl">
                    {{ $isAr ? 'أضف فرصتك واحصل على اهتمام المهتمين' : 'Submit your opportunity and reach interested partners' }}
                </h1>

                <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-400 sm:text-base">
                    {{ $isAr
                        ? 'أدخل المعلومات الأساسية فقط، وأضف صورًا أو فيديو إذا كانت متوفرة. سنقوم بمراجعة الفرصة قبل نشرها.'
                        : 'Provide the essential information, add images or a video if available, and our team will review the opportunity before publishing it.' }}
                </p>

            </div>


            {{-- =========================================================
                SERVER VALIDATION
            ========================================================== --}}

            @if ($errors->any())

                <div class="mb-6 rounded-2xl border border-red-400/20 bg-red-400/10 p-5">

                    <div class="flex gap-3">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-400/10">
                            <svg class="h-5 w-5 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v4m0 4h.01M10.29 3.86l-7.5 13A2 2 0 004.53 20h14.94a2 2 0 001.74-3.14l-7.5-13a2 2 0 00-3.42 0z" />
                            </svg>
                        </div>

                        <div class="min-w-0">

                            <div class="font-bold text-red-300">
                                {{ $isAr ? 'يرجى مراجعة البيانات' : 'Please review your information' }}
                            </div>

                            <ul class="mt-2 space-y-1 text-sm leading-6 text-red-300">
                                @foreach ($errors->all() as $error)
                                    <li>• {{ $error }}</li>
                                @endforeach
                            </ul>

                        </div>

                    </div>

                </div>

            @endif


            {{-- =========================================================
                MAIN WIZARD
            ========================================================== --}}

            <div class="grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">


                {{-- =====================================================
                    DESKTOP STEPS
                ====================================================== --}}

                <aside class="hidden lg:block">

                    <div class="sticky top-24 rounded-3xl border border-white/10 bg-white/[0.03] p-5 backdrop-blur-xl">

                        <div class="mb-6">

                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-300">
                                {{ $isAr ? 'إنشاء الفرصة' : 'Create Opportunity' }}
                            </div>

                            <div class="mt-2 text-sm text-slate-500">
                                <span x-text="progress"></span>%
                                {{ $isAr ? 'مكتمل' : 'complete' }}
                            </div>

                        </div>


                        <div class="space-y-2">

                            @foreach ([
        1 => $isAr ? 'الأساسيات' : 'Basics',
        2 => $isAr ? 'الموقع والتفاصيل' : 'Location & Details',
        3 => $isAr ? 'القيمة والعمولة' : 'Financials',
        4 => $isAr ? 'الصور والفيديو' : 'Media',
        5 => $isAr ? 'المراجعة والإرسال' : 'Review & Submit',
    ] as $number => $label)
                                <button type="button" @click="goToStep({{ $number }})"
                                    class="group flex w-full items-center gap-3 rounded-2xl p-3 text-start transition"
                                    :class="step === {{ $number }} ?
                                        'bg-cyan-400/10' :
                                        'hover:bg-white/5'">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border text-sm font-bold transition"
                                        :class="step >= {{ $number }} ?
                                            'border-cyan-300 bg-cyan-400 text-slate-950' :
                                            'border-white/10 bg-white/5 text-slate-500'">

                                        <span x-show="step <= {{ $number }}">
                                            {{ $number }}
                                        </span>

                                        <svg x-show="step > {{ $number }}" class="h-4 w-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>

                                    </div>

                                    <div class="min-w-0">

                                        <div class="text-sm font-semibold"
                                            :class="step === {{ $number }} ?
                                                'text-cyan-300' :
                                                'text-slate-300'">
                                            {{ $label }}
                                        </div>

                                        <div class="mt-0.5 text-[11px] text-slate-600">
                                            {{ $isAr ? 'الخطوة' : 'Step' }} {{ $number }}
                                        </div>

                                    </div>

                                </button>
                            @endforeach

                        </div>


                        <div class="mt-6 h-1.5 overflow-hidden rounded-full bg-white/5">

                            <div class="h-full rounded-full bg-cyan-400 transition-all duration-500"
                                :style="`width:${progress}%`"></div>

                        </div>


                        <div class="mt-6 rounded-2xl border border-cyan-400/10 bg-cyan-400/5 p-4">

                            <div class="flex gap-3">

                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-cyan-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 110-20 10 10 0 010 20z" />
                                </svg>

                                <p class="text-xs leading-6 text-slate-400">
                                    {{ $isAr
                                        ? 'لا تحتاج إلى إكمال كل التفاصيل. يمكنك إضافة المعلومات المتوفرة لديك فقط.'
                                        : 'You do not need every detail. Add only the information you currently have.' }}
                                </p>

                            </div>

                        </div>

                    </div>

                </aside>


                {{-- =====================================================
                    CONTENT
                ====================================================== --}}

                <div class="min-w-0">


                    {{-- =================================================
                        MOBILE PROGRESS
                    ================================================== --}}

                    <div class="mb-5 rounded-3xl border border-white/10 bg-white/[0.03] p-4 backdrop-blur-xl lg:hidden">

                        <div class="flex items-center justify-between">

                            <div>

                                <div class="text-xs font-semibold text-cyan-300">
                                    {{ $isAr ? 'الخطوة' : 'Step' }}
                                    <span x-text="step"></span>
                                    {{ $isAr ? 'من 5' : 'of 5' }}
                                </div>

                                <div class="mt-1 text-sm font-bold">
                                    <span x-text="stepTitle"></span>
                                </div>

                            </div>

                            <div class="text-sm font-black text-cyan-300">
                                <span x-text="progress"></span>%
                            </div>

                        </div>

                        <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-white/5">

                            <div class="h-full rounded-full bg-cyan-400 transition-all duration-500"
                                :style="`width:${progress}%`"></div>

                        </div>

                    </div>


                    {{-- =================================================
                        FORM
                    ================================================== --}}

                    <form method="POST" action="{{ route('opportunities.store') }}" enctype="multipart/form-data"
                        @submit="handleSubmit($event)">

                        @csrf

                        {{-- =============================================
                            STEP 1
                        ============================================== --}}

                        <section x-show="step === 1" x-transition.opacity.duration.200ms
                            class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 backdrop-blur-xl sm:p-7 lg:p-8">

                            <div class="mb-8">

                                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-400/10">

                                    <svg class="h-6 w-6 text-cyan-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v12m6-6H6" />
                                    </svg>

                                </div>

                                <h2 class="text-xl font-black sm:text-2xl">
                                    {{ $isAr ? 'ما هي الفرصة؟' : 'What is the opportunity?' }}
                                </h2>

                                <p class="mt-2 text-sm leading-7 text-slate-500">
                                    {{ $isAr
                                        ? 'ابدأ بالمعلومات التي تجعل الشخص يفهم الفرصة خلال ثوانٍ.'
                                        : 'Start with the information that lets someone understand the opportunity in seconds.' }}
                                </p>

                            </div>


                            <div class="space-y-6">


                                {{-- Title --}}

                                <div>

                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'عنوان واضح للفرصة' : 'Opportunity Title' }}
                                        <span class="text-cyan-400">*</span>
                                    </label>

                                    <input type="text" name="title" x-model="form.title" maxlength="255"
                                        required
                                        placeholder="{{ $isAr
                                            ? 'مثال: توريد منتجات غذائية إلى متاجر في دبي'
                                            : 'Example: Food supply opportunity for retailers in Dubai' }}"
                                        class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-4 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/10">

                                    <p class="mt-2 text-xs text-slate-600">
                                        {{ $isAr ? 'اجعل العنوان قصيرًا ومباشرًا.' : 'Keep it short and specific.' }}
                                    </p>

                                </div>


                                <div class="grid gap-5 md:grid-cols-2">


                                    {{-- Type --}}

                                    <div>

                                        <label class="mb-2 block text-sm font-semibold text-slate-300">
                                            {{ $isAr ? 'نوع الفرصة' : 'Opportunity Type' }}
                                            <span class="text-cyan-400">*</span>
                                        </label>


{{-- HAG OPPORTUNITY CARD SELECTOR --}}
<div x-init="
    form.opportunity_type = @js(old('opportunity_type', ''));
    form.category = @js(old('category', ''));
"></div>
<input type="hidden" name="opportunity_type"
    x-model="form.opportunity_type">

<div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
    @foreach ($opportunityTypes as $value => $label)
        @php
            $icons = [
                'business' => '💼',
                'investment' => '📈',
                'trade' => '📦',
                'procurement' => '🛒',
                'distribution' => '🚚',
                'logistics' => '🚢',
                'partnership' => '🤝',
                'vessel_brokerage' => '⛵',
                'other' => '📂',
            ];
        @endphp

        <button type="button"
            @click="form.opportunity_type = @js($value); form.category = ''"
            :aria-pressed="form.opportunity_type === @js($value)"
            :class="form.opportunity_type === @js($value)
                ? 'scale-[1.02] border-cyan-400 bg-cyan-400/15 text-cyan-200 shadow-lg shadow-cyan-400/10'
                : 'border-white/10 bg-[#07111f] text-slate-300 hover:border-cyan-400/40 hover:bg-white/5'"
            class="relative min-h-28 rounded-2xl border p-4 text-center transition-all duration-200">

            <span class="mb-2 block text-3xl">
                {{ $icons[$value] ?? '📂' }}
            </span>

            <span class="block text-sm font-bold">
                {{ $label }}
            </span>

            <span x-show="form.opportunity_type === @js($value)"
                class="absolute end-2 top-2 text-cyan-300">
                ✓
            </span>
        </button>
    @endforeach
</div>


                                    </div>


                                    {{-- Category --}}

                                    <div>

                                        <label class="mb-2 block text-sm font-semibold text-slate-300">
                                            {{ $isAr ? 'الفئة' : 'Category' }}
                                        </label>


<input type="hidden" name="category" x-model="form.category">

<div x-show="form.opportunity_type !== ''" x-cloak>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
        @foreach ($categories as $value => $label)
            @php
                $icons = [
                    'import' => '📥',
                    'export' => '📤',
                    'sea_freight' => '🚢',
                    'distribution' => '🚚',
                    'manufacturing' => '🏭',
                    'real_estate' => '🏢',
                    'technology' => '💻',
                    'services' => '🛠️',
                    'other' => '📂',
                ];
            @endphp

            <button type="button"
                x-show="form.opportunity_type !== 'vessel_brokerage'
                    || ['sea_freight','services','other'].includes(@js($value))"
                @click="form.category = @js($value)"
                :aria-pressed="form.category === @js($value)"
                :class="form.category === @js($value)
                    ? 'scale-[1.02] border-cyan-400 bg-cyan-400/15 text-cyan-200 shadow-lg shadow-cyan-400/10'
                    : 'border-white/10 bg-[#07111f] text-slate-300 hover:border-cyan-400/40 hover:bg-white/5'"
                class="relative min-h-24 rounded-2xl border p-4 text-center transition-all duration-200">

                <span class="mb-2 block text-2xl">
                    {{ $icons[$value] ?? '📂' }}
                </span>

                <span class="block text-sm font-bold">
                    {{ $label }}
                </span>

                <span x-show="form.category === @js($value)"
                    class="absolute end-2 top-2 text-cyan-300">
                    ✓
                </span>
            </button>
        @endforeach
    </div>
</div>


                                    </div>

                                </div>


                                {{-- Description --}}

                                <div>

                                    <div class="mb-2 flex items-center justify-between">

                                        <label class="block text-sm font-semibold text-slate-300">
                                            {{ $isAr ? 'اشرح الفرصة' : 'Describe the Opportunity' }}
                                            <span class="text-cyan-400">*</span>
                                        </label>

                                        <span class="text-xs"
                                            :class="descriptionLength < 20 ?
                                                'text-slate-600' :
                                                'text-cyan-300'">
                                            <span x-text="descriptionLength"></span>/3000
                                        </span>

                                    </div>

                                    <textarea name="description" x-model="form.description" rows="8" maxlength="3000" required
                                        placeholder="{{ $isAr
                                            ? 'مثال: لدينا مورد لمنتج معين ونبحث عن موزع أو مشتري داخل الإمارات. الكمية المتوقعة... السوق المستهدف... طريقة التعاون...'
                                            : 'Example: We have a supplier for a specific product and are looking for a distributor or buyer in the UAE. Expected volume, target market, cooperation model...' }}"
                                        class="w-full resize-y rounded-2xl border border-white/10 bg-[#07111f] px-4 py-4 text-sm leading-7 text-white outline-none placeholder:text-slate-600 transition focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/10"></textarea>

                                    <p class="mt-2 text-xs text-slate-600">
                                        {{ $isAr
                                            ? 'اذكر المنتج أو الخدمة، السوق المستهدف، المطلوب، وطريقة التعاون.'
                                            : 'Mention the product/service, target market, requirement and cooperation model.' }}
                                    </p>

                                </div>


                                {{-- Quick Example --}}

                                <div class="rounded-2xl border border-cyan-400/10 bg-cyan-400/5 p-4">

                                    <div class="flex gap-3">

                                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-cyan-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 110-20 10 10 0 010 20z" />
                                        </svg>

                                        <p class="text-xs leading-6 text-slate-400">
                                            {{ $isAr
                                                ? 'كلما كان الوصف واضحًا، أصبح من الأسهل على المشتري أو المستثمر فهم الفرصة.'
                                                : 'A clear description makes it easier for buyers, investors and partners to understand the opportunity.' }}
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </section>


                        {{-- =============================================
                            STEP 2
                        ============================================== --}}

                        <section x-show="step === 2" x-transition.opacity.duration.200ms
                            class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 backdrop-blur-xl sm:p-7 lg:p-8">

                        {{-- VESSEL BROKERAGE FIELDS --}}
                        <div
                            x-show="form.opportunity_type === 'vessel_brokerage'"
                            x-cloak
                            class="mb-8 rounded-3xl border border-cyan-400/20 bg-cyan-400/5 p-5 sm:p-7">

                            <h3 class="mb-2 text-xl font-black text-cyan-300">
                                {{ $isAr ? 'تفاصيل السفينة أو اليخت' : 'Vessel & Yacht Details' }}
                            </h3>

                            <p class="mb-6 text-sm text-slate-400">
                                {{ $isAr ? 'اختر المواصفات المناسبة. لا تحتاج إلى كتابة كل التفاصيل.' : 'Select the relevant specifications. Only a few details need typing.' }}
                            </p>

                            <div class="grid gap-5 md:grid-cols-2">

                                @foreach ([
                                    'listing_type' => [
                                        'label' => $isAr ? 'نوع الإعلان' : 'Listing Type',
                                        'options' => [
                                            'sale' => $isAr ? 'عرض للبيع' : 'For Sale',
                                            'wanted' => $isAr ? 'مطلوب شراء' : 'Wanted',
                                            'charter' => $isAr ? 'عرض للتأجير' : 'For Charter',
                                        ],
                                    ],
                                    'vessel_category' => [
                                        'label' => $isAr ? 'تصنيف المركب' : 'Vessel Category',
                                        'options' => [
                                            'yacht' => $isAr ? 'يخت' : 'Yacht',
                                            'commercial_vessel' => $isAr ? 'سفينة تجارية' : 'Commercial Vessel',
                                            'boat' => $isAr ? 'قارب' : 'Boat',
                                        ],
                                    ],
                                    'vessel_type' => [
                                        'label' => $isAr ? 'نوع المركب' : 'Vessel Type',
                                        'options' => [
                                            'motor_yacht' => $isAr ? 'يخت بمحرك' : 'Motor Yacht',
                                            'sailing_yacht' => $isAr ? 'يخت شراعي' : 'Sailing Yacht',
                                            'superyacht' => $isAr ? 'يخت فاخر كبير' : 'Superyacht',
                                            'bulk_carrier' => $isAr ? 'ناقلة بضائع سائبة' : 'Bulk Carrier',
                                            'container_ship' => $isAr ? 'سفينة حاويات' : 'Container Ship',
                                            'tanker' => $isAr ? 'ناقلة' : 'Tanker',
                                            'general_cargo' => $isAr ? 'سفينة بضائع عامة' : 'General Cargo',
                                            'tugboat' => $isAr ? 'قاطرة بحرية' : 'Tugboat',
                                            'speedboat' => $isAr ? 'قارب سريع' : 'Speedboat',
                                            'fishing_boat' => $isAr ? 'قارب صيد' : 'Fishing Boat',
                                            'other' => $isAr ? 'أخرى' : 'Other',
                                        ],
                                    ],
                                    'condition' => [
                                        'label' => $isAr ? 'حالة المركب' : 'Condition',
                                        'options' => [
                                            'new' => $isAr ? 'جديد' : 'New',
                                            'used' => $isAr ? 'مستعمل' : 'Used',
                                            'refurbished' => $isAr ? 'مجدد' : 'Refurbished',
                                        ],
                                    ],
                                ] as $field => $config)

                                    <div>
                                        <label class="mb-2 block text-sm font-semibold text-slate-300">
                                            {{ $config['label'] }}
                                        </label>

                                        <select
                                            name="vessel[{{ $field }}]"
                                            class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-3.5 text-sm text-white focus:border-cyan-400/50">

                                            <option value="">
                                                {{ $isAr ? 'اختر من القائمة' : 'Select an option' }}
                                            </option>

                                            @foreach ($config['options'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    @selected(old('vessel.' . $field) === $value)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                @endforeach

                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'سنة الصنع' : 'Year Built' }}
                                    </label>

                                    <select name="vessel[year_built]"
                                        class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-3.5 text-sm text-white">
                                        <option value="">
                                            {{ $isAr ? 'اختر السنة' : 'Select Year' }}
                                        </option>

                                        @for ($year = now()->year; $year >= 1950; $year--)
                                            <option value="{{ $year }}"
                                                @selected((string) old('vessel.year_built') === (string) $year)>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'اسم السفينة (اختياري)' : 'Vessel Name (Optional)' }}
                                    </label>

                                    <input type="text" name="vessel[vessel_name]"
                                        maxlength="255"
                                        value="{{ old('vessel.vessel_name') }}"
                                        class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-3.5 text-sm text-white">
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'الشركة المصنعة (اختياري)' : 'Builder (Optional)' }}
                                    </label>

                                    <input type="text" name="vessel[builder]"
                                        maxlength="255"
                                        value="{{ old('vessel.builder') }}"
                                        class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-3.5 text-sm text-white">
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'الطول بالمتر (اختياري)' : 'Length in Meters (Optional)' }}
                                    </label>

                                    <input type="number" name="vessel[length_m]"
                                        min="0.01" step="0.01"
                                        value="{{ old('vessel.length_m') }}"
                                        class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-3.5 text-sm text-white">
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'رقم IMO إن وجد' : 'IMO Number (If Applicable)' }}
                                    </label>

                                    <input type="text" name="vessel[imo_number]"
                                        inputmode="numeric" maxlength="7"
                                        value="{{ old('vessel.imo_number') }}"
                                        class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-3.5 text-sm text-white">
                                </div>

                            </div>

                        </div>


                            <div class="mb-8">

                                <div
                                    class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-400/10">

                                    <svg class="h-6 w-6 text-cyan-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2" />
                                    </svg>

                                </div>

                                <h2 class="text-xl font-black sm:text-2xl">
                                    {{ $isAr ? 'أين وما هي التفاصيل؟' : 'Where and what are the details?' }}
                                </h2>

                                <p class="mt-2 text-sm leading-7 text-slate-500">
                                    {{ $isAr
                                        ? 'هذه المعلومات اختيارية، لكنها تساعد في الوصول إلى الأشخاص المناسبين.'
                                        : 'These details are optional but help connect your opportunity with the right people.' }}
                                </p>

                            </div>


                            <div class="grid gap-5 md:grid-cols-2">


                                <div>

                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'الدولة' : 'Country' }}
                                    </label>

                                    <input type="text" name="country" x-model="form.country" maxlength="100"
                                        placeholder="{{ $isAr ? 'الإمارات العربية المتحدة' : 'United Arab Emirates' }}"
                                        class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-4 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50">

                                </div>


                                <div>

                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'المدينة' : 'City' }}
                                    </label>

                                    <input type="text" name="city" x-model="form.city" maxlength="100"
                                        placeholder="{{ $isAr ? 'دبي' : 'Dubai' }}"
                                        class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-4 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50">

                                </div>


                                <div>

                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'نوع البضاعة / المنتج' : 'Cargo / Product' }}
                                    </label>

                                    <input type="text" name="cargo_type" x-model="form.cargo_type"
                                        maxlength="255"
                                        placeholder="{{ $isAr ? 'مواد غذائية، إلكترونيات، أثاث...' : 'Food, electronics, furniture...' }}"
                                        class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-4 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50">

                                </div>


                                <div>

                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'نوع الحاوية' : 'Container Type' }}
                                    </label>

                                    <input type="text" name="container_type" x-model="form.container_type"
                                        maxlength="255" placeholder="20GP / 40HC"
                                        class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-4 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50">

                                </div>


                                <div class="md:col-span-2">

                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'تفاصيل إضافية' : 'Additional Details' }}
                                    </label>

                                    <textarea name="notes" x-model="form.notes" rows="5"
                                        placeholder="{{ $isAr
                                            ? 'مثال: ميناء التحميل، ميناء الوصول، الكمية، شروط التسليم، الجدول الزمني...'
                                            : 'Example: Loading port, destination port, quantity, delivery terms, timeline...' }}"
                                        class="w-full resize-y rounded-2xl border border-white/10 bg-[#07111f] px-4 py-4 text-sm leading-7 text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50"></textarea>

                                </div>

                            </div>


                            <div class="mt-6 rounded-2xl border border-white/10 bg-white/[0.02] p-4">

                                <div class="flex gap-3">

                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-slate-500" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>

                                    <p class="text-xs leading-6 text-slate-500">
                                        {{ $isAr
                                            ? 'إذا كانت الفرصة غير مرتبطة بشحنة، يمكنك ترك تفاصيل الحاوية فارغة.'
                                            : 'If the opportunity is not related to shipping, you can leave container details empty.' }}
                                    </p>

                                </div>

                            </div>

                        </section>


                        {{-- =============================================
                            STEP 3
                        ============================================== --}}

                        <section x-show="step === 3" x-transition.opacity.duration.200ms
                            class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 backdrop-blur-xl sm:p-7 lg:p-8">

                            <div class="mb-8">

                                <div
                                    class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-400/10">

                                    <svg class="h-6 w-6 text-cyan-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V4m0 10v6m8-9h-4M8 11H4" />
                                    </svg>

                                </div>

                                <h2 class="text-xl font-black sm:text-2xl">
                                    {{ $isAr ? 'القيمة والعمولة والموعد' : 'Value, commission & timeline' }}
                                </h2>

                                <p class="mt-2 text-sm leading-7 text-slate-500">
                                    {{ $isAr
                                        ? 'يمكن أن تكون الأرقام تقريبية. الاتفاق النهائي يتم لاحقًا.'
                                        : 'Figures can be approximate. Final terms can be agreed later.' }}
                                </p>

                            </div>


                            <div class="grid gap-5 md:grid-cols-2">


                                {{-- Estimated Value --}}

                                <div>

                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'القيمة التقديرية' : 'Estimated Value' }}
                                    </label>

                                    <input type="number" name="estimated_value" x-model="form.estimated_value"
                                        min="0" step="0.01" inputmode="decimal" placeholder="0.00"
                                        class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-4 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50">

                                </div>


                                {{-- Required Capital --}}

                                <div>

                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'رأس المال المطلوب' : 'Required Capital' }}
                                    </label>

                                    <input type="number" name="required_capital" x-model="form.required_capital"
                                        min="0" step="0.01" inputmode="decimal" placeholder="0.00"
                                        class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-4 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50">

                                </div>


                                {{-- Minimum Investment --}}

                                <div>

                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'الحد الأدنى للاستثمار' : 'Minimum Investment' }}
                                    </label>

                                    <input type="number" name="min_investment" x-model="form.min_investment"
                                        min="0" step="0.01" inputmode="decimal" placeholder="0.00"
                                        class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-4 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50">

                                </div>


                                {{-- Currency --}}

                                <div>

                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'العملة' : 'Currency' }}
                                        <span class="text-cyan-400">*</span>
                                    </label>

                                    <select name="currency" x-model="form.currency" required
                                        class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-4 text-sm text-white outline-none focus:border-cyan-400/50">

                                        @foreach ($currencies as $value => $label)
                                            <option value="{{ $value }}">
                                                {{ $label }}
                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                            </div>


                            <div class="my-8 h-px bg-white/10"></div>


                            {{-- Commission --}}

                            <div class="mb-5">

                                <h3 class="font-bold">
                                    {{ $isAr ? 'نموذج العمولة' : 'Commission Model' }}
                                </h3>

                                <p class="mt-1 text-xs leading-6 text-slate-500">
                                    {{ $isAr
                                        ? 'هذه قيمة مقترحة فقط، ويتم الاتفاق النهائي بين الأطراف.'
                                        : 'This is a proposed value. Final terms are agreed between the parties.' }}
                                </p>

                            </div>


                            <div class="grid gap-5 md:grid-cols-2">

                                <div>

                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'نوع العمولة' : 'Commission Type' }}
                                        <span class="text-cyan-400">*</span>
                                    </label>

                                    <select name="commission_type" x-model="form.commission_type" required
                                        class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-4 text-sm text-white outline-none focus:border-cyan-400/50">

                                        <option value="percentage">
                                            {{ $isAr ? 'نسبة مئوية' : 'Percentage' }}
                                        </option>

                                        <option value="fixed">
                                            {{ $isAr ? 'مبلغ ثابت' : 'Fixed Amount' }}
                                        </option>

                                    </select>

                                </div>


                                <div>

                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'قيمة العمولة' : 'Commission Value' }}
                                    </label>

                                    <div class="relative">

                                        <input type="number" name="commission_rate" x-model="form.commission_rate"
                                            min="0" step="0.01" inputmode="decimal"
                                            :max="form.commission_type === 'percentage' ? 100 : null"
                                            placeholder="{{ $isAr ? 'مثال: 2.5' : 'Example: 2.5' }}"
                                            class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-4 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50">

                                        <span
                                            class="pointer-events-none absolute end-4 top-1/2 -translate-y-1/2 text-xs font-bold text-cyan-300"
                                            x-text="form.commission_type === 'percentage' ? '%' : form.currency"></span>

                                    </div>

                                </div>

                            </div>


                            <div class="my-8 h-px bg-white/10"></div>


                            {{-- Timeline --}}

                            <div class="mb-5">

                                <h3 class="font-bold">
                                    {{ $isAr ? 'الجدول الزمني' : 'Timeline' }}
                                </h3>

                            </div>


                            <div class="grid gap-5 md:grid-cols-2">

                                <div>

                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'تاريخ الإغلاق المتوقع' : 'Expected Close Date' }}
                                    </label>

                                    <input type="date" name="expected_close_date"
                                        x-model="form.expected_close_date" min="{{ now()->format('Y-m-d') }}"
                                        class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-4 text-sm text-white outline-none focus:border-cyan-400/50">

                                </div>


                                <div>

                                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                                        {{ $isAr ? 'تاريخ انتهاء الفرصة' : 'Opportunity Expiry' }}
                                    </label>

                                    <input type="datetime-local" name="expires_at" x-model="form.expires_at"
                                        min="{{ now()->format('Y-m-d\TH:i') }}"
                                        class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-4 text-sm text-white outline-none focus:border-cyan-400/50">

                                </div>

                            </div>

                        </section>


                        {{-- =============================================
                            STEP 4
                        ============================================== --}}

                        <section x-show="step === 4" x-transition.opacity.duration.200ms
                            class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 backdrop-blur-xl sm:p-7 lg:p-8">

                            <div class="mb-8">

                                <div
                                    class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-400/10">

                                    <svg class="h-6 w-6 text-cyan-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>

                                </div>

                                <h2 class="text-xl font-black sm:text-2xl">
                                    {{ $isAr ? 'أضف صورًا أو فيديو' : 'Add images or video' }}
                                </h2>

                                <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-500">
                                    {{ $isAr
                                        ? 'المحتوى المرئي يساعد المستخدم على فهم الفرصة بشكل أسرع.'
                                        : 'Visual content helps people understand the opportunity faster.' }}
                                </p>

                            </div>


                            {{-- Upload Zone --}}

                            <div @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false"
                                @drop.prevent="handleDrop($event)" @click="$refs.mediaInput.click()"
                                :class="dragging
                                    ?
                                    'border-cyan-300 bg-cyan-400/10 scale-[1.01]' :
                                    'border-white/10 bg-[#07111f]'"
                                class="group cursor-pointer rounded-3xl border-2 border-dashed p-8 text-center transition sm:p-12">

                                <div
                                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-cyan-400/10 transition group-hover:scale-105">

                                    <svg class="h-8 w-8 text-cyan-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 16V4m0 0l-4 4m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2" />
                                    </svg>

                                </div>

                                <h3 class="mt-5 font-bold">
                                    {{ $isAr ? 'اسحب الملفات هنا أو اضغط للاختيار' : 'Drag files here or click to browse' }}
                                </h3>

                                <p class="mt-2 text-sm text-slate-500">
                                    JPG, JPEG, PNG, WEBP, MP4, MOV, WEBM
                                </p>

                                <p class="mt-2 text-xs text-slate-600">
                                    {{ $isAr ? 'حتى 10 ملفات — 20MB لكل ملف' : 'Up to 10 files — 20MB per file' }}
                                </p>

                                <input x-ref="mediaInput" type="file" name="media[]" multiple
                                    accept=".jpg,.jpeg,.png,.webp,.mp4,.mov,.webm,image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm"
                                    class="hidden" @change="handleFiles($event)">

                            </div>


                            {{-- Media Error --}}

                            <div x-show="mediaError" x-transition
                                class="mt-4 rounded-2xl border border-red-400/20 bg-red-400/10 p-4 text-sm text-red-300">
                                <span x-text="mediaError"></span>
                            </div>


                            {{-- Selected Files --}}

                            <div x-show="media.length > 0" class="mt-7">

                                <div class="mb-4 flex items-center justify-between gap-4">

                                    <div>

                                        <h3 class="font-bold">
                                            {{ $isAr ? 'الملفات المختارة' : 'Selected media' }}
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-500">
                                            <span x-text="media.length"></span>/10
                                        </p>

                                    </div>

                                    <button type="button" @click="clearMedia()"
                                        class="text-xs font-semibold text-red-300 hover:text-red-200">
                                        {{ $isAr ? 'إزالة الكل' : 'Remove all' }}
                                    </button>

                                </div>


                                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">

                                    <template x-for="(file, index) in media" :key="file.id">

                                        <div
                                            class="group overflow-hidden rounded-2xl border border-white/10 bg-[#07111f]">

                                            <div class="relative">

                                                <template x-if="file.type.startsWith('image/')">

                                                    <img :src="file.preview" :alt="file.name"
                                                        class="h-48 w-full object-cover">

                                                </template>


                                                <template x-if="file.type.startsWith('video/')">

                                                    <video :src="file.preview" controls preload="metadata"
                                                        class="h-48 w-full object-cover"></video>

                                                </template>


                                                {{-- Primary --}}

                                                <button type="button" @click.stop="setPrimary(index)"
                                                    x-show="file.type.startsWith('image/')"
                                                    class="absolute start-3 top-3 rounded-full border px-3 py-1.5 text-[10px] font-bold backdrop-blur transition"
                                                    :class="primaryMediaIndex === index ?
                                                        'border-cyan-300 bg-cyan-400 text-slate-950' :
                                                        'border-white/10 bg-slate-950/80 text-white hover:border-cyan-300/50'">
                                                    <span x-show="primaryMediaIndex === index">
                                                        {{ $isAr ? 'الصورة الرئيسية' : 'Cover' }}
                                                    </span>

                                                    <span x-show="primaryMediaIndex !== index">
                                                        {{ $isAr ? 'اجعلها رئيسية' : 'Set cover' }}
                                                    </span>
                                                </button>


                                                {{-- Remove --}}

                                                <button type="button" @click.stop="removeMedia(index)"
                                                    class="absolute end-3 top-3 flex h-8 w-8 items-center justify-center rounded-full border border-white/10 bg-slate-950/80 text-white backdrop-blur transition hover:bg-red-500">

                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>

                                                </button>

                                            </div>


                                            <div class="p-3">

                                                <div class="truncate text-xs font-semibold text-slate-200"
                                                    x-text="file.name"></div>

                                                <div class="mt-1 text-[11px] text-slate-500">
                                                    <span x-text="formatSize(file.size)"></span>
                                                </div>

                                            </div>

                                        </div>

                                    </template>

                                </div>

                            </div>


                            {{-- Hidden primary media index --}}

                            <input type="hidden" name="primary_media_index" :value="primaryMediaIndex">


                            <div class="mt-7 rounded-2xl border border-cyan-400/10 bg-cyan-400/5 p-4">

                                <div class="flex gap-3">

                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-cyan-300" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 110-20 10 10 0 010 20z" />
                                    </svg>

                                    <p class="text-xs leading-6 text-slate-400">
                                        {{ $isAr
                                            ? 'استخدم صورًا واضحة للمنتج أو الموقع. لا ترفع جوازات سفر أو هويات أو معلومات بنكية أو مستندات سرية.'
                                            : 'Use clear product or location images. Do not upload passports, IDs, banking information or confidential documents.' }}
                                    </p>

                                </div>

                            </div>

                        </section>


                        {{-- =============================================
                            STEP 5
                        ============================================== --}}

                        <section x-show="step === 5" x-transition.opacity.duration.200ms class="space-y-5">

                            <div
                                class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 backdrop-blur-xl sm:p-7 lg:p-8">

                                <div class="mb-8">

                                    <div
                                        class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-400/10">

                                        <svg class="h-6 w-6 text-cyan-300" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>

                                    </div>

                                    <h2 class="text-xl font-black sm:text-2xl">
                                        {{ $isAr ? 'جاهز للإرسال؟' : 'Ready to submit?' }}
                                    </h2>

                                    <p class="mt-2 text-sm leading-7 text-slate-500">
                                        {{ $isAr
                                            ? 'راجع المعلومات سريعًا ثم أرسل الفرصة لفريق HAG Brokers.'
                                            : 'Quickly review the information and submit your opportunity to HAG Brokers.' }}
                                    </p>

                                </div>


                                {{-- Main Summary --}}

                                <div class="space-y-4">


                                    {{-- Basics --}}

                                    <div class="overflow-hidden rounded-2xl border border-white/10">

                                        <div
                                            class="flex items-center justify-between gap-4 border-b border-white/10 bg-white/[0.03] px-5 py-4">

                                            <h3 class="font-bold">
                                                {{ $isAr ? 'الأساسيات' : 'Basics' }}
                                            </h3>

                                            <button type="button" @click="goToStep(1)"
                                                class="text-xs font-bold text-cyan-300 hover:text-cyan-200">
                                                {{ $isAr ? 'تعديل' : 'Edit' }}
                                            </button>

                                        </div>

                                        <div class="grid gap-5 p-5 sm:grid-cols-2">

                                            <div class="sm:col-span-2">

                                                <div class="text-xs text-slate-500">
                                                    {{ $isAr ? 'العنوان' : 'Title' }}
                                                </div>

                                                <div class="mt-1 text-sm font-bold text-white"
                                                    x-text="form.title || '—'"></div>

                                            </div>


                                            <div>

                                                <div class="text-xs text-slate-500">
                                                    {{ $isAr ? 'النوع' : 'Type' }}
                                                </div>

                                                <div class="mt-1 text-sm text-slate-200" x-text="displayType()"></div>

                                            </div>


                                            <div>

                                                <div class="text-xs text-slate-500">
                                                    {{ $isAr ? 'الفئة' : 'Category' }}
                                                </div>

                                                <div class="mt-1 text-sm text-slate-200" x-text="displayCategory()">
                                                </div>

                                            </div>


                                            <div class="sm:col-span-2">

                                                <div class="text-xs text-slate-500">
                                                    {{ $isAr ? 'الوصف' : 'Description' }}
                                                </div>

                                                <div class="mt-1 whitespace-pre-line text-sm leading-7 text-slate-300"
                                                    x-text="form.description || '—'"></div>

                                            </div>

                                        </div>

                                    </div>


                                    {{-- Location --}}

                                    <div class="overflow-hidden rounded-2xl border border-white/10">

                                        <div
                                            class="flex items-center justify-between gap-4 border-b border-white/10 bg-white/[0.03] px-5 py-4">

                                            <h3 class="font-bold">
                                                {{ $isAr ? 'الموقع والتفاصيل' : 'Location & Details' }}
                                            </h3>

                                            <button type="button" @click="goToStep(2)"
                                                class="text-xs font-bold text-cyan-300 hover:text-cyan-200">
                                                {{ $isAr ? 'تعديل' : 'Edit' }}
                                            </button>

                                        </div>

                                        <div class="grid gap-5 p-5 sm:grid-cols-2 lg:grid-cols-4">

                                            <div>
                                                <div class="text-xs text-slate-500">
                                                    {{ $isAr ? 'الدولة' : 'Country' }}
                                                </div>

                                                <div class="mt-1 text-sm text-slate-200" x-text="form.country || '—'">
                                                </div>
                                            </div>

                                            <div>
                                                <div class="text-xs text-slate-500">
                                                    {{ $isAr ? 'المدينة' : 'City' }}
                                                </div>

                                                <div class="mt-1 text-sm text-slate-200" x-text="form.city || '—'">
                                                </div>
                                            </div>

                                            <div>
                                                <div class="text-xs text-slate-500">
                                                    {{ $isAr ? 'المنتج' : 'Product' }}
                                                </div>

                                                <div class="mt-1 text-sm text-slate-200"
                                                    x-text="form.cargo_type || '—'"></div>
                                            </div>

                                            <div>
                                                <div class="text-xs text-slate-500">
                                                    {{ $isAr ? 'الحاوية' : 'Container' }}
                                                </div>

                                                <div class="mt-1 text-sm text-slate-200"
                                                    x-text="form.container_type || '—'"></div>
                                            </div>

                                        </div>

                                    </div>


                                    {{-- Financial --}}

                                    <div class="overflow-hidden rounded-2xl border border-white/10">

                                        <div
                                            class="flex items-center justify-between gap-4 border-b border-white/10 bg-white/[0.03] px-5 py-4">

                                            <h3 class="font-bold">
                                                {{ $isAr ? 'القيمة والعمولة' : 'Value & Commission' }}
                                            </h3>

                                            <button type="button" @click="goToStep(3)"
                                                class="text-xs font-bold text-cyan-300 hover:text-cyan-200">
                                                {{ $isAr ? 'تعديل' : 'Edit' }}
                                            </button>

                                        </div>

                                        <div class="grid gap-5 p-5 sm:grid-cols-2 lg:grid-cols-4">

                                            <div>

                                                <div class="text-xs text-slate-500">
                                                    {{ $isAr ? 'القيمة' : 'Value' }}
                                                </div>

                                                <div class="mt-1 text-sm font-semibold text-slate-200">

                                                    <span x-text="form.estimated_value || '—'"></span>

                                                    <span x-show="form.estimated_value"
                                                        class="ms-1 text-xs text-cyan-300"
                                                        x-text="form.currency"></span>

                                                </div>

                                            </div>


                                            <div>

                                                <div class="text-xs text-slate-500">
                                                    {{ $isAr ? 'رأس المال' : 'Capital' }}
                                                </div>

                                                <div class="mt-1 text-sm font-semibold text-slate-200">

                                                    <span x-text="form.required_capital || '—'"></span>

                                                    <span x-show="form.required_capital"
                                                        class="ms-1 text-xs text-cyan-300"
                                                        x-text="form.currency"></span>

                                                </div>

                                            </div>


                                            <div>

                                                <div class="text-xs text-slate-500">
                                                    {{ $isAr ? 'الحد الأدنى' : 'Minimum' }}
                                                </div>

                                                <div class="mt-1 text-sm font-semibold text-slate-200">

                                                    <span x-text="form.min_investment || '—'"></span>

                                                    <span x-show="form.min_investment"
                                                        class="ms-1 text-xs text-cyan-300"
                                                        x-text="form.currency"></span>

                                                </div>

                                            </div>


                                            <div>

                                                <div class="text-xs text-slate-500">
                                                    {{ $isAr ? 'العمولة' : 'Commission' }}
                                                </div>

                                                <div class="mt-1 text-sm font-semibold text-slate-200">

                                                    <span x-text="form.commission_rate || '—'"></span>

                                                    <span x-show="form.commission_rate"
                                                        class="ms-1 text-xs text-cyan-300"
                                                        x-text="form.commission_type === 'percentage' ? '%' : form.currency"></span>

                                                </div>

                                            </div>

                                        </div>

                                    </div>


                                    {{-- Media --}}

                                    <div class="overflow-hidden rounded-2xl border border-white/10">

                                        <div
                                            class="flex items-center justify-between gap-4 border-b border-white/10 bg-white/[0.03] px-5 py-4">

                                            <h3 class="font-bold">
                                                {{ $isAr ? 'الوسائط' : 'Media' }}
                                            </h3>

                                            <button type="button" @click="goToStep(4)"
                                                class="text-xs font-bold text-cyan-300 hover:text-cyan-200">
                                                {{ $isAr ? 'تعديل' : 'Edit' }}
                                            </button>

                                        </div>

                                        <div class="p-5">

                                            <div x-show="media.length === 0" class="text-sm text-slate-500">
                                                {{ $isAr ? 'لم تتم إضافة صور أو فيديو.' : 'No images or video added.' }}
                                            </div>

                                            <div x-show="media.length > 0" class="flex flex-wrap gap-2">

                                                <template x-for="file in media" :key="file.id">

                                                    <span
                                                        class="inline-flex max-w-full items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-2 text-xs text-slate-300">

                                                        <span
                                                            class="h-1.5 w-1.5 shrink-0 rounded-full bg-cyan-300"></span>

                                                        <span class="max-w-[180px] truncate"
                                                            x-text="file.name"></span>

                                                    </span>

                                                </template>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            {{-- Submission Notice --}}

                            <div class="rounded-3xl border border-cyan-400/20 bg-cyan-400/5 p-5 sm:p-6">

                                <div class="flex gap-4">

                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-cyan-400/10">

                                        <svg class="h-5 w-5 text-cyan-300" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 110-20 10 10 0 010 20z" />
                                        </svg>

                                    </div>

                                    <div>

                                        <h3 class="font-bold text-cyan-300">
                                            {{ $isAr ? 'ماذا يحدث بعد الإرسال؟' : 'What happens next?' }}
                                        </h3>

                                        <p class="mt-2 text-sm leading-7 text-slate-400">
                                            {{ $isAr
                                                ? 'سيتم حفظ الفرصة كطلب قيد المراجعة. سيقوم فريق HAG Brokers بمراجعة البيانات والوسائط قبل ظهورها للعامة.'
                                                : 'Your opportunity will be saved as a pending submission. HAG Brokers will review the information and media before it becomes publicly visible.' }}
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </section>


                        {{-- =================================================
                            NAVIGATION
                        ================================================== --}}

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                            <a href="{{ route('opportunities.index') }}"
                                class="order-3 rounded-2xl border border-white/10 px-6 py-3.5 text-center text-sm font-semibold text-slate-300 transition hover:bg-white/5 sm:order-1">
                                {{ $isAr ? 'إلغاء' : 'Cancel' }}
                            </a>


                            <div class="flex flex-col gap-3 sm:order-2 sm:flex-row">


                                {{-- Previous --}}

                                <button type="button" x-show="step > 1" @click="previousStep()"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/10 px-6 py-3.5 text-sm font-semibold text-slate-300 transition hover:bg-white/5">

                                    <svg class="h-4 w-4 {{ $isAr ? 'rotate-180' : '' }}" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 12H5m7 7l-7-7 7-7" />
                                    </svg>

                                    {{ $isAr ? 'السابق' : 'Previous' }}

                                </button>


                                {{-- Continue --}}

                                <button type="button" x-show="step < 5" @click="nextStep()"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-cyan-400 px-7 py-3.5 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/10 transition hover:bg-cyan-300">

                                    {{ $isAr ? 'متابعة' : 'Continue' }}

                                    <svg class="h-4 w-4 {{ $isAr ? 'rotate-180' : '' }}" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 12h14m-6-6l6 6-6 6" />
                                    </svg>

                                </button>


                                {{-- Submit --}}

                                <button type="submit" x-show="step === 5" :disabled="submitting"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-cyan-400 px-7 py-3.5 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/10 transition hover:bg-cyan-300 disabled:cursor-not-allowed disabled:opacity-60">

                                    <svg x-show="!submitting" class="h-5 w-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>

                                    <svg x-show="submitting" class="h-5 w-5 animate-spin" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4" />

                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                    </svg>

                                    <span x-show="!submitting">
                                        {{ $isAr ? 'إرسال للمراجعة' : 'Submit for Review' }}
                                    </span>

                                    <span x-show="submitting">
                                        {{ $isAr ? 'جاري الإرسال...' : 'Submitting...' }}
                                    </span>

                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </main>

    </div>


    {{-- =============================================================
        ALPINE
    ============================================================= --}}

    <script>
        function opportunityWizard() {

            return {

                step: 1,

                submitting: false,

                dragging: false,

                media: [],

                mediaError: '',

                primaryMediaIndex: null,

                form: {

                    title: @js(old('title', '')),

                    opportunity_type: @js(old('opportunity_type', 'business')),

                    category: @js(old('category', '')),

                    description: @js(old('description', '')),

                    country: @js(old('country', '')),

                    city: @js(old('city', '')),

                    cargo_type: @js(old('cargo_type', '')),

                    container_type: @js(old('container_type', '')),

                    estimated_value: @js(old('estimated_value', '')),

                    required_capital: @js(old('required_capital', '')),

                    min_investment: @js(old('min_investment', '')),

                    currency: @js(old('currency', 'AED')),

                    commission_type: @js(old('commission_type', 'percentage')),

                    commission_rate: @js(old('commission_rate', '')),

                    expected_close_date: @js(old('expected_close_date', '')),

                    expires_at: @js(old('expires_at', '')),

                    notes: @js(old('notes', '')),

                },


                get progress() {

                    return this.step * 20;

                },


                get stepTitle() {

                    const titles = @js([
    1 => $isAr ? 'الأساسيات' : 'Basics',
    2 => $isAr ? 'الموقع والتفاصيل' : 'Location & Details',
    3 => $isAr ? 'القيمة والعمولة' : 'Financials',
    4 => $isAr ? 'الصور والفيديو' : 'Media',
    5 => $isAr ? 'المراجعة والإرسال' : 'Review & Submit',
]);

                    return titles[this.step] || '';

                },


                get descriptionLength() {

                    return this.form.description ?
                        this.form.description.length :
                        0;

                },


                nextStep() {

                    if (!this.validateStep(this.step)) {

                        return;

                    }

                    if (this.step < 5) {

                        this.step++;

                        this.scrollTop();

                    }

                },


                previousStep() {

                    if (this.step > 1) {

                        this.step--;

                        this.scrollTop();

                    }

                },


                goToStep(target) {

                    if (target < this.step) {

                        this.step = target;

                        this.scrollTop();

                        return;

                    }


                    if (target === this.step) {

                        return;

                    }


                    let current = this.step;


                    while (current < target) {

                        if (!this.validateStep(current)) {

                            this.step = current;

                            this.scrollTop();

                            return;

                        }

                        current++;

                    }


                    this.step = target;

                    this.scrollTop();

                },


                validateStep(step) {

                    if (step === 1) {

                        if (!this.form.title.trim()) {

                            this.showMessage(
                                @js($isAr ? 'يرجى إدخال عنوان الفرصة.' : 'Please enter the opportunity title.')
                            );

                            return false;

                        }


                        if (!this.form.opportunity_type) {

                            this.showMessage(
                                @js($isAr ? 'يرجى اختيار نوع الفرصة.' : 'Please select an opportunity type.')
                            );

                            return false;

                        }


                        if (!this.form.description.trim()) {

                            this.showMessage(
                                @js($isAr ? 'يرجى كتابة وصف الفرصة.' : 'Please describe the opportunity.')
                            );

                            return false;

                        }


                        if (this.form.description.trim().length < 20) {

                            this.showMessage(
                                @js($isAr ? 'يجب أن يحتوي وصف الفرصة على 20 حرفًا على الأقل.' : 'The opportunity description must be at least 20 characters.')
                            );

                            return false;

                        }

                    }


                    if (step === 3) {

                        if (!this.form.currency) {

                            this.showMessage(
                                @js($isAr ? 'يرجى اختيار العملة.' : 'Please select a currency.')
                            );

                            return false;

                        }


                        if (!this.form.commission_type) {

                            this.showMessage(
                                @js($isAr ? 'يرجى اختيار نوع العمولة.' : 'Please select a commission type.')
                            );

                            return false;

                        }


                        if (
                            this.form.commission_type === 'percentage' &&
                            this.form.commission_rate !== '' &&
                            Number(this.form.commission_rate) > 100
                        ) {

                            this.showMessage(
                                @js($isAr ? 'نسبة العمولة لا يمكن أن تتجاوز 100%.' : 'Commission percentage cannot exceed 100%.')
                            );

                            return false;

                        }

                    }


                    return true;

                },


                handleSubmit(event) {

                    if (!this.validateStep(5)) {

                        event.preventDefault();

                        return;

                    }


                    if (!this.validateStep(1)) {

                        event.preventDefault();

                        this.step = 1;

                        this.scrollTop();

                        return;

                    }


                    if (!this.validateStep(3)) {

                        event.preventDefault();

                        this.step = 3;

                        this.scrollTop();

                        return;

                    }


                    this.syncFiles();

                    this.submitting = true;

                },


                showMessage(message) {

                    window.dispatchEvent(
                        new CustomEvent('opportunity-message', {
                            detail: message
                        })
                    );

                    alert(message);

                },


                scrollTop() {

                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });

                },


                handleFiles(event) {

                    const files = Array.from(
                        event.target.files || []
                    );

                    this.addFiles(files);

                    event.target.value = '';

                },


                handleDrop(event) {

                    this.dragging = false;

                    const files = Array.from(
                        event.dataTransfer.files || []
                    );

                    this.addFiles(files);

                },


                addFiles(files) {

                    this.mediaError = '';


                    for (const file of files) {

                        if (this.media.length >= 10) {

                            this.mediaError = @js($isAr ? 'يمكنك إضافة 10 ملفات كحد أقصى.' : 'You can upload a maximum of 10 files.');

                            break;

                        }


                        const isImage =
                            file.type.startsWith('image/');

                        const isVideo =
                            file.type.startsWith('video/');


                        if (!isImage && !isVideo) {

                            this.mediaError = @js($isAr ? 'نوع ملف غير مدعوم.' : 'Unsupported file type.');

                            continue;

                        }


                        const maxSize = 20 * 1024 * 1024;


                        if (file.size > maxSize) {

                            this.mediaError = @js($isAr ? 'حجم كل ملف يجب ألا يتجاوز 20MB.' : 'Each file must be smaller than 20MB.');

                            continue;

                        }


                        const duplicate = this.media.some(
                            existing =>
                            existing.name === file.name &&
                            existing.size === file.size
                        );


                        if (duplicate) {

                            continue;

                        }


                        const index = this.media.length;


                        this.media.push({

                            id: Date.now() +
                                Math.random()
                                .toString(36)
                                .substring(2),

                            file: file,

                            name: file.name,

                            size: file.size,

                            type: file.type,

                            preview: URL.createObjectURL(file)

                        });


                        if (
                            this.primaryMediaIndex === null &&
                            isImage
                        ) {

                            this.primaryMediaIndex = index;

                        }

                    }


                    this.syncFiles();

                },


                setPrimary(index) {

                    if (
                        !this.media[index] ||
                        !this.media[index].type.startsWith('image/')
                    ) {

                        return;

                    }


                    this.primaryMediaIndex = index;

                },


                removeMedia(index) {

                    const item = this.media[index];


                    if (item?.preview) {

                        URL.revokeObjectURL(
                            item.preview
                        );

                    }


                    this.media.splice(index, 1);


                    if (this.media.length === 0) {

                        this.primaryMediaIndex = null;

                    } else if (
                        this.primaryMediaIndex === index
                    ) {

                        const nextImage =
                            this.media.findIndex(
                                item =>
                                item.type.startsWith('image/')
                            );

                        this.primaryMediaIndex =
                            nextImage >= 0 ?
                            nextImage :
                            null;

                    } else if (
                        this.primaryMediaIndex !== null &&
                        this.primaryMediaIndex > index
                    ) {

                        this.primaryMediaIndex--;

                    }


                    this.syncFiles();

                },


                clearMedia() {

                    this.media.forEach(item => {

                        if (item.preview) {

                            URL.revokeObjectURL(
                                item.preview
                            );

                        }

                    });


                    this.media = [];

                    this.primaryMediaIndex = null;

                    this.mediaError = '';

                    this.syncFiles();

                },


                syncFiles() {

                    const input =
                        this.$refs.mediaInput;


                    if (!input) {

                        return;

                    }


                    const dataTransfer =
                        new DataTransfer();


                    this.media.forEach(item => {

                        dataTransfer.items.add(
                            item.file
                        );

                    });


                    input.files =
                        dataTransfer.files;

                },


                formatSize(bytes) {

                    if (!bytes) {

                        return '0 KB';

                    }


                    const units = [
                        'Bytes',
                        'KB',
                        'MB',
                        'GB'
                    ];


                    const index =
                        Math.min(
                            Math.floor(
                                Math.log(bytes) /
                                Math.log(1024)
                            ),
                            units.length - 1
                        );


                    return (
                        parseFloat(
                            (
                                bytes /
                                Math.pow(1024, index)
                            ).toFixed(1)
                        ) +
                        ' ' +
                        units[index]
                    );

                },


                displayType() {

                    const types =
                        @js($opportunityTypes);

                    return (
                        types[
                            this.form
                            .opportunity_type
                        ] || '—'
                    );

                },


                displayCategory() {

                    const categories =
                        @js($categories);

                    return (
                        categories[
                            this.form.category
                        ] || '—'
                    );

                }

            };

        }
    </script>

</x-app-layout>
