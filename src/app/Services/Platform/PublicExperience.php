<?php

namespace App\Services\Platform;

use App\Models\Opportunity;
use App\Models\Port;
use App\Services\Seo\PublicSeo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class PublicExperience
{
    public function settings(): array
    {
        $row = DB::table('public_experience_settings')->where('id', 1)->first();

        return $row ? (array) $row : ['version' => 0, 'download_enabled' => false, 'map_enabled' => true,
            'android_url' => null, 'android_version' => null, 'released_on' => null];
    }

    // No network requests or arbitrary redirects: link only to this site's downloads
    // directory or the existing HAG Expo project. Files are managed by administrators.
    public function safeDownloadUrl(?string $url): bool
    {
        if (! $url || strlen($url) > 500 || preg_match('/[\\\\\s\x00-\x1f]/', $url)) {
            return false;
        }
        $u = parse_url($url);
        $base = parse_url(app(PublicSeo::class)->base());
        if (! is_array($u) || ($u['scheme'] ?? '') !== 'https' || empty($u['host'])
            || isset($u['user']) || isset($u['pass']) || isset($u['query']) || isset($u['fragment']) || isset($u['port'])) {
            return false;
        }
        if ($u['host'] === 'expo.dev') {
            return (bool) preg_match('~^/accounts/asmarweb/projects/hag-brokers-mobile/builds/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$~D', $u['path'] ?? '');
        }
        $prefix = $base['path'] ?? '';

        return strtolower($u['host']) === strtolower($base['host'] ?? '')
            && (bool) preg_match('~^'.preg_quote(rtrim($prefix, '/'), '~').'/downloads/[A-Za-z0-9_-][A-Za-z0-9_.-]*\.apk$~D', $u['path'] ?? '');
    }

    public function download(): ?array
    {
        $s = $this->settings();

        return $s['download_enabled'] && $this->safeDownloadUrl($s['android_url']) ? $s : null;
    }

    public function localApkPath(array $release): ?string
    {
        $url = $release['android_url'] ?? null;

        if (! $this->safeDownloadUrl($url)) {
            return null;
        }

        $u = parse_url($url);
        $base = parse_url(app(PublicSeo::class)->base());
        $prefix = rtrim($base['path'] ?? '', '/').'/downloads/';

        if (strtolower($u['host'] ?? '') !== strtolower($base['host'] ?? '')
            || ! str_starts_with($u['path'] ?? '', $prefix)) {
            return null;
        }

        $filename = substr($u['path'], strlen($prefix));

        if (! preg_match('/^[A-Za-z0-9_-][A-Za-z0-9_.-]*\\.apk$/D', $filename)) {
            return null;
        }

        $directory = realpath(public_path('downloads'));
        $file = realpath(public_path('downloads/'.$filename));

        if (! $directory || ! $file || ! is_file($file)
            || ! str_starts_with($file, $directory.DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $file;
    }

    public function marineQuery()
    {
        return Opportunity::query()->whereIn('opportunity_type', ['sell', 'vessel_brokerage', 'service_offer'])
            ->whereHas('vessel', fn ($q) => $q->whereIn('listing_type', ['sale', 'charter'])
                ->whereIn('vessel_category', ['commercial_vessel', 'boat', 'yacht']));
    }

    public function location(Opportunity $opportunity): array
    {
        $row = DB::table('opportunity_map_locations')->where('opportunity_id', $opportunity->id)->first();

        return $row ? (array) $row : ['version' => 0, 'latitude' => null, 'longitude' => null, 'label' => '', 'is_public' => false];
    }

    public function map(): array
    {
        $empty = ['kind' => 'listing_locations', 'live_tracking' => false, 'limit' => 300, 'truncated' => false, 'listings' => [], 'ports' => []];
        if (! $this->settings()['map_enabled']) {
            return $empty;
        }
        $query = app(CompanyMarketplaceService::class)->publicQuery()
            ->whereIn('opportunities.id', $this->marineQuery()->select('opportunities.id'))
            ->join('opportunity_map_locations as loc', 'loc.opportunity_id', '=', 'opportunities.id')
            ->where('loc.is_public', true)->whereNotNull('loc.confirmed_at')
            ->whereBetween('loc.latitude', [-90, 90])->whereBetween('loc.longitude', [-180, 180])
            ->select('opportunities.*', 'loc.latitude as map_lat', 'loc.longitude as map_lng', 'loc.label as map_label', 'loc.updated_at as map_updated')
            ->with(['vessel', 'media'])->latest('published_at')->orderByDesc('opportunities.id')->limit(301)->get();
        $items = $query->take(300)->map(function ($o) {
            $photo = $o->media->where('type', 'image')->sortBy('sort_order')->first();
            $image = $photo && str_starts_with($photo->path, 'opportunities/') && ! str_contains($photo->path, '..')
                ? Storage::disk('public')->url($photo->path) : null;

            return ['id' => $o->id, 'title' => $o->title, 'category' => $o->vessel->vessel_category,
                'listing_type' => $o->vessel->listing_type, 'latitude' => (float) $o->map_lat, 'longitude' => (float) $o->map_lng,
                'location_label' => $o->map_label, 'approximate' => true, 'location_updated_at' => $o->map_updated,
                'image' => $image, 'url' => app(PublicSeo::class)->url('opportunities.show', ['opportunity' => $o], app()->isLocale('ar') ? 'ar' : 'en')];
        })->values()->all();
        $ports = Port::query()->where('is_active', true)->whereBetween('latitude', [-90, 90])->whereBetween('longitude', [-180, 180])
            ->orderBy('id')->limit(300)->get(['id', 'name', 'latitude', 'longitude'])->map(fn ($port) => [
                'name' => $port->name, 'latitude' => (float) $port->latitude, 'longitude' => (float) $port->longitude,
            ])->all();

        return array_replace($empty, ['listings' => $items, 'ports' => $ports, 'truncated' => $query->count() > 300]);
    }
}
