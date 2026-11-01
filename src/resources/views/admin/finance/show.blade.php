<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';
        $money = \App\Services\Revenue\ReportMoney::class;
    @endphp
    <div class="mx-auto max-w-6xl space-y-6" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <header class="flex flex-wrap items-center justify-between gap-4"><div><p class="text-sm font-bold text-cyan-700 dark:text-cyan-300">{{ __('finance.read_only') }}</p><h1 class="mt-2 font-bold">{{ $deal->reference }}</h1><p class="hag-broker-muted mt-2">{{ $deal->customer?->company_name ?? '—' }}</p></div><a href="{{ route('admin.finance.index') }}" class="hag-broker-action">{{ __('finance.back') }}</a></header>
        <p class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm leading-7 text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">{{ __('finance.cash_note') }}</p>
        <section class="hag-broker-card"><h2>{{ __('finance.deal_details') }}</h2>
            <dl class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                <div><dt class="hag-broker-muted">{{ __('finance.approved_at') }}</dt><dd class="mt-2 font-semibold"><bdi>{{ $deal->approved_at->format('Y-m-d H:i') }}</bdi></dd></div>
                <div><dt class="hag-broker-muted">{{ __('finance.collection_model') }}</dt><dd class="mt-2 font-semibold">{{ in_array($deal->collection_model, ['commission_only', 'full_service'], true) ? __('finance.'.$deal->collection_model) : $deal->collection_model }}</dd></div>
                <div><dt class="hag-broker-muted">{{ __('finance.deal_value') }}</dt><dd class="mt-2 font-semibold"><bdi>{{ $money::format((string) $deal->deal_amount) }} {{ $deal->currency }}</bdi></dd></div>
                <div><dt class="hag-broker-muted">{{ __('finance.supplier_cost') }}</dt><dd class="mt-2 font-semibold"><bdi>{{ $deal->supplier_cost === null ? '—' : $money::format((string) $deal->supplier_cost).' '.$deal->currency }}</bdi></dd></div>
                <div><dt class="hag-broker-muted">{{ __('finance.expected_revenue') }}</dt><dd class="mt-2 font-semibold"><bdi>{{ $money::format((string) $deal->platform_revenue) }} {{ $deal->currency }}</bdi></dd></div>
                <div><dt class="hag-broker-muted">{{ __('finance.received_cash') }}</dt><dd class="mt-2 font-semibold">{{ __('finance.unavailable') }}</dd></div>
            </dl>
            <p class="hag-broker-muted mt-5">{{ __('finance.revenue_note') }}</p>
            <div class="mt-4 flex flex-wrap gap-4 text-sm font-semibold text-cyan-700 dark:text-cyan-300">
                @if ($deal->quotation)<a href="{{ route('admin.quotations.show', $deal->quotation) }}">{{ __('finance.quotation') }}: {{ $deal->quotation->reference_no }}</a>@endif
                @if ($deal->opportunity)<a href="{{ route('admin.opportunities.show', $deal->opportunity) }}">{{ __('finance.opportunity') }}: {{ $deal->opportunity->title }}</a>@endif
            </div>
        </section>
        <section class="hag-broker-card"><h2>{{ __('finance.agreements') }}</h2>
            @forelse ($deal->agreements->where('status', 'approved') as $agreement)
                <article class="mt-4 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                    <h3 class="font-semibold">{{ __('finance.version') }} {{ $agreement->version }} · {{ in_array($agreement->fee_kind, ['percent','fixed','spread'], true) ? __('finance.'.$agreement->fee_kind) : $agreement->fee_kind }}</h3>
                    <p class="hag-broker-muted mt-2">{{ __('finance.approved_at') }}: <bdi>{{ $agreement->approved_at?->format('Y-m-d H:i') ?? '—' }}</bdi></p>
                    @if ($agreement->terms)<p class="mt-3 whitespace-pre-wrap break-words text-sm leading-7">{{ $agreement->terms }}</p>@endif
                </article>
            @empty<p class="hag-broker-muted mt-4">{{ __('finance.no_agreement') }}</p>@endforelse
        </section>
        <section class="hag-broker-card"><h2>{{ __('finance.commissions') }}</h2>
            @forelse ($deal->commissions as $commission)
                <article class="mt-4 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                    <div><h3 class="font-semibold"><bdi>{{ $money::format((string) $commission->amount) }} {{ $commission->currency }}</bdi></h3><p class="hag-broker-muted mt-2">{{ __('finance.version') }}: {{ $commission->agreement?->version ?? '—' }} · {{ __('finance.basis') }}: <bdi>{{ $money::format((string) $commission->basis_amount) }} {{ $commission->currency }}</bdi></p></div>
                    <span class="rounded-lg bg-cyan-50 px-3 py-2 text-sm font-bold text-cyan-900 dark:bg-cyan-400/10 dark:text-cyan-200">{{ in_array($commission->status, ['pending','earned','reversed'], true) ? __('finance.'.$commission->status) : __('finance.other') }}</span>
                </article>
            @empty<p class="hag-broker-muted mt-4">{{ __('finance.no_commission') }}</p>@endforelse
        </section>
    </div>
</x-admin-layout>
