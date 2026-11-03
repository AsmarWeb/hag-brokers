<?php

namespace App\Console\Commands;

use App\Services\Seo\PublicSeo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckPublicSeo extends Command
{
    protected $signature = 'seo:check';

    protected $description = 'Read-only checks for canonical URLs, indexing and opportunity slugs';

    public function handle(PublicSeo $seo): int
    {
        try {
            $base = $seo->base();
        } catch (\RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
        $this->line('Canonical base: '.$base);
        $this->line('Indexing: '.(config('seo.indexing_enabled') ? 'ENABLED' : 'DISABLED'));
        $errors = false;
        if (app()->isProduction() && config('seo.indexing_enabled') && ! str_starts_with($base, 'https://')) {
            $this->error('Production indexing requires your public HTTPS canonical URL.');
            $errors = true;
        }
        if (is_file(public_path('robots.txt'))) {
            $this->error('Static public/robots.txt masks the dynamic route.');
            $errors = true;
        }
        if (! Schema::hasColumn('opportunities', 'slug')) {
            $this->error('Run the additive SEO migration first.');

            return self::FAILURE;
        }
        $missing = DB::table('opportunities')->whereNull('slug')->orWhere('slug', '')->count();
        $this->line('Missing slugs: '.$missing);
        if ($missing || ! Schema::hasIndex('opportunities', 'opportunities_slug_unique')) {
            $errors = true;
        }
        $this->line('Sitemap: '.$seo->url('seo.sitemap.index'));
        $this->line('Robots: '.$seo->url('seo.robots'));
        $this->line('Search Console verification tag: '.(filled(config('seo.google_verification')) ? 'CONFIGURED' : 'NOT CONFIGURED (DNS verification is another option)'));
        $this->line('No remote submission or record updates performed.');

        return $errors ? self::FAILURE : self::SUCCESS;
    }
}
