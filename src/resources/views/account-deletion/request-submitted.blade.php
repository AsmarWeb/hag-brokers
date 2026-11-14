<x-guest-layout>
    <div class="space-y-5 text-center">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-950 dark:text-cyan-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20 6 9 17l-5-5"/>
            </svg>
        </div>

        @if ($language === 'ar')
            <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">
                تم تقديم طلب حذف الحساب
            </h1>
            <p class="text-sm leading-7 text-slate-600 dark:text-slate-300">
                تم التحقق من ملكية البريد الإلكتروني وتسجيل طلب حذف حسابك للمراجعة لدى فريق HAG Brokers.
            </p>
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200">
                لم يُحذف حسابك حتى الآن. تأكيد البريد الإلكتروني يقدّم الطلب للمراجعة ولا ينفّذ الحذف الفعلي.
            </div>
            <p class="text-xs leading-6 text-slate-500 dark:text-slate-400">
                قد تُحتفظ ببعض البيانات عند وجود متطلبات قانونية أو أمنية مشروعة، وفق سياسة الخصوصية.
            </p>
            <a href="{{ route('legal.show', ['slug' => 'privacy']) }}"
               class="inline-flex font-semibold text-cyan-700 underline dark:text-cyan-400">
                الاطلاع على سياسة الخصوصية
            </a>
        @else
            <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">
                Account deletion request submitted
            </h1>
            <p class="text-sm leading-7 text-slate-600 dark:text-slate-300">
                Your email ownership has been verified and your account deletion request has been submitted for review by the HAG Brokers team.
            </p>
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200">
                Your account has not been deleted. Email verification submits the request for review and does not perform the actual deletion.
            </div>
            <p class="text-xs leading-6 text-slate-500 dark:text-slate-400">
                Certain data may be retained where legitimate legal or security requirements apply, as described in our Privacy Policy.
            </p>
            <a href="{{ route('legal.show', ['slug' => 'privacy']) }}"
               class="inline-flex font-semibold text-cyan-700 underline dark:text-cyan-400">
                Review the Privacy Policy
            </a>
        @endif
    </div>
</x-guest-layout>
