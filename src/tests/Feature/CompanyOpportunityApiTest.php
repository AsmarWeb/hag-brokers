<?php

namespace Tests\Feature;

use App\Models\CompanyMember;
use App\Models\CompanyPlatformAudit;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Role;
use App\Models\User;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CompanyOpportunityApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Storage::fake('public');
        config(['platform_billing.free_launch' => true]);
    }

    private function company(string $role = 'owner'): array
    {
        $this->artisan('platform:install-plans');
        $c = Customer::create(['company_name' => 'API Company '.Str::random(6), 'is_active' => true]);
        $u = User::factory()->create(['customer_id' => $c->id]);
        $w = app(CompanyWorkspaceService::class)->activateNewRegistration($c, $u);
        if ($role !== 'owner') {
            $u = User::factory()->create(['customer_id' => $c->id]);
            CompanyMember::create(['user_id' => $u->id, 'company_workspace_id' => $w->id, 'role' => $role]);
        }

        return [$u, $w];
    }

    private function asDevice(User $u): void
    {
        auth()->forgetGuards();
        $this->withHeaders(['Authorization' => 'Bearer '.$u->createToken('mobile:api', ['mobile:v1'], now()->addDay())->plainTextToken]);
    }

    private function payload(array $x = []): array
    {
        return array_replace(['request_key' => (string) Str::uuid(), 'title' => 'Equipment for international buyers', 'description' => 'Complete public description for commercial equipment buyers.', 'opportunity_type' => 'sell', 'category' => 'machinery', 'currency' => 'AED', 'estimated_value' => '100000.05', 'expires_at' => now('Asia/Dubai')->addDays(30)->toDateString(), 'notes' => 'PRIVATE COMPANY NOTE'], $x);
    }

    private function publish(User $u, array $x = []): array
    {
        $this->asDevice($u);

        return $this->postJson('/api/v1/company/opportunities', $this->payload($x))->assertCreated()->json('data');
    }

    private function path(array $o): string
    {
        return '/api/v1/company/opportunities/'.$o['id'];
    }

    public function test_create_uses_server_identity_free_quota_and_exact_amount_without_financial_writes(): void
    {
        [$u,$w] = $this->company();
        $o = $this->publish($u);
        $this->assertSame('100000.05', $o['estimated_value']);
        $this->assertTrue($o['is_public']);
        $this->assertFalse($o['reviewed']);
        $this->assertDatabaseHas('opportunities', ['id' => $o['id'], 'customer_id' => $u->customer_id, 'company_workspace_id' => $w->id, 'submitted_by' => $u->id, 'verification_status' => 'auto_eligible']);
        $this->postJson('/api/v1/company/opportunities', $this->payload())->assertUnprocessable();
        $this->assertDatabaseCount('opportunities', 1);
        foreach (['deals', 'invoice_drafts', 'issued_invoices', 'invoice_payments', 'company_billing_receipts'] as $table) {
            if (Schema::hasTable($table)) {
                $this->assertDatabaseCount($table, 0);
            }
        }
    }

    public function test_same_publish_reuses_listing_and_changed_retry_is_rejected(): void
    {
        [$u] = $this->company();
        $this->asDevice($u);
        $d = $this->payload();
        $first = $this->postJson('/api/v1/company/opportunities', $d)->assertCreated()->json('data.id');
        $this->postJson('/api/v1/company/opportunities', $d)->assertOk()->assertJsonPath('data.id', $first)->assertJsonPath('meta.created', false);
        $this->postJson('/api/v1/company/opportunities', array_replace($d, ['title' => 'Changed terms']))->assertUnprocessable();
        $this->assertDatabaseCount('company_marketplace_requests', 1);
        $this->assertDatabaseCount('opportunities', 1);
    }

    public function test_privilege_fields_and_invalid_payload_do_not_store_files_or_consume_quota(): void
    {
        [$u] = $this->company();
        $this->asDevice($u);
        $this->postJson('/api/v1/company/opportunities', $this->payload(['customer_id' => 999]))->assertUnprocessable();
        $this->postJson('/api/v1/company/opportunities', $this->payload(['commercial_details' => ['unit_price' => '12.00']]))->assertUnprocessable();
        $this->postJson('/api/v1/company/opportunities', $this->payload(['commission_rate' => '101.00']))->assertUnprocessable();
        $this->postJson('/api/v1/company/opportunities', $this->payload(['expires_at' => null]))->assertUnprocessable();
        $this->assertDatabaseCount('opportunities', 0);
        $this->assertDatabaseCount('company_marketplace_requests', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_company_reads_are_private_scoped_paginated_and_leader_only(): void
    {
        [$a] = $this->company();
        $mine = $this->publish($a);
        [$b] = $this->company();
        $foreign = $this->publish($b);
        $this->asDevice($a);
        $this->getJson('/api/v1/company/opportunities?per_page=1')->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.id', $mine['id'])->assertJsonPath('data.0.notes', 'PRIVATE COMPANY NOTE');
        $this->getJson($this->path($foreign))->assertNotFound();
        $this->patchJson($this->path($foreign), ['revision' => $foreign['revision'], 'title' => 'Hijack'])->assertNotFound();
        $this->postJson($this->path($foreign).'/close', ['revision' => $foreign['revision'], 'confirmation' => true])->assertNotFound();
        $this->getJson('/api/v1/company/opportunities?per_page=51')->assertUnprocessable();
        $this->getJson('/api/v1/opportunities/'.$mine['slug'])->assertOk()->assertDontSee('PRIVATE COMPANY NOTE');
    }

    public function test_partial_update_preserves_slug_financial_fields_and_other_content(): void
    {
        [$u] = $this->company();
        $o = $this->publish($u);
        $this->patchJson($this->path($o), ['revision' => $o['revision'], 'title' => 'Updated equipment'])->assertOk()->assertJsonPath('data.slug', $o['slug'])->assertJsonPath('data.estimated_value', '100000.05')->assertJsonPath('data.notes', 'PRIVATE COMPANY NOTE');
        $updated = Opportunity::find($o['id']);
        $this->assertSame('sell', $updated->opportunity_type);
        $this->patchJson($this->path($o), ['revision' => $o['revision'], 'title' => 'Stale edit'])->assertStatus(409);
        $fresh = $this->getJson($this->path($o))->assertOk()->json('data');
        $this->patchJson($this->path($o), ['revision' => $fresh['revision'], 'estimated_value' => '1.00'])->assertUnprocessable();
        $this->patchJson($this->path($o), ['title' => 'No revision'])->assertUnprocessable();
    }

    public function test_same_second_changes_and_web_changes_invalidate_api_revision(): void
    {
        [$u] = $this->company();
        $o = $this->publish($u);
        $this->freezeTime();
        app(CompanyMarketplaceService::class)->update($u, Opportunity::find($o['id']), ['title' => 'Changed on website']);
        $this->patchJson($this->path($o), ['revision' => $o['revision'], 'title' => 'Overwrite website'])->assertStatus(409);
        $this->postJson($this->path($o).'/close', ['revision' => $o['revision'], 'confirmation' => true])->assertStatus(409);
    }

    public function test_close_requires_confirmation_preserves_data_and_releases_slot(): void
    {
        [$u] = $this->company();
        $o = $this->publish($u);
        $this->postJson($this->path($o).'/close', ['revision' => $o['revision']])->assertUnprocessable();
        $closed = $this->postJson($this->path($o).'/close', ['revision' => $o['revision'], 'confirmation' => true])->assertOk()->assertJsonPath('data.is_public', false)->assertJsonPath('data.marketplace_state', 'closed')->json('data');
        $this->postJson($this->path($o).'/close', ['revision' => $closed['revision'], 'confirmation' => true])->assertOk();
        $this->getJson('/api/v1/opportunities/'.$o['slug'])->assertNotFound();
        $this->patchJson($this->path($o), ['revision' => $closed['revision'], 'title' => 'Reopen'])->assertUnprocessable();
        $this->publish($u, ['title' => 'New opportunity after closing']);
        $this->assertDatabaseCount('opportunities', 2);
    }

    public function test_members_individuals_unverified_and_suspended_companies_cannot_write(): void
    {
        [$member] = $this->company('member');
        $this->asDevice($member);
        $this->postJson('/api/v1/company/opportunities', $this->payload())->assertForbidden();
        $this->getJson('/api/v1/company/opportunities')->assertForbidden();
        $this->asDevice(User::factory()->create());
        $this->postJson('/api/v1/company/opportunities', $this->payload())->assertForbidden();
        [$owner,$w] = $this->company();
        $owner->forceFill(['email_verified_at' => null])->save();
        $this->asDevice($owner);
        $this->postJson('/api/v1/company/opportunities', $this->payload())->assertForbidden();
        $owner->forceFill(['email_verified_at' => now()])->save();
        $w->update(['status' => 'suspended']);
        $this->asDevice($owner);
        $this->postJson('/api/v1/company/opportunities', $this->payload())->assertForbidden();
    }

    public function test_manager_can_publish_but_guest_admin_and_wrong_scope_are_blocked(): void
    {
        [$manager] = $this->company('manager');
        $o = $this->publish($manager);
        $this->withHeaders(['Authorization' => ''])->getJson($this->path($o))->assertUnauthorized();
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        $this->asDevice($admin);
        $this->postJson('/api/v1/company/opportunities', $this->payload())->assertForbidden();
    }

    public function test_images_and_video_publish_and_retries_do_not_leak_files(): void
    {
        [$u] = $this->company();
        $this->asDevice($u);
        $d = $this->payload();
        $video = fn () => UploadedFile::fake()->createWithContent('tour.mp4', str_repeat("\x00", 3)."\x18ftypmp42\x00\x00\x00\x00mp42isom");
        $file = UploadedFile::fake()->image('photo.jpg');
        $bytes = file_get_contents($file->getRealPath());
        $d['media'] = [UploadedFile::fake()->createWithContent('photo.jpg', $bytes), $video()];
        $id = $this->post('/api/v1/company/opportunities', $d, ['Accept' => 'application/json'])->assertCreated()->assertJsonCount(2, 'data.media')->json('data.id');
        $d['media'] = [UploadedFile::fake()->createWithContent('photo.jpg', $bytes), $video()];
        $this->post('/api/v1/company/opportunities', $d, ['Accept' => 'application/json'])->assertOk()->assertJsonPath('data.id', $id);
        $this->assertCount(2, Storage::disk('public')->allFiles());
        $this->assertDatabaseCount('opportunity_media', 2);
    }

    public function test_invalid_file_types_count_and_total_size_leave_storage_untouched(): void
    {
        [$u] = $this->company();
        $this->asDevice($u);
        $this->post('/api/v1/company/opportunities', $this->payload(['media' => [UploadedFile::fake()->create('file.html', 1, 'text/html')]]), ['Accept' => 'application/json'])->assertUnprocessable();
        $this->post('/api/v1/company/opportunities', $this->payload(['media' => array_map(fn () => UploadedFile::fake()->image('large.jpg')->size(11000), range(1, 2))]), ['Accept' => 'application/json'])->assertUnprocessable();
        $this->post('/api/v1/company/opportunities', $this->payload(['media' => array_map(fn () => UploadedFile::fake()->image('a.jpg'), range(1, 11))]), ['Accept' => 'application/json'])->assertUnprocessable();
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->assertDatabaseCount('opportunities', 0);
    }

    public function test_failed_audit_rolls_back_listing_usage_and_removes_uploaded_files(): void
    {
        [$u] = $this->company();
        $this->asDevice($u);
        CompanyPlatformAudit::creating(function ($audit) {
            if ($audit->action === 'opportunity_published') {
                throw new \RuntimeException('Audit failure');
            }
        });
        $this->post('/api/v1/company/opportunities', $this->payload(['media' => [UploadedFile::fake()->image('a.jpg')]]), ['Accept' => 'application/json'])->assertStatus(500);
        $this->assertDatabaseCount('opportunities', 0);
        $this->assertDatabaseCount('company_marketplace_requests', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_marine_creation_and_partial_update_keep_specs_and_public_page(): void
    {
        [$u] = $this->company();
        $o = $this->publish($u, ['category' => 'marine', 'commercial_details' => ['subcategory' => 'yachts'],
            'vessel' => ['listing_type' => 'sale', 'vessel_category' => 'yacht', 'vessel_name' => 'Sea Pearl', 'length_m' => '35.50']]);
        $this->patchJson($this->path($o), ['revision' => $o['revision'], 'title' => 'Sea Pearl yacht'])->assertOk()->assertJsonPath('data.vessel.vessel_name', 'Sea Pearl');
        $this->getJson('/api/v1/opportunities/'.$o['slug'])->assertOk()->assertJsonPath('data.vessel.length_m', '35.50');
    }

    public function test_admin_hold_cannot_be_removed_by_editing_and_changes_revision(): void
    {
        [$u] = $this->company();
        $o = $this->publish($u);
        $model = Opportunity::find($o['id']);
        $model->moderation()->create(['hidden' => true, 'version' => 1]);
        $this->patchJson($this->path($o), ['revision' => $o['revision'], 'title' => 'Stale after moderation'])->assertStatus(409);
        $fresh = $this->getJson($this->path($o))->assertOk()->assertJsonPath('data.is_public', false)->json('data');
        $this->patchJson($this->path($o), ['revision' => $fresh['revision'], 'title' => 'Safe edit while hidden'])->assertOk()->assertJsonPath('data.administratively_hidden', true)->assertJsonPath('data.is_public', false);
    }

    public function test_nested_key_order_is_idempotent_and_update_cannot_accept_media_or_state(): void
    {
        [$u] = $this->company();
        $this->asDevice($u);
        $d = $this->payload(['commercial_details' => ['product_name' => 'Machine', 'attributes' => ['brand' => 'Maker', 'model' => 'M1']]]);
        $o = $this->postJson('/api/v1/company/opportunities', $d)->assertCreated()->json('data');
        $this->getJson('/api/v1/opportunities/'.$o['slug'])->assertOk()->assertJsonPath('data.commercial_details.attributes.brand', 'Maker');
        $d['commercial_details'] = ['attributes' => ['model' => 'M1', 'brand' => 'Maker'], 'product_name' => 'Machine'];
        $this->postJson('/api/v1/company/opportunities', $d)->assertOk();
        $this->patchJson($this->path($o), ['revision' => $o['revision'], 'marketplace_state' => 'published', 'media' => []])->assertUnprocessable();
    }

    public function test_both_languages_have_json_no_store_and_bounded_private_lists(): void
    {
        [$u] = $this->company();
        $o = $this->publish($u);
        foreach (['ar', 'en'] as $lang) {
            $r = $this->getJson($this->path($o).'?lang='.$lang)->assertOk()->assertHeader('Content-Language', $lang);
            $this->assertStringContainsString('no-store', $r->headers->get('Cache-Control'));
        }
        $this->getJson('/api/v1/company/opportunities?state=invalid')->assertUnprocessable();
    }
}
