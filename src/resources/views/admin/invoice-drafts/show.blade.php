<x-admin-layout>
    @php $isAr = app()->getLocale() === 'ar'; @endphp
    <div class="mx-auto max-w-6xl space-y-6" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <header class="flex flex-wrap items-center justify-between gap-4"><div><p class="text-sm font-bold text-cyan-700 dark:text-cyan-300">{{ __('invoice_drafts.internal_draft') }}</p><h1 class="mt-2 font-bold">{{ $draft->reference }}</h1></div><a href="{{ route('admin.invoice-drafts.index') }}" class="hag-broker-action">{{ __('invoice_drafts.back') }}</a></header>
        @if (session('success'))<div role="status" class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">{{ session('success') }}</div>@endif
        @if ($errors->any())<ul role="alert" class="list-disc rounded-xl border border-red-300 bg-red-50 p-5 ps-10 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
        <p class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm leading-7 text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">{{ __('invoice_drafts.draft_note') }}</p>
        @if (!$snapshotValid)<p role="alert" class="rounded-xl border border-red-400 p-4 font-bold text-red-700 dark:text-red-300">{{ __('invoice_drafts.snapshot_invalid') }}</p>@endif
        <section class="hag-broker-card"><h2>{{ __('invoice_drafts.customer_snapshot') }}</h2><dl class="mt-5 grid gap-5 sm:grid-cols-2">
            @foreach (['company_name','contact_person','email','country_code','city'] as $field)<div><dt class="hag-broker-muted">{{ __('invoice_drafts.'.$field) }}</dt><dd class="mt-2 break-words text-sm font-semibold">{{ data_get($draft->snapshot, 'customer.'.$field) ?: '—' }}</dd></div>@endforeach
        </dl><p class="hag-broker-muted mt-4">{{ __('invoice_drafts.snapshot_note') }}</p></section>
        <section class="hag-broker-card"><h2>{{ __('invoice_drafts.financial_details') }}</h2><div class="mt-5 flex flex-wrap items-center justify-between gap-5"><div><p class="font-bold">{{ __('invoice_drafts.line_description') }}</p><p class="hag-broker-muted mt-2">{{ __('invoice_drafts.agreement_version') }}: {{ data_get($draft->snapshot, 'agreement_version', '—') }}</p></div><p class="text-2xl font-bold"><bdi>{{ \App\Services\Revenue\ReportMoney::format((string) $draft->amount) }} {{ $draft->currency }}</bdi></p></div><p class="hag-broker-muted mt-4">{{ __('invoice_drafts.amount_note') }}</p><a href="{{ route('admin.finance.show', $draft->deal_id) }}" class="mt-4 inline-flex min-h-11 items-center text-sm font-bold text-cyan-700 dark:text-cyan-300">{{ __('invoice_drafts.deal') }}: {{ data_get($draft->snapshot, 'deal_reference', '—') }}</a></section>
        @if ($snapshotValid && $draft->status === 'draft')
            <section class="hag-broker-card"><h2>{{ __('invoice_drafts.edit_metadata') }}</h2><p class="hag-broker-muted mt-2">{{ __('invoice_drafts.edit_note') }}</p><form action="{{ route('admin.invoice-drafts.update', $draft) }}" method="POST" class="mt-5 space-y-5">@csrf @method('PATCH')
                <div class="max-w-sm"><label for="draft-due-date" class="mb-2 block text-sm font-semibold">{{ __('invoice_drafts.due_date') }}</label><input id="draft-due-date" type="date" name="due_date" value="{{ old('due_date', $draft->due_date?->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></div>
                <div><label for="draft-notes" class="mb-2 block text-sm font-semibold">{{ __('invoice_drafts.notes') }}</label><textarea id="draft-notes" name="notes" rows="4" maxlength="4000" class="w-full rounded-xl border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">{{ old('notes', $draft->notes) }}</textarea></div>
                <button type="submit" class="hag-broker-action">{{ __('invoice_drafts.save') }}</button>
            </form></section>
        @endif
    </div>
</x-admin-layout>
