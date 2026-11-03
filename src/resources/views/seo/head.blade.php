@php $seo = app(\App\Services\Seo\PublicSeo::class)->data(request()); @endphp
<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
<meta name="author" content="HAG Brokers">
<meta name="robots" content="{{ $seo['indexable'] ? 'index, follow, max-image-preview:large' : 'noindex, nofollow' }}">
@if ($seo['public'])
<link rel="canonical" href="{{ $seo['canonical'] }}">
@foreach ($seo['alternates'] as $locale => $url)<link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}">@endforeach
<meta property="og:type" content="website">
<meta property="og:site_name" content="HAG Brokers">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
<meta property="og:locale" content="{{ $seo['language'] === 'ar' ? 'ar_AE' : 'en_AE' }}">
@if ($seo['image'])<meta property="og:image" content="{{ $seo['image'] }}"><meta name="twitter:image" content="{{ $seo['image'] }}">@endif
<meta name="twitter:card" content="{{ $seo['image'] ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
@endif
@if (config('seo.google_verification'))<meta name="google-site-verification" content="{{ config('seo.google_verification') }}">@endif
@if (config('seo.bing_verification'))<meta name="msvalidate.01" content="{{ config('seo.bing_verification') }}">@endif
@foreach ($seo['schemas'] as $schema)
<script type="application/ld+json">{!! json_encode($schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) !!}</script>
@endforeach
