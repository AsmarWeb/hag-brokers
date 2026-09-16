<x-guest-layout>
    <div class="mb-8 text-center">
        <div
            class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-500 text-xl font-extrabold text-slate-950 shadow-lg shadow-cyan-500/20">
            HAG
        </div>

        <h1 class="text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">
            {{ __('auth.create_account_title') }}
        </h1>

        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
            {{ __('auth.register_description') }}
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('auth.name')" />

            <x-text-input id="name" class="mt-1 block w-full" type="text" name="name" :value="old('name')"
                required autofocus autocomplete="name" :placeholder="__('auth.name_placeholder')" />

            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('auth.email')" />

            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')"
                required autocomplete="username" :placeholder="__('auth.email_placeholder')" />

            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('auth.password')" />

            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required
                autocomplete="new-password" :placeholder="__('auth.password_placeholder')" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('auth.confirm_password')" />

            <x-text-input id="password_confirmation" class="mt-1 block w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" :placeholder="__('auth.confirm_password_placeholder')" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-1">
            <x-primary-button
                class="w-full justify-center bg-cyan-500 py-3 text-slate-950 hover:bg-cyan-400 focus:bg-cyan-400 active:bg-cyan-600">
                {{ __('auth.register') }}
            </x-primary-button>
        </div>
    </form>

    @if (Route::has('login'))
        <div class="mt-7 border-t border-slate-200 pt-6 text-center dark:border-slate-800">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ __('auth.already_registered') }}

                <a href="{{ route('login') }}"
                    class="font-bold text-cyan-600 transition hover:text-cyan-500 dark:text-cyan-400 dark:hover:text-cyan-300">
                    {{ __('auth.login') }}
                </a>
            </p>
        </div>
    @endif

    <div class="mt-6 flex items-center justify-center gap-2 text-xs text-slate-400">
        <span class="inline-flex h-2 w-2 rounded-full bg-cyan-500"></span>
        <span>{{ __('auth.secure_platform') }}</span>
    </div>
</x-guest-layout>
