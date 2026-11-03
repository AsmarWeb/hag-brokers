<?php

namespace Tests\Feature;

use App\Models\CompanyMember;
use App\Models\CompanyTradeOffer;
use App\Models\CompanyTradeOfferEvent;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Role;
use App\Models\User;
use App\Notifications\AdminPlatformNotification;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class TradeWorkflowApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Storage::fake('rfq_private');
        config(['platform_billing.free_launch' => true]);
    }

    private function company(string $role = 'owner'): array
    {
        $this->artisan('platform:install-plans');
        $c = Customer::create(['company_name' => 'Trade Co '.Str::random(6), 'is_active' => true]);
        $u = User::factory()->create(['customer_id' => $c->id]);
        $w = app(CompanyWorkspaceService::class)->activateNewRegistration($c, $u);
        if ($role !== 'owner') {
            $u = User::factory()->create(['customer_id' => $c->id]);
            CompanyMember::create(['company_workspace_id' => $w->id, 'user_id' => $u->id, 'role' => $role]);
        }

return [$u, $w];
    }

    private function setupTrade(): array
    {
        [$buyer,$w] = $this->company();
        $o = app(CompanyMarketplaceService::class)->publish($buyer, ['title' => 'Buy equipment', 'opportunity_type' => 'buy', 'description' => 'We need equipment with specified commercial requirements.', 'currency' => 'AED', 'expires_at' => now()->addMonth()->format('Y-m-d H:i:s')], (string) Str::uuid())['opportunity'];
        [$supplier] = $this->company();

        return [$buyer, $supplier, $o];
    }

    private function device(User $u): void
    {
        auth()->forgetGuards();
        $this->withHeaders(['Authorization' => 'Bearer '.$u->createToken('mobile:trade', ['mobile:v1'], now()->addDay())->plainTextToken]);
    }

    private function data(array $x = []): array
    {
        return array_replace(['request_key' => (string) Str::uuid(), 'quantity' => '2.125', 'unit' => 'piece', 'unit_price' => '19.99', 'currency' => 'AED', 'tax_basis' => 'exclusive', 'delivery_days' => 7, 'valid_until' => now('Asia/Dubai')->addDays(10)->toDateString(), 'payment_terms' => 'Bank transfer after delivery confirmation.', 'notes' => 'Offer private terms', 'confirmed' => true], $x);
    }

    private function submit(User $supplier, Opportunity $o, array $x = []): array
    {
        $this->device($supplier);

        return $this->postJson('/api/v1/company/opportunities/'.$o->id.'/offers', $this->data($x))->assertCreated()->json('data');
    }

    private function url(array $q): string
    {
        return '/api/v1/company/trade-offers/'.$q['id'];
    }

    public function test_submission_is_exact_private_idempotent_and_creates_no_invoice(): void
    {
        [$buyer,$supplier,$o] = $this->setupTrade();
        $this->device($supplier);
        $d = $this->data();
        $url = '/api/v1/company/opportunities/'.$o->id.'/offers';
        $q = $this->postJson($url, $d)->assertCreated()->assertJsonPath('data.base_amount', '42.48')->assertJsonPath('meta.created', true)->json('data');
        $this->postJson($url, $d)->assertOk()->assertJsonPath('data.id', $q['id'])->assertJsonPath('meta.created', false);
        $this->postJson($url, array_replace($d, ['unit_price' => '20.00']))->assertUnprocessable();
        $this->assertDatabaseCount('company_trade_offers', 1);
        $this->getJson('/api/v1/opportunities/'.$o->slug)->assertDontSee('Offer private terms');
        foreach (['deals', 'invoice_drafts', 'issued_invoices', 'invoice_payments'] as $t) {
            if (Schema::hasTable($t)) {
                $this->assertDatabaseCount($t, 0);
            }
        }
    }

    public function test_buyer_can_compare_and_supplier_cannot_read_competing_offers(): void
    {
        [$buyer,$supplier,$o] = $this->setupTrade();
        $q = $this->submit($supplier, $o);
        [$second] = $this->company();
        $foreign = $this->submit($second, $o);
        $this->device($buyer);
        $this->getJson('/api/v1/company/opportunities/'.$o->id.'/offers?per_page=1')->assertOk()->assertJsonPath('meta.total', 2)->assertJsonCount(1, 'data');
        $this->device($supplier);
        $this->getJson($this->url($foreign))->assertNotFound();
        $this->getJson('/api/v1/company/opportunities/'.$o->id.'/offers')->assertNotFound();
        $this->getJson('/api/v1/company/trade-offers?direction=sent')->assertOk()->assertJsonPath('meta.total', 1);
    }

    public function test_decisions_obey_party_version_confirmation_and_single_selection(): void
    {
        [$buyer,$supplier,$o] = $this->setupTrade();
        $q = $this->submit($supplier, $o);
        $d = ['action' => 'selected', 'version' => 0, 'reason' => 'Commercial requirements match.', 'confirmed' => true];
        $this->postJson($this->url($q).'/decision', $d)->assertForbidden();
        $this->device($buyer);
        $this->postJson($this->url($q).'/decision', array_replace($d, ['confirmed' => false]))->assertUnprocessable();
        $this->postJson($this->url($q).'/decision', $d)->assertOk()->assertJsonPath('data.status', 'selected')->assertJsonPath('data.version', 1);
        $this->postJson($this->url($q).'/decision', $d)->assertUnprocessable();
        [$second] = $this->company();
        $other = $this->submit($second, $o);
        $this->device($buyer);
        $this->postJson($this->url($other).'/decision', $d)->assertUnprocessable();
        $this->getJson($this->url($q).'/events')->assertOk()->assertJsonPath('meta.total', 2);
    }

    public function test_attachments_require_auth_integrity_and_never_expose_paths(): void
    {
        [$buyer,$supplier,$o] = $this->setupTrade();
        $this->device($supplier);
        $d = $this->data(['attachments' => [UploadedFile::fake()->createWithContent('terms.pdf', "%PDF-1.4\nPrivate RFQ document")]]);
        $res = $this->post('/api/v1/company/opportunities/'.$o->id.'/offers', $d, ['Accept' => 'application/json'])->assertCreated();
        $q = $res->json('data');
        $res->assertDontSee('sha256')->assertDontSee('offers/');
        $this->get($this->url($q).'/attachments/0')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        [$outsider] = $this->company();
        $this->device($outsider);
        $this->getJson($this->url($q).'/attachments/0')->assertNotFound();
        $this->device($buyer);
        $path = CompanyTradeOffer::find($q['id'])->attachments[0]['path'];
        Storage::disk('rfq_private')->put($path, 'tampered');
        $this->getJson($this->url($q).'/attachments/0')->assertStatus(409);
    }

    public function test_invalid_files_and_injected_terms_are_rejected_without_storage(): void
    {
        [$buyer,$supplier,$o] = $this->setupTrade();
        $this->device($supplier);
        $url = '/api/v1/company/opportunities/'.$o->id.'/offers';
        $this->postJson($url, $this->data(['base_amount' => '1']))->assertUnprocessable();
        $this->postJson($url, $this->data(['quantity' => '-1']))->assertUnprocessable();
        $this->post($url, $this->data(['attachments' => [UploadedFile::fake()->create('bad.html', 1, 'text/html')]]), ['Accept' => 'application/json'])->assertUnprocessable();
        $this->assertDatabaseCount('company_trade_offers', 0);
        $this->assertSame([], Storage::disk('rfq_private')->allFiles());
    }

    public function test_message_polling_is_json_incremental_and_does_not_acknowledge(): void
    {
        [$buyer,$supplier,$o] = $this->setupTrade();
        $q = $this->submit($supplier, $o);
        $url = $this->url($q).'/messages';
        for ($i = 0; $i < 3; $i++) {
            $this->postJson($url, ['request_key' => (string) Str::uuid(), 'body' => 'Message '.$i])->assertOk();
        }
        $this->device($buyer);
        $r = $this->getJson($url.'?after_id=0&limit=2')->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('meta.has_more', true)->assertJsonPath('room.unread', 3);
        $last = $r->json('meta.last_id');
        $this->getJson($url.'?after_id='.$last)->assertOk()->assertJsonCount(1, 'data');
        $this->getJson($url.'?limit=1')->assertOk()->assertJsonPath('meta.has_more', true);
        $this->assertDatabaseCount('trade_offer_reads', 0);
        $this->getJson($url.'?after_id=1&before_id=9')->assertUnprocessable();
        $this->getJson($url.'?limit=51')->assertUnprocessable();
    }

    public function test_message_retry_is_idempotent_and_changed_body_fails(): void
    {
        [$buyer,$supplier,$o] = $this->setupTrade();
        $q = $this->submit($supplier, $o);
        $d = ['request_key' => (string) Str::uuid(), 'body' => '<script>render as text</script>'];
        $url = $this->url($q).'/messages';
        $id = $this->postJson($url, $d)->assertOk()->json('data.id');
        $this->postJson($url, $d)->assertOk()->assertJsonPath('data.id', $id);
        $this->postJson($url, array_replace($d, ['body' => 'Changed']))->assertUnprocessable();
        $this->assertDatabaseCount('trade_offer_messages', 1);
        $this->getJson($url)->assertOk()->assertJsonPath('data.0.body', $d['body']);
    }

    public function test_read_watermark_is_explicit_company_shared_monotonic_and_scoped(): void
    {
        [$buyer,$supplier,$o] = $this->setupTrade();
        $q = $this->submit($supplier, $o);
        $url = $this->url($q);
        $a = $this->postJson($url.'/messages', ['request_key' => (string) Str::uuid(), 'body' => 'First'])->json('data.id');
        $b = $this->postJson($url.'/messages', ['request_key' => (string) Str::uuid(), 'body' => 'Second'])->json('data.id');
        $this->device($buyer);
        $this->postJson($url.'/read', ['up_to' => $b])->assertNoContent();
        $this->postJson($url.'/read', ['up_to' => $a])->assertNoContent();
        $this->getJson($url.'/messages')->assertJsonPath('room.unread', 0)->assertJsonPath('room.own_read_up_to', $b);
        $manager = User::factory()->create(['customer_id' => $buyer->customer_id]);
        $w = CompanyMember::where('user_id', $buyer->id)->value('company_workspace_id');
        CompanyMember::create(['company_workspace_id' => $w, 'user_id' => $manager->id, 'role' => 'manager']);
        $this->device($manager);
        $this->getJson($url.'/messages')->assertJsonPath('room.own_read_up_to', $b);
        [$b2,$s2,$o2] = $this->setupTrade();
        $q2 = $this->submit($s2, $o2);
        $foreign = $this->postJson($this->url($q2).'/messages', ['request_key' => (string) Str::uuid(), 'body' => 'Other room'])->json('data.id');
        $this->device($buyer);
        $this->postJson($url.'/read', ['up_to' => $foreign])->assertNotFound();
    }

    public function test_outsiders_members_individuals_and_admins_cannot_access_trade_rooms(): void
    {
        [$buyer,$supplier,$o] = $this->setupTrade();
        $q = $this->submit($supplier, $o);
        [$outside] = $this->company();
        $this->device($outside);
        $this->getJson($this->url($q).'/messages')->assertNotFound();
        $member = User::factory()->create(['customer_id' => $supplier->customer_id]);
        CompanyMember::create(['company_workspace_id' => CompanyMember::where('user_id', $supplier->id)->value('company_workspace_id'), 'user_id' => $member->id, 'role' => 'member']);
        foreach ([$member, User::factory()->create()] as $u) {
            $this->device($u);
            $this->getJson($this->url($q))->assertForbidden();
            $this->getJson('/api/v1/company/notifications')->assertForbidden();
        }
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        $this->device($admin);
        $this->getJson($this->url($q))->assertForbidden();
    }

    public function test_closed_source_is_read_only_and_integrity_failure_hides_terms(): void
    {
        [$buyer,$supplier,$o] = $this->setupTrade();
        $q = $this->submit($supplier, $o);
        app(CompanyMarketplaceService::class)->close($buyer, $o);
        $this->getJson($this->url($q).'/messages')->assertOk()->assertJsonPath('room.writable', false);
        $this->postJson($this->url($q).'/messages', ['request_key' => (string) Str::uuid(), 'body' => 'Forbidden new message'])->assertUnprocessable();
        DB::table('company_trade_offers')->where('id', $q['id'])->update(['unit_price' => '0.01']);
        $this->getJson($this->url($q))->assertOk()->assertJsonPath('data.integrity', false)->assertDontSee('payment_terms');
        $this->getJson($this->url($q).'/messages')->assertStatus(409);
        $this->getJson($this->url($q).'/events')->assertStatus(409);
    }

    public function test_notification_list_is_scoped_safe_and_read_is_explicit(): void
    {
        [$buyer,$supplier,$o] = $this->setupTrade();
        $q = $this->submit($supplier, $o);
        $this->postJson($this->url($q).'/messages', ['request_key' => (string) Str::uuid(), 'body' => 'SECRET CHAT BODY'])->assertOk();
        $this->device($buyer);
        $r = $this->getJson('/api/v1/company/notifications')->assertOk()->assertJsonPath('meta.unread', 2)->assertDontSee('SECRET CHAT BODY')->assertDontSee('Offer private terms');
        $id = $r->json('data.0.id');
        $this->assertNull($buyer->notifications()->find($id)->read_at);
        $this->patchJson('/api/v1/company/notifications/'.$id.'/read')->assertNoContent();
        $this->getJson('/api/v1/company/notifications?unread=1')->assertJsonPath('meta.total', 1);
        $this->device($supplier);
        $this->patchJson('/api/v1/company/notifications/'.$id.'/read')->assertNotFound();
    }

    public function test_stale_membership_and_unrelated_notifications_are_excluded(): void
    {
        [$buyer,$supplier,$o] = $this->setupTrade();
        $q = $this->submit($supplier, $o);
        $buyer->notify(new AdminPlatformNotification('account.welcome', 'PRIVATE', 'PRIVATE', null, ['offer_id' => $q['id']]));
        [$outsider] = $this->company();
        $outsider->notify(new AdminPlatformNotification('rfq.submitted', 'PRIVATE', 'PRIVATE', null, ['offer_id' => $q['id']]));
        $this->device($outsider);
        $this->getJson('/api/v1/company/notifications')->assertJsonPath('meta.total', 0);
        $this->device($buyer);
        $this->getJson('/api/v1/company/notifications')->assertJsonPath('meta.total', 1);
        $member = CompanyMember::where('user_id', $buyer->id)->first();
        $member->delete();
        $this->getJson('/api/v1/company/notifications')->assertForbidden();
    }

    public function test_failed_event_rolls_back_submission_and_private_file(): void
    {
        [$buyer,$supplier,$o] = $this->setupTrade();
        $this->device($supplier);
        CompanyTradeOfferEvent::creating(fn () => throw new \RuntimeException('Audit failure'));
        $this->post('/api/v1/company/opportunities/'.$o->id.'/offers', $this->data(['attachments' => [UploadedFile::fake()->image('terms.png')]]), ['Accept' => 'application/json'])->assertStatus(500);
        $this->assertDatabaseCount('company_trade_offers', 0);
        $this->assertDatabaseCount('opportunity_interests', 0);
        $this->assertSame([], Storage::disk('rfq_private')->allFiles());
    }

    public function test_both_languages_json_headers_and_pagination_limits(): void
    {
        [$buyer,$supplier,$o] = $this->setupTrade();
        $q = $this->submit($supplier,$o);
        foreach (['ar', 'en'] as $lang) {
            $r = $this->getJson($this->url($q).'/messages?lang='.$lang)->assertOk()->assertHeader('Content-Language',$lang);
            $this->assertStringContainsString('no-store',$r->headers->get('Cache-Control'));
        }
        $this->getJson('/api/v1/company/trade-offers?per_page=99')->assertUnprocessable();
        $this->getJson('/api/v1/company/notifications?per_page=99')->assertUnprocessable();
    }
}
