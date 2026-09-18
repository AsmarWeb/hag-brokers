@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
@endphp

<x-guest-layout>
    <div class="mb-8 text-center" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        {{-- Logo --}}
        <div
            class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-500 text-xl font-extrabold text-slate-950 shadow-lg shadow-cyan-500/20">
            HAG
        </div>

        {{-- Title --}}
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">
            {{ __('auth.create_account_title') }}
        </h1>

        {{-- Description --}}
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
            {{ __('auth.register_description') }}
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        @csrf

        {{-- Company Name --}}
        <div>
            <x-input-label for="company_name" :value="__('auth.company_name')" />

            <x-text-input id="company_name" class="mt-1 block w-full" type="text" name="company_name"
                :value="old('company_name')" required autofocus autocomplete="organization" :placeholder="__('auth.company_name_placeholder')" />

            <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
        </div>

        {{-- Contact Person --}}
        <div>
            <x-input-label for="contact_person" :value="__('auth.contact_person')" />

            <x-text-input id="contact_person" class="mt-1 block w-full" type="text" name="contact_person"
                :value="old('contact_person')" required autocomplete="name" :placeholder="__('auth.contact_person_placeholder')" />

            <x-input-error :messages="$errors->get('contact_person')" class="mt-2" />
        </div>

        {{-- Phone --}}
        <div>
            <x-input-label for="phone" :value="__('auth.phone')" />

            <x-text-input id="phone" class="mt-1 block w-full" type="tel" name="phone" :value="old('phone')"
                required autocomplete="tel" :placeholder="__('auth.phone_placeholder')" />

            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        {{-- Country --}}
        <div>
            <x-input-label for="country_code" :value="__('auth.country')" />

            <select id="country_code" name="country_code" required
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <option value="AE" @selected(old('country_code', 'AE') === 'AE')>
                    {{ __('auth.united_arab_emirates') }}
                </option>

                <option value="SA" @selected(old('country_code') === 'SA')>
                    {{ __('auth.saudi_arabia') }}
                </option>

                <option value="OM" @selected(old('country_code') === 'OM')>
                    {{ __('auth.oman') }}
                </option>

                <option value="QA" @selected(old('country_code') === 'QA')>
                    {{ __('auth.qatar') }}
                </option>

                <option value="BH" @selected(old('country_code') === 'BH')>
                    {{ __('auth.bahrain') }}
                </option>

                <option value="KW" @selected(old('country_code') === 'KW')>
                    {{ __('auth.kuwait') }}
                </option>
            </select>

            <x-input-error :messages="$errors->get('country_code')" class="mt-2" />
        </div>

        {{-- City --}}
        <div>
            <x-input-label for="city" :value="__('auth.city')" />

            <x-text-input id="city" class="mt-1 block w-full" type="text" name="city" :value="old('city')"
                autocomplete="address-level2" :placeholder="__('auth.city_placeholder')" />

            <x-input-error :messages="$errors->get('city')" class="mt-2" />
        </div>

        {{-- Email --}}
        <div>
            <x-input-label for="email" :value="__('auth.email')" />

            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')"
                required autocomplete="username" :placeholder="__('auth.email_placeholder')" />

            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div>
            <x-input-label for="password" :value="__('auth.password')" />

            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required
                autocomplete="new-password" :placeholder="__('auth.password_placeholder')" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Confirm Password --}}
        <div>
            <x-input-label for="password_confirmation" :value="__('auth.confirm_password')" />

            <x-text-input id="password_confirmation" class="mt-1 block w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" :placeholder="__('auth.confirm_password_placeholder')" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- Register Button --}}
        <div class="pt-1">
            <x-primary-button
                class="w-full justify-center bg-cyan-500 py-3 text-slate-950 hover:bg-cyan-400 focus:bg-cyan-400 active:bg-cyan-600">
                {{ __('auth.register') }}
            </x-primary-button>
        </div>
    </form>

    {{-- Login --}}
    @if (Route::has('login'))
        <div class="mt-7 border-t border-slate-200 pt-6 text-center dark:border-slate-800"
            dir="{{ $isAr ? 'rtl' : 'ltr' }}">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ __('auth.already_registered') }}

                <a href="{{ route('login') }}"
                    class="font-bold text-cyan-600 transition hover:text-cyan-500 dark:text-cyan-400 dark:hover:text-cyan-300">
                    {{ __('auth.login') }}
                </a>
            </p>
        </div>
    @endif

    {{-- Security --}}
    <div class="mt-6 flex items-center justify-center gap-2 text-xs text-slate-400" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <span class="inline-flex h-2 w-2 rounded-full bg-cyan-500"></span>

        <span>
            {{ __('auth.secure_platform') }}
        </span>
    </div>
</x-guest-layout>
