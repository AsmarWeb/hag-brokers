<x-market-layout>
@include('public-experience.styles')
<main class="hag-release" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<div class="hag-release-card">
<span class="hag-release-wordmark">HAG</span><p>{{ __('public_experience.direct') }}</p>
<h1>{{ __('public_experience.download_title') }}</h1><p>{{ __('public_experience.download_body') }}</p>
<p>{{ __('public_experience.version') }} {{ $release['android_version'] }} · {{ __('public_experience.released') }} {{ $release['released_on'] }}</p>
<a class="hag-release-action" href="{{ $release['android_url'] }}" rel="noopener noreferrer">{{ __('public_experience.download_android') }} ↓</a>
</div>
<section class="hag-release-guide"><h2>{{ __('public_experience.install_title') }}</h2><ol>
@foreach(['step1','step2','step3'] as $step)<li>{{ __('public_experience.'.$step) }}</li>@endforeach
</ol><p>{{ __('public_experience.ios') }}</p></section>
<nav class="hag-release-links"><a href="{{ route('home') }}">{{ __('public_experience.home') }}</a><a href="{{ route('legal.show', 'privacy') }}">{{ __('public_experience.privacy') }}</a><a href="{{ route('legal.show', 'terms') }}">{{ __('public_experience.terms') }}</a></nav>
</main>
</x-market-layout>
