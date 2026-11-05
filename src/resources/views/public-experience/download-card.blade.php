@if($androidRelease)
@include('public-experience.styles')
<section class="hag-release" id="android-app" aria-labelledby="hag-download-heading">
<div class="hag-release-card">
<span class="hag-release-wordmark">HAG</span>
<p>{{ __('public_experience.direct') }}</p>
<h2 id="hag-download-heading">{{ __('public_experience.download_title') }}</h2>
<p>{{ __('public_experience.download_body') }}</p>
<a class="hag-release-action" href="{{ route('app.download', ['lang' => $isAr ? 'ar' : 'en']) }}">{{ __('public_experience.get_app') }} ↗</a>
</div>
</section>
@endif
