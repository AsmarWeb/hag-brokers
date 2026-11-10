<x-carrier-layout :workspace="true">
    <section class="space-y-5">
        <header class="carrier-hero">
            <p class="carrier-eyebrow">HAG BROKERS / {{ __('carrier_partner.portal') }}</p>
            <h1 class="mt-3 break-words text-3xl font-black sm:text-4xl"><bdi>{{ $carrier->name }}</bdi></h1>
            <p class="mt-3 max-w-xl text-sm leading-relaxed text-slate-200">{{ __('carrier_partner.overview') }}</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="#carrier-vessel-requests" class="hag-broker-action">{{ __('carrier_vessels.add') }}</a>
                <a href="#carrier-fleet" class="carrier-secondary">{{ __('carrier_partner.fleet') }}</a>
            </div>
        </header>

        <dl class="grid grid-cols-2 gap-3">
            <div class="hag-broker-card">
                <dt class="hag-broker-muted">{{ __('carrier_partner.fleet') }}</dt>
                <dd class="hag-broker-number mt-1">{{ $vessels->total() }}</dd>
            </div>
            <div class="hag-broker-card">
                <dt class="hag-broker-muted">{{ __('carrier_partner.pending_requests') }}</dt>
                <dd class="hag-broker-number mt-1">{{ $pendingRequests }}</dd>
            </div>
        </dl>

        @if(session('carrier_status'))
            <p role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">{{ session('carrier_status') }}</p>
        @endif
        @if($errors->any())
            <ul role="alert" class="space-y-1 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-950 dark:border-rose-800 dark:bg-rose-950 dark:text-rose-200">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif

        <section id="carrier-fleet" class="space-y-3" aria-labelledby="carrier-fleet-heading">
            <h2 id="carrier-fleet-heading" class="text-xl font-black">{{ __('carrier_partner.fleet') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                @forelse($vessels as $vessel)
                    <article class="hag-broker-card flex min-w-0 items-start gap-3">
                        <span class="carrier-vessel-mark" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13V8h14v5M9 8V4h6v4m-12 7 9-4 9 4-2 5H5l-2-5Z"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="break-words font-bold"><bdi>{{ $vessel->name }}</bdi></h3>
                            <p class="hag-broker-muted">IMO · <bdi>{{ $vessel->imo ?? '—' }}</bdi></p>
                            @if($vessel->vessel_type)
                                <p class="break-words text-sm"><bdi>{{ $vessel->vessel_type }}</bdi></p>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="hag-broker-empty sm:col-span-2">{{ __('carrier_partner.empty_fleet') }}</p>
                @endforelse
            </div>
            {{ $vessels->fragment('carrier-fleet')->links() }}
        </section>

        <section id="carrier-vessel-requests" class="hag-broker-card space-y-4">
            <h2>{{ __('carrier_vessels.add') }}</h2>
            <p class="hag-broker-muted">{{ __('carrier_vessels.instructions') }}</p>
            <form method="POST" action="{{ route('carrier.fleet.requests.store') }}" class="grid items-end gap-3 sm:grid-cols-2">
                @csrf
                <input type="hidden" name="_carrier_form" value="vessel">
                <label class="grid gap-1 text-sm font-semibold">{{ __('sailings.vessel_name') }}
                    <x-text-input name="name" :value="old('_carrier_form') === 'vessel' ? old('name') : null" maxlength="120" required />
                </label>
                <label class="grid gap-1 text-sm font-semibold">IMO
                    <x-text-input name="imo" :value="old('imo')" inputmode="numeric" pattern="[0-9]{7}" maxlength="7" required />
                </label>
                <label class="grid gap-1 text-sm font-semibold">{{ __('carrier_vessels.type') }}
                    <x-text-input name="vessel_type" :value="old('vessel_type')" maxlength="100" required />
                </label>
                <button class="hag-broker-action">{{ __('carrier_vessels.submit') }}</button>
            </form>
            <h3 class="font-bold">{{ __('carrier_vessels.history') }}</h3>
            @forelse($vesselRequests as $proposal)
                <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                    <p class="font-semibold"><bdi>{{ $proposal->name }} · IMO {{ $proposal->imo }}</bdi></p>
                    <p class="mt-1 inline-flex rounded-full px-2 py-1 text-xs font-bold {{ match($proposal->status) {
                        'approved' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200',
                        'rejected' => 'bg-rose-100 text-rose-900 dark:bg-rose-950 dark:text-rose-200',
                        default => 'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-200',
                    } }}">{{ __('carrier_vessels.statuses.'.$proposal->status) }}</p>
                    @if($proposal->review_note)<p class="break-words text-sm">{{ $proposal->review_note }}</p>@endif
                </div>
            @empty
                <p class="hag-broker-muted">{{ __('carrier_vessels.empty') }}</p>
            @endforelse
            {{ $vesselRequests->fragment('carrier-vessel-requests')->links() }}
        </section>

        <div class="grid gap-4 lg:grid-cols-2">
            <form id="carrier-profile" method="POST" action="{{ route('carrier.profile.update') }}" class="hag-broker-card space-y-3">
                @csrf @method('PATCH')
                <input type="hidden" name="_carrier_form" value="profile">
                <h2>{{ __('carrier_partner.profile') }}</h2>
                <label class="grid gap-1 text-sm font-semibold">{{ __('carrier_partner.name') }}
                    <x-text-input name="name" :value="old('_carrier_form') === 'profile' ? old('name') : auth()->user()->name" required maxlength="255" autocomplete="name" />
                </label>
                <label class="grid gap-1 text-sm font-semibold">{{ __('carrier_partner.phone') }}
                    <x-text-input name="phone" type="tel" :value="old('phone', auth()->user()->phone)" required maxlength="30" autocomplete="tel" />
                </label>
                <button class="hag-broker-action">{{ __('carrier_partner.save_profile') }}</button>
            </form>
            <section id="carrier-api" class="hag-broker-card space-y-3">
                <h2>{{ __('carrier_partner.api') }}</h2>
                <p class="hag-broker-muted">{{ __('carrier_partner.api_help') }}</p>
                <p class="overflow-x-auto rounded-lg bg-slate-100 p-2 text-sm dark:bg-slate-950"><bdi><code>GET /api/v1/carrier/fleet</code></bdi></p>
                @if(session('carrier_api_token'))
                    <label class="grid gap-1 text-sm font-semibold">{{ __('carrier_partner.copy_token') }}
                        <x-text-input :value="session('carrier_api_token')" readonly dir="ltr" />
                    </label>
                @endif
                <form method="POST" action="{{ route('carrier.token.issue') }}" class="space-y-3">
                    @csrf
                    <label class="grid gap-1 text-sm font-semibold">{{ __('carrier_partner.current_password') }}
                        <x-text-input type="password" name="password" required autocomplete="current-password" />
                    </label>
                    <div class="flex flex-wrap items-center gap-3">
                        <button class="hag-broker-action">{{ __('carrier_partner.issue_token') }}</button>
                        <button formaction="{{ route('carrier.token.revoke') }}" name="_method" value="DELETE" class="text-sm font-semibold text-rose-800 underline dark:text-rose-300">{{ __('carrier_partner.revoke_token') }}</button>
                    </div>
                </form>
            </section>
        </div>

    </section>
</x-carrier-layout>
