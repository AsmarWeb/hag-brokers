<x-admin-layout>
<x-slot name="header">{{ __('public_experience.admin_title') }}</x-slot>
@include('public-experience.styles')
<main class="hag-experience-form" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<h1>{{ __('public_experience.admin_title') }}</h1>
@if(session('success'))<p role="status">{{ session('success') }}</p>@endif
@if($errors->any())<ul class="errors" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
<form method="POST" action="{{ route('admin.public-experience.update') }}" enctype="multipart/form-data">@csrf @method('PUT')
<input type="hidden" name="version" value="{{ $settings['version'] }}">
<section>
<input type="hidden" name="download_enabled" value="0"><label><input type="checkbox" name="download_enabled" value="1" @checked(old('download_enabled',$settings['download_enabled']))> {{ __('public_experience.enable_download') }}</label>
<label for="android_apk">{{ __('public_experience.apk_file') }}</label><input id="android_apk" name="android_apk" type="file" accept=".apk,application/vnd.android.package-archive"><p>{{ __('public_experience.apk_help') }}</p>
<label for="android_url">{{ __('public_experience.download_url') }}</label><input id="android_url" name="android_url" type="url" maxlength="500" dir="ltr" value="{{ old('android_url',$settings['android_url']) }}"><p>{{ __('public_experience.url_help') }}</p>
<label for="android_version">{{ __('public_experience.version') }}</label><input id="android_version" name="android_version" maxlength="40" dir="ltr" value="{{ old('android_version',$settings['android_version']) }}">
<label for="released_on">{{ __('public_experience.released') }}</label><input id="released_on" name="released_on" type="date" value="{{ old('released_on',$settings['released_on']) }}">
<input type="hidden" name="map_enabled" value="0"><label><input type="checkbox" name="map_enabled" value="1" @checked(old('map_enabled',$settings['map_enabled']))> {{ __('public_experience.enable_map') }}</label>
<button type="submit">{{ __('public_experience.save') }}</button>
</section></form>
<section><h2>{{ __('public_experience.marine_list') }}</h2><p>{{ __('public_experience.approximate') }}</p>
@forelse($opportunities as $opportunity)<p><a href="{{ route('admin.public-experience.location',$opportunity) }}">#{{ $opportunity->id }} · {{ $opportunity->title }} — {{ __('public_experience.manage_location') }}</a></p>@empty<p>{{ __('public_experience.no_marine') }}</p>@endforelse
{{ $opportunities->links() }}
</section>
</main>
</x-admin-layout>
