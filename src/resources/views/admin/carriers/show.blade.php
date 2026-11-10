<x-admin-layout>

    <div class="mx-auto max-w-7xl">

        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-5 md:flex-row md:items-center md:justify-between">

            <div>

                <div class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">

                    <a href="{{ route('admin.carriers.index') }}" class="transition hover:text-cyan-800 dark:hover:text-cyan-400">
                        {{ __('admin_ui.label_016') }}
                    </a>

                    <span>/</span>

                    <span class="text-slate-600 dark:text-slate-400">
                        {{ $carrier->name }}
                    </span>

                </div>


                <div class="flex items-center gap-4">

                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/10 text-3xl">
                        ⚓
                    </div>

                    <div>

                        <h1 class="text-3xl font-black text-slate-900 dark:text-white">
                            {{ $carrier->name }}
                        </h1>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Carrier #{{ $carrier->id }}

                            @if ($carrier->code)
                                · {{ $carrier->code }}
                            @endif
                        </p>

                    </div>

                </div>

            </div>


            <div class="flex gap-3">

                <a href="{{ route('admin.carriers.edit', $carrier) }}"
                    class="rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950 transition hover:bg-cyan-300">
                    {{ __('admin_ui.label_062') }}
                </a>

                <a href="{{ route('admin.carriers.index') }}"
                    class="rounded-xl border border-slate-300 dark:border-slate-700 px-5 py-3 text-sm font-bold text-slate-700 dark:text-slate-300 transition hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-950 dark:hover:text-white">
                    {{ __('admin_ui.label_030') }}
                </a>

            </div>

        </div>


        <section class="hag-broker-card mb-6 space-y-3">
            <h2>{{ __('carrier_partner.invitation') }}</h2>
            <p class="hag-broker-muted">{{ __('carrier_partner.instructions') }}</p>
            @if($carrier->is_active)
                <form method="POST" action="{{ route('admin.carriers.invitations.store', $carrier) }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <label class="grid min-w-0 flex-1 gap-2 text-sm font-semibold">
                        {{ __('carrier_partner.email') }}
                        <input name="email" type="email" value="{{ old('email') }}" maxlength="255" required
                            class="w-full border-slate-300 bg-white text-slate-900 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-100">
                    </label>
                    <button class="hag-broker-action">{{ __('carrier_partner.invite') }}</button>
                </form>
                @error('email')<p role="alert" class="text-sm text-rose-800 dark:text-rose-300">{{ $message }}</p>@enderror
                @if(session('carrier_invitation_url'))
                    <label class="grid gap-2 text-sm font-semibold">
                        {{ __('carrier_partner.link') }}
                        <input type="url" readonly dir="ltr" value="{{ session('carrier_invitation_url') }}"
                            class="w-full border-slate-300 bg-white text-slate-900 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-100">
                    </label>
                @endif
            @else
                <p class="hag-broker-muted">{{ __('carrier_partner.inactive') }}</p>
            @endif
            <div class="space-y-3 border-t border-slate-200 pt-4 dark:border-slate-700">
                <h3 class="font-bold">{{ __('carrier_partner.history') }}</h3>
                @if(session('carrier_invitation_status'))
                    <p role="status" class="hag-broker-muted">{{ session('carrier_invitation_status') }}</p>
                @endif
                @forelse($invitations as $invitation)
                    @php($status = $invitation->status())
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-slate-50 p-3 dark:bg-slate-950">
                        <div class="min-w-0">
                            <p class="break-all text-sm font-semibold"><bdi>{{ $invitation->email }}</bdi></p>
                            <p class="hag-broker-muted">{{ __('carrier_partner.statuses.'.$status) }} · {{ __('carrier_partner.expires') }} <bdi>{{ $invitation->expires_at->setTimezone('Asia/Dubai')->format('Y-m-d H:i') }}</bdi> · {{ __('carrier_partner.dubai_time') }}</p>
                        </div>
                        @if(in_array($status, ['pending', 'accepted'], true))
                            <details>
                                <summary class="cursor-pointer text-sm font-semibold text-rose-800 dark:text-rose-300">{{ __('carrier_partner.revoke') }}</summary>
                                <form method="POST" action="{{ route('admin.carriers.invitations.revoke', [$carrier, $invitation]) }}" class="mt-2">
                                    @csrf @method('PATCH')
                                    <button class="rounded-lg bg-rose-100 px-3 py-2 text-sm font-bold text-rose-950 dark:bg-rose-950 dark:text-rose-200">{{ __('carrier_partner.confirm_revoke') }}</button>
                                </form>
                            </details>
                        @endif
                    </div>
                @empty
                    <p class="hag-broker-muted">{{ __('carrier_partner.no_invitations') }}</p>
                @endforelse
                {{ $invitations->links() }}
            </div>
        </section>

        {{-- Overview --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Main Info --}}
            <div class="lg:col-span-2 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950">

                <div class="border-b border-slate-200 dark:border-slate-800 px-6 py-5">

                    <h2 class="font-black text-slate-900 dark:text-white">
                        {{ __('admin_ui.label_065') }}
                    </h2>

                </div>


                <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2">

                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Carrier Name
                        </p>

                        <p class="mt-2 text-lg font-black text-slate-900 dark:text-white">
                            {{ $carrier->name }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Carrier Code
                        </p>

                        <p class="mt-2 font-mono text-lg font-bold text-cyan-800 dark:text-cyan-400">
                            {{ $carrier->code ?? '—' }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            {{ __('admin_ui.label_010') }}
                        </p>

                        <p class="mt-2 font-bold text-slate-900 dark:text-white">
                            {{ $carrier->country ?? '—' }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Country Code
                        </p>

                        <p class="mt-2 font-mono font-bold text-slate-900 dark:text-white">
                            {{ $carrier->country_code ?? '—' }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            {{ __('admin_ui.label_067') }}
                        </p>

                        @if ($carrier->website)
                            <a href="{{ $carrier->website }}" target="_blank" rel="noopener noreferrer"
                                class="mt-2 inline-block font-semibold text-cyan-800 dark:text-cyan-400 hover:text-cyan-950 dark:hover:text-cyan-300">
                                {{ $carrier->website }}
                                ↗
                            </a>
                        @else
                            <p class="mt-2 text-slate-600">
                                —
                            </p>
                        @endif

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            API Provider
                        </p>

                        <p class="mt-2 font-bold text-slate-900 dark:text-white">
                            {{ $carrier->api_provider ?? 'Manual Management' }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- Status --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950">

                <div class="border-b border-slate-200 dark:border-slate-800 px-6 py-5">

                    <h2 class="font-black text-slate-900 dark:text-white">
                        {{ __('admin_ui.label_032') }}
                    </h2>

                </div>


                <div class="space-y-5 p-6">

                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            {{ __('admin_ui.label_001') }}
                        </p>

                        <span
                            class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $carrier->is_active ? 'bg-emerald-400/10 text-emerald-800 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                            {{ $carrier->is_active ? 'Active' : 'Inactive' }}
                        </span>

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            {{ __('admin_ui.label_148') }}
                        </p>

                        <p class="mt-2 text-3xl font-black text-cyan-800 dark:text-cyan-400">
                            {{ $carrier->vessels_count ?? $carrier->vessels->count() }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Quotation Offers
                        </p>

                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">
                            {{ $carrier->quotation_offers_count ?? $carrier->quotationOffers->count() }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            {{ __('admin_ui.label_014') }}
                        </p>

                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">
                            {{ $carrier->shipments_count ?? $carrier->shipments->count() }}
                        </p>

                    </div>

                </div>

            </div>

        </div>


        {{-- Vessels --}}
        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950">

            <div
                class="flex flex-col gap-3 border-b border-slate-200 dark:border-slate-800 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <h2 class="font-black text-slate-900 dark:text-white">
                        Carrier Vessels
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Vessels currently assigned to this carrier.
                    </p>

                </div>


                <a href="{{ route('admin.vessels.create') }}"
                    class="rounded-xl bg-cyan-400 px-4 py-2.5 text-center text-xs font-black text-slate-950 transition hover:bg-cyan-300">
                    + Add Vessel
                </a>

            </div>


            @if ($carrier->vessels->count())

                <div class="divide-y divide-slate-200 dark:divide-slate-800">

                    @foreach ($carrier->vessels as $vessel)
                        <div class="flex flex-col gap-4 px-6 py-5 md:flex-row md:items-center md:justify-between">

                            <div class="flex items-center gap-4">

                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-400/10">
                                    🚢
                                </div>

                                <div>

                                    <p class="font-bold text-slate-900 dark:text-white">
                                        {{ $vessel->name }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">

                                        @if ($vessel->imo)
                                            IMO {{ $vessel->imo }}
                                        @else
                                            No IMO
                                        @endif

                                        ·

                                        {{ ucfirst($vessel->status) }}

                                    </p>

                                </div>

                            </div>


                            <div class="flex items-center gap-3">

                                @if ($vessel->is_in_gulf)
                                    <span class="rounded-full bg-cyan-400/10 px-3 py-1 text-xs font-bold text-cyan-800 dark:text-cyan-400">
                                        {{ __('admin_ui.label_069') }}
                                    </span>
                                @endif


                                <a href="{{ route('admin.vessels.show', $vessel) }}"
                                    class="rounded-lg border border-slate-300 dark:border-slate-700 px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-300 transition hover:border-cyan-400 hover:text-cyan-800 dark:hover:text-cyan-400">
                                    {{ __('admin_ui.label_004') }}
                                </a>

                            </div>

                        </div>
                    @endforeach

                </div>
            @else
                <div class="px-6 py-12 text-center">

                    <div class="text-4xl">
                        🚢
                    </div>

                    <p class="mt-3 font-bold text-slate-900 dark:text-white">
                        No vessels assigned
                    </p>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        This carrier does not have any vessels yet.
                    </p>

                </div>

            @endif

        </div>

    </div>

</x-admin-layout>
