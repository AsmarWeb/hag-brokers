<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';
        $money = \App\Services\Revenue\ReportMoney::class;
    @endphp
    <div class="space-y-6" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <header class="hag-broker-hero">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div><p class="hag-broker-eyebrow">HAG BROKERS · {{ __('finance.read_only') }}</p><h1 class="mt-3 font-bold">{{ __('finance.title') }}</h1><p class="mt-3 max-w-3xl text-sm leading-7 text-slate-300">{{ __('finance.subtitle') }}</p></div>
                <span class="rounded-xl border border-cyan-300/30 px-4 py-2 text-xs font-bold text-cyan-200">{{ __('finance.approved_only') }}</span>
            </div>
        </header>
        <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm leading-7 text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200" role="note">
            <strong>{{ __('finance.cash_unavailable') }}</strong> {{ __('finance.cash_note') }}
        </div>
        <section class="hag-broker-card">
            <h2>{{ __('finance.filters') }}</h2>
            @if ($errors->any())
                <ul role="alert" class="mt-3 list-disc ps-5 text-sm text-red-700 dark:text-red-300">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            @endif
            <form method="GET" action="{{ route('admin.finance.index') }}" class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div><label for="date-from" class="mb-2 block text-sm font-semibold">{{ __('finance.date_from') }}</label><input id="date-from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full rounded-xl border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></div>
                <div><label for="date-to" class="mb-2 block text-sm font-semibold">{{ __('finance.date_to') }}</label><input id="date-to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full rounded-xl border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></div>
                <div><label for="finance-customer" class="mb-2 block text-sm font-semibold">{{ __('finance.customer') }}</label><select id="finance-customer" name="customer_id" class="w-full rounded-xl border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"><option value="">{{ __('finance.all_customers') }}</option>@foreach ($customers as $customer)<option value="{{ $customer->id }}" @selected((string) ($filters['customer_id'] ?? '') === (string) $customer->id)>{{ $customer->company_name }}</option>@endforeach</select></div>
                <div><label for="finance-currency" class="mb-2 block text-sm font-semibold">{{ __('finance.deal_currency') }}</label><select id="finance-currency" name="currency" class="w-full rounded-xl border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"><option value="">{{ __('finance.all_currencies') }}</option>@foreach ($currencies as $currency)<option value="{{ $currency }}" @selected(($filters['currency'] ?? '') === $currency)>{{ $currency }}</option>@endforeach</select></div>
                <div><label for="finance-status" class="mb-2 block text-sm font-semibold">{{ __('finance.commission_status') }}</label><select id="finance-status" name="commission_status" class="w-full rounded-xl border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"><option value="">{{ __('finance.all_statuses') }}</option>@foreach (['pending', 'earned', 'reversed'] as $status)<option value="{{ $status }}" @selected(($filters['commission_status'] ?? '') === $status)>{{ __('finance.'.$status) }}</option>@endforeach</select></div>
                <div class="flex flex-wrap items-center gap-3 sm:col-span-2 xl:col-span-5"><button type="submit" class="hag-broker-action">{{ __('finance.apply') }}</button><a href="{{ route('admin.finance.index') }}" class="rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold dark:border-slate-700">{{ __('finance.reset') }}</a><p class="hag-broker-muted">{{ __('finance.date_note') }}</p></div>
            </form>
        </section>
        <section aria-labelledby="currency-summary-title" class="space-y-4">
            <h2 id="currency-summary-title" class="text-lg font-bold">{{ __('finance.currency_summary') }}</h2>
            @forelse ($summary as $currency => $totals)
                <article class="hag-broker-card">
                    <div class="flex flex-wrap items-center justify-between gap-3"><h3 class="font-bold text-cyan-800 dark:text-cyan-300">{{ $currency }}</h3><span class="hag-broker-muted">{{ __('finance.deal_count') }}: {{ $totals['deal_count'] }} · {{ __('finance.commission_count') }}: {{ $totals['commission_count'] }}</span></div>
                    <dl class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach (['deal_value', 'pending', 'earned', 'reversed'] as $metric)
                            <div><dt class="hag-broker-muted">{{ __('finance.'.$metric) }}</dt><dd dir="ltr" class="mt-2 break-all text-start text-xl font-bold">{{ $money::format($totals[$metric]) }} <span class="text-xs font-normal">{{ $currency }}</span></dd></div>
                        @endforeach
                    </dl>
                    @if ($totals['other'] !== '0.00')<p class="hag-broker-muted mt-4">{{ __('finance.other') }}: <bdi>{{ $money::format($totals['other']) }} {{ $currency }}</bdi></p>@endif
                </article>
            @empty
                <div class="hag-broker-empty"><h3 class="font-bold">{{ __('finance.empty_title') }}</h3><p class="hag-broker-muted mt-2">{{ __('finance.empty_note') }}</p></div>
            @endforelse
            <p class="hag-broker-muted">{{ __('finance.summary_note') }}</p>
        </section>
        <section class="hag-broker-card">
            <div class="flex flex-wrap items-center justify-between gap-3"><h2>{{ __('finance.deals') }}</h2><span class="hag-broker-muted">{{ __('finance.results') }}: {{ $deals->total() }}</span></div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-start text-sm">
                    <caption class="sr-only">{{ __('finance.deals') }}</caption>
                    <thead class="border-b border-slate-200 text-slate-600 dark:border-slate-700 dark:text-slate-300"><tr>@foreach (['reference', 'customer', 'approved_at', 'deal_value', 'commissions', 'details'] as $column)<th scope="col" class="px-3 text-start font-semibold">{{ __('finance.'.$column) }}</th>@endforeach</tr></thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse ($deals as $deal)
                            <tr>
                                <td class="px-3 font-semibold"><a href="{{ route('admin.finance.show', $deal) }}" class="text-cyan-700 hover:underline dark:text-cyan-300">{{ $deal->reference }}</a></td>
                                <td class="px-3">{{ $deal->customer?->company_name ?? '—' }}</td>
                                <td class="whitespace-nowrap px-3"><bdi>{{ $deal->approved_at->format('Y-m-d') }}</bdi></td>
                                <td class="whitespace-nowrap px-3"><bdi>{{ $money::format((string) $deal->deal_amount) }} {{ $deal->currency }}</bdi></td>
                                <td class="px-3">@forelse ($deal->commissions->filter(fn ($commission) => empty($filters['commission_status']) || $commission->status === $filters['commission_status']) as $commission)<div class="whitespace-nowrap py-1"><bdi>{{ $money::format((string) $commission->amount) }} {{ $commission->currency }}</bdi><span class="ms-2 text-xs text-slate-600 dark:text-slate-400">{{ in_array($commission->status, ['pending','earned','reversed'], true) ? __('finance.'.$commission->status) : __('finance.other') }}</span></div>@empty<span class="hag-broker-muted">{{ __('finance.no_commission') }}</span>@endforelse</td>
                                <td class="px-3"><a href="{{ route('admin.finance.show', $deal) }}" class="inline-flex min-h-11 items-center font-semibold text-cyan-700 dark:text-cyan-300">{{ __('finance.view') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-3 py-8 text-center">{{ __('finance.empty_title') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($deals->hasPages())<div class="mt-5">{{ $deals->links() }}</div>@endif
        </section>
    </div>
</x-admin-layout>
