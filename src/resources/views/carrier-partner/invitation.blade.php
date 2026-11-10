<x-carrier-layout>
    <section>
        <div class="hag-broker-card space-y-4 text-slate-900 dark:text-slate-100">
            <h1 class="text-2xl font-black">{{ __('carrier_partner.invitation') }}</h1>
            <p>{{ __('carrier_partner.invited_to', ['carrier' => $invitation->carrier->name]) }}</p>
            <p class="hag-broker-muted"><bdi>{{ $invitation->email }}</bdi></p>
            <form method="POST" action="{{ route('carrier.invitation.accept', ['token' => $token]) }}">
                @csrf
                <button class="hag-broker-action">{{ __('carrier_partner.accept') }}</button>
            </form>
        </div>
    </section>
</x-carrier-layout>
