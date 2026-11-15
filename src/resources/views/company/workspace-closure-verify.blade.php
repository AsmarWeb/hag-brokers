<x-guest-layout>
    <div class="mx-auto max-w-xl space-y-5 p-6">
        <h1 class="text-2xl font-bold">{{ app()->getLocale() === 'ar' ? 'تأكيد البريد الإلكتروني' : 'Verify email address' }}</h1>
        <p>{{ app()->getLocale() === 'ar' ? 'هذه الخطوة تؤكد ملكية البريد فقط. لن يتم إغلاق مساحة الشركة قبل تسجيل الدخول والتأكيد النهائي.' : 'This verifies email ownership only. The workspace will not close until you sign in and submit a separate final confirmation.' }}</p>
        <form method="POST" action="{{ route('company.workspace.closure.verify', ['token' => $token]) }}">
            @csrf
            <button class="hag-broker-action">{{ app()->getLocale() === 'ar' ? 'تأكيد البريد الإلكتروني' : 'Verify email address' }}</button>
        </form>
    </div>
</x-guest-layout>
