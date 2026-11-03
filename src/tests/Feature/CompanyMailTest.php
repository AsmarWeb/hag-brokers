<?php

namespace Tests\Feature;

use App\Jobs\SendCompanyMail;
use App\Mail\CompanyActivityMail;
use App\Models\CompanyMailDelivery;
use App\Models\CompanyMailPreference;
use App\Models\CompanyMember;
use App\Models\CompanyTradeOffer;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Role;
use App\Models\TradeOfferRead;
use App\Models\User;
use App\Notifications\AdminPlatformNotification;
use App\Services\Platform\CompanyMailService;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class CompanyMailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['company_mail.enabled' => true, 'company_mail.mailer' => 'array', 'mail.from.address' => 'alerts@example.test', 'app.url' => 'https://market.example.test']);
        Mail::fake();
    }

    private function company(): array
    {
        $c = Customer::create(['company_name' => 'Dubai business', 'is_active' => true]);
        $u = User::factory()->create(['customer_id' => $c->id]);
        $w = app(CompanyWorkspaceService::class)->activateNewRegistration($c, $u);

        return [$u, $w];
    }

    private function fixture(): array
    {
        [$buyer,$w] = $this->company();
        $this->actingAs($buyer)->post(route('opportunities.store'), ['request_key' => (string) Str::uuid(), 'title' => 'Commercial sourcing', 'description' => 'Business sourcing for office furniture in Dubai.', 'opportunity_type' => 'buy', 'category' => 'consumer_goods', 'country' => 'UAE', 'city' => 'Dubai', 'currency' => 'AED'])->assertRedirect();
        $o = Opportunity::latest('id')->first();
        CompanyMailPreference::create(['workspace_id' => $w->id, 'enabled' => true, 'offers' => true, 'messages' => true, 'locale' => 'ar', 'enabled_at' => now()->subMinute()]);
        [$supplier,$sw] = $this->company();
        $this->actingAs($supplier)->post(route('company.trade-offers.store', $o), ['request_key' => (string) Str::uuid(), 'quantity' => '10', 'unit' => 'piece', 'unit_price' => '19.99', 'currency' => 'AED', 'tax_basis' => 'exclusive', 'delivery_days' => 7, 'valid_until' => now('Asia/Dubai')->addDays(10)->toDateString(), 'payment_terms' => 'Payment after delivery.', 'notes' => 'Private supplier notes', 'confirmed' => 1])->assertRedirect();

        return [$buyer, $w, CompanyTradeOffer::latest('id')->firstOrFail(), $supplier, $sw];
    }

    public function test_collect_and_duplicate_workers_send_only_one_generic_email(): void
    {
        [$buyer,$w,$offer] = $this->fixture();
        $s = app(CompanyMailService::class);
        $this->assertSame(1, $s->collect());
        $this->assertSame(0, $s->collect());
        $d = CompanyMailDelivery::firstOrFail();
        $s->deliver($d->id);
        $s->deliver($d->id);
        Mail::assertSent(CompanyActivityMail::class, 1);
        Mail::assertSent(CompanyActivityMail::class, fn ($m) => $m->hasTo($buyer->email));
        $this->assertSame('sent', $d->fresh()->status);
    }

    public function test_disabled_master_has_no_collection_queue_or_mail(): void
    {
        $this->fixture();
        config(['company_mail.enabled' => false]);
        Queue::fake();
        $s = app(CompanyMailService::class);
        $this->assertSame(0, $s->collect());
        $this->assertSame(0, $s->enqueue());
        Queue::assertNothingPushed();
        Mail::assertNothingSent();
    }

    public function test_preferences_opt_in_and_reenable_do_not_replay_old_notifications(): void
    {
        [$buyer,$w] = $this->fixture();
        $p = CompanyMailPreference::first();
        $p->update(['enabled' => false]);
        $s = app(CompanyMailService::class);
        $this->assertSame(0, $s->collect());
        $this->travel(2)->minutes();
        $this->actingAs($buyer)->put(route('company.mail.update'), ['enabled' => 1, 'offers' => 1, 'messages' => 1, 'locale' => 'en'])->assertRedirect();
        $this->assertSame(0, $s->collect());
    }

    public function test_queue_is_database_company_mail_and_enqueue_does_not_duplicate_pending_jobs(): void
    {
        $this->fixture();
        $s = app(CompanyMailService::class);
        $s->collect();
        Queue::fake();
        $this->assertSame(1, $s->enqueue());
        $this->assertSame(0, $s->enqueue());
        Queue::assertPushed(SendCompanyMail::class, fn ($job) => $job->connection === 'database' && $job->queue === 'company-mail');
        Mail::assertNothingSent();
    }

    public function test_membership_customer_admin_and_verification_are_rechecked_before_send(): void
    {
        foreach (['member', 'unverified', 'admin', 'suspended', 'customer'] as $mode) {
            [$u,$w] = $this->fixture();
            $s = app(CompanyMailService::class);
            $s->collect();
            $d = CompanyMailDelivery::where('user_id', $u->id)->firstOrFail();
            if ($mode === 'member') {
                CompanyMember::where('user_id', $u->id)->update(['role' => 'member']);
            }
            if ($mode === 'unverified') {
                $u->email_verified_at = null;
                $u->save();
            }
            if ($mode === 'admin') {
                $u->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
            }
            if ($mode === 'suspended') {
                $w->update(['status' => 'suspended']);
            }
            if ($mode === 'customer') {
                Customer::whereKey($w->customer_id)->update(['is_active' => false]);
            }
            $s->deliver($d->id);
            $this->assertSame('skipped', $d->fresh()->status);
        }Mail::assertNothingSent();
    }

    public function test_disabled_type_and_preference_change_skip_pending_mail(): void
    {
        $this->fixture();
        $s = app(CompanyMailService::class);
        $p = CompanyMailPreference::first();
        $p->update(['offers' => false]);
        $this->assertSame(0, $s->collect());
        $p->update(['offers' => true]);
        $s->collect();
        $p->update(['enabled' => false]);
        $s->deliver(CompanyMailDelivery::first()->id);
        Mail::assertNothingSent();
    }

    public function test_read_notification_is_skipped(): void
    {
        [$u] = $this->fixture();
        $s = app(CompanyMailService::class);
        $s->collect();
        $u->unreadNotifications->markAsRead();
        $d = CompanyMailDelivery::first();
        $s->deliver($d->id);
        $this->assertSame('skipped', $d->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_unrelated_notification_and_foreign_offer_never_enter_outbox(): void
    {
        [$u,$w,$offer] = $this->fixture();
        $u->notifications()->delete();
        $u->notify(new AdminPlatformNotification('irrelevant', 'Private title', 'Private body', 'https://evil.test'));
        [$third,$thirdW] = $this->company();
        CompanyMailPreference::create(['workspace_id' => $thirdW->id, 'enabled' => true, 'offers' => true, 'messages' => true, 'locale' => 'en', 'enabled_at' => now()->subMinute()]);
        $third->notify(new AdminPlatformNotification('rfq.submitted', 'Title', 'Body', null, ['offer_id' => $offer->id]));
        $this->assertSame(0, app(CompanyMailService::class)->collect());
    }

    public function test_email_is_bilingual_generic_and_uses_only_trusted_app_url(): void
    {
        foreach (['ar', 'en'] as $locale) {
            $m = new CompanyActivityMail('rfq.message', 42, $locale);
            $html = $m->render();
            $this->assertStringContainsString('https://market.example.test/company/trade-offers/42/room?lang='.$locale, $html);
            $this->assertStringNotContainsString('Private supplier notes', $html);
            $this->assertStringNotContainsString('19.99', $html);
            $this->assertStringContainsString('dir="'.($locale === 'ar' ? 'rtl' : 'ltr').'"', $html);
        }
    }

    public function test_send_occurs_outside_its_database_transaction_and_preserves_offer(): void
    {
        [$u,$w,$offer] = $this->fixture();
        $s = app(CompanyMailService::class);
        $s->collect();
        $d = CompanyMailDelivery::first();
        $baseline = DB::transactionLevel();
        $before = $offer->getAttributes();
        $transport = \Mockery::mock();
        $transport->shouldReceive('to')->once()->with($u->email)->andReturnSelf();
        $transport->shouldReceive('send')->once()->andReturnUsing(function ($mail) use ($baseline) {
            $this->assertInstanceOf(CompanyActivityMail::class, $mail);
            $this->assertSame($baseline, DB::transactionLevel());
        });
        Mail::swap(\Mockery::mock()->shouldReceive('mailer')->with('array')->andReturn($transport)->getMock());
        $s->deliver($d->id);
        $this->assertSame('sent', $d->fresh()->status);
        $this->assertSame($before, $offer->fresh()->getAttributes());
        $this->assertDatabaseCount('company_billing_receipts', 0);
    }

    public function test_settings_are_scoped_and_protected_and_render_both_languages(): void
    {
        [$u,$w] = $this->company();
        [$other,$ow] = $this->company();
        foreach (['ar', 'en'] as $lang) {
            $this->actingAs($u)->get(route('company.mail.edit', ['lang' => $lang]))->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        }
        $data = ['enabled' => 1, 'offers' => 1, 'messages' => 0, 'locale' => 'en'];
        $this->actingAs($u)->put(route('company.mail.update'), $data)->assertRedirect();
        $this->assertDatabaseHas('company_mail_preferences', ['workspace_id' => $w->id, 'locale' => 'en']);
        $this->put(route('company.mail.update'), $data + ['workspace_id' => $ow->id])->assertSessionHasErrors('workspace_id');
        $this->actingAs($other)->get(route('company.mail.edit'))->assertOk();
        $this->assertDatabaseMissing('company_mail_preferences', ['workspace_id' => $ow->id]);
        CompanyMember::where('user_id', $u->id)->update(['role' => 'member']);
        $this->actingAs($u)->put(route('company.mail.update'), $data)->assertForbidden();
    }

    public function test_readiness_check_is_read_only(): void
    {
        $this->fixture();
        $this->artisan('platform:company-mail --check')->expectsOutputToContain('Read-only check')->assertSuccessful();
        $this->assertDatabaseCount('company_mail_deliveries', 0);
        Mail::assertNothingSent();
    }

    public function test_mail_failure_retries_with_backoff_and_stops_after_three_attempts(): void
    {
        $this->fixture();
        $s = app(CompanyMailService::class);
        $s->collect();
        $d = CompanyMailDelivery::firstOrFail();
        $transport = \Mockery::mock();
        $transport->shouldReceive('to')->andReturnSelf();
        $transport->shouldReceive('send')->times(3)->andThrow(new \RuntimeException('provider secret must never be stored'));
        Mail::swap(\Mockery::mock()->shouldReceive('mailer')->with('array')->andReturn($transport)->getMock());
        $s->deliver($d->id);
        $this->assertSame('pending', $d->fresh()->status);
        $this->assertSame(1, $d->fresh()->attempts);
        $s->deliver($d->id);
        $this->assertSame(1, $d->fresh()->attempts);
        $this->travel(6)->minutes();
        $s->deliver($d->id);
        $this->travel(11)->minutes();
        $s->deliver($d->id);
        $this->assertSame('failed', $d->fresh()->status);
        $this->assertSame(3, $d->fresh()->attempts);
        $this->assertSame('delivery_failed', $d->fresh()->last_error);
    }

    public function test_company_read_receipt_skips_message_email(): void
    {
        [$u,$w,$offer] = $this->fixture();
        $u->notifications()->delete();
        $u->notify(new AdminPlatformNotification('rfq.message', 'Private title', 'Secret conversation', null, ['offer_id' => $offer->id, 'message_id' => 123]));
        TradeOfferRead::create(['offer_id' => $offer->id, 'workspace_id' => $w->id, 'last_message_id' => 123, 'read_by' => $u->id, 'read_at' => now()]);
        $s = app(CompanyMailService::class);
        $s->collect();
        $d = CompanyMailDelivery::firstOrFail();
        $s->deliver($d->id);
        $this->assertSame('already_read', $d->fresh()->last_error);
        Mail::assertNothingSent();
    }

    public function test_processing_delivery_is_not_automatically_resent(): void
    {
        $this->fixture();
        $s = app(CompanyMailService::class);
        $s->collect();
        $d = CompanyMailDelivery::first();
        $d->update(['status' => 'processing']);
        $this->travel(1)->hours();
        $s->deliver($d->id);
        Queue::fake();
        $this->assertSame(0, $s->enqueue());
        Mail::assertNothingSent();
    }
}
