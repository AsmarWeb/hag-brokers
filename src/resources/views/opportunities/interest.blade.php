<x-app-layout>

    @php
        $isAr = app()->getLocale() === 'ar';
        $dir = $isAr ? 'rtl' : 'ltr';

        $roles = [
            'investor' => $isAr ? 'مستثمر' : 'Investor',
            'business_owner' => $isAr ? 'صاحب عمل' : 'Business Owner',
            'buyer' => $isAr ? 'مشتري' : 'Buyer',
            'supplier' => $isAr ? 'مورد' : 'Supplier',
            'broker' => $isAr ? 'وسيط' : 'Broker',
            'partner' => $isAr ? 'شريك' : 'Partner',
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


    <div dir="{{ $dir }}" class="min-h-screen bg-[#06101d] text-white" x-data="interestWizard()">

        <main class="mx-auto max-w-5xl px-4 pb-20 pt-28 sm:px-6 lg:px-8">

            {{-- Back --}}
            <a href="{{ route('opportunities.show', $opportunity) }}"
                class="mb-7 inline-flex items-center gap-2 text-sm text-slate-400 transition hover:text-cyan-300">

                <svg class="h-4 w-4 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>

                {{ $isAr ? 'العودة إلى الفرصة' : 'Back to Opportunity' }}

            </a>


            {{-- Header --}}
            <div class="mb-8">

                <div
                    class="inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1.5 text-xs font-semibold text-cyan-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-cyan-300"></span>

                    {{ $isAr ? 'إبداء الاهتمام' : 'Express Interest' }}
                </div>


                <h1 class="mt-4 text-3xl font-black tracking-tight md:text-4xl">
                    {{ $isAr ? 'خطوة واحدة نحو الفرصة' : 'Take the Next Step' }}
                </h1>


                <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-400">
                    {{ $isAr
                        ? 'أخبرنا عن اهتمامك وسيتولى فريق HAG Brokers مراجعة طلبك والتواصل معك.'
                        : 'Tell us about your interest and the HAG Brokers team will review your request and follow up with you.' }}
                </p>

            </div>


            {{-- Existing Interest --}}
            @if ($existingInterest)

                <section class="rounded-3xl border border-amber-400/20 bg-amber-400/5 p-6 md:p-8">

                    <div class="flex items-start gap-4">

                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-400/10 text-lg font-black text-amber-300">
                            !
                        </div>

                        <div class="min-w-0">

                            <h2 class="font-bold text-amber-300">
                                {{ $isAr ? 'تم إرسال اهتمامك مسبقاً' : 'Interest Already Submitted' }}
                            </h2>

                            <p class="mt-2 text-sm leading-7 text-slate-400">
                                {{ $isAr
                                    ? 'لقد قمت بالفعل بإبداء اهتمامك بهذه الفرصة. سيقوم فريق HAG Brokers بمراجعة طلبك.'
                                    : 'You have already expressed interest in this opportunity. HAG Brokers will review your request.' }}
                            </p>


                            <div class="mt-5">

                                @if ($existingInterest->status === 'pending')
                                    <span
                                        class="rounded-full bg-amber-400/10 px-3 py-1.5 text-xs font-semibold text-amber-300">
                                        {{ $isAr ? 'قيد المراجعة' : 'Pending Review' }}
                                    </span>
                                @elseif ($existingInterest->status === 'approved')
                                    <span
                                        class="rounded-full bg-emerald-400/10 px-3 py-1.5 text-xs font-semibold text-emerald-300">
                                        {{ $isAr ? 'تمت الموافقة' : 'Approved' }}
                                    </span>
                                @elseif ($existingInterest->status === 'rejected')
                                    <span
                                        class="rounded-full bg-red-400/10 px-3 py-1.5 text-xs font-semibold text-red-300">
                                        {{ $isAr ? 'مرفوض' : 'Rejected' }}
                                    </span>
                                @else
                                    <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs text-slate-400">
                                        {{ ucfirst($existingInterest->status) }}
                                    </span>
                                @endif

                            </div>

                        </div>

                    </div>

                </section>
            @else
                {{-- Validation --}}
                @if ($errors->any())

                    <div class="mb-6 rounded-2xl border border-red-400/20 bg-red-400/10 p-5 text-sm text-red-300">

                        <div class="font-bold">
                            {{ $isAr ? 'يرجى تصحيح الأخطاء:' : 'Please correct the following:' }}
                        </div>

                        <ul class="mt-2 list-disc space-y-1 ps-5">

                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach

                        </ul>

                    </div>

                @endif


                {{-- Opportunity Summary --}}
                <section class="mb-7 overflow-hidden rounded-3xl border border-cyan-400/15 bg-white/[0.035]">

                    <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:justify-between md:p-6">

                        <div class="min-w-0">

                            <div class="text-xs font-semibold uppercase tracking-wider text-cyan-300">
                                {{ $isAr ? 'أنت مهتم بـ' : 'You are interested in' }}
                            </div>

                            <h2 class="mt-2 truncate text-xl font-black text-white">
                                {{ $opportunity->title }}
                            </h2>


                            @if ($opportunity->country || $opportunity->city)
                                <div class="mt-2 flex items-center gap-2 text-sm text-slate-400">

                                    <svg class="h-4 w-4 text-cyan-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-width="1.8"
                                            d="M17.657 16.657L13.414 21a2 2 0 01-2.828 0l-4.243-4.343a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-width="1.8"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>

                                    {{ collect([$opportunity->city, $opportunity->country])->filter()->implode(', ') }}

                                </div>
                            @endif

                        </div>


                        <a href="{{ route('opportunities.show', $opportunity) }}"
                            class="shrink-0 rounded-xl border border-white/10 px-4 py-2.5 text-center text-xs font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">
                            {{ $isAr ? 'عرض الفرصة' : 'View Opportunity' }}
                        </a>

                    </div>

                </section>


                {{-- Progress --}}
                <section class="mb-7">

                    <div class="flex items-center justify-between">

                        <div>
                            <div class="text-xs font-semibold text-cyan-300">
                                {{ $isAr ? 'الخطوة' : 'Step' }}
                                <span x-text="step"></span>
                                {{ $isAr ? 'من 3' : 'of 3' }}
                            </div>

                            <div class="mt-1 text-sm font-bold" x-text="stepTitle"></div>
                        </div>


                        <div class="text-xs text-slate-500">
                            <span x-text="progress"></span>%
                        </div>

                    </div>


                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/5">

                        <div class="h-full rounded-full bg-cyan-400 transition-all duration-500"
                            :style="`width: ${progress}%`"></div>

                    </div>


                    <div class="mt-3 grid grid-cols-3 gap-2">

                        <template x-for="item in [1,2,3]" :key="item">

                            <div class="h-1 rounded-full transition"
                                :class="item <= step ? 'bg-cyan-400' : 'bg-white/10'"></div>

                        </template>

                    </div>

                </section>


                {{-- Form --}}
                <form method="POST" action="{{ route('opportunities.interest.store', $opportunity) }}"
                    @submit="submitting = true">

                    @csrf


                    {{-- =================================================
                        STEP 1
                    ================================================== --}}
                    <section x-show="step === 1" x-transition.opacity.duration.200ms
                        class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl md:p-8">

                        <div class="flex items-start gap-4">

                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-cyan-400/10 text-sm font-black text-cyan-300">
                                01
                            </div>

                            <div>

                                <h2 class="text-xl font-bold">
                                    {{ $isAr ? 'من أنت؟' : 'Who Are You?' }}
                                </h2>

                                <p class="mt-2 text-sm leading-7 text-slate-500">
                                    {{ $isAr
                                        ? 'اختر الصفة التي تصف دورك في هذه الفرصة.'
                                        : 'Choose the role that best describes your involvement in this opportunity.' }}
                                </p>

                            </div>

                        </div>


                        <div class="mt-8">

                            <label class="mb-3 block text-sm font-semibold text-slate-300">

                                {{ $isAr ? 'أنا' : 'I am a' }}

                                <span class="text-cyan-400">*</span>

                            </label>


                            <div class="grid gap-3 sm:grid-cols-2">

                                @foreach ($roles as $value => $label)
                                    <label class="group cursor-pointer">

                                        <input type="radio" name="role" value="{{ $value }}"
                                            class="peer sr-only" @checked(old('role') === $value) required>

                                        <div
                                            class="rounded-2xl border border-white/10 bg-black/10 p-4 transition group-hover:border-cyan-400/30 peer-checked:border-cyan-400/60 peer-checked:bg-cyan-400/10">

                                            <div class="flex items-center justify-between gap-3">

                                                <span
                                                    class="text-sm font-semibold text-slate-300 peer-checked:text-white">
                                                    {{ $label }}
                                                </span>

                                                <span
                                                    class="flex h-5 w-5 items-center justify-center rounded-full border border-white/20 peer-checked:border-cyan-400 peer-checked:bg-cyan-400">
                                                    <span
                                                        class="h-2 w-2 rounded-full bg-slate-950 opacity-0 peer-checked:opacity-100"></span>
                                                </span>

                                            </div>

                                        </div>

                                    </label>
                                @endforeach

                            </div>

                        </div>

                    </section>


                    {{-- =================================================
                        STEP 2
                    ================================================== --}}
                    <section x-show="step === 2" x-cloak x-transition.opacity.duration.200ms
                        class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl md:p-8">

                        <div class="flex items-start gap-4">

                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-cyan-400/10 text-sm font-black text-cyan-300">
                                02
                            </div>

                            <div>

                                <h2 class="text-xl font-bold">
                                    {{ $isAr ? 'قدرتك المالية' : 'Your Financial Capacity' }}
                                </h2>

                                <p class="mt-2 text-sm leading-7 text-slate-500">
                                    {{ $isAr
                                        ? 'هذه المعلومات تساعد الفريق على فهم طبيعة اهتمامك. يمكنك ترك المبلغ فارغاً إذا لم يكن محدداً بعد.'
                                        : 'This helps the team understand your level of interest. You can leave the amount empty if it is not determined yet.' }}
                                </p>

                            </div>

                        </div>


                        <div class="mt-8 grid gap-5 md:grid-cols-2">

                            <div>

                                <label class="mb-2 block text-sm font-semibold text-slate-300">
                                    {{ $isAr ? 'رأس المال المتاح' : 'Available Capital' }}
                                </label>

                                <input type="number" step="0.01" min="0" name="available_capital"
                                    value="{{ old('available_capital') }}" placeholder="0.00"
                                    class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-3.5 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/20">

                            </div>


                            <div>

                                <label class="mb-2 block text-sm font-semibold text-slate-300">
                                    {{ $isAr ? 'العملة' : 'Currency' }}
                                </label>

                                <select name="currency"
                                    class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-3.5 text-sm text-white outline-none focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/20">

                                    @foreach ($currencies as $value => $label)
                                        <option value="{{ $value }}" @selected(old('currency', 'AED') === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>

                        </div>


                        <div class="mt-6 rounded-2xl border border-white/5 bg-white/[0.02] p-4">

                            <div class="flex gap-3">

                                <div class="text-cyan-300">ⓘ</div>

                                <p class="text-xs leading-6 text-slate-500">
                                    {{ $isAr
                                        ? 'لن يتم عرض معلوماتك المالية بشكل عام في صفحة الفرصة. يتم استخدامها ضمن عملية مراجعة الطلب.'
                                        : 'Your financial information will not be displayed publicly on the opportunity page. It is used as part of the request review process.' }}
                                </p>

                            </div>

                        </div>

                    </section>


                    {{-- =================================================
                        STEP 3
                    ================================================== --}}
                    <section x-show="step === 3" x-cloak x-transition.opacity.duration.200ms
                        class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl md:p-8">

                        <div class="flex items-start gap-4">

                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-cyan-400/10 text-sm font-black text-cyan-300">
                                03
                            </div>

                            <div>

                                <h2 class="text-xl font-bold">
                                    {{ $isAr ? 'أخبرنا عن اهتمامك' : 'Tell Us About Your Interest' }}
                                </h2>

                                <p class="mt-2 text-sm leading-7 text-slate-500">
                                    {{ $isAr
                                        ? 'اكتب رسالة مختصرة توضح سبب اهتمامك وكيف يمكنك المساهمة.'
                                        : 'Write a short message explaining why you are interested and how you may contribute.' }}
                                </p>

                            </div>

                        </div>


                        <div class="mt-8">

                            <label class="mb-2 block text-sm font-semibold text-slate-300">

                                {{ $isAr ? 'رسالتك' : 'Your Message' }}

                                <span class="text-cyan-400">*</span>

                            </label>


                            <textarea name="message" rows="8" required minlength="10" maxlength="5000"
                                placeholder="{{ $isAr
                                    ? 'مثال: لدي خبرة في توزيع المنتجات الغذائية في سوق الإمارات وأرغب في مناقشة هذه الفرصة...'
                                    : 'Example: I have experience distributing food products in the UAE market and would like to discuss this opportunity...' }}"
                                class="w-full rounded-2xl border border-white/10 bg-[#07111f] px-4 py-4 text-sm leading-7 text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/20">{{ old('message') }}</textarea>


                            <div class="mt-2 flex justify-between text-[11px] text-slate-600">

                                <span>
                                    {{ $isAr ? '10 أحرف على الأقل' : 'Minimum 10 characters' }}
                                </span>

                                <span>
                                    {{ $isAr ? 'حد أقصى 5000 حرف' : 'Maximum 5000 characters' }}
                                </span>

                            </div>

                        </div>


                        {{-- Final review --}}
                        <div class="mt-7 rounded-3xl border border-cyan-400/15 bg-cyan-400/[0.035] p-5">

                            <div class="flex items-start gap-4">

                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-300">
                                    ✓
                                </div>

                                <div>

                                    <h3 class="font-bold text-cyan-300">
                                        {{ $isAr ? 'قبل الإرسال' : 'Before You Submit' }}
                                    </h3>

                                    <p class="mt-2 text-sm leading-7 text-slate-400">
                                        {{ $isAr
                                            ? 'سيتم إرسال طلب اهتمامك إلى فريق HAG Brokers للمراجعة. إبداء الاهتمام لا يعني إتمام الصفقة أو ضمان العائد.'
                                            : 'Your request will be sent to HAG Brokers for review. Expressing interest does not guarantee a transaction or return.' }}
                                    </p>

                                </div>

                            </div>

                        </div>

                    </section>


                    {{-- Navigation --}}
                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">

                        <a x-show="step === 1" href="{{ route('opportunities.show', $opportunity) }}"
                            class="rounded-xl border border-white/10 px-6 py-3 text-center text-sm font-semibold text-slate-300 transition hover:bg-white/5">
                            {{ $isAr ? 'إلغاء' : 'Cancel' }}
                        </a>


                        <button type="button" x-show="step > 1" x-cloak @click="previousStep()"
                            class="rounded-xl border border-white/10 px-6 py-3 text-sm font-semibold text-slate-300 transition hover:bg-white/5">
                            {{ $isAr ? 'السابق' : 'Back' }}
                        </button>


                        <div class="flex-1"></div>


                        <button type="button" x-show="step < 3" @click="nextStep()"
                            class="rounded-xl bg-cyan-400 px-7 py-3 text-sm font-bold text-slate-950 transition hover:bg-cyan-300">
                            {{ $isAr ? 'التالي' : 'Continue' }}

                            <span class="ms-2">→</span>
                        </button>


                        <button type="submit" x-show="step === 3" x-cloak :disabled="submitting"
                            class="rounded-xl bg-cyan-400 px-7 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-cyan-500/10 transition hover:bg-cyan-300 disabled:cursor-not-allowed disabled:opacity-60">

                            <span x-show="!submitting">
                                {{ $isAr ? 'إرسال طلب الاهتمام' : 'Submit Interest' }}
                            </span>

                            <span x-show="submitting">
                                {{ $isAr ? 'جاري الإرسال...' : 'Submitting...' }}
                            </span>

                        </button>

                    </div>

                </form>

            @endif

        </main>

    </div>


    {{-- Wizard --}}
    <script>
        function interestWizard() {
            return {
                step: 1,
                submitting: false,

                get progress() {
                    return this.step * 33.3333;
                },

                get stepTitle() {
                    const titles = {
                        1: @json($isAr ? 'معلوماتك' : 'Your Information'),
                        2: @json($isAr ? 'القدرة المالية' : 'Financial Capacity'),
                        3: @json($isAr ? 'رسالتك والمراجعة' : 'Message & Review'),
                    };

                    return titles[this.step];
                },

                nextStep() {
                    const form = this.$root.querySelector('form');

                    if (!form) {
                        return;
                    }

                    const currentSection = form.querySelector(
                        `[x-show*="step === ${this.step}"]`
                    );

                    if (currentSection) {
                        const fields = currentSection.querySelectorAll(
                            'input[required], select[required], textarea[required]'
                        );

                        for (const field of fields) {

                            if (!field.checkValidity()) {
                                field.reportValidity();
                                return;
                            }
                        }
                    }

                    if (this.step < 3) {
                        this.step++;
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                },

                previousStep() {
                    if (this.step > 1) {
                        this.step--;

                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                }
            };
        }
    </script>

</x-app-layout>
