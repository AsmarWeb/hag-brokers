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

    <div dir="{{ $dir }}" class="min-h-screen bg-[#06101d] text-white">

        <main class="mx-auto max-w-4xl px-5 pb-20 pt-32 lg:px-8">

            {{-- Back --}}
            <a href="{{ route('opportunities.show', $opportunity) }}"
                class="mb-6 inline-flex items-center gap-2 text-sm text-slate-400 transition hover:text-cyan-300">

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

                <h1 class="mt-4 text-3xl font-black md:text-4xl">
                    {{ $isAr ? 'أخبرنا عن اهتمامك' : 'Tell Us About Your Interest' }}
                </h1>

                <p class="mt-3 text-sm leading-7 text-slate-400">
                    {{ $isAr
                        ? 'أرسل معلوماتك إلى فريق HAG Brokers وسنقوم بمراجعة طلبك والتواصل معك.'
                        : 'Send your information to HAG Brokers. Our team will review your request and contact you.' }}
                </p>

            </div>

            {{-- Opportunity Summary --}}
            <div class="mb-6 rounded-3xl border border-cyan-400/20 bg-cyan-400/5 p-6">

                <div class="text-xs text-slate-500">
                    {{ $isAr ? 'الفرصة' : 'Opportunity' }}
                </div>

                <h2 class="mt-2 text-xl font-bold text-white">
                    {{ $opportunity->title }}
                </h2>

                @if ($opportunity->country || $opportunity->city)
                    <div class="mt-3 text-sm text-slate-400">
                        {{ collect([$opportunity->city, $opportunity->country])->filter()->implode(', ') }}
                    </div>
                @endif

            </div>

            {{-- Existing Interest --}}
            @if ($existingInterest)

                <div class="rounded-3xl border border-amber-400/20 bg-amber-400/5 p-6">

                    <div class="flex items-start gap-4">

                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-400/10 text-amber-300">
                            !
                        </div>

                        <div>

                            <h2 class="font-bold text-amber-300">
                                {{ $isAr ? 'تم إرسال اهتمامك مسبقاً' : 'Interest Already Submitted' }}
                            </h2>

                            <p class="mt-2 text-sm leading-7 text-slate-400">
                                {{ $isAr
                                    ? 'لقد قمت بالفعل بإبداء اهتمامك بهذه الفرصة. سيقوم فريق HAG Brokers بمراجعة الطلب.'
                                    : 'You have already expressed interest in this opportunity. HAG Brokers will review your request.' }}
                            </p>

                            <div class="mt-4">

                                @if ($existingInterest->status === 'pending')
                                    <span class="rounded-full bg-amber-400/10 px-3 py-1.5 text-xs text-amber-300">
                                        {{ $isAr ? 'قيد المراجعة' : 'Pending Review' }}
                                    </span>
                                @elseif ($existingInterest->status === 'approved')
                                    <span class="rounded-full bg-emerald-400/10 px-3 py-1.5 text-xs text-emerald-300">
                                        {{ $isAr ? 'تمت الموافقة' : 'Approved' }}
                                    </span>
                                @elseif ($existingInterest->status === 'rejected')
                                    <span class="rounded-full bg-red-400/10 px-3 py-1.5 text-xs text-red-300">
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

                </div>
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

                {{-- Form --}}
                <form method="POST" action="{{ route('opportunities.interest.store', $opportunity) }}"
                    class="space-y-6">

                    @csrf

                    <section class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl md:p-8">

                        <h2 class="text-xl font-bold">
                            {{ $isAr ? 'معلوماتك' : 'Your Information' }}
                        </h2>

                        <p class="mt-2 text-sm text-slate-500">
                            {{ $isAr ? 'اختر الصفة التي تمثل اهتمامك بهذه الفرصة.' : 'Select the role that best describes your interest.' }}
                        </p>

                        <div class="mt-6">

                            <label class="mb-2 block text-sm font-semibold text-slate-300">
                                {{ $isAr ? 'أنا' : 'I am a' }}
                                <span class="text-cyan-400">*</span>
                            </label>

                            <select name="role" required
                                class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none focus:border-cyan-400/50">

                                <option value="">
                                    {{ $isAr ? 'اختر الصفة' : 'Select your role' }}
                                </option>

                                @foreach ($roles as $value => $label)
                                    <option value="{{ $value }}" @selected(old('role') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                    </section>

                    {{-- Capital --}}
                    <section class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl md:p-8">

                        <h2 class="text-xl font-bold">
                            {{ $isAr ? 'القدرة المالية' : 'Financial Capacity' }}
                        </h2>

                        <p class="mt-2 text-sm leading-7 text-slate-500">
                            {{ $isAr
                                ? 'إذا كانت لديك قدرة مالية محددة للمشاركة، يمكنك إدخالها هنا.'
                                : 'If you have a specific amount available for participation, you can provide it here.' }}
                        </p>

                        <div class="mt-6 grid gap-5 md:grid-cols-2">

                            <div>

                                <label class="mb-2 block text-sm font-semibold text-slate-300">
                                    {{ $isAr ? 'رأس المال المتاح' : 'Available Capital' }}
                                </label>

                                <input type="number" step="0.01" min="0" name="available_capital"
                                    value="{{ old('available_capital') }}" placeholder="0.00"
                                    class="w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50">

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

                    {{-- Message --}}
                    <section class="rounded-3xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl md:p-8">

                        <h2 class="text-xl font-bold">
                            {{ $isAr ? 'رسالتك' : 'Your Message' }}
                        </h2>

                        <p class="mt-2 text-sm leading-7 text-slate-500">
                            {{ $isAr
                                ? 'اشرح لماذا أنت مهتم بالفرصة وكيف يمكنك المساهمة.'
                                : 'Explain why you are interested and how you may contribute.' }}
                        </p>

                        <textarea name="message" rows="7" required minlength="10" maxlength="5000"
                            placeholder="{{ $isAr
                                ? 'مثال: لدي خبرة في توزيع المنتجات الغذائية في سوق الإمارات وأرغب في مناقشة فرصة الشراكة...'
                                : 'Example: I have experience distributing food products in the UAE market and would like to discuss the partnership opportunity...' }}"
                            class="mt-5 w-full rounded-xl border border-white/10 bg-[#07111f] px-4 py-3 text-sm leading-7 text-white outline-none placeholder:text-slate-600 focus:border-cyan-400/50">{{ old('message') }}</textarea>

                    </section>

                    {{-- Privacy / Review --}}
                    <div class="rounded-3xl border border-cyan-400/20 bg-cyan-400/5 p-6">

                        <div class="flex gap-4">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-400/10">

                                <svg class="h-5 w-5 text-cyan-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 11c0-1.105.895-2 2-2s2 .895 2 2-2 4-2 4-2-2.895-2-4z" />

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 20a7 7 0 0114 0" />

                                </svg>

                            </div>

                            <div>

                                <h3 class="font-bold text-cyan-300">
                                    {{ $isAr ? 'مراجعة الطلب' : 'Request Review' }}
                                </h3>

                                <p class="mt-2 text-sm leading-7 text-slate-400">
                                    {{ $isAr
                                        ? 'سيتم إرسال طلب اهتمامك إلى فريق HAG Brokers للمراجعة. إبداء الاهتمام لا يعني إتمام الصفقة أو ضمان العائد.'
                                        : 'Your interest request will be reviewed by HAG Brokers. Expressing interest does not guarantee a transaction or return.' }}
                                </p>

                            </div>

                        </div>

                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                        <a href="{{ route('opportunities.show', $opportunity) }}"
                            class="rounded-xl border border-white/10 px-6 py-3 text-center text-sm font-semibold text-slate-300 transition hover:bg-white/5">
                            {{ $isAr ? 'إلغاء' : 'Cancel' }}
                        </a>

                        <button type="submit"
                            class="rounded-xl bg-cyan-400 px-7 py-3 text-sm font-bold text-slate-950 transition hover:bg-cyan-300">

                            {{ $isAr ? 'إرسال طلب الاهتمام' : 'Submit Interest' }}

                        </button>

                    </div>

                </form>

            @endif

        </main>

    </div>

</x-app-layout>
