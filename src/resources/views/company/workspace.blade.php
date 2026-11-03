<x-app-layout>
@php $cwRole=\App\Models\CompanyMember::where('user_id',auth()->id())->value('role'); $cwLeader=in_array($cwRole,['owner','manager'],true); $cwOwner=$cwRole==='owner'; @endphp
@section('title', __('company_ui.workspace_heading'))
<div class="cw-page trade-page">
@include('company.partials.navigation')
<header class="cw-hero"><div><p class="cw-eyebrow">HAG · COMPANY MANAGEMENT</p><h1>{{ $company->company_name }}</h1><p>{{ __('company_ui.workspace_intro') }}</p></div><span class="cw-status"><span aria-hidden="true"></span>{{ __('company_ui.company_workspace') }}</span></header>
<div class="cw-workspace-grid"><div>
@include('company.partials.plan-card')
<section class="trade-panel cw-team"><header class="cw-section-heading"><div><p class="cw-eyebrow">{{ __('company_ui.your_people') }}</p><h2>{{ __('platform.team') }}</h2></div><span class="cw-count">{{ $members->count() }}</span></header><div class="cw-member-list">@foreach($members as $member)<article><span class="cw-avatar" aria-hidden="true">{{ mb_substr($member['name'] ?: 'H',0,1) }}</span><div><h3>{{ $member['name'] ?: '—' }}</h3><p>{{ __('platform.role_'.$member['role']) }}</p></div><span class="cw-member-role">{{ __('platform.role_'.$member['role']) }}</span></article>@endforeach</div><p class="cw-help">{{ __('platform.portal_help') }}</p></section>
</div><aside class="cw-workspace-aside"><section class="cw-identity-card"><span class="cw-identity-monogram" aria-hidden="true">{{ mb_substr($company->company_name,0,1) }}</span><p class="cw-eyebrow">{{ __('company_ui.company_identity') }}</p><h2>{{ $company->company_name }}</h2><p>{{ __('company_ui.profile_intro') }}</p>@if($cwLeader)<a class="cw-button cw-button-gold" href="{{ route('company.profile.edit') }}">{{ __('company_ui.nav.profile') }} ↗</a>@endif</section>
<section class="trade-panel cw-quick-links"><h2>{{ __('company_ui.quick_actions') }}</h2><a href="{{ route('company.marketplace') }}">{{ __('company_ui.nav.overview') }} <span>↗</span></a>@if($cwOwner)<a href="{{ route('company.billing') }}">{{ __('company_ui.nav.billing') }} <span>↗</span></a>@endif @if($cwLeader)<a href="{{ route('company.mail.edit') }}">{{ __('company_ui.nav.email') }} <span>↗</span></a>@endif<a href="{{ route('opportunities.index') }}">{{ __('company_ui.browse') }} <span>↗</span></a></section></aside></div>
</div></x-app-layout>
