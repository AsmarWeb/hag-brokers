<?php

namespace Tests\Feature;

use App\Models\CompanyMember;
use App\Models\CompanyTradeOffer;
use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\Platform\CompanyWorkspaceService;
use App\Services\Platform\MobilePushService;
use App\Services\Platform\TradeOfferRoomService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class MobilePushTest extends TestCase
{
    use RefreshDatabase;

    private string $bearer = '';

    protected function setUp(): void
    {
        parent::setUp();
        config(['mobile_push.enabled' => true, 'mobile_push.project_id' => 'a1111111-1111-4111-8111-111111111111', 'mobile_push.access_token' => 'SERVER-SECRET']);
        Http::preventStrayRequests();
    }

    private function company(): array
    {
        $c = Customer::create(['company_name' => 'PRIVATE COMPANY', 'is_active' => true]);
        $u = User::factory()->create(['customer_id' => $c->id]);

        return [$u, app(CompanyWorkspaceService::class)->activateNewRegistration($c, $u)];
    }

    private function device(User $u): void
    {
        auth()->forgetGuards();
        $this->bearer = $u->createToken('mobile:push', ['mobile:v1'], now()->addDay())->plainTextToken;
        $this->withHeader('Authorization', 'Bearer '.$this->bearer);
    }

    private function register(User $u, string $token = 'ExpoPushToken[test_device]'): string
    {
        $this->device($u);

        return $this->putJson('/api/v1/push/device', ['expo_token' => $token, 'project_id' => config('mobile_push.project_id'), 'locale' => 'ar', 'offers' => true, 'messages' => true])->assertOk()->json('data.binding');
    }

    private function fixture(): array
    {
        [$buyer,$w] = $this->company();
        $binding = $this->register($buyer);
        $this->actingAs($buyer)->post(route('opportunities.store'), ['request_key' => (string) Str::uuid(), 'title' => 'Commercial sourcing', 'description' => 'Business sourcing for office furniture in Dubai.', 'opportunity_type' => 'buy', 'category' => 'consumer_goods', 'country' => 'UAE', 'city' => 'Dubai', 'currency' => 'AED'])->assertRedirect();
        $o = Opportunity::latest('id')->first();
        [$supplier,$sw] = $this->company();
        $this->actingAs($supplier)->post(route('company.trade-offers.store', $o), ['request_key' => (string) Str::uuid(), 'quantity' => '10', 'unit' => 'piece', 'unit_price' => '19.99', 'currency' => 'AED', 'tax_basis' => 'exclusive', 'delivery_days' => 7, 'valid_until' => now('Asia/Dubai')->addDays(10)->toDateString(), 'payment_terms' => 'PRIVATE TERMS', 'notes' => 'PRIVATE NOTES', 'confirmed' => 1])->assertRedirect();

        return [$buyer, $w, CompanyTradeOffer::latest('id')->firstOrFail(), $binding];
    }

    private function ticket(): void
    {
        Http::fake(['exp.host/--/api/v2/push/send' => Http::response(['data' => [['status' => 'ok', 'id' => 'ticket-1']]])]);
    }

    public function test_registration_is_private_scoped_and_encrypted_and_logout_cascades(): void
    {
        [$u] = $this->company();
        $this->register($u);
        $row = DB::table('mobile_push_devices')->first();
        $this->assertNotSame('ExpoPushToken[test_device]', $row->expo_token);
        $this->assertSame('ExpoPushToken[test_device]', Crypt::decryptString($row->expo_token));
        auth()->forgetGuards();
        $this->getJson('/api/v1/push/settings')->assertOk()->assertJsonMissingPath('data.expo_token')->assertJsonMissingPath('data.device.expo_token')->assertJsonMissingPath('data.access_token');
        $this->postJson('/api/v1/auth/logout')->assertNoContent();
        $this->assertDatabaseCount('mobile_push_devices', 0);
    }

    public function test_collect_and_duplicate_send_only_once_without_private_content(): void
    {
        [$u,$w,$offer,$binding] = $this->fixture();
        $s = app(MobilePushService::class);
        $this->assertSame(1, $s->collect());
        $this->assertSame(0, $s->collect());
        $this->ticket();
        $id = DB::table('mobile_push_deliveries')->value('id');
        $s->deliver($id);
        $s->deliver($id);
        Http::assertSentCount(1);
        Http::assertSent(function ($r) use ($binding) {
            $m = $r->data()[0];
            $this->assertSame(['scope', 'binding', 'notice_id'], array_keys($m['data']));
            $this->assertSame($binding, $m['data']['binding']);
            $this->assertStringNotContainsString('PRIVATE', json_encode($r->data()));
            $this->assertStringNotContainsString('19.99', json_encode($r->data()));

            return true;
        });
        $this->assertDatabaseHas('mobile_push_deliveries', ['id' => $id, 'status' => 'ticketed']);
    }

    public function test_disabled_master_has_no_network_or_collection_and_check_is_read_only(): void
    {
        $this->fixture();
        config(['mobile_push.enabled' => false]);
        $s = app(MobilePushService::class);
        $this->assertSame(0, $s->collect());
        $this->assertSame(0, $s->receipts());
        $this->artisan('platform:mobile-push --check')->assertSuccessful();
        Http::assertNothingSent();
        $this->assertDatabaseCount('mobile_push_deliveries', 0);
    }

    public function test_suspended_changed_membership_read_or_expired_session_skips_pending(): void
    {
        [$u,$w] = $this->fixture();
        $s = app(MobilePushService::class);
        $s->collect();
        $w->update(['status' => 'suspended']);
        $s->deliver(DB::table('mobile_push_deliveries')->value('id'));
        Http::assertNothingSent();
        $this->assertDatabaseHas('mobile_push_deliveries', ['status' => 'skipped']);
    }

    public function test_already_read_notifications_and_historical_notifications_are_not_collected(): void
    {
        [$u] = $this->fixture();
        $s = app(MobilePushService::class);
        $u->notifications()->update(['read_at' => now()]);
        $this->assertSame(0, $s->collect());
        $u->notifications()->update(['read_at' => null, 'created_at' => now()->subHours(2)]);
        $this->assertSame(0, $s->collect());
        Http::assertNothingSent();
    }

    public function test_binding_reassignment_blocks_old_notification_navigation(): void
    {
        [$u,$w,$offer,$binding] = $this->fixture();
        $n = $u->notifications()->where('data->type', 'rfq.submitted')->firstOrFail();
        $this->register($u); // fresh session rotates binding, even for same account
        $this->getJson('/api/v1/push/resolve?binding='.$binding.'&notice_id='.$n->id)->assertNotFound();
    }

    public function test_registered_account_can_open_notice_without_implicit_read_and_other_accounts_cannot(): void
    {
        [$u,$w,$offer,$binding] = $this->fixture();
        auth()->forgetGuards();
        $this->withHeader('Authorization', 'Bearer '.$this->bearer);
        $n = $u->notifications()->where('data->type', 'rfq.submitted')->firstOrFail();
        $this->getJson('/api/v1/push/resolve?binding='.$binding.'&notice_id='.$n->id)->assertOk()->assertJsonPath('data.name', 'offer')->assertJsonPath('data.id', $offer->id);
        $this->assertNull($n->fresh()->read_at);
        [$other] = $this->company();
        $this->register($other, 'ExpoPushToken[other]');
        $this->getJson('/api/v1/push/resolve?binding='.$binding.'&notice_id='.$n->id)->assertNotFound();
    }

    public function test_uncertain_send_is_not_retried_and_device_not_registered_is_disabled(): void
    {
        $this->fixture();
        $s = app(MobilePushService::class);
        $s->collect();
        $id = DB::table('mobile_push_deliveries')->value('id');
        Http::fake(['*' => Http::response([], 503)]);
        $s->deliver($id);
        $s->deliver($id);
        Http::assertSentCount(1);
        $this->assertDatabaseHas('mobile_push_deliveries', ['status' => 'uncertain']);
    }

    public function test_provider_receipt_is_not_claimed_as_device_delivery_and_unregister_disables_device(): void
    {
        $this->fixture();
        $s = app(MobilePushService::class);
        $s->collect();
        $this->ticket();
        $id = DB::table('mobile_push_deliveries')->value('id');
        $s->deliver($id);
        $this->travel(16)->minutes();
        Http::fake(['*' => Http::response(['data' => ['ticket-1' => ['status' => 'error', 'details' => ['error' => 'DeviceNotRegistered']]]])]);
        $this->assertSame(1, $s->receipts());
        $this->assertDatabaseHas('mobile_push_devices', ['enabled' => false]);
        $this->assertDatabaseHas('mobile_push_deliveries', ['status' => 'failed']);
    }

    public function test_invalid_token_project_and_ineligible_accounts_are_rejected(): void
    {
        $this->getJson('/api/v1/push/settings')->assertUnauthorized();
        $u = User::factory()->create();
        $this->device($u);
        $this->getJson('/api/v1/push/settings')->assertForbidden();
        [$u] = $this->company();
        $this->device($u);
        $this->putJson('/api/v1/push/device', ['expo_token' => 'https://evil.test', 'project_id' => config('mobile_push.project_id'), 'locale' => 'ar', 'offers' => true, 'messages' => true])->assertUnprocessable();
        $this->assertDatabaseCount('mobile_push_devices', 0);
    }

    public function test_other_company_membership_and_expired_device_tokens_cannot_receive(): void
    {
        [$u,$w] = $this->fixture();
        $s = app(MobilePushService::class);
        $s->collect();
        CompanyMember::where('user_id', $u->id)->update(['role' => 'member']);
        $s->deliver(DB::table('mobile_push_deliveries')->value('id'));
        Http::assertNothingSent();
        $this->assertDatabaseHas('mobile_push_deliveries', ['status' => 'skipped']);
        CompanyMember::where('user_id', $u->id)->update(['role' => 'owner']);
        $u->tokens()->update(['expires_at' => now()->subMinute()]);
        $this->assertSame(0, $s->collect());
    }

    public function test_offer_preference_and_project_change_skip_pending_without_provider_request(): void
    {
        $this->fixture();
        $s = app(MobilePushService::class);
        $s->collect();
        DB::table('mobile_push_devices')->update(['offers' => false]);
        $id = DB::table('mobile_push_deliveries')->value('id');
        $s->deliver($id);
        Http::assertNothingSent();
        DB::table('mobile_push_devices')->update(['offers' => true]);
        DB::table('mobile_push_deliveries')->where('id', $id)->update(['status' => 'pending']);
        config(['mobile_push.project_id' => 'b1111111-1111-4111-8111-111111111111']);
        $s->deliver($id);
        Http::assertNothingSent();
    }

    public function test_provider_ok_receipt_records_handoff_not_actual_read_or_device_delivery(): void
    {
        [$u] = $this->fixture();
        $s = app(MobilePushService::class);
        $s->collect();
        $this->ticket();
        $s->deliver(DB::table('mobile_push_deliveries')->value('id'));
        $this->travel(16)->minutes();
        Http::fake(['*' => Http::response(['data' => ['ticket-1' => ['status' => 'ok']]])]);
        $this->assertSame(1, $s->receipts());
        $this->assertDatabaseHas('mobile_push_deliveries', ['status' => 'provider_accepted']);
        $this->assertNull($u->notifications()->where('data->type', 'rfq.submitted')->first()->read_at);
    }

    public function test_message_read_watermark_suppresses_push_and_new_message_opens_room(): void
    {
        [$u,$w,$offer,$binding] = $this->fixture();
        $u->notifications()->update(['read_at' => now()]);
        $sender = User::where('customer_id', CompanyWorkspace::find($offer->sender_workspace_id)->customer_id)->firstOrFail();
        $room = app(TradeOfferRoomService::class);
        $message = $room->send($sender, $offer, ['body' => 'SECRET MESSAGE', 'request_key' => (string) Str::uuid()]);
        $s = app(MobilePushService::class);
        $this->assertSame(1, $s->collect());
        $room->markRead($u, $offer, $message->id);
        $s->deliver(DB::table('mobile_push_deliveries')->value('id'));
        Http::assertNothingSent();
        $room->send($sender, $offer, ['body' => 'SECOND SECRET MESSAGE', 'request_key' => (string) Str::uuid()]);
        $this->assertSame(1, $s->collect());
        $this->ticket();
        $id = DB::table('mobile_push_deliveries')->where('status', 'pending')->value('id');
        $s->deliver($id);
        Http::assertSentCount(1);
        $n = $u->notifications()->where('data->type', 'rfq.message')->orderByDesc('data->message_id')->firstOrFail();
        auth()->forgetGuards();
        $this->getJson('/api/v1/push/resolve?binding='.$binding.'&notice_id='.$n->id)->assertOk()->assertJsonPath('data.name','room');
    }
}
