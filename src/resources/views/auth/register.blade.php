@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
@endphp

<x-guest-layout>
    <div class="mb-8 text-center" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-500 text-xl font-extrabold text-slate-950 shadow-lg shadow-cyan-500/20">HAG</div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">{{ __('auth.create_account_title') }}</h1>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $isAr ? 'أنشئ حسابك الشخصي أولاً، ثم أكمل بيانات شركتك بعد تسجيل الدخول.' : 'Create your personal account first. You can add your company after signing in.' }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        @csrf
        <div>
            <x-input-label for="name" :value="$isAr ? 'الاسم الكامل' : 'Full name'" />
            <x-text-input id="name" class="mt-1 block w-full" type="text" name="name" :value="old('name')" required autofocus maxlength="160" autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="email" :value="__('auth.email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required maxlength="255" autocomplete="username" :placeholder="__('auth.email_placeholder')" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="password" :value="__('auth.password')" />
            <x-password-toggle id="password" name="password" required minlength="8" maxlength="128" autocomplete="new-password" :placeholder="__('auth.password_placeholder')" class="mt-1 block w-full" />
            <p class="mt-1 text-xs text-slate-500">{{ __('auth.password_length_help') }}</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="password_confirmation" :value="__('auth.confirm_password')" />
            <x-password-toggle id="password_confirmation" name="password_confirmation" required minlength="8" maxlength="128" autocomplete="new-password" :placeholder="__('auth.confirm_password_placeholder')" class="mt-1 block w-full" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>
        <div class="pt-1">
            <x-primary-button class="w-full justify-center bg-cyan-500 py-3 text-slate-950 hover:bg-cyan-400 focus:bg-cyan-400 active:bg-cyan-600">{{ __('auth.register') }}</x-primary-button>
        </div>
    </form>

    @if (Route::has('login'))
        <div class="mt-7 border-t border-slate-200 pt-6 text-center" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('auth.already_registered') }}
                <a href="{{ route('login') }}" class="font-bold text-cyan-600 transition hover:text-cyan-500 dark:text-cyan-400 dark:hover:text-cyan-300">{{ __('auth.login') }}</a>
            </p>
        </div>
    @endif
    <div class="mt-6 flex items-center justify-center gap-2 text-xs text-slate-400" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <span class="inline-flex h-2 w-2 rounded-full bg-cyan-500"></span>
        <span>{{ __('auth.secure_platform') }}</span>
    </div>
</x-guest-layout>
