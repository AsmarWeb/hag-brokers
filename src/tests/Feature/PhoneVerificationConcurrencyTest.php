<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Auth\PhoneVerificationService;
use App\Services\Auth\TwilioSmsGateway;
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

        $service = new PhoneVerificationService(new TwilioSmsGateway());

        $this->assertFalse($service->verify($staleUser, '123456'));
        $this->assertNull($user->fresh()->phone_verified_at);
        $this->assertDatabaseHas('phone_verifications', [
            'user_id' => $user->getKey(),
            'phone' => '+971501234567',
        ]);
    }
}
