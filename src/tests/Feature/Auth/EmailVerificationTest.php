<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_administrators_can_sign_in_without_email_or_phone_verification(): void
    {
        $admin = User::factory()->unverified()->create([
            'password' => bcrypt('Admin-password-123!'),
            'phone_verified_at' => null,
            'phone_verification_required' => true,
        ]);
        $admin->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']));

        $this->assertTrue($admin->hasVerifiedEmail());
        $this->assertTrue($admin->hasVerifiedPhone());
        $this->assertTrue($admin->hasVerifiedAccount());

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'Admin-password-123!',
        ])->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($admin);
    }

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', __('auth.email_verified_login'));
    }

    public function test_legacy_email_verification_does_not_require_phone_verification(): void
    {
        $user = User::factory()->unverified()->create([
            'phone_verified_at' => null,
            'phone_verification_required' => false,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->get($verificationUrl)->assertRedirect(route('login'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertFalse($user->fresh()->hasVerifiedPhone());
    }

    public function test_guest_can_request_a_resend_without_revealing_account_existence(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->post(route('verification.send'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status', __('auth.verification_email_sent'));
        Notification::assertSentTo($user, VerifyEmail::class);

        $this->post(route('verification.send'), ['email' => 'missing@example.test'])
            ->assertRedirect()
            ->assertSessionHas('status', __('auth.verification_email_sent'));
    }

    public function test_authenticated_verification_notice_can_resend_without_email_input(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->post(route('verification.send'))
            ->assertRedirect()
            ->assertSessionHas('status', __('auth.verification_email_sent'));

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->get($verificationUrl)->assertForbidden();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
