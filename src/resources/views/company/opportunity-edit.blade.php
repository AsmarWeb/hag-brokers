<x-app-layout>
@section('title', __('trade.marketplace'))
@section('meta_description', __('trade.intro'))
<div class="cw-page trade-page" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<div class="trade-wrap">
<a class="trade-link" href="{{ route('company.marketplace') }}">{{ __('trade.workspace') }}</a>
@include('company.partials.navigation')<header class="trade-hero"><p class="trade-eyebrow">HAG BROKERS · B2B</p><h1>{{ __('trade.edit') }}</h1><p>{{ __('trade.guide_intro') }}</p></header>
@include('admin.platform.messages')
@include('opportunities.partials.form', ['editing' => true])
@if(app(\App\Services\Platform\PublicExperience::class)->marineQuery()->whereKey($opportunity->id)->exists())
<a class="trade-button" href="{{ route('company.map-location.edit', $opportunity) }}">{{ __('public_experience.manage_location') }}</a>
@endif
</div></div>
</x-app-layout>
