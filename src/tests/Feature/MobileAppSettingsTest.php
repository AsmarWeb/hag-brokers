<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\Platform\MobileAppSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MobileAppSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): User
    {
        $u = User::factory()->create();
        $u->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));

        return $u;
    }

    private function payload(array $extra = []): array
    {
        return array_replace(app(MobileAppSettings::class)->defaults(), ['version' => 0], $extra);
    }

    public function test_public_config_is_readonly_and_contains_no_secrets(): void
    {
        $this->getJson('/api/v1/app-config?lang=ar')->assertOk()->assertJsonPath('data.app_name', 'HAG Brokers')->assertHeader('X-API-Version', '1')->assertJsonMissingPath('data.secret')->assertJsonMissingPath('data.actor_id');
        $this->assertDatabaseCount('mobile_app_settings', 0);
        $this->assertDatabaseCount('mobile_app_setting_audits', 0);
    }

    public function test_only_verified_platform_administrators_can_manage_content(): void
    {
        $this->get('/admin/mobile-app')->assertRedirect();
        $this->actingAs(User::factory()->create())->get('/admin/mobile-app')->assertForbidden();
        $this->put('/admin/mobile-app', $this->payload())->assertForbidden();
        $u = $this->administrator();
        $u->forceFill(['email_verified_at' => null])->save();
        $this->actingAs($u)->get('/admin/mobile-app')->assertRedirect(route('verification.notice'));
        $this->assertDatabaseCount('mobile_app_settings', 0);
    }

    public function test_admin_saves_bilingual_content_and_public_api_reflects_database(): void
    {
        $this->actingAs($this->administrator())->put('/admin/mobile-app', $this->payload(['app_name' => 'HAG Global', 'home_title_ar' => 'فرص جديدة', 'welcome_enabled' => false, 'categories' => ['marine', 'food']]))->assertRedirect();
        $this->getJson('/api/v1/app-config')->assertOk()->assertJsonPath('data.version', 1)->assertJsonPath('data.app_name', 'HAG Global')->assertJsonPath('data.home_title_ar', 'فرص جديدة')->assertJsonPath('data.welcome_enabled', false)->assertJsonPath('data.categories', ['marine', 'food']);
        $this->assertDatabaseCount('mobile_app_setting_audits', 1);
        foreach (['ar', 'en'] as $lang) {
            $this->get('/admin/mobile-app?lang='.$lang)->assertOk()->assertSee('multipart/form-data', false);
        }
    }

    public function test_stale_updates_cannot_overwrite_another_admin(): void
    {
        $this->actingAs($this->administrator())->put('/admin/mobile-app', $this->payload(['app_name' => 'First']))->assertRedirect();
        $this->put('/admin/mobile-app', $this->payload(['app_name' => 'Stale']))->assertStatus(409);
        $this->getJson('/api/v1/app-config')->assertJsonPath('data.app_name', 'First');
        $this->assertDatabaseCount('mobile_app_setting_audits', 1);
    }

    public function test_invalid_categories_images_and_values_do_not_write(): void
    {
        $this->actingAs($this->administrator())->putJson('/admin/mobile-app', $this->payload(['categories' => ['invented'], 'home_image' => 'https://evil.test/x.svg', 'welcome_enabled' => 'yes']))->assertUnprocessable()->assertJsonValidationErrors(['categories.0', 'home_image', 'welcome_enabled']);
        $this->assertDatabaseCount('mobile_app_settings', 0);
    }

    public function test_upload_is_scoped_and_stale_upload_is_cleaned_up(): void
    {
        Storage::fake('public');
        $this->actingAs($this->administrator())->put('/admin/mobile-app', $this->payload(['home_image' => UploadedFile::fake()->image('hero.jpg', 900, 600)]))->assertRedirect();
        $files = Storage::disk('public')->allFiles('mobile-app');
        $this->assertCount(1, $files);
        $this->getJson('/api/v1/app-config')->assertJsonPath('data.home_image', Storage::disk('public')->url($files[0]));
        $this->put('/admin/mobile-app', $this->payload(['home_image' => UploadedFile::fake()->image('other.jpg')]))->assertStatus(409);
        $this->assertSame($files, Storage::disk('public')->allFiles('mobile-app'));
        $this->put('/admin/mobile-app', $this->payload(['version' => 1, 'remove_home_image' => true]))->assertRedirect();
        $this->getJson('/api/v1/app-config')->assertJsonPath('data.home_image', null);
    }

    public function test_financial_and_private_tables_are_unchanged_by_config_save(): void
    {
        $counts = [];
        foreach (['opportunities', 'company_billing_receipts', 'issued_invoices', 'company_workspaces'] as $table) {
            $counts[$table] = DB::table($table)->count();
        }
        $this->actingAs($this->administrator())->put('/admin/mobile-app', $this->payload(['stripe_secret' => 'do-not-expose', 'plan' => 'professional']))->assertRedirect();
        foreach ($counts as $table => $count) {
            $this->assertDatabaseCount($table,$count);
        }
        $this->getJson('/api/v1/app-config')->assertDontSee('do-not-expose')->assertJsonMissingPath('data.plan');
    }
}
