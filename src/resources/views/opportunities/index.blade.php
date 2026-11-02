<x-app-layout>
@section('title', __('trade.marketplace'))
@section('meta_description', __('trade.intro'))
@php $catalog = \App\Services\Platform\OpportunityCatalog::class; @endphp
<div class="trade-page" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"><div class="trade-wrap">
<header class="trade-hero"><p class="trade-eyebrow">HAG BROKERS · B2B</p><h1>{{ __('trade.hero') }}</h1><p>{{ __('trade.intro') }}</p><div class="trade-actions"><a class="trade-button" href="{{ route('opportunities.create') }}">{{ __('trade.new') }}</a><a class="trade-link" href="{{ route('opportunities.index', ['country' => 'AE']) }}">{{ __('trade.uae') }}</a></div></header>
@include('admin.platform.messages')
<form method="GET" action="{{ route('opportunities.index') }}" class="trade-panel trade-filter">
<div class="trade-field"><label for="trade-search">{{ __('trade.search') }}</label><input id="trade-search" name="search" value="{{ request('search') }}" maxlength="150" type="search"></div>
@foreach (['type' => $catalog::TYPES, 'category' => $catalog::CATEGORIES] as $field => $values)<div class="trade-field"><label for="filter-{{ $field }}">{{ __('trade.fields.'.($field === 'type' ? 'opportunity_type' : 'category')) }}</label><select id="filter-{{ $field }}" name="{{ $field }}"><option value="">{{ __('trade.all') }}</option>@foreach ($values as $value)<option value="{{ $value }}" @selected(request($field) === $value)>{{ __('trade.'.($field === 'type' ? 'types' : 'categories').'.'.$value) }}</option>@endforeach</select></div>@endforeach
<div class="trade-field"><label for="filter-country">{{ __('trade.fields.country') }}</label><input id="filter-country" name="country" maxlength="100" value="{{ request('country') }}"></div>
<div class="trade-actions"><button class="trade-button">{{ __('trade.filter') }}</button><a class="trade-link" href="{{ route('opportunities.index') }}">{{ __('trade.reset') }}</a></div>
</form>
<p class="trade-count"><bdi>{{ $opportunities->total() }}</bdi> {{ __('trade.results') }}</p>
<div class="trade-listings">@forelse ($opportunities as $opportunity)<article class="trade-listing">
@php $cover = $opportunity->media->firstWhere('is_primary', true) ?? $opportunity->media->first(); @endphp
@if ($cover && $cover->type === 'image')<a href="{{ route('opportunities.show', $opportunity) }}" tabindex="-1" aria-hidden="true"><img class="trade-cover" src="{{ asset('storage/'.$cover->path) }}" alt="" loading="lazy" width="640" height="360"></a>@else<div class="trade-cover trade-placeholder">HAG <span>BROKERS</span></div>@endif
<div class="trade-listing-body"><div class="trade-badges"><span>{{ \Illuminate\Support\Facades\Lang::has('trade.types.'.$opportunity->opportunity_type) ? __('trade.types.'.$opportunity->opportunity_type) : $opportunity->opportunity_type }}</span>@if ($opportunity->category)<span>{{ \Illuminate\Support\Facades\Lang::has('trade.categories.'.$opportunity->category) ? __('trade.categories.'.$opportunity->category) : $opportunity->category }}</span>@endif</div>
<h2><a href="{{ route('opportunities.show', $opportunity) }}">{{ $opportunity->title }}</a></h2><p>{{ \Illuminate\Support\Str::limit($opportunity->description, 170) }}</p><p>{{ $opportunity->city }}{{ $opportunity->city && $opportunity->country ? ' · ' : '' }}{{ $opportunity->country }}</p>
@if ($opportunity->commercial_details['product_name'] ?? null)<p>{{ $opportunity->commercial_details['product_name'] }}</p>@endif
<p class="trade-small">{{ $opportunity->customer?->company_name }}</p><a class="trade-link" href="{{ route('opportunities.show', $opportunity) }}">{{ __('trade.view') }} ↗</a></div></article>@empty<p class="trade-panel">{{ __('trade.empty') }}</p>@endforelse</div>
<div class="trade-pagination">{{ $opportunities->links() }}</div>
</div></div></x-app-layout>
