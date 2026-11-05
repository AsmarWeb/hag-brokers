<x-dynamic-component :component="$admin ? 'admin-layout' : 'app-layout'">
@include('public-experience.styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<main class="hag-experience-form" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<a href="{{ $admin ? route('admin.public-experience.index') : route('company.opportunities.edit',$opportunity) }}">{{ __('public_experience.back') }}</a>
<h1>{{ __('public_experience.manage_location') }}</h1><h2>{{ $opportunity->title }}</h2>
@if(session('success'))<p role="status">{{ session('success') }}</p>@endif
@if($errors->any())<ul class="errors" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
<p>{{ __('public_experience.location_help') }}</p>
<form method="POST" action="{{ $admin ? route('admin.public-experience.location.update',$opportunity) : route('company.map-location.update',$opportunity) }}">@csrf @method('PUT')
<input type="hidden" name="version" value="{{ $location['version'] }}">
<section>
<label for="label">{{ __('public_experience.location_label') }}</label><input id="label" name="label" maxlength="120" value="{{ old('label',$location['label']) }}">
<label for="latitude">{{ __('public_experience.latitude') }}</label><input id="latitude" name="latitude" type="number" step="any" min="-90" max="90" dir="ltr" value="{{ old('latitude',$location['latitude']) }}">
<label for="longitude">{{ __('public_experience.longitude') }}</label><input id="longitude" name="longitude" type="number" step="any" min="-180" max="180" dir="ltr" value="{{ old('longitude',$location['longitude']) }}">
<div id="location-picker" aria-label="{{ __('public_experience.manage_location') }}"></div>
<input type="hidden" name="is_public" value="0"><label><input type="checkbox" name="is_public" value="1" @checked(old('is_public',$location['is_public']))> {{ __('public_experience.publish_location') }}</label>
<label><input type="checkbox" name="confirm_public" value="1"> {{ __('public_experience.consent') }}</label>
<p>{{ __('public_experience.hide_help') }}</p>
<button type="submit">{{ __('public_experience.save') }}</button>
</section></form>
</main>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function(){
if(typeof L==='undefined')return;
const lat=document.getElementById('latitude'),lng=document.getElementById('longitude');
const map=L.map('location-picker',{scrollWheelZoom:false}).setView([25.2,55.3],7);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:16,attribution:'© OpenStreetMap contributors'}).addTo(map);
let marker;
function position(recenter){if(lat.value===''||lng.value==='')return;const a=Number(lat.value),b=Number(lng.value);if(!Number.isFinite(a)||!Number.isFinite(b)||Math.abs(a)>90||Math.abs(b)>180)return;if(marker)marker.setLatLng([a,b]);else marker=L.marker([a,b]).addTo(map);if(recenter)map.setView([a,b],9);}
position(true);map.on('click',function(e){lat.value=e.latlng.lat.toFixed(2);lng.value=e.latlng.wrap().lng.toFixed(2);position(false);});lat.addEventListener('change',()=>position(true));lng.addEventListener('change',()=>position(true));
})();
</script>
</x-dynamic-component>
