<x-app-layout><div class="mx-auto max-w-6xl space-y-6 px-4 py-8" dir="{{ app()->getLocale()==='ar'?'rtl':'ltr' }}">
@if (\App\Models\CompanyMember::where('user_id',auth()->id())->where('role','owner')->exists())<a class="hag-broker-action" href="{{ route('company.billing') }}">{{ __('billing.title') }}</a>@endif
<header><h1 class="text-2xl font-bold">{{ $company->company_name }}</h1><p class="mt-2">{{ __('platform.workspace') }}</p></header>
@include('company.plan-summary')
@if(\App\Models\CompanyMember::where('user_id',auth()->id())->whereIn('role',['owner','manager'])->exists())<section class="trade-panel company-profile-invite"><div><h2>{{ __('company_profile.invite') }}</h2><p>{{ __('company_profile.invite_help') }}</p></div><a class="trade-button" href="{{ route('company.profile.edit') }}">{{ __('company_profile.title') }} ↗</a></section>@endif

<section class="hag-broker-card"><h2 class="text-lg font-bold">{{ __('platform.team') }}</h2><ul class="mt-4 space-y-3">@foreach ($members as $member)<li>{{ $member['name'] }} · {{ __('platform.role_'.$member['role']) }}</li>@endforeach</ul><p class="hag-broker-muted mt-4">{{ __('platform.portal_help') }}</p></section>
<a href="{{ route('company.marketplace') }}" class="hag-broker-action">{{ __('market.title') }}</a>
<a href="{{ route('opportunities.index') }}" class="hag-broker-action">{{ __('platform.browse') }}</a>
</div></x-app-layout>
