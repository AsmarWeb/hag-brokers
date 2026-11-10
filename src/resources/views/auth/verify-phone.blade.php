<x-guest-layout>
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-500 text-xl font-extrabold text-slate-950">HAG</div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">{{ __('auth.phone_verification_title') }}</h1>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('auth.phone_verification_description') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('phone.verification.verify') }}" class="space-y-5">
        @csrf
        <div>
            <x-input-label for="email" :value="__('auth.email')" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $email)" required
                autocomplete="email" class="mt-1 block w-full" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="code" :value="__('auth.verification_code')" />
            <x-text-input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}"
                minlength="6" maxlength="6" autocomplete="one-time-code" required class="mt-1 block w-full"
                placeholder="123456" dir="ltr" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>
        <x-primary-button class="w-full justify-center bg-cyan-500 py-3 text-slate-950 hover:bg-cyan-400">
            {{ __('auth.verify_phone_code') }}
        </x-primary-button>
    </form>

    <div class="mt-6 border-t border-slate-200 pt-5 dark:border-slate-800">
        <form method="POST" action="{{ route('phone.verification.send') }}" class="space-y-3">
            @csrf
            <div>
                <x-input-label for="resend_email" :value="__('auth.email')" />
                <x-text-input id="resend_email" name="email" type="email" :value="old('email', $email)" required
                    autocomplete="email" class="mt-1 block w-full" />
            </div>
            <button type="submit" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                {{ __('auth.send_phone_code') }}
            </button>
        </form>
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('login') }}" class="text-sm font-semibold text-cyan-600 hover:text-cyan-500 dark:text-cyan-400">
            {{ __('auth.login') }}
        </a>
    </div>
</x-guest-layout>
