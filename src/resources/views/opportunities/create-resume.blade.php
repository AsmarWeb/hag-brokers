<x-market-layout>
@section('title', app()->getLocale() === 'ar' ? 'مراجعة الفرصة التجارية' : 'Review commercial opportunity')
<div class="trade-page" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<div class="trade-wrap">
<header class="trade-hero"><p class="trade-eyebrow">HAG BROKERS · B2B</p><h1>{{ app()->getLocale() === 'ar' ? 'راجع الفرصة قبل نشرها' : 'Review before publishing' }}</h1><p>{{ app()->getLocale() === 'ar' ? 'لن تظهر الفرصة في السوق ولن تُحتسب من حصتك حتى تؤكد النشر.' : 'The opportunity will not appear in the marketplace or use your quota until you confirm publication.' }}</p></header>
@if($errors->any())<div class="trade-notice" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<section class="trade-panel">
<div class="trade-badges"><span>{{ __('trade.types.'.$data['opportunity_type']) }}</span>@if(!empty($data['category']))<span>{{ __('trade.categories.'.$data['category']) }}</span>@endif</div>
<h2>{{ $data['title'] }}</h2><p class="trade-prose">{{ $data['description'] }}</p>
<dl class="trade-data">
<div><dt>{{ __('trade.fields.country') }}</dt><dd>{{ $data['country'] ?? '—' }}</dd></div>
<div><dt>{{ __('trade.fields.city') }}</dt><dd>{{ $data['city'] ?? '—' }}</dd></div>
<div><dt>{{ __('trade.fields.currency') }}</dt><dd>{{ $data['currency'] }}</dd></div>
<div><dt>{{ __('trade.fields.expires_at') }}</dt><dd>{{ \Illuminate\Support\Carbon::parse($data['expires_at'])->setTimezone('Asia/Dubai')->format('Y-m-d') }}</dd></div>
@if(!empty($data['commercial_details']['product_name']))<div><dt>{{ __('trade.fields.product_name') }}</dt><dd>{{ $data['commercial_details']['product_name'] }}</dd></div>@endif
@if(!empty($data['origin_port_id']))<div><dt>{{ __('trade.fields.origin_port_id') }}</dt><dd>{{ \App\Models\Port::find($data['origin_port_id'])?->name ?? '—' }}</dd></div>@endif
@if(!empty($data['destination_port_id']))<div><dt>{{ __('trade.fields.destination_port_id') }}</dt><dd>{{ \App\Models\Port::find($data['destination_port_id'])?->name ?? '—' }}</dd></div>@endif
@if(!empty($data['vessel']))
<div><dt>{{ app()->getLocale() === 'ar' ? 'اسم السفينة' : 'Vessel name' }}</dt><dd>{{ $data['vessel']['vessel_name'] ?? '—' }}</dd></div>
<div><dt>IMO</dt><dd>{{ $data['vessel']['imo_number'] ?? '—' }}</dd></div>
<div><dt>{{ app()->getLocale() === 'ar' ? 'فئة السفينة' : 'Vessel category' }}</dt><dd>{{ $data['vessel']['vessel_category'] ?? '—' }}</dd></div>
<div><dt>{{ app()->getLocale() === 'ar' ? 'سنة البناء' : 'Year built' }}</dt><dd>{{ $data['vessel']['year_built'] ?? '—' }}</dd></div>
<div><dt>{{ app()->getLocale() === 'ar' ? 'الطول (متر)' : 'Length (m)' }}</dt><dd>{{ $data['vessel']['length_m'] ?? '—' }}</dd></div>
<div><dt>{{ app()->getLocale() === 'ar' ? 'الحمولة الساكنة' : 'Deadweight tonnage' }}</dt><dd>{{ $data['vessel']['deadweight_tonnage'] ?? '—' }}</dd></div>
@endif
</dl>
@if($files)<h3 class="mt-5 font-bold">{{ app()->getLocale() === 'ar' ? 'المرفقات' : 'Attachments' }}</h3><ul class="mt-2 list-disc ps-5">@foreach($files as $file)<li>{{ $file['original_name'] }}</li>@endforeach</ul>@endif
</section>
<form method="POST" action="{{ route('opportunities.create.resume.submit', ['draft' => request()->route('draft')]) }}" class="trade-actions">@csrf
<button class="trade-button" type="submit">{{ __('trade.publish') }}</button>
<a class="trade-button trade-secondary" href="{{ route('opportunities.create') }}">{{ app()->getLocale() === 'ar' ? 'العودة إلى النموذج' : 'Back to form' }}</a>
</form>
</div></div>
</x-market-layout>
