<x-admin-layout>
    @php $isAr = app()->getLocale() === 'ar'; @endphp
    <div class="space-y-6" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <header class="hag-broker-hero"><p class="hag-broker-eyebrow">HAG BROKERS</p><h1 class="mt-3 font-bold">{{ __('invoice_drafts.title') }}</h1><p class="mt-3 max-w-3xl text-sm leading-7 text-slate-300">{{ __('invoice_drafts.subtitle') }}</p></header>
        <p class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm leading-7 text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">{{ __('invoice_drafts.draft_note') }}</p>
        <section class="hag-broker-card">
            <div class="flex flex-wrap items-center justify-between gap-3"><h2>{{ __('invoice_drafts.list') }}</h2><a href="{{ route('admin.finance.index') }}" class="text-sm font-bold text-cyan-700 dark:text-cyan-300">{{ __('invoice_drafts.finance') }}</a></div>
            <div class="mt-4 overflow-x-auto"><table class="min-w-full text-sm"><caption class="sr-only">{{ __('invoice_drafts.list') }}</caption><thead class="border-b border-slate-200 dark:border-slate-700"><tr>@foreach (['reference','customer','deal','amount','due_date','details'] as $column)<th scope="col" class="px-3 text-start">{{ __('invoice_drafts.'.$column) }}</th>@endforeach</tr></thead><tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($drafts as $draft)
                    <tr><td class="px-3"><a href="{{ route('admin.invoice-drafts.show', $draft) }}" class="font-bold text-cyan-700 dark:text-cyan-300">{{ $draft->reference }}</a></td><td class="px-3">{{ data_get($draft->snapshot, 'customer.company_name', '—') }}</td><td class="px-3">{{ data_get($draft->snapshot, 'deal_reference', '—') }}</td><td class="whitespace-nowrap px-3"><bdi>{{ \App\Services\Revenue\ReportMoney::format((string) $draft->amount) }} {{ $draft->currency }}</bdi></td><td class="whitespace-nowrap px-3"><bdi>{{ $draft->due_date?->format('Y-m-d') ?? '—' }}</bdi></td><td class="px-3"><a href="{{ route('admin.invoice-drafts.show', $draft) }}" class="inline-flex min-h-11 items-center font-semibold text-cyan-700 dark:text-cyan-300">{{ __('invoice_drafts.view') }}</a></td></tr>
                @empty<tr><td colspan="6" class="px-3 py-8 text-center"><h3 class="font-bold">{{ __('invoice_drafts.empty') }}</h3><p class="hag-broker-muted mt-2">{{ __('invoice_drafts.empty_help') }}</p></td></tr>@endforelse
            </tbody></table></div>
            @if ($drafts->hasPages())<div class="mt-4">{{ $drafts->links() }}</div>@endif
        </section>
    </div>
</x-admin-layout>
