<x-carrier-layout>
    <div class="hag-broker-card space-y-5">
        <header>
            <p class="text-sm font-bold text-cyan-800 dark:text-cyan-300">HAG BROKERS</p>
            <h1 class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ __('carrier_partner.invitation') }}</h1>
            <p class="mt-2 text-slate-700 dark:text-slate-300">{{ __('carrier_partner.invited_to', ['carrier' => $invitation->carrier->name]) }}</p>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400"><bdi>{{ $invitation->email }}</bdi></p>
        </header>
        <form method="POST" action="{{ route('carrier.onboarding.register', $token) }}" class="space-y-4">
            @csrf
            <div>
                <x-input-label for="partner-name" :value="__('carrier_partner.name')" />
                <x-text-input id="partner-name" name="name" :value="old('name')" maxlength="255" required autocomplete="name" class="mt-1 block w-full" />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="partner-password" :value="__('carrier_partner.new_password')" />
                <x-text-input id="partner-password" name="password" type="password" minlength="12" required autocomplete="new-password" class="mt-1 block w-full" />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="partner-confirmation" :value="__('carrier_partner.confirm_password')" />
                <x-text-input id="partner-confirmation" name="password_confirmation" type="password" minlength="12" required autocomplete="new-password" class="mt-1 block w-full" />
            </div>
            <button class="hag-broker-action">{{ __('carrier_partner.create_account') }}</button>
        </form>
        <a class="text-sm font-semibold text-cyan-800 underline dark:text-cyan-300" href="{{ route('carrier.invitation.show', $token) }}">{{ __('carrier_partner.sign_in') }}</a>
    </div>
</x-carrier-layout>
