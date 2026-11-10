<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class MobileAccountSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function account(): User
    {
        return User::factory()->create(['password' => Hash::make('Old-password-123!')]);
    }

    private function bearer(User $u, array $abilities = ['mobile:v1']): array
    {
        auth()->forgetGuards();

        return ['Authorization' => 'Bearer '.$u->createToken('mobile:current', $abilities, now()->addDay())->plainTextToken];
    }

    private function payload(array $x = []): array
    {
        return array_replace(['current_password' => 'Old-password-123!', 'password' => 'New-password-456!', 'password_confirmation' => 'New-password-456!'], $x);
    }

    public function test_valid_individual_login_returns_a_usable_scoped_token(): void
    {
        $u = $this->account();
        // Legacy accounts predate the phone-verification gate and remain email-verified only.
        $u->forceFill(['phone_verified_at' => null, 'phone_verification_required' => false])->save();
        $r = $this->postJson('/api/v1/auth/login', ['email' => $u->email, 'password' => 'Old-password-123!', 'device_name' => 'phone'])->assertOk()->assertJsonPath('data.user.id', $u->id);
        $token = $r->json('data.access_token');
        auth()->forgetGuards();
        $this->withHeaders(['Authorization' => 'Bearer '.$token])->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.email', $u->email);
    }

    public function test_unverified_accounts_cannot_login_or_use_existing_mobile_tokens(): void
    {
        $u = User::factory()->unverified()->create([
            'password' => Hash::make('Old-password-123!'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $u->email, 'password' => 'Old-password-123!', 'device_name' => 'phone',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->withHeaders($this->bearer($u))->getJson('/api/v1/me')->assertForbidden();
    }

    public function test_disposable_email_domains_are_rejected_for_registration_and_login(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'account_type' => 'individual',
            'name' => 'Disposable User',
            'email' => 'signup@mailinator.com',
            'password' => 'Strong-password-123!',
            'password_confirmation' => 'Strong-password-123!',
            'device_name' => 'phone',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('users', 0);
        $u = User::factory()->create([
            'email' => 'legacy@mailinator.com',
            'password' => Hash::make('Old-password-123!'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $u->email,
            'password' => 'Old-password-123!',
            'device_name' => 'phone',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_admin_credentials_explain_web_only_access_without_issuing_token(): void
    {
        $u = $this->account();
        $u->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        $this->postJson('/api/v1/auth/login', ['email' => $u->email, 'password' => 'Old-password-123!', 'device_name' => 'phone'])->assertForbidden()->assertJsonPath('error_code', 'AUTH_ADMIN_WEB_ONLY');
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_wrong_admin_password_does_not_reveal_role(): void
    {
        $u = $this->account();
        $u->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']));
        $this->postJson('/api/v1/auth/login', ['email' => $u->email, 'password' => 'incorrect', 'device_name' => 'phone'])->assertUnprocessable()->assertJsonMissingPath('error_code')->assertJsonValidationErrors('email');
    }

    public function test_password_change_preserves_current_token_and_revokes_other_tokens(): void
    {
        $u = $this->account();
        $other = $u->createToken('mobile:other', ['mobile:v1'])->plainTextToken;
        $headers = $this->bearer($u);
        $this->withHeaders($headers)->putJson('/api/v1/auth/password', $this->payload())->assertNoContent();
        $this->assertTrue(Hash::check('New-password-456!', $u->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertNull(PersonalAccessToken::findToken($other));
        auth()->forgetGuards();
        $this->withHeaders($headers)->getJson('/api/v1/me')->assertOk();
    }

    public function test_wrong_current_password_preserves_hash_and_tokens(): void
    {
        $u = $this->account();
        $headers = $this->bearer($u);
        $hash = $u->password;
        $this->withHeaders($headers)->putJson('/api/v1/auth/password', $this->payload(['current_password' => 'wrong']))->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->assertSame($hash, $u->fresh()->password);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_weak_mismatched_and_unchanged_passwords_are_rejected(): void
    {
        $u = $this->account();
        $headers = $this->bearer($u);
        foreach ([['password' => 'short', 'password_confirmation' => 'short'], ['password_confirmation' => 'mismatch'], ['password' => 'Old-password-123!', 'password_confirmation' => 'Old-password-123!']] as $x) {
            $this->withHeaders($headers)->putJson('/api/v1/auth/password', $this->payload($x))->assertUnprocessable()->assertJsonValidationErrors('password');
        }
        $this->assertTrue(Hash::check('Old-password-123!', $u->fresh()->password));
    }

    public function test_guest_and_wrong_scope_tokens_cannot_change_password(): void
    {
        $this->putJson('/api/v1/auth/password', $this->payload())->assertUnauthorized();
        $u = $this->account();
        $this->withHeaders($this->bearer($u, ['other']))->putJson('/api/v1/auth/password', $this->payload())->assertForbidden();
    }

    public function test_readiness_check_is_readonly_and_detects_missing_mobile_settings(): void
    {
        $this->artisan('platform:mobile-check')->expectsOutputToContain('mobile_app_settings: READY')->assertSuccessful();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseCount('mobile_app_settings', 0);
        Schema::drop('mobile_app_settings');
        $this->artisan('platform:mobile-check')->expectsOutputToContain('mobile_app_settings: MISSING')->assertFailed();
    }

    public function test_company_registration_reports_missing_company_fields_without_creating_records(): void
    {
        $this->postJson('/api/v1/auth/register', ['account_type' => 'company', 'name' => 'Owner', 'email' => 'new@example.test', 'password' => 'Strong-password-123', 'password_confirmation' => 'Strong-password-123', 'device_name' => 'phone'])
            ->assertUnprocessable()->assertJsonValidationErrors(['company_name', 'phone', 'country_code']);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_registration_email_conflict_is_a_field_error_and_does_not_create_a_second_account(): void
    {
        $user = $this->account();
        $this->postJson('/api/v1/auth/register', ['account_type' => 'individual', 'name' => 'Owner', 'email' => $user->email, 'password' => 'Strong-password-123', 'password_confirmation' => 'Strong-password-123', 'device_name' => 'phone'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
