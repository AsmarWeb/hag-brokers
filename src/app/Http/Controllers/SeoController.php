<?php

namespace App\Http\Controllers;

use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Seo\PublicSeo;

class SeoController extends Controller
{
    private function xml(string $root, array $entries)
    {
        $xml = new \XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement($root);
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        foreach ($entries as $entry) {
            $xml->startElement($root === 'sitemapindex' ? 'sitemap' : 'url');
            $xml->writeElement('loc', $entry['loc']);
            if ($entry['lastmod'] ?? null) {
                $xml->writeElement('lastmod', $entry['lastmod']);
            }
            $xml->endElement();
        }
        $xml->endElement();
        $xml->endDocument();

        return response($xml->outputMemory(), 200, ['Content-Type' => 'application/xml; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }

    private function pageSize(): int
    {
        return max(1, min(1000, (int) config('seo.sitemap_page_size', 1000)));
    }

    public function index(PublicSeo $seo, CompanyMarketplaceService $market)
    {
        $pages = (int) ceil($market->publicQuery()->whereNotNull('slug')->count() / $this->pageSize());
        abort_if($pages > 49999, 503);
        $entries = [['loc' => $seo->url('seo.sitemap.pages')]];
        for ($i = 1; $i <= $pages; $i++) {
            $entries[] = ['loc' => $seo->url('seo.sitemap.opportunities', ['page' => $i])];
        }

        return $this->xml('sitemapindex', $entries);
    }

    public function pages(PublicSeo $seo)
    {
        $entries = [];
        foreach (['home', 'opportunities.index'] as $route) {
            foreach (['ar', 'en'] as $locale) {
                $entries[] = ['loc' => $seo->url($route, [], $locale)];
            }
        }

        return $this->xml('urlset', $entries);
    }

    public function opportunities(string $page, PublicSeo $seo, CompanyMarketplaceService $market)
    {
        $items = $market->publicQuery()->whereNotNull('slug')->orderBy('id')->skip(((int) $page - 1) * $this->pageSize())->take($this->pageSize())->get(['id', 'slug', 'seo_updated_at']);
        abort_if($items->isEmpty(), 404);
        $entries = [];
        foreach ($items as $item) {
            foreach (['ar', 'en'] as $locale) {
                $entries[] = ['loc' => $seo->url('opportunities.show', ['opportunity' => $item], $locale), 'lastmod' => $item->seo_updated_at?->toAtomString()];
            }
        }

        return $this->xml('urlset', $entries);
    }

    public function robots(PublicSeo $seo)
    {
        $text = "User-agent: *\n";
        if (! config('seo.indexing_enabled')) {
            $text .= "Disallow: /\n";
        } else {
            $text .= "Allow: /\n";
            foreach (['/admin', '/company', '/api', '/dashboard', '/profile', '/notifications', '/quotations', '/shipments', '/login', '/register', '/forgot-password', '/reset-password', '/verify-email', '/email', '/language/', '/opportunities/create', '/opportunities/*/interest'] as $path) {
                $text .= 'Disallow: '.$path."\n";
            }
        }
        $text .= 'Sitemap: '.$seo->url('seo.sitemap.index')."\n";

        return response($text, 200, ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }
}
