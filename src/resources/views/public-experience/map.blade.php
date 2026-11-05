@if($mapEnabled)
@include('public-experience.styles')
<section id="network" class="scroll-mt-24 py-24" aria-labelledby="marine-map-heading">
<div class="mx-auto max-w-7xl px-5 lg:px-8">
<p class="text-sm text-cyan-300">HAG · MARITIME OPPORTUNITIES</p>
<h2 id="marine-map-heading" class="mt-4 text-3xl font-black">{{ __('public_experience.map_title') }}</h2>
<p class="mt-3 text-slate-400">{{ __('public_experience.map_intro') }}</p>
<p class="hag-map-status">{{ __('public_experience.approximate') }}</p>
<div class="hag-map-filter" role="group" aria-label="{{ __('public_experience.map_title') }}">
@foreach(['all','commercial_vessel','yacht','boat'] as $kind)
<button type="button" data-marine-filter="{{ $kind }}" aria-pressed="{{ $kind === 'all' ? 'true' : 'false' }}">{{ __('public_experience.'.$kind) }} <span>{{ $kind === 'all' ? count($maritimeMap['listings']) : count(array_filter($maritimeMap['listings'], fn($item) => $item['category'] === $kind)) }}</span></button>
@endforeach
<label><input type="checkbox" id="marine-ports" checked> {{ __('public_experience.ports') }}</label>
</div>
<div id="gulf-map" style="height:480px;min-height:360px;border-radius:24px;overflow:hidden" aria-label="{{ __('public_experience.map_title') }}"></div>
<p id="marine-map-status" class="hag-map-status" role="status" aria-live="polite">{{ count($maritimeMap['listings']) ? __('public_experience.approximate') : __('public_experience.empty') }}</p>
@if($maritimeMap['truncated'])<p>{{ __('public_experience.limited') }}</p>@endif
<a class="trade-link" href="{{ route('opportunities.index',['category'=>'marine','lang'=>$isAr ? 'ar':'en']) }}">{{ __('public_experience.browse') }} ↗</a>
<div class="hag-map-list">
@foreach($maritimeMap['listings'] as $item)
<article class="hag-map-item" data-marine-kind="{{ $item['category'] }}">
@if($item['image'])<img src="{{ $item['image'] }}" loading="lazy" alt="">@endif
<h3>{{ $item['title'] }}</h3><p>{{ __('public_experience.'.$item['category']) }} · {{ __('public_experience.'.$item['listing_type']) }}</p>
<p>{{ $item['location_label'] }}</p><a href="{{ $item['url'] }}">{{ __('public_experience.view') }}</a>
</article>
@endforeach
</div>
<noscript><p>{{ __('public_experience.map_unavailable') }}</p></noscript>
</div>
</section>
@endif
