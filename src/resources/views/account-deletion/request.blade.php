<x-guest-layout>
    <div class="space-y-5">
        <div>
            @if ($language === 'ar')
                <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">
                    طلب حذف حسابك
                </h1>
                <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
                    يمكنك تقديم طلب حذف حساب HAG Brokers والبيانات المرتبطة به من هذه الصفحة دون الحاجة إلى تسجيل الدخول أو استخدام التطبيق.
                </p>
            @else
                <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">
                    Request account deletion
                </h1>
                <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
                    You can request deletion of your HAG Brokers account and associated data from this page without signing in or using the app.
                </p>
            @endif
        </div>

        @if (session('status'))
            <div role="status"
                 class="rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-3 text-sm leading-6 text-cyan-900 dark:border-cyan-900 dark:bg-cyan-950/40 dark:text-cyan-200">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('account-deletion.request.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">
                    {{ $language === 'ar' ? 'البريد الإلكتروني المرتبط بحسابك' : 'Email address associated with your account' }}
                </label>

                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    maxlength="254"
                    required
                    autocomplete="email"
                    inputmode="email"
                    autofocus
                    dir="ltr"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-left text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    placeholder="name@example.com"
                >

                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="flex w-full items-center justify-center rounded-xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-600/20 transition hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2"
            >
                {{ $language === 'ar' ? 'إرسال رابط التحقق' : 'Send verification link' }}
            </button>
        </form>

        <div class="space-y-3 border-t border-slate-200 pt-4 text-xs leading-6 text-slate-500 dark:border-slate-700 dark:text-slate-400">
            @if ($language === 'ar')
                <p>
                    سنرسل رابط تحقق إلى البريد الإلكتروني إذا كان مرتبطاً بحساب. يجب فتح الرابط وتأكيد الطلب لإثبات ملكية البريد الإلكتروني.
                </p>
                <p>
                    إرسال الطلب أو التحقق من البريد لا يحذف الحساب مباشرة. سيُحال الطلب إلى المراجعة، وقد تخضع بعض البيانات لفترات احتفاظ قانونية أو أمنية مشروعة وفق سياسة الخصوصية.
                </p>
                <p>
                    راجع <a href="{{ route('legal.show', ['slug' => 'privacy']) }}" class="font-semibold text-cyan-700 underline dark:text-cyan-400">سياسة الخصوصية</a> لمعرفة تفاصيل البيانات والاحتفاظ بها.
                </p>
            @else
                <p>
                    We will send a verification link if the email address is associated with an account. You must open that link and confirm the request to prove ownership of the email address.
                </p>
                <p>
                    Submitting the form or verifying your email does not immediately delete your account. The request will be reviewed. Certain data may be subject to legitimate legal or security retention requirements under our Privacy Policy.
                </p>
                <p>
                    See our <a href="{{ route('legal.show', ['slug' => 'privacy']) }}" class="font-semibold text-cyan-700 underline dark:text-cyan-400">Privacy Policy</a> for information about data handling and retention.
                </p>
            @endif
        </div>
    </div>
</x-guest-layout>
