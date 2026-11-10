<x-guest-layout>
    <div class="mb-8 text-center">
        <div
            class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-500 text-xl font-extrabold text-slate-950 shadow-lg shadow-cyan-500/20">
            HAG
        </div>

        <h1 class="text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">
            {{ __('auth.welcome_back') }}
        </h1>

        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
            {{ __('auth.login_description') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('auth.email')" />

            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')"
                required autofocus autocomplete="username" :placeholder="__('auth.email_placeholder')" />

            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between gap-3">
                <x-input-label for="password" :value="__('auth.password')" />

                @if (Route::has('password.request'))
                    <a class="text-sm font-semibold text-cyan-600 transition hover:text-cyan-500 dark:text-cyan-400 dark:hover:text-cyan-300"
                        href="{{ route('password.request') }}">
                        {{ __('auth.forgot_password') }}
                    </a>
                @endif
            </div>

            <x-password-toggle id="password" name="password" required :minlength="null" :maxlength="null"
                autocomplete="current-password" :placeholder="__('auth.password_placeholder')" class="mt-1 block w-full" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <label for="remember_me" class="flex cursor-pointer items-center gap-3">
            <input id="remember_me" type="checkbox"
                class="rounded border-slate-300 text-cyan-600 shadow-sm focus:ring-cyan-500 dark:border-slate-700 dark:bg-slate-900"
                name="remember">

            <span class="text-sm text-slate-600 dark:text-slate-300">
                {{ __('auth.remember_me') }}
            </span>
        </label>

        <!-- Submit -->
        <x-primary-button
            class="w-full justify-center bg-cyan-500 py-3 text-slate-950 hover:bg-cyan-400 focus:bg-cyan-400 active:bg-cyan-600">
            {{ __('auth.login') }}
        </x-primary-button>
    </form>

    <div class="mt-6 border-t border-slate-200 pt-5 dark:border-slate-800">
        <p class="mb-3 text-sm text-slate-500 dark:text-slate-400">
            {{ __('auth.verification_help') }}
        </p>
        <form method="POST" action="{{ route('verification.send') }}" class="space-y-3">
            @csrf
            <div>
                <x-input-label for="verification_email" :value="__('auth.email')" />
                <x-text-input id="verification_email" class="mt-1 block w-full" type="email" name="email"
                    :value="old('email')" required autocomplete="email" :placeholder="__('auth.email_placeholder')" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <button type="submit"
                class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                {{ __('auth.resend_verification_email') }}
            </button>
        </form>
    </div>

    <div class="mt-5 text-center">
        <a href="{{ route('phone.verification.notice') }}"
            class="text-sm font-semibold text-cyan-600 hover:text-cyan-500 dark:text-cyan-400">
            {{ __('auth.phone_verification_title') }}
        </a>
    </div>

    @if (Route::has('register'))
        <div class="mt-7 border-t border-slate-200 pt-6 text-center dark:border-slate-800">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ __('auth.no_account') }}
                <a href="{{ route('register') }}"
                    class="font-bold text-cyan-600 transition hover:text-cyan-500 dark:text-cyan-400 dark:hover:text-cyan-300">
                    {{ __('auth.create_account') }}
                </a>
            </p>
        </div>
    @endif

    <div class="mt-6 flex items-center justify-center gap-2 text-xs text-slate-400">
        <span class="inline-flex h-2 w-2 rounded-full bg-cyan-500"></span>
        <span>{{ __('auth.secure_platform') }}</span>
    </div>
</x-guest-layout>
