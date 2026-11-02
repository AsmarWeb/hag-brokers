<x-admin-layout>
@php $money = \App\Services\Revenue\ReportMoney::class; @endphp
<div class="mx-auto max-w-6xl space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4"><div><p class="text-sm font-bold text-cyan-700 dark:text-cyan-300">{{ __('payments.title') }}</p><h1 class="mt-2 font-bold"><bdi>{{ $invoice->reference }}</bdi></h1></div><a class="hag-broker-action" href="{{ route('admin.invoices.show', $invoice) }}">{{ __('payments.back') }}</a></header>
    <p class="hag-broker-muted">{{ __('payments.guidance') }}</p>
    @if (session('success'))<p role="status" class="rounded-xl border border-emerald-400 p-4">{{ session('success') }}</p>@endif
    @if ($errors->any())<ul role="alert" class="list-disc rounded-xl border border-red-400 p-5 ps-10">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    @if ($integrityError)<p role="alert" class="rounded-xl border border-red-400 p-4">{{ $integrityError }}</p>@else
    <section class="grid gap-4 sm:grid-cols-3" aria-label="{{ __('payments.summary') }}">
    @foreach (['total','paid','remaining'] as $key)<div class="hag-broker-card"><p class="hag-broker-muted">{{ __('payments.'.$key) }}</p><p class="mt-3 text-xl font-bold"><bdi>{{ $money::format($summary[$key]) }} {{ $summary['currency'] }}</bdi></p></div>@endforeach
    </section>
    <p class="font-bold">{{ __('payments.status') }}: {{ __('payments.status_'.$summary['status']) }}</p>
    @if ($summary['remaining'] !== '0.00')
    <section class="hag-broker-card"><h2 class="text-lg font-bold">{{ __('payments.add') }}</h2><p class="hag-broker-muted mt-2">{{ __('payments.form_help') }}</p>
        <form method="POST" action="{{ route('admin.invoices.payments.store', $invoice) }}" class="mt-5 grid gap-5 sm:grid-cols-2">
            @csrf<input type="hidden" name="request_key" value="{{ old('request_key', $requestKey) }}">
            <div><label for="payment-amount" class="block text-sm font-bold">{{ __('payments.amount') }} ({{ $summary['currency'] }})</label><input id="payment-amount" type="text" inputmode="decimal" name="amount" value="{{ old('amount') }}" required pattern="[0-9]+(\.[0-9]{1,2})?" maxlength="19" dir="ltr" class="mt-2 w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900"><p class="hag-broker-muted mt-1">{{ __('payments.amount_help') }}</p></div>
            <div><label for="payment-date" class="block text-sm font-bold">{{ __('payments.date') }}</label><input id="payment-date" type="date" name="paid_on" value="{{ old('paid_on', now('Asia/Dubai')->toDateString()) }}" min="{{ $invoice->snapshot['issue_date'] }}" max="{{ now('Asia/Dubai')->toDateString() }}" required class="mt-2 w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900"></div>
            <div><label for="payment-method" class="block text-sm font-bold">{{ __('payments.method') }}</label><select id="payment-method" name="method" required class="mt-2 w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900"><option value="">{{ __('payments.choose') }}</option>@foreach (['bank_transfer','cash','card','other'] as $method)<option value="{{ $method }}" @selected(old('method') === $method)>{{ __('payments.method_'.$method) }}</option>@endforeach</select></div>
            <div><label for="payment-reference" class="block text-sm font-bold">{{ __('payments.reference') }}</label><input id="payment-reference" name="reference" value="{{ old('reference') }}" maxlength="120" required class="mt-2 w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900"><p class="hag-broker-muted mt-1">{{ __('payments.reference_help') }}</p></div>
            <div class="sm:col-span-2"><label for="payment-notes" class="block text-sm font-bold">{{ __('payments.notes') }}</label><textarea id="payment-notes" name="notes" rows="3" maxlength="1000" class="mt-2 w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900">{{ old('notes') }}</textarea></div>
            <div class="sm:col-span-2"><button class="hag-broker-action" type="submit">{{ __('payments.save') }}</button></div>
        </form>
    </section>@endif
    <section class="hag-broker-card"><h2 class="text-lg font-bold">{{ __('payments.history') }}</h2><p class="hag-broker-muted mt-2">{{ __('payments.history_help') }}</p>
        <div class="mt-5 overflow-x-auto"><table class="min-w-full text-sm"><thead><tr>@foreach (['kind','amount','date','method','reference','actor'] as $key)<th scope="col" class="px-3 py-3 text-start">{{ __('payments.'.$key) }}</th>@endforeach</tr></thead><tbody>
        @forelse ($summary['events'] as $event)<tr class="border-t border-slate-200 dark:border-slate-700"><td class="px-3 py-3">{{ __('payments.kind_'.$event->kind) }} @if (isset($summary['reversed'][$event->id]))<span class="block hag-broker-muted">{{ __('payments.reversed') }}</span>@endif</td><td class="whitespace-nowrap px-3"><bdi>{{ $money::format($event->amount) }} {{ $event->currency }}</bdi></td><td class="whitespace-nowrap px-3"><bdi>{{ $event->paid_on->format('Y-m-d') }}</bdi></td><td class="px-3">{{ __('payments.method_'.$event->method) }}</td><td class="px-3">{{ $event->reference }}</td><td class="px-3">{{ $event->recorded_by_name }} <bdi>#{{ $event->recorded_by }}</bdi><span class="block whitespace-nowrap text-xs" dir="ltr">{{ $event->recorded_at->format('Y-m-d H:i') }} UTC</span></td></tr>
            @if ($event->notes || $event->reason)<tr><td colspan="6" class="whitespace-pre-wrap px-3 pb-3">{{ $event->reason ?: $event->notes }}</td></tr>@endif
            @if ($event->kind === 'receipt' && !isset($summary['reversed'][$event->id]))<tr><td colspan="6" class="px-3 pb-4"><details><summary class="cursor-pointer font-semibold text-red-700 dark:text-red-300">{{ __('payments.reverse') }}</summary><p class="hag-broker-muted my-3">{{ __('payments.reverse_help') }}</p><form method="POST" action="{{ route('admin.invoices.payments.reverse', [$invoice, $event]) }}" class="space-y-3">@csrf<input type="hidden" name="request_key" value="{{ \Illuminate\Support\Str::uuid() }}"><label for="reverse-{{ $event->id }}" class="block">{{ __('payments.reason') }}</label><textarea id="reverse-{{ $event->id }}" name="reason" maxlength="1000" required rows="2" class="w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900"></textarea><button type="submit" class="hag-broker-action">{{ __('payments.confirm_reverse') }}</button></form></details></td></tr>@endif
        @empty<tr><td colspan="6" class="p-5 hag-broker-muted">{{ __('payments.empty') }}</td></tr>@endforelse
        </tbody></table></div>
    </section>
    @endif
</div>
</x-admin-layout>
