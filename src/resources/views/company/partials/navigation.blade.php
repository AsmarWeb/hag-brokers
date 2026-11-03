@php
    $cwMember=\App\Models\CompanyMember::where('user_id',auth()->id())->first();
    $cwLeader=$cwMember && in_array($cwMember->role,['owner','manager'],true);
    $cwOwner=$cwMember?->role==='owner';
    $cwLinks=[['company.marketplace','overview',true],['company.workspace','workspace',true],['company.trade-offers.index','offers',$cwLeader],['company.profile.edit','profile',$cwLeader],['company.billing','billing',$cwOwner],['company.mail.edit','email',$cwLeader]];
@endphp
<nav class="cw-navigation" aria-label="{{ __('company_ui.navigation') }}"><a class="cw-brand-mark" href="{{ route('company.marketplace') }}" aria-label="HAG Brokers"><span aria-hidden="true">H</span><strong>HAG<span> BUSINESS</span></strong></a><div class="cw-nav-links">@foreach($cwLinks as [$route,$key,$allowed])@if($allowed)<a href="{{ route($route) }}" @class(['is-active'=>request()->routeIs($route) || ($key==='offers' && request()->routeIs('company.trade-offers.*')) || ($key==='profile' && request()->routeIs('company.profile.*'))]) @if(request()->routeIs($route)) aria-current="page" @endif>{{ __('company_ui.nav.'.$key) }}</a>@endif @endforeach</div><a class="cw-browse-link" href="{{ route('opportunities.index') }}">{{ __('company_ui.browse') }} ↗</a></nav>
