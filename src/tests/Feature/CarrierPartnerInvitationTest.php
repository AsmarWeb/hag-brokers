<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\CarrierInvitation;
use App\Models\Role;
use App\Models\User;
use App\Models\Vessel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CarrierPartnerInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitation_links_only_the_verified_recipient_to_the_carriers_fleet(): void
    {
        $carrier = Carrier::create(['name' => 'Gulf Partner', 'code' => 'PARTNER', 'is_active' => true]);
        $other = Carrier::create(['name' => 'Other Carrier', 'code' => 'OTHER', 'is_active' => true]);
        Vessel::create(['name' => 'Partner vessel', 'carrier_id' => $carrier->id]);
        Vessel::create(['name' => 'Unrelated vessel', 'carrier_id' => $other->id]);
        $recipient = User::factory()->create();
        $outsider = User::factory()->create();
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin']));

        $issue = route('admin.carriers.invitations.store', $carrier);
        $this->actingAs($outsider)->post($issue, ['email' => $recipient->email])->assertForbidden();
        $this->actingAs($admin)->get(route('admin.carriers.show', $carrier))->assertOk()->assertSee($issue);
        $this->post($issue, ['email' => strtoupper($recipient->email)])
            ->assertRedirect(route('admin.carriers.show', $carrier))->assertSessionHas('carrier_invitation_url');
        $link = route('carrier.invitation.show', basename(session('carrier_invitation_url')));
        $invitation = CarrierInvitation::sole();
        $this->assertSame(hash('sha256', basename($link)), $invitation->token_hash);

        $this->actingAs($outsider)->get($link)->assertForbidden();
        $this->post($link)->assertForbidden();
        $this->get(route('carrier.fleet'))->assertForbidden();
        $this->assertNull($invitation->fresh()->accepted_at);
        $recipient->forceFill(['email_verified_at' => null])->save();
        $this->actingAs($recipient)->get($link)->assertRedirect(route('verification.notice'));
        $this->postJson($link)->assertForbidden();

        $recipient->markEmailAsVerified();
        $this->actingAs($recipient)->get($link)->assertOk()->assertSee($carrier->name)
            ->assertHeader('Referrer-Policy', 'no-referrer');
        $this->post($link)->assertRedirect(route('carrier.fleet'));
        $this->assertEquals($recipient->id, $invitation->fresh()->user_id);
        $this->post($link)->assertNotFound();
        $this->get(route('dashboard'))->assertOk()->assertSee(route('carrier.fleet'));
        $this->get(route('carrier.fleet', ['carrier_id' => $other->id]))
            ->assertOk()->assertSee('Partner vessel')->assertDontSee('Unrelated vessel');

        $carrier->update(['is_active' => false]);
        $this->get(route('carrier.fleet'))->assertForbidden();
    }

    public function test_disposable_email_addresses_cannot_receive_carrier_invitations(): void
    {
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin']));
        $carrier = Carrier::create(['name' => 'Partner', 'code' => 'PARTNER', 'is_active' => true]);

        $this->actingAs($admin)->post(route('admin.carriers.invitations.store', $carrier), [
            'email' => 'contact@mailinator.com',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('carrier_invitations', 0);
    }

    public function test_revoking_access_also_closes_outstanding_links_for_that_representative(): void
    {
        $recipient = User::factory()->create();
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin']));
        $carrier = Carrier::create(['name' => 'Partner', 'code' => 'PARTNER', 'is_active' => true]);
        $other = Carrier::create(['name' => 'Other', 'code' => 'OTHER', 'is_active' => true]);
        $issue = route('admin.carriers.invitations.store', $carrier);
        $this->actingAs($admin)->post($issue, ['email' => $recipient->email])->assertRedirect();
        $firstLink = route('carrier.invitation.show', basename(session('carrier_invitation_url')));
        $first = CarrierInvitation::sole();
        $this->post($issue, ['email' => $recipient->email])->assertRedirect();
        $secondLink = route('carrier.invitation.show', basename(session('carrier_invitation_url')));
        $this->actingAs($recipient)->post($firstLink)->assertRedirect(route('carrier.fleet'));
        $revoke = route('admin.carriers.invitations.revoke', [$carrier, $first]);
        $this->patch($revoke)->assertForbidden();

        $this->actingAs($admin)->patch(route('admin.carriers.invitations.revoke', [$other, $first]))->assertNotFound();
        $this->patch($revoke)->assertRedirect(route('admin.carriers.show', $carrier));
        $this->get(route('admin.carriers.show', $carrier))->assertOk()->assertSee(__('carrier_partner.statuses.revoked'));
        $this->actingAs($recipient)->get(route('carrier.fleet'))->assertForbidden();
        $this->post($secondLink)->assertNotFound();
        $this->assertSame('revoked', $first->fresh()->status());
    }

    public function test_invited_representative_can_register_verify_and_access_the_read_only_api(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $carrier = Carrier::create(['name' => 'Partner', 'code' => 'PARTNER', 'is_active' => true]);
        $other = Carrier::create(['name' => 'Other', 'code' => 'OTHER', 'is_active' => true]);
        Vessel::create(['name' => 'Visible vessel', 'carrier_id' => $carrier->id]);
        Vessel::create(['name' => 'Private vessel', 'carrier_id' => $other->id]);
        $token = str_repeat('b', 64);
        $email = 'partner@example.test';
        $password = 'Long-Partner-Password-123!';
        CarrierInvitation::create(['carrier_id' => $carrier->id, 'email' => $email,
            'token_hash' => hash('sha256', $token), 'expires_at' => now()->addDay()]);
        $join = route('carrier.onboarding.show', $token);
        $accept = route('carrier.invitation.show', $token);

        $this->get($join)->assertOk()->assertSee($email);
        $this->post($join, ['name' => 'Partner', 'phone' => '+971501234567', 'password' => $password, 'password_confirmation' => $password,
            'email' => 'spoof@example.test'])->assertRedirect(route('login'));
        $user = User::where('email', $email)->sole();
        $this->assertNull($user->customer_id);
        $this->assertFalse($user->hasVerifiedEmail());
        \Illuminate\Support\Facades\Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);
        $this->postJson($accept)->assertUnauthorized();
        $verification = \Illuminate\Support\Facades\URL::temporarySignedRoute('verification.verify',
            now()->addMinutes(10), ['id' => $user->id, 'hash' => sha1($email)]);
        $this->get($verification)->assertRedirect(route('phone.verification.notice', ['email' => $email]));
        $user->forceFill(['phone_verified_at' => now()])->save();
        auth()->forgetGuards();
        $this->post('/login', ['email' => $email, 'password' => $password])
            ->assertRedirect($accept);
        $this->post($accept)->assertRedirect(route('carrier.fleet'));
        $this->patch(route('carrier.profile.update'), ['name' => 'Verified Representative', 'phone' => '+971 50 000 0000'])
            ->assertRedirect();
        $this->assertSame('Verified Representative', $user->fresh()->name);

        // Changing the phone invalidates verification and must block token issuance.
        $this->postJson(route('carrier.token.issue'), ['password' => $password])->assertForbidden();

        // Simulate successful verification of the newly registered phone number.
        $user->forceFill(['phone_verified_at' => now()])->save();
        $this->postJson(route('carrier.token.issue'), ['password' => 'wrong'])->assertUnprocessable();
        $this->post(route('carrier.token.issue'), ['password' => $password])->assertRedirect();
        $key = session('carrier_api_token');
        $this->assertNotEmpty($key);
        $this->get(route('carrier.fleet'))->assertOk()->assertSee($key);
        $this->get(route('carrier.fleet'))->assertOk()->assertDontSee($key);

        \Illuminate\Support\Facades\Auth::guard('web')->logout();
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->getJson('/api/v1/carrier/fleet')->assertUnauthorized();
        $this->withToken($key)->getJson('/api/v1/carrier/fleet?carrier_id='.$other->id)->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Visible vessel');

        $otherKey = $user->createToken('unscoped', ['*'])->plainTextToken;
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->withToken($otherKey)->getJson('/api/v1/carrier/fleet')->assertForbidden();
        $carrier->update(['is_active' => false]);
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->withToken($key)->getJson('/api/v1/carrier/fleet')->assertForbidden();
    }

    public function test_expired_invitation_cannot_be_accepted(): void
    {
        $user = User::factory()->create();
        $carrier = Carrier::create(['name' => 'Gulf Partner', 'code' => 'PARTNER', 'is_active' => true]);
        $token = str_repeat('a', 64);
        $invitation = CarrierInvitation::create([
            'carrier_id' => $carrier->id, 'email' => $user->email,
            'token_hash' => hash('sha256', $token), 'expires_at' => now()->subMinute(),
        ]);

        $this->actingAs($user)->post(route('carrier.invitation.accept', ['token' => $token]))->assertNotFound();
        $this->assertNull($invitation->fresh()->user_id);
    }
}
