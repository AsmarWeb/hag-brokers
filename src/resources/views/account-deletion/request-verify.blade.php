<x-guest-layout>
    <div class="space-y-5">
        <div>
            @if ($language === 'ar')
                <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">
                    التحقق من ملكية البريد الإلكتروني
                </h1>
                <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
                    اضغط على الزر أدناه لتأكيد أنك تملك البريد الإلكتروني الذي استُخدم لتقديم طلب حذف حساب HAG Brokers.
                </p>
            @else
                <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">
                    Verify email ownership
                </h1>
                <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
                    Select the button below to confirm that you own the email address used to request deletion of your HAG Brokers account.
                </p>
            @endif
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200">
            {{ $language === 'ar'
                ? 'هذا الإجراء يقدّم طلب الحذف للمراجعة فقط. لن يُحذف حسابك بمجرد الضغط على الزر.'
                : 'This action submits your deletion request for review only. Your account will not be deleted by selecting this button.' }}
        </div>

        <form method="POST"
              action="{{ route('account-deletion.request.verify', ['token' => $token]) }}"
              class="space-y-4">
            @csrf
            <input type="hidden" name="lang" value="{{ $language }}">

            <button
                type="submit"
                class="flex w-full items-center justify-center rounded-xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-600/20 transition hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2"
            >
                {{ $language === 'ar' ? 'أؤكد ملكية البريد وأقدّم طلب الحذف' : 'Verify email and submit deletion request' }}
            </button>
        </form>

        <p class="text-xs leading-6 text-slate-500 dark:text-slate-400">
            {{ $language === 'ar'
                ? 'إذا لم تطلب حذف الحساب، فلا تضغط على الزر ويمكنك إغلاق هذه الصفحة.'
                : 'If you did not request account deletion, do not select the button. You can safely close this page.' }}
        </p>
    </div>
</x-guest-layout>
