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

        $currencies = [
            'USD' => 'USD — US Dollar',
            'AED' => 'AED — UAE Dirham',
            'EUR' => 'EUR — Euro',
            'GBP' => 'GBP — British Pound',
            'SAR' => 'SAR — Saudi Riyal',
        ];
    @endphp

    <div dir="{{ $dir }}" class="min-h-screen bg-[#06101d] text-white">

        <main class="mx-auto max-w-5xl px-5 pb-20 pt-32 lg:px-8">

            {{-- Header --}}
            <div class="mb-10">

                <a href="{{ route('opportunities.index') }}"
                    class="mb-5 inline-flex items-center gap-2 text-sm text-slate-400 transition hover:text-cyan-300">

                    <svg class="h-4 w-4 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>

                    {{ $isAr ? 'العودة إلى الفرص' : 'Back to Opportunities' }}

                </a>

                <div
                    class="inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1.5 text-xs font-semibold text-cyan-300">
                    {{ $isAr ? 'إضافة فرصة' : 'Add Opportunity' }}
                </div>

                <h1 class="mt-4 text-3xl font-black md:text-4xl">
                    {{ $isAr ? 'أضف فرصتك التجارية' : 'Submit Your Opportunity' }}
                </h1>

                <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-400">
                    {{ $isAr
                        ? 'أرسل فرصة تجارية أو استثمارية أو لوجستية إلى شبكة HAG Brokers. ستتم مراجعتها قبل نشرها.'
                        : 'Submit a business, investment or logistics opportunity to the HAG Brokers network. Your opportunity will be reviewed before publication.' }}
                </p>

            </div>

            {{-- Validation --}}
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-400/20 bg-red-400/10 p-5 text-sm text-red-300">

                    <div class="font-bold">
                        {{ $isAr ? 'يرجى تصحيح الأخطاء التالية:' : 'Please correct the following errors:' }}
                    </div>

                    <ul class="mt-2 list-disc space-y-1 ps-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('opportunities.store') }}" class="space-y-6">

                @csrf

                {{-- Opportunity Information --}}
                <section class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl md:p-8">

                    <div class="mb-6">
                        <h2 class="text-xl font-bold">
                            {{ $isAr ? 'معلومات الفرصة' : 'Opportunity Information' }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $isAr ? 'المعلومات الأساسية للفرصة.' : 'Basic information about the opportunity.' }}
                        </p>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">

                        {{-- Title --}}
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-semibold text-slate-300">
                                {{ $isAr ? 'عنوان الفرصة' : 'Opportunity Title' }}
                                <span class="text-cyan-400">*</span>
                            </label>

                            <input type="text" name="title" value="{{ old('title') }}" required
                                placeholder="{{ $isAr ? 'مثال: فرصة استيراد منتجات غذائية إلى الإمارات' : 'Example: Food import opportunity to UAE' }}"
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/20">
                        </div>

                        {{-- Type --}}
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-300">
                                {{ $isAr ? 'نوع الفرصة' : 'Opportunity Type' }}
                                <span class="text-cyan-400">*</span>
                            </label>

                            <select name="opportunity_type" required
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none focus:border-cyan-400/50">

                                @foreach ($opportunityTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('opportunity_type', 'business') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        {{-- Category --}}
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-300">
                                {{ $isAr ? 'الفئة' : 'Category' }}
                            </label>

                            <select name="category"
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none focus:border-cyan-400/50">

                                <option value="">
                                    {{ $isAr ? 'اختر الفئة' : 'Select Category' }}
                                </option>

                                @foreach ($categories as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        {{-- Description --}}
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-semibold text-slate-300">
                                {{ $isAr ? 'وصف الفرصة' : 'Opportunity Description' }}
                                <span class="text-cyan-400">*</span>
                            </label>

                            <textarea name="description" rows="6" required
                                placeholder="{{ $isAr ? 'اشرح الفرصة، المنتج أو الخدمة، السوق المستهدف، الاحتياج، وطريقة التعاون...' : 'Describe the opportunity, product/service, target market, requirements and potential cooperation...' }}"
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm leading-7 text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/20">{{ old('description') }}</textarea>
                        </div>

                    </div>

                </section>

                {{-- Location --}}
                <section class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl md:p-8">

                    <div class="mb-6">
                        <h2 class="text-xl font-bold">
                            {{ $isAr ? 'الموقع والشحن' : 'Location & Logistics' }}
                        </h2>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-300">
                                {{ $isAr ? 'الدولة' : 'Country' }}
                            </label>

                            <input type="text" name="country" value="{{ old('country') }}"
                                placeholder="{{ $isAr ? 'الإمارات العربية المتحدة' : 'United Arab Emirates' }}"
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-300">
                                {{ $isAr ? 'المدينة' : 'City' }}
                            </label>

                            <input type="text" name="city" value="{{ old('city') }}"
                                placeholder="{{ $isAr ? 'دبي' : 'Dubai' }}"
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-300">
                                {{ $isAr ? 'نوع البضاعة' : 'Cargo Type' }}
                            </label>

                            <input type="text" name="cargo_type" value="{{ old('cargo_type') }}"
                                placeholder="{{ $isAr ? 'مواد غذائية، إلكترونيات...' : 'Food, Electronics...' }}"
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-300">
                                {{ $isAr ? 'نوع الحاوية' : 'Container Type' }}
                            </label>

                            <input type="text" name="container_type" value="{{ old('container_type') }}"
                                placeholder="40HC"
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50">
                        </div>

                    </div>

                </section>

                {{-- Financial --}}
                <section class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl md:p-8">

                    <div class="mb-6">
                        <h2 class="text-xl font-bold">
                            {{ $isAr ? 'المعلومات المالية' : 'Financial Information' }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $isAr ? 'أدخل الأرقام التقريبية إذا كانت متاحة.' : 'Provide approximate figures if available.' }}
                        </p>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-300">
                                {{ $isAr ? 'القيمة التقديرية' : 'Estimated Value' }}
                            </label>

                            <input type="number" step="0.01" min="0" name="estimated_value"
                                value="{{ old('estimated_value') }}"
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none focus:border-cyan-400/50">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-300">
                                {{ $isAr ? 'رأس المال المطلوب' : 'Required Capital' }}
                            </label>

                            <input type="number" step="0.01" min="0" name="required_capital"
                                value="{{ old('required_capital') }}"
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none focus:border-cyan-400/50">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-300">
                                {{ $isAr ? 'الحد الأدنى للاستثمار' : 'Minimum Investment' }}
                            </label>

                            <input type="number" step="0.01" min="0" name="min_investment"
                                value="{{ old('min_investment') }}"
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none focus:border-cyan-400/50">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-300">
                                {{ $isAr ? 'العملة' : 'Currency' }}
                            </label>

                            <select name="currency"
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none focus:border-cyan-400/50">

                                @foreach ($currencies as $value => $label)
                                    <option value="{{ $value }}" @selected(old('currency', 'USD') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                    </div>

                </section>

                {{-- Commission --}}
                <section class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl md:p-8">

                    <div class="mb-6">
                        <h2 class="text-xl font-bold">
                            {{ $isAr ? 'العمولة' : 'Commission' }}
                        </h2>

                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            {{ $isAr
                                ? 'يمكن تحديد نموذج العمولة المقترح. سيتم الاتفاق النهائي بشكل رسمي عند إتمام الصفقة.'
                                : 'Define the proposed commission model. Final terms will be agreed contractually when the deal is completed.' }}
                        </p>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-300">
                                {{ $isAr ? 'نوع العمولة' : 'Commission Type' }}
                            </label>

                            <select name="commission_type"
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none focus:border-cyan-400/50">

                                <option value="percentage" @selected(old('commission_type', 'percentage') === 'percentage')>
                                    {{ $isAr ? 'نسبة مئوية' : 'Percentage' }}
                                </option>

                                <option value="fixed" @selected(old('commission_type') === 'fixed')>
                                    {{ $isAr ? 'مبلغ ثابت' : 'Fixed Amount' }}
                                </option>

                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-300">
                                {{ $isAr ? 'قيمة العمولة' : 'Commission Rate' }}
                            </label>

                            <input type="number" step="0.01" min="0" name="commission_rate"
                                value="{{ old('commission_rate') }}"
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none focus:border-cyan-400/50">
                        </div>

                    </div>

                </section>

                {{-- Timeline --}}
                <section class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl md:p-8">

                    <div class="mb-6">
                        <h2 class="text-xl font-bold">
                            {{ $isAr ? 'الجدول الزمني' : 'Timeline' }}
                        </h2>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-300">
                                {{ $isAr ? 'تاريخ الإغلاق المتوقع' : 'Expected Close Date' }}
                            </label>

                            <input type="date" name="expected_close_date"
                                value="{{ old('expected_close_date') }}"
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none focus:border-cyan-400/50">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-300">
                                {{ $isAr ? 'تاريخ انتهاء الفرصة' : 'Opportunity Expiry' }}
                            </label>

                            <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none focus:border-cyan-400/50">
                        </div>

                    </div>

                </section>

                {{-- Notes --}}
                <section class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl md:p-8">

                    <label class="mb-2 block text-sm font-semibold text-slate-300">
                        {{ $isAr ? 'ملاحظات إضافية' : 'Additional Notes' }}
                    </label>

                    <textarea name="notes" rows="5"
                        placeholder="{{ $isAr ? 'أي معلومات إضافية يمكن أن تساعد فريق HAG Brokers...' : 'Any additional information that may help HAG Brokers review the opportunity...' }}"
                        class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm leading-7 text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50">{{ old('notes') }}</textarea>

                </section>

                {{-- Verification Notice --}}
                <div class="rounded-3xl border border-cyan-400/20 bg-cyan-400/5 p-6">

                    <div class="flex gap-4">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10">
                            <svg class="h-5 w-5 text-cyan-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 110-20 10 10 0 010 20z" />
                            </svg>
                        </div>

                        <div>
                            <h3 class="font-bold text-cyan-300">
                                {{ $isAr ? 'مراجعة HAG Brokers' : 'HAG Brokers Review' }}
                            </h3>

                            <p class="mt-2 text-sm leading-7 text-slate-400">
                                {{ $isAr
                                    ? 'بعد إرسال الفرصة، سيقوم فريق HAG Brokers بمراجعة المعلومات. لن يتم نشر الفرصة للعامة حتى يتم التحقق منها والموافقة عليها.'
                                    : 'After submission, the HAG Brokers team will review the information. The opportunity will not be publicly listed until it has been reviewed and verified.' }}
                            </p>
                        </div>

                    </div>

                </div>

                {{-- Actions --}}
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                    <a href="{{ route('opportunities.index') }}"
                        class="rounded-xl border border-white/10 px-6 py-3 text-center text-sm font-semibold text-slate-300 transition hover:bg-white/5">
                        {{ $isAr ? 'إلغاء' : 'Cancel' }}
                    </a>

                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-400 px-7 py-3 text-sm font-bold text-slate-950 transition hover:bg-cyan-300">

                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>

                        {{ $isAr ? 'إرسال الفرصة للمراجعة' : 'Submit Opportunity for Review' }}

                    </button>

                </div>

            </form>

        </main>

    </div>

</x-app-layout>
