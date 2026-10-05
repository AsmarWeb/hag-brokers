@php
    $isAr = app()->getLocale() === 'ar';
    $user = auth()->user();
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        {{ $isAr ? 'جلب صفقة | HAG Brokers' : 'Bring a Deal | HAG Brokers' }}
    </title>

    <meta name="description"
        content="{{ $isAr
            ? 'أرسل تفاصيل صفقة أو فرصة تجارية إلى فريق HAG Brokers.'
            : 'Submit your business deal or commercial opportunity to HAG Brokers.' }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            background:
                radial-gradient(circle at 10% 10%,
                    rgba(34, 211, 238, .08),
                    transparent 28%),
                radial-gradient(circle at 90% 20%,
                    rgba(14, 165, 233, .07),
                    transparent 30%),
                #06101d;
        }

        .glass {
            background: rgba(8, 22, 37, .76);
            border: 1px solid rgba(148, 163, 184, .12);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .input-field {
            width: 100%;
            border-radius: 0.9rem;
            border: 1px solid rgba(255, 255, 255, .08);
            background: rgba(3, 12, 22, .7);
            color: white;
            padding: .8rem 1rem;
            font-size: .875rem;
            outline: none;
            transition: .2s ease;
        }

        .input-field:focus {
            border-color: rgba(34, 211, 238, .5);
            box-shadow: 0 0 0 3px rgba(34, 211, 238, .06);
        }

        .input-field::placeholder {
            color: #475569;
        }

        .input-error {
            border-color: rgba(248, 113, 113, .5) !important;
        }

        select.input-field option {
            background: #06101d;
            color: white;
        }

        textarea.input-field {
            min-height: 150px;
        }
    </style>

</head>


<body class="min-h-screen text-slate-100 antialiased">


    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <header class="border-b border-white/5 bg-[#06101d]/90 backdrop-blur-xl">

        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">

            <a href="{{ route('home') }}" class="flex items-center gap-3">

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-300">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3 20h18M5 20V9l7-5 7 5v11M9 20v-6h6v6" />
                    </svg>

                </div>

                <div>

                    <div class="font-black text-white">
                        HAG <span class="text-cyan-300">Brokers</span>
                    </div>

                    <div class="text-[9px] uppercase tracking-[.25em] text-slate-600">
                        Business Opportunities
                    </div>

                </div>

            </a>


            <a href="{{ route('home') }}"
                class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-bold text-slate-300 transition hover:bg-white/10 hover:text-white">
                {{ $isAr ? 'العودة للرئيسية' : 'Back to website' }}
            </a>

        </div>

    </header>


    {{-- =========================================================
         PAGE
    ========================================================== --}}

    <main>

        <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">


            {{-- INTRO --}}
            <div class="mx-auto max-w-3xl text-center">

                <div
                    class="inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/5 px-3 py-1.5 text-xs font-bold text-cyan-300">

                    <span class="h-1.5 w-1.5 rounded-full bg-cyan-300"></span>

                    HAG Brokers Deal Desk

                </div>


                <h1 class="mt-5 text-3xl font-black tracking-tight text-white sm:text-5xl">
                    {{ $isAr ? 'لديك صفقة؟ دعنا نساعدك في جلبها.' : 'Have a deal? Let us help you bring it to the right people.' }}
                </h1>


                <p class="mx-auto mt-5 max-w-2xl text-sm leading-7 text-slate-400 sm:text-base">
                    {{ $isAr
                        ? 'أخبرنا باختصار عن الصفقة أو الفرصة. لا تحتاج إلى معرفة كل التفاصيل الآن، فريق HAG Brokers سيتواصل معك لفهم الفرصة وتأهيلها.'
                        : 'Tell us briefly about your deal or opportunity. You do not need to know every detail yet. Our team will contact you to understand and qualify the opportunity.' }}
                </p>

            </div>


            {{-- SUCCESS --}}
            @if (session('success'))
                <div class="mx-auto mt-8 max-w-4xl rounded-2xl border border-emerald-400/20 bg-emerald-400/5 p-5">

                    <div class="flex gap-3">

                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-400/10 text-emerald-300">
                            ✓
                        </div>

                        <div>

                            <div class="font-bold text-emerald-300">
                                {{ $isAr ? 'تم استلام الطلب' : 'Request received' }}
                            </div>

                            <div class="mt-1 text-sm leading-6 text-slate-400">
                                {{ session('success') }}
                            </div>

                        </div>

                    </div>

                </div>
            @endif


            {{-- VALIDATION ERRORS --}}
            @if ($errors->any())

                <div class="mx-auto mt-8 max-w-4xl rounded-2xl border border-red-400/20 bg-red-400/5 p-5">

                    <div class="font-bold text-red-300">
                        {{ $isAr ? 'يرجى مراجعة البيانات' : 'Please review the information' }}
                    </div>

                    <ul class="mt-3 space-y-1 text-sm text-red-200/70">

                        @foreach ($errors->all() as $error)
                            <li>
                                • {{ $error }}
                            </li>
                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- FORM --}}
            <form method="POST" action="{{ route('leads.store') }}" class="mx-auto mt-8 max-w-4xl">

                @csrf


                <div class="glass rounded-3xl p-5 shadow-2xl sm:p-8">


                    {{-- =================================================
                         STEP 1
                    ================================================== --}}

                    <section>

                        <div class="mb-6 flex items-start gap-4">

                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10 font-black text-cyan-300">
                                01
                            </div>

                            <div>

                                <h2 class="text-lg font-black text-white">
                                    {{ $isAr ? 'من أنت؟' : 'Who are you?' }}
                                </h2>

                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    {{ $isAr ? 'بيانات بسيطة حتى يتمكن فريقنا من التواصل معك.' : 'Basic information so our team can contact you.' }}
                                </p>

                            </div>

                        </div>


                        <div class="grid gap-4 sm:grid-cols-2">


                            {{-- NAME --}}
                            <div>

                                <label for="name" class="mb-2 block text-xs font-bold text-slate-400">
                                    {{ $isAr ? 'الاسم' : 'Name' }}
                                    <span class="text-cyan-400">*</span>
                                </label>

                                <input id="name" type="text" name="name"
                                    value="{{ old('name', $user?->name ?? '') }}" required autocomplete="name"
                                    dir="{{ $isAr ? 'rtl' : 'ltr' }}"
                                    class="input-field {{ $errors->has('name') ? 'input-error' : '' }}"
                                    placeholder="{{ $isAr ? 'اسمك الكامل' : 'Your full name' }}">

                                @error('name')
                                    <p class="mt-1.5 text-xs text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- COMPANY --}}
                            <div>

                                <label for="company_name" class="mb-2 block text-xs font-bold text-slate-400">
                                    {{ $isAr ? 'اسم الشركة' : 'Company name' }}
                                </label>

                                <input id="company_name" type="text" name="company_name"
                                    value="{{ old('company_name') }}" autocomplete="organization"
                                    dir="{{ $isAr ? 'rtl' : 'ltr' }}"
                                    class="input-field {{ $errors->has('company_name') ? 'input-error' : '' }}"
                                    placeholder="{{ $isAr ? 'اسم الشركة إن وجد' : 'Company name if applicable' }}">

                                @error('company_name')
                                    <p class="mt-1.5 text-xs text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- EMAIL --}}
                            <div>

                                <label for="email" class="mb-2 block text-xs font-bold text-slate-400">
                                    {{ $isAr ? 'البريد الإلكتروني' : 'Email' }}
                                </label>

                                <input id="email" type="email" name="email"
                                    value="{{ old('email', $user?->email ?? '') }}" autocomplete="email" dir="ltr"
                                    class="input-field {{ $errors->has('email') ? 'input-error' : '' }}"
                                    placeholder="name@company.com">

                                @error('email')
                                    <p class="mt-1.5 text-xs text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- PHONE --}}
                            <div>

                                <label for="phone" class="mb-2 block text-xs font-bold text-slate-400">
                                    {{ $isAr ? 'رقم الهاتف / واتساب' : 'Phone / WhatsApp' }}
                                    <span class="text-cyan-400">*</span>
                                </label>

                                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}"
                                    required inputmode="tel" autocomplete="tel" dir="ltr"
                                    class="input-field {{ $errors->has('phone') ? 'input-error' : '' }}"
                                    placeholder="+971 5X XXX XXXX">

                                @error('phone')
                                    <p class="mt-1.5 text-xs text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>

                    </section>


                    <div class="my-8 border-t border-white/5"></div>


                    {{-- =================================================
                         STEP 2
                    ================================================== --}}

                    <section>

                        <div class="mb-6 flex items-start gap-4">

                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10 font-black text-cyan-300">
                                02
                            </div>

                            <div>

                                <h2 class="text-lg font-black text-white">
                                    {{ $isAr ? 'ما نوع الصفقة؟' : 'What kind of deal?' }}
                                </h2>

                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    {{ $isAr ? 'اختر الأقرب إلى طبيعة الصفقة.' : 'Choose the option closest to your deal.' }}
                                </p>

                            </div>

                        </div>


                        <div class="grid gap-4 sm:grid-cols-2">


                            {{-- DEAL TYPE --}}
                            <div>

                                <label for="deal_type" class="mb-2 block text-xs font-bold text-slate-400">
                                    {{ $isAr ? 'نوع الصفقة' : 'Deal type' }}
                                    <span class="text-cyan-400">*</span>
                                </label>

                                <select id="deal_type" name="deal_type" required
                                    class="input-field {{ $errors->has('deal_type') ? 'input-error' : '' }}">

                                    <option value="">
                                        {{ $isAr ? 'اختر نوع الصفقة' : 'Select deal type' }}
                                    </option>

                                    @foreach ([
        'trade' => $isAr ? 'تجارة' : 'Trade',
        'import' => $isAr ? 'استيراد' : 'Import',
        'export' => $isAr ? 'تصدير' : 'Export',
        'investment' => $isAr ? 'استثمار' : 'Investment',
        'partnership' => $isAr ? 'شراكة' : 'Partnership',
        'procurement' => $isAr ? 'مشتريات' : 'Procurement',
        'distribution' => $isAr ? 'توزيع' : 'Distribution',
        'logistics' => $isAr ? 'لوجستيات' : 'Logistics',
        'other' => $isAr ? 'أخرى' : 'Other',
    ] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('deal_type') === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach

                                </select>

                                @error('deal_type')
                                    <p class="mt-1.5 text-xs text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- CATEGORY --}}
                            <div>

                                <label for="category" class="mb-2 block text-xs font-bold text-slate-400">
                                    {{ $isAr ? 'المجال' : 'Category' }}
                                </label>

                                <select id="category" name="category"
                                    class="input-field {{ $errors->has('category') ? 'input-error' : '' }}">

                                    <option value="">
                                        {{ $isAr ? 'اختر المجال' : 'Select category' }}
                                    </option>

                                    @foreach ([
        'sea_freight' => $isAr ? 'شحن بحري' : 'Sea Freight',
        'import' => $isAr ? 'استيراد' : 'Import',
        'export' => $isAr ? 'تصدير' : 'Export',
        'distribution' => $isAr ? 'توزيع' : 'Distribution',
        'manufacturing' => $isAr ? 'تصنيع' : 'Manufacturing',
        'real_estate' => $isAr ? 'عقارات' : 'Real Estate',
        'technology' => $isAr ? 'تقنية' : 'Technology',
        'services' => $isAr ? 'خدمات' : 'Services',
    ] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('category') === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach

                                </select>

                                @error('category')
                                    <p class="mt-1.5 text-xs text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>

                    </section>


                    <div class="my-8 border-t border-white/5"></div>


                    {{-- =================================================
                         STEP 3
                    ================================================== --}}

                    <section>

                        <div class="mb-6 flex items-start gap-4">

                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10 font-black text-cyan-300">
                                03
                            </div>

                            <div>

                                <h2 class="text-lg font-black text-white">
                                    {{ $isAr ? 'أخبرنا عن الصفقة' : 'Tell us about the deal' }}
                                </h2>

                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    {{ $isAr
                                        ? 'لا تحتاج إلى صياغة احترافية. فقط أعطنا الصورة الأساسية.'
                                        : 'You do not need a polished pitch. Just give us the basic picture.' }}
                                </p>

                            </div>

                        </div>


                        {{-- TITLE --}}
                        <div>

                            <label for="title" class="mb-2 block text-xs font-bold text-slate-400">
                                {{ $isAr ? 'عنوان الصفقة' : 'Deal title' }}
                                <span class="text-cyan-400">*</span>
                            </label>

                            <input id="title" type="text" name="title" value="{{ old('title') }}"
                                required class="input-field {{ $errors->has('title') ? 'input-error' : '' }}"
                                placeholder="{{ $isAr
                                    ? 'مثال: فرصة توزيع منتجات غذائية في الإمارات'
                                    : 'Example: Food products distribution opportunity in UAE' }}">

                            @error('title')
                                <p class="mt-1.5 text-xs text-red-400">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- DESCRIPTION --}}
                        <div class="mt-4">

                            <label for="description" class="mb-2 block text-xs font-bold text-slate-400">
                                {{ $isAr ? 'تفاصيل الصفقة' : 'Deal details' }}
                                <span class="text-cyan-400">*</span>
                            </label>

                            <textarea id="description" name="description" rows="6" required minlength="20" maxlength="10000"
                                class="input-field resize-y leading-7 {{ $errors->has('description') ? 'input-error' : '' }}"
                                placeholder="{{ $isAr
                                    ? 'ما الذي تبحث عنه؟ من الطرف الآخر؟ ما المنتج أو الخدمة؟ وما المطلوب لإنجاز الصفقة؟'
                                    : 'What are you looking for? Who is the other party? What is the product or service? What is needed to close the deal?' }}">{{ old('description') }}</textarea>

                            <div class="mt-1 flex justify-between">

                                @error('description')
                                    <p class="text-xs text-red-400">
                                        {{ $message }}
                                    </p>
                                @else
                                    <span></span>
                                @enderror

                                <span class="text-[10px] text-slate-600">
                                    {{ $isAr ? '20 حرفًا على الأقل' : 'Minimum 20 characters' }}
                                </span>

                            </div>

                        </div>

                    </section>


                    <div class="my-8 border-t border-white/5"></div>


                    {{-- =================================================
                         STEP 4
                    ================================================== --}}

                    <section>

                        <div class="mb-6 flex items-start gap-4">

                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10 font-black text-cyan-300">
                                04
                            </div>

                            <div>

                                <h2 class="text-lg font-black text-white">
                                    {{ $isAr ? 'القيمة والموقع' : 'Value & location' }}
                                </h2>

                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    {{ $isAr
                                        ? 'هذه المعلومات اختيارية، لكنها تساعدنا على تقييم الصفقة بسرعة.'
                                        : 'Optional information that helps us qualify the deal faster.' }}
                                </p>

                            </div>

                        </div>


                        <div class="grid gap-4 sm:grid-cols-3">


                            {{-- VALUE --}}
                            <div class="sm:col-span-2">

                                <label for="estimated_value" class="mb-2 block text-xs font-bold text-slate-400">
                                    {{ $isAr ? 'القيمة التقديرية' : 'Estimated value' }}
                                </label>

                                <input id="estimated_value" type="number" name="estimated_value"
                                    value="{{ old('estimated_value') }}" min="0" step="0.01"
                                    inputmode="decimal"
                                    class="input-field {{ $errors->has('estimated_value') ? 'input-error' : '' }}"
                                    placeholder="0.00">

                                @error('estimated_value')
                                    <p class="mt-1.5 text-xs text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- CURRENCY --}}
                            <div>

                                <label for="currency" class="mb-2 block text-xs font-bold text-slate-400">
                                    {{ $isAr ? 'العملة' : 'Currency' }}
                                    <span class="text-cyan-400">*</span>
                                </label>

                                <select id="currency" name="currency" required
                                    class="input-field {{ $errors->has('currency') ? 'input-error' : '' }}">

                                    @foreach ([
        'AED' => 'AED — UAE Dirham',
        'USD' => 'USD — US Dollar',
        'EUR' => 'EUR — Euro',
        'GBP' => 'GBP — Pound',
        'SAR' => 'SAR — Saudi Riyal',
    ] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('currency', 'AED') === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach

                                </select>

                                @error('currency')
                                    <p class="mt-1.5 text-xs text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- COUNTRY --}}
                            <div>

                                <label for="country" class="mb-2 block text-xs font-bold text-slate-400">
                                    {{ $isAr ? 'الدولة' : 'Country' }}
                                </label>

                                <input id="country" type="text" name="country"
                                    value="{{ old('country', 'UAE') }}" autocomplete="country-name"
                                    class="input-field {{ $errors->has('country') ? 'input-error' : '' }}"
                                    placeholder="{{ $isAr ? 'الإمارات' : 'UAE' }}">

                                @error('country')
                                    <p class="mt-1.5 text-xs text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- CITY --}}
                            <div>

                                <label for="city" class="mb-2 block text-xs font-bold text-slate-400">
                                    {{ $isAr ? 'المدينة' : 'City' }}
                                </label>

                                <input id="city" type="text" name="city" value="{{ old('city') }}"
                                    autocomplete="address-level2"
                                    class="input-field {{ $errors->has('city') ? 'input-error' : '' }}"
                                    placeholder="{{ $isAr ? 'دبي' : 'Dubai' }}">

                                @error('city')
                                    <p class="mt-1.5 text-xs text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>

                    </section>


                    <div class="my-8 border-t border-white/5"></div>


                    {{-- =================================================
                         STEP 5
                    ================================================== --}}

                    <section>

                        <div class="mb-6 flex items-start gap-4">

                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10 font-black text-cyan-300">
                                05
                            </div>

                            <div>

                                <h2 class="text-lg font-black text-white">
                                    {{ $isAr ? 'كيف نتواصل معك؟' : 'How should we contact you?' }}
                                </h2>

                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    {{ $isAr
                                        ? 'سنستخدم هذه الطريقة للتواصل الأولي بشأن الصفقة.'
                                        : 'We will use this preference for the initial follow-up.' }}
                                </p>

                            </div>

                        </div>


                        <div class="grid gap-3 sm:grid-cols-3">

                            @foreach ([
        'whatsapp' => $isAr ? 'واتساب' : 'WhatsApp',
        'phone' => $isAr ? 'اتصال هاتفي' : 'Phone',
        'email' => $isAr ? 'البريد الإلكتروني' : 'Email',
    ] as $value => $label)
                                <label class="cursor-pointer">

                                    <input type="radio" name="preferred_contact" value="{{ $value }}"
                                        class="peer sr-only" @checked(old('preferred_contact', 'whatsapp') === $value)>

                                    <div
                                        class="rounded-xl border border-white/10 bg-white/[.025] p-4 text-center transition peer-checked:border-cyan-400/50 peer-checked:bg-cyan-400/10">

                                        <div class="text-sm font-bold text-white">
                                            {{ $label }}
                                        </div>

                                    </div>

                                </label>
                            @endforeach

                        </div>

                        @error('preferred_contact')
                            <p class="mt-2 text-xs text-red-400">
                                {{ $message }}
                            </p>
                        @enderror

                    </section>


                    {{-- TRUST NOTICE --}}
                    <div class="mt-8 rounded-2xl border border-cyan-400/15 bg-cyan-400/5 p-5">

                        <div class="flex gap-3">

                            <div class="shrink-0 text-lg text-cyan-300">
                                🔒
                            </div>

                            <div>

                                <div class="text-sm font-bold text-cyan-300">
                                    {{ $isAr ? 'معلوماتك تستخدم لتأهيل الصفقة فقط' : 'Your information is used to qualify the deal' }}
                                </div>

                                <p class="mt-1 text-xs leading-6 text-slate-500">
                                    {{ $isAr
                                        ? 'سيقوم فريق HAG Brokers بمراجعة البيانات والتواصل معك لفهم الصفقة. لن يتم نشر بيانات الاتصال الخاصة بك للعامة.'
                                        : 'The HAG Brokers team will review the information and contact you to understand the deal. Your contact details will not be publicly displayed.' }}
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- SUBMIT --}}
                    <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">

                        <a href="{{ route('home') }}"
                            class="text-center text-sm font-bold text-slate-500 transition hover:text-white">
                            {{ $isAr ? 'إلغاء' : 'Cancel' }}
                        </a>


                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-cyan-400 px-7 py-3.5 text-sm font-black text-[#06101d] shadow-lg shadow-cyan-950/20 transition hover:bg-cyan-300">

                            {{ $isAr ? 'إرسال تفاصيل الصفقة' : 'Submit deal details' }}

                            <span class="{{ $isAr ? 'mr-2' : 'ml-2' }}">
                                {{ $isAr ? '←' : '→' }}
                            </span>

                        </button>

                    </div>

                </div>

            </form>

        </section>

    </main>


    {{-- FOOTER --}}
    <footer class="border-t border-white/5 bg-[#040c15]">

        <div class="mx-auto max-w-7xl px-4 py-8 text-center text-xs text-slate-600">

            © {{ date('Y') }} HAG Brokers.
            {{ $isAr ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}

        </div>

    </footer>

</body>

</html>
