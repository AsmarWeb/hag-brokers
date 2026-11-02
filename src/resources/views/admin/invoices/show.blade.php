<x-admin-layout>
    <div class="mx-auto max-w-6xl space-y-6">
        <header class="flex flex-wrap items-center justify-between gap-4"><div><p class="text-sm font-bold text-cyan-700 dark:text-cyan-300">{{ __('invoices.issued') }}</p><h1 class="mt-2 font-bold">{{ $invoice->reference }}</h1></div><a href="{{ route('admin.invoices.index') }}" class="hag-broker-action">{{ __('invoices.back') }}</a></header>
        @if (session('success'))<p role="status" class="rounded-xl border border-emerald-400 p-4 text-sm text-emerald-800 dark:text-emerald-200">{{ session('success') }}</p>@endif
        @if ($errors->any())<ul role="alert" class="list-disc rounded-xl border border-red-400 p-5 ps-10 text-sm text-red-700 dark:text-red-300">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
        @if (!$snapshotValid)<p role="alert" class="rounded-xl border border-red-400 p-4 font-bold text-red-700 dark:text-red-300">{{ __('invoices.snapshot_invalid') }}</p>@else
            <div class="flex flex-wrap gap-3">
                <a class="hag-broker-action" href="{{ route('admin.invoices.payments', $invoice) }}">{{ __('payments.title') }}</a>
                <a class="hag-broker-action" href="{{ route('admin.invoices.pdf', [$invoice, 'ar']) }}">{{ __('invoices.pdf_ar') }}</a>
                <a class="hag-broker-action" href="{{ route('admin.invoices.pdf', [$invoice, 'en']) }}">{{ __('invoices.pdf_en') }}</a>
            </div>
            @include('admin.invoices._document', ['document' => $invoice->snapshot])
            <p class="hag-broker-muted">{{ __('invoices.locked_note') }}</p>
        @endif
    </div>
</x-admin-layout>
