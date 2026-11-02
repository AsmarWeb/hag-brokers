@php
    $ar = $language === 'ar';
    $t = fn (string $key) => trans('invoices.'.$key, [], $language);
    $money = fn ($value) => \App\Services\Revenue\ReportMoney::format($value);
    $issuer = $document['issuer'];
    $input = $document['input'];
    $amounts = $document['amounts'];
@endphp
<!doctype html>
<html lang="{{ $language }}" dir="{{ $ar ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><title>{{ $document['reference'] }}</title>
<style>
body {font-family: dejavusans; font-size:10pt; color:#182d47; line-height:1.65;}
table {width:100%; border-collapse:collapse;} td,th {vertical-align:top; padding:9px;}
.brand {font-size:22pt;font-weight:bold;color:#142b46;} .accent {color:#087e96;}
.header {border-bottom:3px solid #13a2b8;margin-bottom:20px;}
.title {font-size:20pt;font-weight:bold;color:#142b46; margin-bottom:8px;}
.label {font-size:8pt;color:#617186;} .value {font-weight:bold;} .muted {color:#617186;}
.panel {background:#f2f6fa;border:1px solid #dce5ed;padding:14px;margin:14px 0;}
.lines {margin-top:20px;table-layout:fixed;} .lines thead th {background:#142b46;color:white;font-size:8pt;}
.lines td {border-bottom:1px solid #dce5ed;font-size:9pt;word-wrap:break-word;}
.amount {direction:ltr;white-space:nowrap;} .totals {margin-top:18px;background:#f2f6fa;}
.total td {background:#142b46;color:#ffffff;font-size:13pt;} .small {font-size:8pt;}
.notes {margin-top:18px;} .payment {margin-top:22px;border-top:1px solid #dce5ed;padding-top:12px;font-size:8pt;color:#617186;}
</style></head>
<body>
<table class="header"><tr><td><div class="brand">HAG<span class="accent"> Brokers</span></div><div class="small muted">{{ $ar ? 'الوساطة التجارية' : 'Commercial Brokerage' }}</div></td><td style="text-align:{{ $ar ? 'left' : 'right' }}"><div class="title">{{ $t('tax_invoice') }}</div><span class="amount" dir="ltr">{{ $document['reference'] }}</span></td></tr></table>
<table><tr><td width="58%"><div class="value">{{ $issuer[$ar && !empty($issuer['name_ar']) ? 'name_ar' : 'name'] }}</div><div>{!! nl2br(e($issuer['address'])) !!}</div><div>{{ $issuer['city'] }} · {{ $issuer['country'] }}</div><div><span class="label">TRN</span> <span class="amount" dir="ltr">{{ $issuer['trn'] }}</span></div></td><td width="42%">
@foreach (['issue_date' => $document['issue_date'], 'supply_date' => $input['supply_date'], 'due_date' => ($input['due_date'] ?: '—')] as $key => $value)
<div><span class="label">{{ $t($key) }}</span> <span class="amount" dir="ltr">{{ $value }}</span></div>
@endforeach
</td></tr></table>
<div class="panel"><div class="label">{{ $t('customer') }}</div><div class="value">{{ $document['customer_name'] }}</div><div>{!! nl2br(e($input['recipient_address'])) !!}</div>
@if ($input['recipient_registered'] === 'yes')<div><span class="label">TRN</span> <span class="amount" dir="ltr">{{ $input['recipient_trn'] }}</span></div>@endif
</div>
<table class="lines"><thead><tr><th width="29%">{{ $t('description') }}</th><th width="9%">{{ $ar ? 'الكمية' : 'Qty' }}</th><th width="18%">{{ $t('unit_price') }}</th><th width="8%">{{ $t('vat_rate') }}</th><th width="17%">{{ $t('tax') }}</th><th width="19%">{{ $t('total') }}</th></tr></thead><tbody><tr>
<td>{!! nl2br(e($input['description_'.$language])) !!}</td><td>1</td><td><span class="amount" dir="ltr">{{ $money($amounts['subtotal']) }}</span><br>{{ $document['currency'] }}</td><td><span class="amount" dir="ltr">{{ intdiv($amounts['tax_rate_bps'],100) }}%</span></td><td><span class="amount" dir="ltr">{{ $money($amounts['tax_amount']) }}</span><br>{{ $document['currency'] }}</td><td><b class="amount" dir="ltr">{{ $money($amounts['total']) }}</b><br>{{ $document['currency'] }}</td></tr>
@if ($document['currency'] !== 'AED')<tr><td class="small">{{ $ar ? 'القيم بالدرهم الإماراتي' : 'Amounts in UAE dirhams' }}</td><td>1</td><td class="amount" dir="ltr">{{ $money($amounts['subtotal_aed']) }}</td><td>{{ intdiv($amounts['tax_rate_bps'],100) }}%</td><td class="amount" dir="ltr">{{ $money($amounts['tax_aed']) }}</td><td class="amount" dir="ltr">{{ $money($amounts['total_aed']) }}</td></tr>@endif
</tbody></table>
<table class="totals">@foreach (['subtotal_aed','tax_aed','total_aed'] as $key)<tr class="{{ $key === 'total_aed' ? 'total' : '' }}"><td>{{ $t($key) }}</td><td style="text-align:{{ $ar ? 'left' : 'right' }}"><span class="amount" dir="ltr">{{ $money($amounts[$key]) }} AED</span></td></tr>@endforeach</table>
<p class="small muted">{{ $t('discount') }}: <span class="amount" dir="ltr">0.00 {{ $document['currency'] }}</span> · {{ $t('amount_basis') }}: {{ $t($input['amount_basis']) }}</p>
@if ($document['currency'] !== 'AED')<p class="small">{{ $t('rate') }}: <span class="amount" dir="ltr">{{ $amounts['exchange_rate'] }} AED / {{ $document['currency'] }}</span><br>{{ $t('rate_date') }}: <span class="amount" dir="ltr">{{ $input['exchange_rate_date'] }}</span><br>{{ $t('rate_reference') }}: {{ $input['exchange_rate_reference'] }}</p>@endif
@if (!empty($input['tax_reason']))<div class="notes small"><b>{{ $t('tax_reason') }}</b><br>{!! nl2br(e($input['tax_reason'])) !!}</div>@endif
@if (!empty($input['public_notes']))<div class="notes small">{!! nl2br(e($input['public_notes'])) !!}</div>@endif
<div class="payment">{{ $t('payment_note') }}</div>
</body></html>
