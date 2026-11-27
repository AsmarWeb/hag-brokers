<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-600 dark:text-cyan-400">HAG Maritime Brokerage</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900 dark:text-white sm:text-3xl">{{ __('deals.title') }}</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-500 dark:text-slate-400">{{ __('deals.subtitle') }}</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                {{ __('deals.back_dashboard') }}
            </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => __('deals.total_deals'), 'value' => $stats['total'], 'tone' => 'slate'],
                ['label' => __('deals.draft_deals'), 'value' => $stats['draft'], 'tone' => 'amber'],
                ['label' => __('deals.approved_deals'), 'value' => $stats['approved'], 'tone' => 'emerald'],
                ['label' => __('deals.pending_commissions'), 'value' => $stats['pending_commissions'], 'tone' => 'cyan'],
            ] as $stat)
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</p>
                    <p class="mt-3 text-3xl font-black tabular-nums text-slate-900 dark:text-white">{{ number_format($stat['value']) }}</p>
                </section>
            @endforeach
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <form method="GET" action="{{ route('admin.deals.index') }}" class="grid gap-3 border-b border-slate-200 p-4 dark:border-slate-800 md:grid-cols-[minmax(0,1fr)_220px_auto]">
                <label class="block">
                    <span class="mb-1.5 block text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('deals.search') }}</span>
                    <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('deals.search_placeholder') }}" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 placeholder:text-slate-400 focus:border-cyan-600 focus:ring-cyan-600 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-xs font-bold text-slate-500 dark:text-slate-400">{{ __('deals.status_filter') }}</span>
                    <select name="status" class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 focus:border-cyan-600 focus:ring-cyan-600 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">{{ __('deals.all_statuses') }}</option>
                        <option value="draft" @selected($filters['status'] === 'draft')>{{ __('deals.statuses.draft') }}</option>
                        <option value="approved" @selected($filters['status'] === 'approved')>{{ __('deals.statuses.approved') }}</option>
                    </select>
                </label>
                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex min-h-[42px] flex-1 items-center justify-center rounded-xl bg-slate-900 px-5 text-sm font-bold text-white transition hover:bg-slate-700 dark:bg-cyan-500 dark:text-slate-950 dark:hover:bg-cyan-400">{{ __('deals.apply_filters') }}</button>
                    <a href="{{ route('admin.deals.index') }}" class="inline-flex min-h-[42px] items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">{{ __('deals.reset') }}</a>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950/70">
                        <tr class="text-start text-[11px] font-extrabold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            <th class="px-5 py-4">{{ __('deals.reference') }}</th>
                            <th class="px-5 py-4">{{ __('deals.customer') }}</th>
                            <th class="px-5 py-4">{{ __('deals.source') }}</th>
                            <th class="px-5 py-4">{{ __('deals.deal_value') }}</th>
                            <th class="px-5 py-4">{{ __('deals.status') }}</th>
                            <th class="px-5 py-4">{{ __('deals.agreement') }}</th>
                            <th class="px-5 py-4">{{ __('deals.commission') }}</th>
                            <th class="px-5 py-4 text-end">{{ __('deals.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($deals as $deal)
                            @php
                                $latestAgreement = $deal->agreements->sortByDesc('version')->first();
                                $commission = $deal->commissions->first();
                                $statusClass = $deal->status === 'approved'
                                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300'
                                    : 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-300';
                            @endphp
                            <tr class="align-middle transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                <td class="whitespace-nowrap px-5 py-4">
                                    <a href="{{ route('admin.deals.show', $deal) }}" class="font-bold text-slate-900 hover:text-cyan-700 dark:text-white dark:hover:text-cyan-300">{{ $deal->reference }}</a>
                                    <p class="mt-1 text-xs text-slate-400">#{{ $deal->id }} · {{ $deal->created_at?->format('d M Y') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="min-w-36 font-semibold text-slate-800 dark:text-slate-200">{{ $deal->customer?->company_name ?? __('deals.unknown_customer') }}</p>
                                    @if ($deal->customer?->contact_person)
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $deal->customer->contact_person }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    @if ($deal->quotation)
                                        <span class="font-semibold">{{ __('deals.quotation') }}</span>
                                        <span class="text-xs text-slate-500">{{ $deal->quotation->reference_no }}</span>
                                    @elseif ($deal->opportunity)
                                        <span class="font-semibold">{{ __('deals.opportunity') }}</span>
                                        <span class="text-xs text-slate-500">{{ $deal->opportunity->title ?? ('#'.$deal->opportunity_id) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-bold tabular-nums text-slate-800 dark:text-slate-200">{{ $deal->currency }} {{ number_format((float) $deal->deal_amount, 2) }}</td>
                                <td class="whitespace-nowrap px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $statusClass }}">{{ __('deals.statuses.'.$deal->status) }}</span></td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    @if ($latestAgreement)
                                        <span class="font-semibold">v{{ $latestAgreement->version }}</span>
                                        <span class="ms-1 text-xs">{{ __('deals.statuses.'.$latestAgreement->status) }}</span>
                                    @else
                                        <span class="text-slate-400">{{ __('deals.not_started') }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700 dark:text-slate-200">
                                    @if ($commission)
                                        <span class="font-bold tabular-nums">{{ $commission->currency }} {{ number_format((float) $commission->amount, 2) }}</span>
                                        <span class="mt-1 block text-xs text-slate-500">{{ __('deals.statuses.'.$commission->status) }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-end">
                                    <a href="{{ route('admin.deals.show', $deal) }}" class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-cyan-500 hover:text-cyan-700 dark:border-slate-700 dark:text-slate-200 dark:hover:text-cyan-300">{{ __('deals.open') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl dark:bg-slate-800">◈</div>
                                    <h2 class="mt-4 font-bold text-slate-800 dark:text-white">{{ __('deals.no_deals') }}</h2>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('deals.no_deals_help') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($deals->hasPages())
                <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">
                    {{ $deals->links() }}
                </div>
            @endif
        </section>
    </div>
</x-admin-layout>
