<x-admin-layout>
    <div class="mx-auto max-w-6xl space-y-6">
        <header><h1 class="font-bold">{{ __('invoices.final_review') }}</h1><p class="hag-broker-muted mt-3">{{ __('invoices.preview_note') }}</p></header>
        @include('admin.invoices._document', ['document' => $context + ['customer_name' => data_get($draft->snapshot, 'customer.company_name')]])
        <form action="{{ route('admin.invoices.issue', $draft) }}" method="POST" class="hag-broker-card space-y-4">@csrf
            <input type="hidden" name="review_token" value="{{ $token }}">
            <label class="flex items-start gap-3 text-sm leading-7"><input type="checkbox" name="confirm" value="1" required class="mt-1 h-5 w-5 rounded border-slate-400 text-cyan-600"><span>{{ __('invoices.confirm') }}</span></label>
            <div class="flex flex-wrap gap-3"><button type="submit" class="hag-broker-action">{{ __('invoices.issue') }}</button><a href="{{ route('admin.invoices.review', $draft) }}" class="rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold dark:border-slate-700">{{ __('invoices.back_review') }}</a></div>
        </form>
    </div>
</x-admin-layout>
