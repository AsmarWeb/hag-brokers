<x-app-layout>
@section('title', __('trade.marketplace'))
@section('meta_description', __('trade.intro'))
<div class="trade-page" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<div class="trade-wrap">
<a class="trade-link" href="{{ route('company.marketplace') }}">{{ __('trade.workspace') }}</a>
<header class="trade-hero"><p class="trade-eyebrow">HAG BROKERS · B2B</p><h1>{{ __('trade.edit') }}</h1><p>{{ __('trade.guide_intro') }}</p></header>
@include('admin.platform.messages')
@include('opportunities.partials.form', ['editing' => true])
</div></div>
</x-app-layout>
