<x-app-layout>
@section('title', __('free_launch.plans'))
<div class="cw-page trade-page cw-billing cw-free-launch">
@include('company.partials.navigation')
<header class="cw-hero"><div><p class="cw-eyebrow">HAG · BUSINESS NETWORK</p><h1>{{ __('free_launch.heading') }}</h1><p>{{ __('free_launch.intro') }}</p></div><span class="cw-status"><span aria-hidden="true"></span>{{ __('free_launch.available') }}</span></header>
@include('admin.platform.messages')
<section class="cw-current-membership"><div><p class="cw-eyebrow">{{ __('company_ui.current_membership') }}</p><h2>{{ $free->{'name_'.app()->getLocale()} }}</h2><p>{{ __('free_launch.current_help') }}</p></div><a class="cw-button cw-button-gold" href="{{ route('company.marketplace') }}">{{ __('free_launch.start') }} ↗</a></section>
<p class="cw-notice">{{ __('free_launch.promise') }}</p>
<header class="cw-section-heading cw-plans-heading"><div><p class="cw-eyebrow">{{ __('free_launch.plans') }}</p><h2>{{ __('free_launch.plan_heading') }}</h2></div></header>
<div class="cw-pricing-grid cw-free-pricing">
<article class="cw-price-card cw-free-card"><span class="cw-free-card-badge">{{ __('free_launch.available') }}</span><header><span class="cw-plan-icon" aria-hidden="true">◈</span><h2>{{ $free->{'name_'.app()->getLocale()} }}</h2><p>{{ __('free_launch.free_intro') }}</p></header><p class="cw-free-price">{{ __('free_launch.free') }}</p><p class="cw-help">{{ __('free_launch.no_card') }}</p><ul>@foreach(['seats','active_opportunities','monthly_applications'] as $key)<li><span aria-hidden="true">✓</span><span>{{ __('platform.'.$key) }}</span><strong><bdi>{{ $effectivePlan[$key] }}</bdi></strong></li>@endforeach</ul><span class="cw-button cw-button-quiet cw-current-label">{{ __('free_launch.current') }} ✓</span></article>
@foreach($upcoming as $plan)<article class="cw-price-card cw-upcoming-card"><span class="cw-free-card-badge">{{ __('free_launch.soon') }}</span><header><span class="cw-plan-icon" aria-hidden="true">{{ $plan->code==='professional'?'◇':'◈' }}</span><h2>{{ $plan->{'name_'.app()->getLocale()} }}</h2><p>{{ $plan->code==='professional' ? __('company_ui.professional_intro') : __('company_ui.business_intro') }}</p></header><p class="cw-future-price">{{ __('free_launch.pricing_later') }}</p><p class="cw-help">{{ __('free_launch.upcoming_help') }}</p><ul>@foreach(['seats','active_opportunities','monthly_applications'] as $key)<li><span aria-hidden="true">✓</span><span>{{ __('platform.'.$key) }}</span><strong><bdi>{{ $plan->$key }}</bdi></strong></li>@endforeach</ul><span class="cw-coming-label">{{ __('free_launch.soon') }}</span></article>@endforeach
</div><p class="cw-help cw-plan-policy">{{ __('free_launch.limits_help') }}</p>
</div></x-app-layout>
