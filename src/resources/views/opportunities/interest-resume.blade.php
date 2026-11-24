@php
    $isAr = app()->getLocale() === 'ar';
    $roles = [
        'investor' => $isAr ? 'مستثمر' : 'Investor',
        'business_owner' => $isAr ? 'صاحب عمل' : 'Business Owner',
        'buyer' => $isAr ? 'مشتري' : 'Buyer',
        'supplier' => $isAr ? 'مورد' : 'Supplier',
        'broker' => $isAr ? 'وسيط' : 'Broker',
        'partner' => $isAr ? 'شريك' : 'Partner',
        'other' => $isAr ? 'أخرى' : 'Other',
    ];
    $statusLabels = [
        'pending' => $isAr ? 'بانتظار المراجعة' : 'Pending review',
        'accepted' => $isAr ? 'تم القبول' : 'Accepted',
        'rejected' => $isAr ? 'تم الرفض' : 'Rejected',
        'withdrawn' => $isAr ? 'تم السحب' : 'Withdrawn',
    ];
@endphp

<x-app-layout>
    <main class="mx-auto min-h-screen max-w-4xl px-4 pb-20 pt-28 sm:px-6 lg:px-8" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <a href="{{ route('opportunities.show', $opportunity) }}" class="mb-7 inline-flex items-center gap-2 text-sm text-slate-500 hover:text-cyan-500">
            ← {{ $isAr ? 'العودة إلى الفرصة' : 'Back to opportunity' }}
        </a>

        <header class="mb-8">
            <p class="text-xs font-bold uppercase tracking-widest text-cyan-500">HAG BROKERS · APPLICATION</p>
            <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $isAr ? 'مراجعة طلب التقديم' : 'Review your application' }}</h1>
            <p class="mt-3 text-sm leading-7 text-slate-500 dark:text-slate-400">
                {{ $isAr ? 'تم حفظ بياناتك. بعد تسجيل الدخول وإكمال إعداد الحساب ومساحة الشركة، راجع التفاصيل وأرسل طلبك.' : 'Your details were saved. After signing in and completing account and company setup, review the details and submit your application.' }}
            </p>
        </header>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 p-6 dark:border-slate-800">
                <p class="text-xs font-semibold text-cyan-600">{{ $isAr ? 'الفرصة التجارية' : 'Commercial opportunity' }}</p>
                <h2 class="mt-2 text-xl font-extrabold text-slate-900 dark:text-white">{{ $opportunity->title }}</h2>
                @if ($opportunity->city || $opportunity->country)
                    <p class="mt-2 text-sm text-slate-500">{{ collect([$opportunity->city, $opportunity->country])->filter()->implode(', ') }}</p>
                @endif
            </div>

            @if ($existingInterest)
                <div class="p-6" role="status">
                    <h3 class="font-bold text-amber-600">{{ $isAr ? 'يوجد تقديم سابق من شركتك' : 'Your company has already applied' }}</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                        {{ $statusLabels[$existingInterest->status] ?? ucfirst($existingInterest->status) }}
                    </p>
                    <a href="{{ route('company.marketplace') }}" class="mt-5 inline-flex rounded-xl bg-cyan-500 px-5 py-3 text-sm font-bold text-slate-950">
                        {{ $isAr ? 'العودة إلى مساحة الشركة' : 'Return to company workspace' }}
                    </a>
                </div>
            @else
                <div class="space-y-5 p-6">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800">
                            <dt class="text-xs text-slate-500">{{ $isAr ? 'الصفة' : 'Your role' }}</dt>
                            <dd class="mt-1 font-bold text-slate-900 dark:text-white">{{ $roles[$application['role']] ?? $application['role'] }}</dd>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800">
                            <dt class="text-xs text-slate-500">{{ $isAr ? 'رأس المال المتاح' : 'Available capital' }}</dt>
                            <dd class="mt-1 font-bold text-slate-900 dark:text-white">
                                @if (filled($application['available_capital'] ?? null))
                                    <bdi>{{ $application['available_capital'] }} {{ strtoupper($application['currency'] ?? 'AED') }}</bdi>
                                @else
                                    {{ $isAr ? 'غير محدد' : 'Not specified' }}
                                @endif
                            </dd>
                        </div>
                    </dl>

                    <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800">
                        <h3 class="text-xs font-semibold text-slate-500">{{ $isAr ? 'رسالتك' : 'Your message' }}</h3>
                        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-800 dark:text-slate-200">{{ $application['message'] }}</p>
                    </div>

                    <p class="text-xs leading-6 text-slate-500 dark:text-slate-400">
                        {{ $isAr ? 'سيُرسل الطلب باسم مساحة شركتك، وتُطبق حدود التقديم الحالية على مستوى الشركة. لن تُعرض بياناتك المالية في صفحة الفرصة العامة.' : 'The application will be submitted under your company workspace and the existing company-level application limit will apply. Your financial details will not appear on the public opportunity page.' }}
                    </p>

                    <form method="POST" action="{{ route('opportunities.interest.resume.submit', ['draft' => $draftToken]) }}">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-cyan-500 px-6 py-4 text-sm font-extrabold text-slate-950 transition hover:bg-cyan-400">
                            {{ $isAr ? 'تأكيد وإرسال طلب التقديم' : 'Confirm and submit application' }}
                        </button>
                    </form>
                </div>
            @endif
        </section>
    </main>
</x-app-layout>
