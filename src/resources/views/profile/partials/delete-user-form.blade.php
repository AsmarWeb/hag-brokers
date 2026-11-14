<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ __('Delete Account') }}
        </h2>

        @if (app()->getLocale() === 'ar')
            <p class="mt-2 text-sm leading-7 text-gray-600 dark:text-gray-300">
                يمكنك تقديم طلب حذف حسابك والتحقق من ملكية بريدك الإلكتروني.
                يخضع الطلب لمراجعة الإدارة وتأكيد نهائي عبر رابط آمن.
                تقديم الطلب لا يؤدي إلى حذف الحساب مباشرة.
            </p>
        @else
            <p class="mt-2 text-sm leading-7 text-gray-600 dark:text-gray-300">
                You can request account deletion and verify ownership of your email address.
                The request is reviewed by the administration and requires final confirmation
                through a secure link. Submitting a request does not immediately delete your account.
            </p>
        @endif
    </header>

    <a href="{{ route('account-deletion.request') }}"
       class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
        {{ app()->getLocale() === 'ar' ? 'الانتقال إلى طلب حذف الحساب' : 'Continue to account deletion request' }}
    </a>
</section>
