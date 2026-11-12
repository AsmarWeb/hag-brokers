<x-guest-layout>
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-500 text-xl font-extrabold text-slate-950">HAG</div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">{{ app()->getLocale() === 'ar' ? 'إكمال خطوة تأكيد رقم الهاتف' : 'Complete phone confirmation' }}</h1>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ app()->getLocale() === 'ar' ? 'أضف رقم هاتفك بعد تسجيل الدخول، ثم أدخل الرمز المرسل إلى بريدك الإلكتروني الموثّق.' : 'Add your phone number after signing in, then enter the code sent to your verified email address.' }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if ($phoneSetupCompleted)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
            {{ app()->getLocale() === 'ar' ? 'تم إكمال خطوة تأكيد الرقم باستخدام بريدك الإلكتروني.' : 'The phone confirmation step was completed using your email.' }}
        </div>
        <a href="{{ route('company.onboarding.show') }}" class="mt-5 block text-center font-semibold text-cyan-600">{{ app()->getLocale() === 'ar' ? 'متابعة إعداد الشركة' : 'Continue to company setup' }}</a>
    @else
        <form method="POST" action="{{ route('phone.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <x-input-label for="phone_country_code" :value="app()->getLocale() === 'ar' ? 'الدولة' : 'Country'" />
                <select id="phone_country_code" name="phone_country_code" required class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    @foreach (config('phone.countries', []) as $iso => $country)
                        <option value="{{ $iso }}" @selected(old('phone_country_code', $phoneCountryCode) === $iso)>{{ $country['name'] ?? $iso }} ({{ $country['dial_code'] }})</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('phone_country_code')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="phone" :value="app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone number'" />
                <x-text-input id="phone" name="phone" type="tel" required autocomplete="tel" inputmode="tel" class="mt-1 block w-full" :value="old('phone', $phone)" placeholder="50 123 4567" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
            @if ($phone)
                <div>
                    <x-input-label for="current_password" :value="app()->getLocale() === 'ar' ? 'كلمة المرور الحالية (عند تغيير رقم موثّق)' : 'Current password (required when changing a verified number)'" />
                    <x-text-input id="current_password" name="current_password" type="password" autocomplete="current-password" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
                </div>
            @endif
            <x-primary-button class="w-full justify-center bg-cyan-500 py-3 text-slate-950 hover:bg-cyan-400">{{ app()->getLocale() === 'ar' ? 'حفظ الرقم وإرسال الرمز' : 'Save number and send code' }}</x-primary-button>
        </form>

        @if ($phone)
            <form method="POST" action="{{ route('phone.verification.verify') }}" class="mt-6 space-y-4 border-t border-slate-200 pt-5 dark:border-slate-800">
                @csrf
                <div>
                    <x-input-label for="code" :value="__('auth.verification_code')" />
                    <x-text-input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" autocomplete="one-time-code" required class="mt-1 block w-full" placeholder="123456" dir="ltr" />
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>
                <x-primary-button class="w-full justify-center">{{ __('auth.verify_phone_code') }}</x-primary-button>
            </form>
            <form method="POST" action="{{ route('phone.verification.send') }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('auth.send_phone_code') }}</button>
            </form>
        @endif
    @endif

    <div class="mt-6 text-center">
        <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-cyan-600 hover:text-cyan-500 dark:text-cyan-400">{{ app()->getLocale() === 'ar' ? 'العودة إلى لوحة التحكم' : 'Return to dashboard' }}</a>
    </div>
</x-guest-layout>
