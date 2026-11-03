<?php

namespace Tests\Feature;

use App\Models\CompanyMember;
use App\Models\CompanyTradeOffer;
use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Role;
use App\Models\TradeOfferMessage;
use App\Models\TradeOfferRead;
use App\Models\User;
use App\Services\Platform\CompanyWorkspaceService;
use App\Services\Platform\TradeOfferRoomService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;

class TradeOfferRoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_feed_is_private_authorized_and_does_not_mark_read(): void
    {
        [$buyer, $supplier, $o] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        $url = route('company.trade-offers.room', $q);
        $this->actingAs($supplier)->postJson($url, ['request_key' => (string) Str::uuid(), 'body' => '<script>unsafe</script>'])->assertOk()->assertJson(['sent' => true])->assertJsonStructure(['request_key']);
        $this->actingAs($buyer)->getJson($url)->assertOk()->assertJsonPath('writable', true)->assertJsonPath('unread', 1)->assertSee('&lt;script&gt;', false)->assertHeader('Cache-Control', 'no-store, private');
        $this->assertDatabaseCount('trade_offer_reads', 0);
        [$outsider] = $this->company();
        $this->actingAs($outsider)->getJson($url)->assertNotFound();
    }

    private function company(string $role = 'owner'): array
    {
        $this->artisan('platform:install-plans');
        $customer = Customer::create(['company_name' => 'UAE Company '.Str::random(6), 'is_active' => true]);
        $owner = User::factory()->create(['customer_id' => $customer->id]);
        $workspace = app(CompanyWorkspaceService::class)->activateNewRegistration($customer, $owner);
        if ($role === 'owner') {
            return [$owner, $workspace];
        }
        $member = User::factory()->create(['customer_id' => $customer->id]);
        CompanyMember::create(['company_workspace_id' => $workspace->id, 'user_id' => $member->id, 'role' => $role]);

        return [$member, $workspace];
    }

    private function listingData(array $extra = []): array
    {
        return array_replace(['request_key' => (string) Str::uuid(), 'title' => 'Office chairs for Dubai businesses',
            'description' => 'Supply of office chairs with adjustable armrests for business offices in Dubai.',
            'opportunity_type' => 'sell', 'category' => 'consumer_goods', 'country' => 'United Arab Emirates', 'city' => 'Dubai', 'currency' => 'AED',
            'commercial_details' => ['product_name' => 'Office chair', 'specifications' => 'Adjustable armrests', 'quantity' => '500.125', 'unit' => 'piece', 'unit_price' => '199.99', 'min_order_quantity' => '10', 'lead_time_days' => 14, 'delivery_terms' => 'dap', 'payment_terms' => 'To be agreed', 'target_market' => 'UAE', 'condition' => 'new', 'price_basis' => 'quotation']], $extra);
    }

    private function publish(User $user, array $extra = []): Opportunity
    {
        $this->actingAs($user)->post(route('opportunities.store'), $this->listingData($extra))->assertRedirect(route('company.marketplace'));

        return Opportunity::latest('id')->firstOrFail();
    }

    private function yachtData(): array
    {
        return ['title' => 'Dubai yacht with private cabins', 'category' => 'marine', 'estimated_value' => '950000.00', 'commercial_details' => ['subcategory' => 'yachts', 'features' => ['stabilizers', 'tender']], 'vessel' => ['listing_type' => 'sale', 'vessel_category' => 'yacht', 'builder' => 'Test shipyard', 'length_m' => '25.50', 'beam_m' => '6.20', 'draft_m' => '1.80', 'cabins' => 4, 'guest_capacity' => 8, 'crew_capacity' => 3, 'engine_make' => 'Test engine', 'engine_hours' => 450, 'price_on_request' => false]];
    }

    private function setupOffer(): array
    {
        [$buyer] = $this->company();
        $o = $this->publish($buyer, ['opportunity_type' => 'buy']);
        [$supplier,$w] = $this->company();

        return [$buyer, $supplier, $o, $w];
    }

    private function data(array $extra = []): array
    {
        return array_replace(['request_key' => (string) Str::uuid(), 'quantity' => '2.125', 'unit' => 'piece', 'unit_price' => '19.99', 'currency' => 'AED', 'tax_basis' => 'exclusive', 'delivery_days' => '7', 'valid_until' => now('Asia/Dubai')->addDays(10)->toDateString(), 'payment_terms' => 'Bank transfer after delivery confirmation.', 'notes' => 'Supplier declared specifications.', 'confirmed' => 1], $extra);
    }

    private function send(User $supplier, Opportunity $o, array $data = []): CompanyTradeOffer
    {
        $this->actingAs($supplier)->post(route('company.trade-offers.store', $o), $data ?: $this->data())->assertRedirect();

        return CompanyTradeOffer::latest('id')->firstOrFail();
    }

    private function decision(CompanyTradeOffer $q, string $action = 'selected', int $version = 0, array $extra = [])
    {
        return $this->post(route('company.trade-offers.decide', $q), array_replace(['action' => $action, 'version' => $version, 'reason' => 'Decision after reviewing the commercial terms.', 'confirmed' => 1], $extra));
    }

    private function admin(): User
    {
        $u = User::factory()->create();
        $u->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));

        return $u;
    }

    private function message(CompanyTradeOffer $q, string $body = 'Please confirm the proposed delivery schedule.', ?string $key = null)
    {
        return $this->post(route('company.trade-offers.room.store', $q), ['body' => $body, 'request_key' => $key ?? (string) Str::uuid()]);
    }

    private function read(CompanyTradeOffer $q, int $id)
    {
        return $this->post(route('company.trade-offers.room.read', $q), ['up_to' => $id]);
    }

    public function test_both_parties_can_message_and_open_room_in_both_languages_privately(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        $this->message($q)->assertRedirect(route('company.trade-offers.room', $q));
        $this->actingAs($buyer);
        $this->message($q, 'Delivery is required within seven business days.')->assertRedirect();
        foreach ([$buyer, $supplier] as $person) {
            foreach (['ar', 'en'] as $lang) {
                $this->actingAs($person)->get(route('company.trade-offers.room', [$q, 'lang' => $lang]))->assertOk()->assertSee('seven business days')->assertSee('noindex')->assertHeader('Cache-Control', 'no-store, private')->assertDontSee('trade_room.');
            }
        }
        $this->assertDatabaseCount('trade_offer_messages', 2);
        $this->assertDatabaseCount('trade_offer_reads', 0);
    }

    public function test_outsiders_members_guests_and_platform_admins_cannot_send_or_acknowledge(): void
    {
        [$buyer,$supplier,$o,$w] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        $this->message($q);
        $id = TradeOfferMessage::first()->id;
        [$other] = $this->company();
        $this->actingAs($other)->get(route('company.trade-offers.room', $q))->assertNotFound();
        $this->message($q)->assertNotFound();
        $this->read($q, $id)->assertNotFound();
        $member = User::factory()->create(['customer_id' => $w->customer_id]);
        CompanyMember::create(['company_workspace_id' => $w->id, 'user_id' => $member->id, 'role' => 'member']);
        $this->actingAs($member)->get(route('company.trade-offers.room', $q))->assertForbidden();
        $this->message($q)->assertForbidden();
        $this->read($q, $id)->assertForbidden();
        $this->actingAs($this->admin())->get(route('admin.trade-offers.room', $q))->assertOk()->assertDontSee('name="body"', false);
        $this->message($q)->assertForbidden();
        $this->read($q, $id)->assertForbidden();
        $this->assertDatabaseCount('trade_offer_reads', 0);
        auth()->logout();
        $this->get(route('company.trade-offers.room', $q))->assertRedirect(route('login'));
    }

    public function test_identical_retry_is_idempotent_and_changed_body_is_rejected(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        $key = (string) Str::uuid();
        $this->message($q, 'Please confirm the proposed delivery schedule.', $key)->assertRedirect();
        $this->message($q, 'Please confirm the proposed delivery schedule.', $key)->assertRedirect();
        $this->message($q, 'A changed message with the old key.', $key)->assertSessionHasErrors('body');
        $this->assertDatabaseCount('trade_offer_messages', 1);
        $this->assertSame(1, $buyer->notifications()->get()->filter(fn ($n) => ($n->data['type'] ?? '') === 'rfq.message')->count());
    }

    public function test_read_state_is_explicit_monotonic_company_scoped_and_does_not_mark_future_messages(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        $this->message($q);
        $first = TradeOfferMessage::first()->id;
        $this->actingAs($buyer)->get(route('company.trade-offers.room', $q))->assertViewHas('unread', 1);
        $this->assertDatabaseCount('trade_offer_reads', 0);
        $this->read($q, $first)->assertRedirect();
        $this->actingAs($supplier);
        $this->message($q, 'A second message arriving after the first review.');
        $second = TradeOfferMessage::latest('id')->first()->id;
        $this->actingAs($buyer);
        $this->read($q, $first)->assertRedirect();
        $this->get(route('company.trade-offers.room', $q))->assertViewHas('unread', 1);
        $this->read($q, $second)->assertRedirect();
        $this->read($q, $first)->assertRedirect();
        $this->assertSame($second, TradeOfferRead::first()->last_message_id);
        $this->actingAs($supplier)->get(route('company.trade-offers.room', $q))->assertViewHas('otherRead', $second);
    }

    public function test_company_manager_shares_read_status_with_owner(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        $this->message($q);
        $w = CompanyWorkspace::where('customer_id', $buyer->customer_id)->first();
        $manager = User::factory()->create(['customer_id' => $buyer->customer_id]);
        CompanyMember::create(['company_workspace_id' => $w->id, 'user_id' => $manager->id, 'role' => 'manager']);
        $this->actingAs($manager);
        $this->read($q, TradeOfferMessage::first()->id)->assertRedirect();
        $this->actingAs($buyer)->get(route('company.trade-offers.room', $q))->assertViewHas('unread', 0);
    }

    public function test_acknowledgement_cannot_reference_message_from_another_offer(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $first = $this->send($supplier, $o);
        $this->message($first);
        $messageId = TradeOfferMessage::first()->id;
        [$supplier2] = $this->company();
        $second = $this->send($supplier2, $o);
        $this->read($second, $messageId)->assertNotFound();
        $this->assertDatabaseCount('trade_offer_reads', 0);
    }

    public function test_messages_do_not_change_offer_source_or_financial_records_and_escape_html(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        $before = $q->getAttributes();
        $source = $o->getAttributes();
        $name = $supplier->name;
        $this->message($q, '<script>alert(1)</script> Please review the terms.')->assertRedirect();
        $supplier->update(['name' => 'Updated author name']);
        $this->actingAs($buyer)->get(route('company.trade-offers.room', $q))->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertSee($name);
        $this->assertSame($before, $q->fresh()->getAttributes());
        $this->assertSame($source, $o->fresh()->getAttributes());
        $this->assertDatabaseCount('company_trade_offer_events', 1);
        foreach (['deals', 'issued_invoices', 'invoice_payments', 'deal_commissions'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }

    public function test_declined_withdrawn_or_unavailable_opportunity_is_read_only_but_keeps_history(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        $this->message($q);
        $this->decision($q, 'withdrawn')->assertRedirect();
        $this->message($q)->assertSessionHasErrors('body');
        $this->get(route('company.trade-offers.room', $q))->assertOk()->assertSee('Please confirm')->assertDontSee('name="body"', false);
        $q->update(['status' => 'submitted']);
        $o->update(['marketplace_state' => 'closed']);
        $this->message($q)->assertSessionHasErrors('body');
        $this->assertDatabaseCount('trade_offer_messages', 1);
    }

    public function test_inactive_counterparty_and_tampered_offer_block_new_messages(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        CompanyWorkspace::where('customer_id', $buyer->customer_id)->update(['status' => 'suspended']);
        $this->message($q)->assertSessionHasErrors('body');
        CompanyWorkspace::where('customer_id', $buyer->customer_id)->update(['status' => 'active']);
        DB::table('company_trade_offers')->where('id', $q->id)->update(['unit_price' => '500.00']);
        $this->get(route('company.trade-offers.room', $q))->assertStatus(409);
        $this->message($q)->assertStatus(409);
        $this->assertDatabaseCount('trade_offer_messages', 0);
    }

    public function test_notifications_are_only_for_other_company_leaders_and_exclude_message_text(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        $w = CompanyWorkspace::where('customer_id', $buyer->customer_id)->first();
        $manager = User::factory()->create(['customer_id' => $buyer->customer_id]);
        $member = User::factory()->create(['customer_id' => $buyer->customer_id]);
        CompanyMember::create(['company_workspace_id' => $w->id, 'user_id' => $manager->id, 'role' => 'manager']);
        CompanyMember::create(['company_workspace_id' => $w->id, 'user_id' => $member->id, 'role' => 'member']);
        $this->message($q, 'PRIVATE COMMERCIAL MESSAGE CONTENT')->assertRedirect();
        $notification = $buyer->notifications()->get()->first(fn ($n) => ($n->data['type'] ?? '') === 'rfq.message');
        $this->assertNotNull($notification);
        $this->assertStringNotContainsString('PRIVATE COMMERCIAL MESSAGE CONTENT', json_encode($notification->data));
        $this->assertSame(1, $manager->notifications()->count());
        $this->assertSame(0, $member->notifications()->count());
        $this->assertSame(0, $supplier->notifications()->count());
    }

    public function test_notification_failure_rolls_back_message_and_model_blocks_editing_deletion(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        $this->message($q);
        $message = TradeOfferMessage::first();
        try {
            $message->update(['body' => 'Changed']);
            $this->fail('Expected immutable message');
        } catch (\LogicException) {
        }
        try {
            $message->delete();
            $this->fail('Expected no delete');
        } catch (\LogicException) {
        }
        Event::listen(NotificationSending::class, function () {
            throw new \RuntimeException('Simulated notification failure');
        });
        $this->message($q, 'Another message should roll back.')->assertServerError();
        $this->assertDatabaseCount('trade_offer_messages', 1);
    }

    public function test_room_is_paginated_and_rejects_blank_oversized_or_invalid_request_keys(): void
    {
        [$buyer,$supplier,$o] = $this->setupOffer();
        $q = $this->send($supplier, $o);
        foreach ([['body' => ''], ['body' => str_repeat('x', 4001)], ['request_key' => 'invalid']] as $bad) {
            $this->postJson(route('company.trade-offers.room.store', $q), array_replace(['body' => 'A valid message', 'request_key' => (string) Str::uuid()], $bad))->assertUnprocessable();
        }
        for ($n = 0; $n < 31; $n++) {
            app(TradeOfferRoomService::class)->send($supplier, $q, ['body' => 'Message number '.$n, 'request_key' => (string) Str::uuid()]);
        }
        $this->actingAs($buyer)->get(route('company.trade-offers.room', $q))->assertOk()->assertViewHas('messages', fn ($m) => $m->count() === 30 && $m->total() === 31);
        $this->get(route('company.trade-offers.room', [$q, 'page' => 2]))->assertViewHas('messages', fn ($m) => $m->count() === 1);
    }
}
