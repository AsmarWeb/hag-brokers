<?php

namespace Tests\Feature;

use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\Platform\CompanyWorkspaceService;
use App\Services\Seo\PublicSeo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PublicSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['seo.indexing_enabled' => true, 'seo.canonical_url' => 'https://market.example.test', 'seo.sitemap_page_size' => 2]);
    }

    private function listing(array $extra = []): Opportunity
    {
        $this->artisan('platform:install-plans');
        $customer = Customer::create(['company_name' => 'SEO Company', 'is_active' => true]);
        $owner = User::factory()->create(['customer_id' => $customer->id]);
        $workspace = app(CompanyWorkspaceService::class)->activateNewRegistration($customer, $owner);

        return Opportunity::create(array_replace(['title' => 'Office chairs in Dubai', 'description' => 'Supply of office chairs for businesses in Dubai with adjustable armrests.', 'opportunity_type' => 'sell', 'category' => 'consumer_goods', 'customer_id' => $customer->id, 'company_workspace_id' => $workspace->id, 'submitted_by' => $owner->id, 'visibility' => 'public', 'verification_status' => 'auto_eligible', 'marketplace_state' => 'published', 'published_at' => now()->subMinute(), 'expires_at' => now()->addMonth(), 'currency' => 'AED'], $extra));
    }

    private function xmlUrls(string $path): array
    {
        $response = $this->get($path)->assertOk();
        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);

        return array_map(fn ($node) => (string) $node, $xml->xpath('//*[local-name()="loc"]'));
    }

    public function test_new_public_links_use_slug_while_admin_and_company_routes_keep_ids(): void
    {
        $listing = $this->listing();
        $this->assertStringStartsWith('office-chairs-in-dubai-', $listing->slug);
        $this->assertStringContainsString('/opportunities/'.$listing->slug, route('opportunities.show', $listing));
        $this->assertStringContainsString('/admin/opportunities/'.$listing->id, route('admin.opportunities.show', $listing));
        $this->assertStringContainsString('/company/opportunities/'.$listing->id.'/edit', route('company.opportunities.edit', $listing));
        $this->get(route('opportunities.show', $listing))->assertOk();
    }

    public function test_numeric_links_redirect_permanently_and_preserve_only_supported_language(): void
    {
        $listing = $this->listing();
        $this->get('/opportunities/'.$listing->id)->assertStatus(301)->assertRedirect('https://market.example.test/opportunities/'.$listing->slug);
        $this->get('/opportunities/'.$listing->id.'?lang=ar&utm_source=old')->assertStatus(301)->assertRedirect('https://market.example.test/opportunities/'.$listing->slug.'?lang=ar');
    }

    public function test_private_closed_expired_and_suspended_links_do_not_redirect_or_leak_slugs(): void
    {
        foreach ([['visibility' => 'private'], ['marketplace_state' => 'closed'], ['expires_at' => now()->subSecond()], ['verification_status' => 'pending'], ['published_at' => now()->addDay()]] as $extra) {
            $listing = $this->listing($extra);
            $this->get('/opportunities/'.$listing->id)->assertNotFound();
            $this->get('/opportunities/'.$listing->slug)->assertNotFound();
        }
        $listing = $this->listing();
        CompanyWorkspace::findOrFail($listing->company_workspace_id)->update(['status' => 'suspended']);
        $this->get('/opportunities/'.$listing->id)->assertNotFound();
    }

    public function test_arabic_empty_numeric_reserved_and_duplicate_titles_get_safe_unique_slugs(): void
    {
        $slugs = [];
        foreach (['توريد أثاث مكتبي إلى دبي', '', '123', 'create', 'Office chairs in Dubai', 'Office chairs in Dubai', str_repeat('م', 300)] as $title) {
            $listing = $this->listing(['title' => $title]);
            $slugs[] = $listing->slug;
            $this->assertLessThanOrEqual(190, mb_strlen($listing->slug));
            $this->assertMatchesRegularExpression('/^[\p{L}\p{N}]+(?:-[\p{L}\p{N}]+)*$/u', $listing->slug);
            $this->get(route('opportunities.show', $listing))->assertOk();
        }
        $this->assertCount(count($slugs), array_unique($slugs));
        $this->assertStringStartsWith('توريد-أثاث-مكتبي-إلى-دبي-', $slugs[0]);
    }

    public function test_title_changes_keep_the_original_public_slug(): void
    {
        $listing = $this->listing();
        $slug = $listing->slug;
        $listing->update(['title' => 'Updated genuine product title']);
        $this->assertSame($slug, $listing->fresh()->slug);
        $this->get('/opportunities/'.$slug)->assertOk()->assertSee('Updated genuine product title');
    }

    public function test_slug_cannot_be_overwritten_through_model_update(): void
    {
        $listing = $this->listing();
        $this->expectException(ValidationException::class);
        $listing->forceFill(['slug' => 'new-identifier'])->save();
    }

    public function test_unique_slug_is_enforced_in_database(): void
    {
        $first = $this->listing();
        $second = $this->listing();
        $this->expectException(QueryException::class);
        DB::table('opportunities')->where('id', $second->id)->update(['slug' => $first->slug]);
    }

    public function test_idempotent_backfill_preserves_existing_data_and_does_not_change_timestamps(): void
    {
        $listing = $this->listing(['notes' => 'PRIVATE-BACKFILL-NOTE']);
        DB::table('opportunities')->where('id', $listing->id)->update(['slug' => null, 'seo_updated_at' => null]);
        $before = DB::table('opportunities')->find($listing->id);
        $migration = require database_path('migrations/2026_11_03_010000_add_opportunity_seo_fields.php');
        $migration->up();
        $after = DB::table('opportunities')->find($listing->id);
        $this->assertNotEmpty($after->slug);
        $this->assertNull($after->seo_updated_at);
        unset($before->slug,$after->slug);
        $this->assertEquals($before, $after);
        $slug = $listing->fresh()->slug;
        $migration->up();
        $this->assertSame($slug, $listing->fresh()->slug);
    }

    public function test_canonical_and_hreflang_are_absolute_and_independent_of_session_and_host(): void
    {
        $listing = $this->listing();
        $response = $this->withSession(['locale' => 'ar'])->get('http://attacker.example/opportunities/'.$listing->slug.'?lang=en');
        $response->assertOk()->assertSee('<html lang="en"', false)->assertSee('href="https://market.example.test/opportunities/'.$listing->slug.'?lang=en"', false)->assertSee('hreflang="ar"', false)->assertSee('hreflang="en"', false);
        preg_match('/<link rel="canonical"[^>]+>/', $response->getContent(), $canonical);
        $this->assertStringNotContainsString('attacker.example', $canonical[0]);
    }

    public function test_localized_pages_are_accessible_without_cookies_and_have_self_canonicals(): void
    {
        foreach (['ar', 'en'] as $language) {
            $this->withSession(['locale' => $language === 'ar' ? 'en' : 'ar'])->get('/?lang='.$language)->assertOk()->assertSee('<html lang="'.$language.'"', false)->assertSee('rel="canonical" href="https://market.example.test/?lang='.$language.'"', false);
        }
    }

    public function test_language_switch_updates_explicit_public_language_and_rejects_foreign_return(): void
    {
        $this->from(url('/opportunities').'?lang=en')->get('/language/ar')->assertRedirect('https://market.example.test/opportunities?lang=ar');
        $this->from('https://evil.example/path')->get('/language/en')->assertRedirect('https://market.example.test/?lang=en');
    }

    public function test_private_pages_search_and_staging_are_noindex(): void
    {
        $this->get('/login')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/opportunities?search=chairs&lang=en')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        config(['seo.indexing_enabled' => false]);
        $this->get('/')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /', false);
    }

    public function test_schema_is_valid_json_escaped_and_contains_no_private_financial_data(): void
    {
        $listing = $this->listing(['title' => '</script><script>SEO-XSS</script>', 'notes' => 'PRIVATE-SEO-NOTE', 'required_capital' => '123456.78']);
        $html = $this->get(route('opportunities.show', $listing, ['lang' => 'en']))->assertOk()->getContent();
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
        $this->assertCount(2, $matches[1]);
        foreach ($matches[1] as $json) {
            $this->assertIsArray(json_decode($json, true, 32, JSON_THROW_ON_ERROR));
            $this->assertStringNotContainsString('PRIVATE-SEO-NOTE', $json);
            $this->assertStringNotContainsString('123456.78', $json);
        }
        $this->assertStringNotContainsString('<script>SEO-XSS</script>', $html);
    }

    public function test_private_note_changes_do_not_falsify_public_lastmod(): void
    {
        $listing = $this->listing();
        $before = $listing->seo_updated_at->toAtomString();
        $this->travel(2)->minutes();
        $listing->update(['notes' => 'Private note update']);
        $this->assertSame($before, $listing->fresh()->seo_updated_at->toAtomString());
        $listing->update(['description' => 'A new public specification for this commercial opportunity.']);
        $this->assertNotSame($before, $listing->fresh()->seo_updated_at->toAtomString());
    }

    public function test_sitemap_index_and_pages_contain_only_canonical_public_languages(): void
    {
        $this->listing();
        $index = $this->xmlUrls('/sitemap.xml');
        $this->assertSame(['https://market.example.test/sitemaps/pages.xml', 'https://market.example.test/sitemaps/opportunities-1.xml'], $index);
        $pages = $this->xmlUrls('/sitemaps/pages.xml');
        $this->assertCount(4, $pages);
        foreach ($pages as $url) {
            $this->assertStringNotContainsString('/login', $url);
            $this->assertStringContainsString('?lang=', $url);
        }
    }

    public function test_sitemap_paginates_and_excludes_nonpublic_or_expired_companies(): void
    {
        $items = [];
        for ($i = 0; $i < 3; $i++) {
            $items[] = $this->listing();
        }
        $this->listing(['visibility' => 'private']);
        $this->listing(['marketplace_state' => 'closed']);
        $this->listing(['expires_at' => now()->subSecond()]);
        $first = $this->xmlUrls('/sitemaps/opportunities-1.xml');
        $second = $this->xmlUrls('/sitemaps/opportunities-2.xml');
        $this->assertCount(4, $first);
        $this->assertCount(2, $second);
        foreach ($items as $item) {
            $this->assertContains(app(PublicSeo::class)->url('opportunities.show', ['opportunity' => $item], 'ar'), array_merge($first, $second));
        }
        $this->get('/sitemaps/opportunities-3.xml')->assertNotFound();
        $this->get('/sitemaps/opportunities-0.xml')->assertNotFound();
    }

    public function test_closing_listing_removes_it_from_sitemap_immediately(): void
    {
        $listing = $this->listing();
        $this->xmlUrls('/sitemaps/opportunities-1.xml');
        $listing->update(['marketplace_state' => 'closed']);
        $this->get('/sitemaps/opportunities-1.xml')->assertNotFound();
        $this->assertCount(1, $this->xmlUrls('/sitemap.xml'));
    }

    public function test_robots_references_canonical_sitemap_and_allows_public_assets(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: https://market.example.test/sitemap.xml', false)->assertSee('Disallow: /admin', false)->assertDontSee('Disallow: /build', false);
        $this->assertFileDoesNotExist(public_path('robots.txt'));
    }

    public function test_seo_check_is_readonly_and_successful_on_valid_configuration(): void
    {
        $listing = $this->listing();
        $before = $listing->fresh()->getRawOriginal();
        $this->artisan('seo:check')->expectsOutputToContain('Missing slugs: 0')->assertSuccessful();
        $this->assertSame($before, $listing->fresh()->getRawOriginal());
    }

    public function test_invalid_canonical_configuration_is_not_replaced_with_host_header(): void
    {
        config(['seo.canonical_url' => 'javascript:alert(1)']);
        $this->expectException(\RuntimeException::class);
        app(PublicSeo::class)->base();
    }

    public function test_verification_tags_and_social_metadata_are_configured_without_exposing_keys(): void
    {
        config(['seo.google_verification' => 'google-verification-code', 'seo.bing_verification' => 'bing-verification-code', 'seo.social_image' => 'storage/real-brand-image.png']);
        $this->get('/?lang=en')->assertOk()->assertSee('name="google-site-verification" content="google-verification-code"', false)->assertSee('https://market.example.test/storage/real-brand-image.png', false)->assertDontSee('OPPORTUNITY_OPENAI_API_KEY');
    }

    public function test_canonical_subdirectory_is_not_duplicated(): void
    {
        $listing = $this->listing();
        config(['app.url' => 'http://localhost/platform', 'seo.canonical_url' => 'https://canonical.example/platform']);
        $url = app(PublicSeo::class)->url('opportunities.show', ['opportunity' => $listing], 'en');
        $this->assertSame('https://canonical.example/platform/opportunities/'.$listing->slug.'?lang=en', $url);
    }
}
