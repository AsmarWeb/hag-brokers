<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Auth\PhoneVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhoneVerificationConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_stale_user_model_cannot_verify_phone_after_number_changes(): void
    {
        $user = User::factory()->create([
            'phone' => '+971501234567',
            'phone_verification_required' => true,
            'phone_verified_at' => null,
        ]);

        // Simulate a verification request that loaded the user before a concurrent phone update.
        $staleUser = User::query()->findOrFail($user->getKey());

        DB::table('phone_verifications')->insert([
            'user_id' => $user->getKey(),
            'phone' => '+971501234567',
            'code_hash' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // The database now contains a different phone number, while the request still holds the old model.
        DB::table('users')->where('id', $user->getKey())->update([
            'phone' => '+971509876543',
            'phone_verified_at' => null,
        ]);

        $service = new PhoneVerificationService();

        $this->assertFalse($service->verify($staleUser, '123456'));
        $this->assertNull($user->fresh()->phone_verified_at);
        $this->assertDatabaseHas('phone_verifications', [
            'user_id' => $user->getKey(),
            'phone' => '+971501234567',
        ]);
    }

    public function test_phone_confirmation_code_is_sent_to_verified_email(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'phone' => '+971501234567',
            'phone_verification_required' => true,
            'phone_verified_at' => null,
        ]);

        $service = new PhoneVerificationService();

        $this->assertTrue($service->sendSafely($user));

        $sentCode = null;

        \Illuminate\Support\Facades\Notification::assertSentOnDemand(
            \App\Notifications\PhoneVerificationCode::class,
            function ($notification, $channels, $notifiable) use ($user, &$sentCode): bool {
                $property = new \ReflectionProperty($notification, 'code');
                $sentCode = $property->getValue($notification);

                return $channels === ['mail']
                    && $notifiable->routeNotificationFor('mail') === $user->email;
            }
        );

        $challenge = DB::table('phone_verifications')
            ->where('user_id', $user->getKey())
            ->first();

        $this->assertNotNull($challenge);
        $this->assertNotNull($sentCode);
        $this->assertTrue(Hash::check($sentCode, $challenge->code_hash));
        $this->assertSame(0, (int) $challenge->attempts);
    }


    public function test_blocked_user_cannot_access_web_phone_setup_routes(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'blocked_at' => now(),
            'phone' => '+971501234567',
            'phone_verification_required' => true,
        ]);

        $this->actingAs($user)
            ->get(route('phone.verification.notice'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_email_code_completes_account_setup_without_verifying_phone_ownership(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'phone' => '+971501234567',
            'phone_verification_required' => true,
            'phone_verified_at' => null,
        ]);

        DB::table('phone_verifications')->insert([
            'user_id' => $user->getKey(),
            'phone' => '+971501234567',
            'code_hash' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = new PhoneVerificationService();

        $this->assertTrue($service->verify($user, '123456'));

        $freshUser = $user->fresh();

        $this->assertFalse((bool) $freshUser->phone_verification_required);
        $this->assertTrue($freshUser->hasCompletedPhoneSetup());
        $this->assertTrue($freshUser->hasVerifiedAccount());
        $this->assertFalse($freshUser->hasVerifiedPhone());
        $this->assertNull($freshUser->phone_verified_at);

        $this->assertDatabaseMissing('phone_verifications', [
            'user_id' => $user->getKey(),
        ]);
    }

}
