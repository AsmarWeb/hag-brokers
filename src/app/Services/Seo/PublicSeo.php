<?php

namespace App\Services\Seo;

use App\Models\Opportunity;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Str;

final class PublicSeo
{
    public function base(): string
    {
        $base = rtrim((string) (config('seo.canonical_url') ?: config('app.url')), '/');
        $parts = parse_url($base);
        if (! is_array($parts) || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true) || empty($parts['host']) || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new \RuntimeException('Set a valid absolute APP_URL or SEO_CANONICAL_URL.');
        }

        return $base;
    }

    public function url(string $route, array $parameters = [], ?string $language = null): string
    {
        if ($language !== null) {
            $parameters['lang'] = $language;
        }

        // Generate only the named route path; do not inherit request Host or
        // the deployment subdirectory twice when composing the canonical base.
        $generator = new UrlGenerator(app('router')->getRoutes(), request());
        $generator->forceRootUrl('https://seo.invalid');

        return $this->base().$generator->route($route, $parameters, false);
    }

    public function plain(?string $text, int $limit = 160): string
    {
        return Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($text ?? '')) ?? ''), $limit, '…');
    }

    public function languageReturn(Request $request, string $locale): string
    {
        $previous = url()->previous();
        // Keep existing language switch behavior, while fixing language-qualified
        // public pages. Never accept a cross-origin return URL.
        if (parse_url($previous, PHP_URL_HOST) !== $request->getHost()
            || (parse_url($previous, PHP_URL_PORT) ?? ($request->isSecure() ? 443 : 80)) !== $request->getPort()) {
            return $this->url('home', [], $locale);
        }
        $path = parse_url($previous, PHP_URL_PATH) ?: '/';
        parse_str(parse_url($previous, PHP_URL_QUERY) ?? '', $query);
        if (array_key_exists('lang', $query)) {
            $query['lang'] = $locale;
        }

        $basePath = rtrim(parse_url($this->base(), PHP_URL_PATH) ?? '', '/');
        if ($basePath !== '' && ($path === $basePath || str_starts_with($path, $basePath.'/'))) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }

        return $this->base().$path.($query ? '?'.http_build_query($query) : '');
    }

    public function data(Request $request): array
    {
        $route = $request->route()?->getName();
        $language = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $public = in_array($route, ['home', 'opportunities.index', 'opportunities.show'], true);
        $filtered = $route === 'opportunities.index' && collect(['search', 'type', 'category', 'country'])->contains(fn ($key) => $request->filled($key));
        $parameters = [];
        $opportunity = $request->route('opportunity');
        if ($route === 'opportunities.show' && $opportunity instanceof Opportunity) {
            $parameters['opportunity'] = $opportunity;
        }
        if ($route === 'opportunities.index' && ! $filtered && $request->integer('page', 1) > 1) {
            $parameters['page'] = $request->integer('page');
        }
        $canonical = $public ? $this->url($route, $parameters, $language) : $this->base().$request->getPathInfo();
        $description = $language === 'ar' ? 'HAG Brokers سوق فرص تجارية في الإمارات لربط الشركات بعروض البيع والتوريد وطلبات الشراء والخدمات والشراكات.' : 'HAG Brokers connects businesses through commercial offers, sourcing requests, services and partnerships in the UAE and beyond.';
        $title = $language === 'ar' ? 'HAG Brokers | فرص تجارية وتوريد وخدمات في الإمارات' : 'HAG Brokers | Business opportunities, sourcing and services in the UAE';
        if ($route === 'opportunities.index') {
            $title = ($language === 'ar' ? 'سوق الفرص التجارية' : 'Business opportunities marketplace').' | HAG Brokers';
        }
        $image = trim((string) config('seo.social_image'));
        if ($image !== '' && ! str_starts_with($image, 'https://') && ! str_starts_with($image, 'http://')) {
            $image = $this->base().'/'.ltrim($image, '/');
        }
        $schemas = [];
        $organization = ['@type' => 'Organization', '@id' => $this->base().'/#organization', 'name' => 'HAG Brokers', 'url' => $this->base().'/'];
        if ($route === 'home') {
            $schemas[] = ['@context' => 'https://schema.org'] + $organization;
            $schemas[] = ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => 'HAG Brokers', 'url' => $this->base().'/', 'inLanguage' => ['ar', 'en']];
        }
        if ($route === 'opportunities.show' && $opportunity instanceof Opportunity) {
            $title = $this->plain($opportunity->title, 150).' | HAG Brokers';
            $description = $this->plain($opportunity->description) ?: $description;
            $opportunity->loadMissing('media');
            $cover = $opportunity->media->firstWhere('type', 'image');
            if ($cover) {
                $image = $this->base().'/storage/'.ltrim($cover->path, '/');
            }
            $page = ['@context' => 'https://schema.org', '@type' => 'WebPage', '@id' => $canonical.'#webpage', 'url' => $canonical, 'name' => $this->plain($opportunity->title, 255), 'description' => $description, 'inLanguage' => $language, 'publisher' => ['@id' => $organization['@id']]];
            if ($opportunity->published_at) {
                $page['datePublished'] = $opportunity->published_at->toAtomString();
            }
            if ($opportunity->seo_updated_at) {
                $page['dateModified'] = $opportunity->seo_updated_at->toAtomString();
            }
            if ($image) {
                $page['primaryImageOfPage'] = $image;
            }
            $schemas[] = $page;
            $schemas[] = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => $language === 'ar' ? 'الرئيسية' : 'Home', 'item' => $this->url('home', [], $language)],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $language === 'ar' ? 'الفرص التجارية' : 'Opportunities', 'item' => $this->url('opportunities.index', [], $language)],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $this->plain($opportunity->title, 255), 'item' => $canonical],
            ]];
        }
        $alternates = [];
        if ($public && ! $filtered) {
            foreach (['ar', 'en'] as $locale) {
                $alternates[$locale] = $this->url($route, $parameters, $locale);
            }
        }

        if ($alternates) {
            $alternates['x-default'] = $alternates[config('app.locale') === 'ar' ? 'ar' : 'en'];
        }

        return compact('title', 'description', 'canonical', 'alternates', 'schemas', 'image', 'language') + [
            'public' => $public,
            'indexable' => $public && ! $filtered && (bool) config('seo.indexing_enabled'),
        ];
    }
}
