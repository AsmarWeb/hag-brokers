<?php

namespace Tests\Feature;

use App\Mail\AccountDeletionRequestVerificationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountDeletionPublicRequestTest extends TestCase
{
    use RefreshDatabase;

    private const NEUTRAL_MESSAGE =
        'If this email address is associated with an account, you will receive an email with verification instructions.';

    private function tokenFromSentMail(): string
    {
        $mail = Mail::sent(AccountDeletionRequestVerificationMail::class)->first();

        $this->assertNotNull($mail, 'The verification email was not sent.');

        $matched = preg_match(
            '#/account-deletion/request/([A-Za-z0-9]{64})(?:\?|$)#',
            $mail->verificationUrl,
            $matches
        );

        $this->assertSame(1, $matched, 'The verification URL does not contain a valid token.');

        return $matches[1];
    }

    public function test_public_deletion_request_form_is_available_without_authentication(): void
    {
        $this->get('/account-deletion')->assertOk();
    }

    public function test_unknown_email_gets_neutral_response_without_creating_records(): void
    {
        Mail::fake();
        app()->setLocale('en');

        $this->from('/account-deletion')
            ->post('/account-deletion', [
                'email' => 'unknown@example.test',
            ])
            ->assertRedirect('/account-deletion')
            ->assertSessionHas('status', self::NEUTRAL_MESSAGE);

        $this->assertDatabaseCount('account_deletion_email_verifications', 0);
        $this->assertDatabaseCount('account_deletion_requests', 0);

        Mail::assertNothingSent();
    }

    public function test_registered_email_requires_explicit_confirmation_before_request_is_created(): void
    {
        Mail::fake();
        app()->setLocale('en');

        $user = User::factory()->create();

        $this->from('/account-deletion')
            ->post('/account-deletion', [
                'email' => $user->email,
            ])
            ->assertRedirect('/account-deletion')
            ->assertSessionHas('status', self::NEUTRAL_MESSAGE);

        $this->assertDatabaseHas('account_deletion_email_verifications', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        $verification = DB::table('account_deletion_email_verifications')
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($verification->token_hash);
        $this->assertNotNull($verification->email_sent_at);
        $this->assertNotNull($verification->expires_at);
        $this->assertNull($verification->verified_at);

        // Sending the email alone must not create a deletion request.
        $this->assertDatabaseCount('account_deletion_requests', 0);

        Mail::assertSent(AccountDeletionRequestVerificationMail::class);

        $token = $this->tokenFromSentMail();

        // Opening the link displays the confirmation page but does not consume it.
        $this->get('/account-deletion/request/'.$token.'?lang=en')->assertOk();

        $this->assertDatabaseCount('account_deletion_requests', 0);

        // Only the explicit POST creates the pending request.
        $this->post('/account-deletion/request/'.$token)
            ->assertOk();

        $this->assertDatabaseHas('account_deletion_requests', [
            'user_id' => $user->id,
            'requested_email' => $user->email,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('account_deletion_email_verifications', [
            'id' => $verification->id,
            'token_hash' => null,
        ]);

        $this->assertNotNull(
            DB::table('account_deletion_email_verifications')
                ->where('id', $verification->id)
                ->value('verified_at')
        );

        // A consumed token cannot be used again.
        $this->post('/account-deletion/request/'.$token)->assertStatus(410);

        $this->assertDatabaseCount('account_deletion_requests', 1);
    }

    public function test_expired_verification_token_cannot_create_a_deletion_request(): void
    {
        $user = User::factory()->create();
        $token = Str::random(64);

        DB::table('account_deletion_email_verifications')->insert([
            'user_id' => $user->id,
            'email' => $user->email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->subMinute(),
            'email_sent_at' => now()->subMinutes(61),
            'verified_at' => null,
            'created_at' => now()->subHours(2),
            'updated_at' => now(),
        ]);

        $this->get('/account-deletion/request/'.$token)->assertStatus(410);
        $this->post('/account-deletion/request/'.$token)->assertStatus(410);

        $this->assertDatabaseCount('account_deletion_requests', 0);
    }

    public function test_verification_link_is_invalidated_when_user_email_changes(): void
    {
        Mail::fake();
        app()->setLocale('en');

        $user = User::factory()->create();

        $this->post('/account-deletion', [
            'email' => $user->email,
        ])->assertRedirect('/account-deletion');

        $token = $this->tokenFromSentMail();

        $user->update([
            'email' => 'changed-address@example.test',
        ]);

        $this->post('/account-deletion/request/'.$token)
            ->assertStatus(410);

        $this->assertDatabaseCount('account_deletion_requests', 0);

        $verification = DB::table('account_deletion_email_verifications')
            ->where('user_id', $user->id)
            ->first();

        $this->assertNull($verification->token_hash);
        $this->assertNull($verification->expires_at);
        $this->assertNull($verification->email_sent_at);
    }

    public function test_public_flow_does_not_duplicate_an_existing_deletion_request(): void
    {
        Mail::fake();
        app()->setLocale('en');

        $user = User::factory()->create();

        DB::table('account_deletion_requests')->insert([
            'user_id' => $user->id,
            'requested_email' => $user->email,
            'status' => 'reviewing',
            'version' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->from('/account-deletion')
            ->post('/account-deletion', [
                'email' => $user->email,
            ])
            ->assertRedirect('/account-deletion')
            ->assertSessionHas('status', self::NEUTRAL_MESSAGE);

        $this->assertDatabaseCount('account_deletion_requests', 1);
        $this->assertDatabaseCount('account_deletion_email_verifications', 0);

        Mail::assertNothingSent();
    }
}
