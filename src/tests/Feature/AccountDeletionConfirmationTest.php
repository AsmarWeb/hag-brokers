<?php

namespace Tests\Feature;

use App\Mail\AccountDeletionConfirmationMail;
use App\Models\User;
use App\Services\AccountDeletion\AccountDeletionConfirmationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccountDeletionConfirmationTest extends TestCase
{
    use RefreshDatabase;

    private function deletionRequest(User $user): int
    {
        return (int) DB::table('account_deletion_requests')->insertGetId([
            'user_id' => $user->id,
            'requested_email' => $user->email,
            'status' => 'reviewing',
            'version' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function sendConfirmation(User $user): string
    {
        Mail::fake();

        $id = $this->deletionRequest($user);

        app(AccountDeletionConfirmationService::class)->send($id, 'en');

        $url = null;

        Mail::assertSent(
            AccountDeletionConfirmationMail::class,
            function (AccountDeletionConfirmationMail $mail) use (&$url): bool {
                $url = $mail->confirmationUrl;

                return true;
            }
        );

        $this->assertNotNull($url);

        return (string) parse_url($url, PHP_URL_PATH);
    }

    public function test_confirmation_requires_explicit_post_and_token_can_only_be_used_once(): void
    {
        $user = User::factory()->create();
        $path = $this->sendConfirmation($user);

        $row = DB::table('account_deletion_requests')->first();

        $this->assertSame('confirmation_sent', $row->status);
        $this->assertNotNull($row->email_sent_at);
        $this->assertNotNull($row->confirmation_token_hash);

        // Opening the email link must not confirm the request.
        $this->get($path)->assertOk();

        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $row->id,
            'status' => 'confirmation_sent',
            'confirmed_at' => null,
        ]);

        // Explicit user action confirms the request.
        $this->post($path, ['lang' => 'en'])->assertOk();

        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $row->id,
            'status' => 'email_confirmed',
        ]);

        $confirmed = DB::table('account_deletion_requests')->find($row->id);

        $this->assertNotNull($confirmed->confirmed_at);
        $this->assertNull($confirmed->confirmation_token_hash);
        $this->assertNull($confirmed->confirmation_expires_at);

        // The same token cannot be reused.
        $this->post($path, ['lang' => 'en'])->assertGone();

        // Confirmation does not delete the account.
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_expired_confirmation_link_is_rejected(): void
    {
        $user = User::factory()->create();
        $path = $this->sendConfirmation($user);

        DB::table('account_deletion_requests')->update([
            'confirmation_expires_at' => now()->subMinute(),
        ]);

        $this->get($path)->assertGone();
        $this->post($path, ['lang' => 'en'])->assertGone();

        $this->assertDatabaseHas('account_deletion_requests', [
            'user_id' => $user->id,
            'status' => 'confirmation_sent',
            'confirmed_at' => null,
        ]);
    }

    public function test_confirmation_is_rejected_until_email_was_successfully_sent(): void
    {
        $user = User::factory()->create();

        $token = str_repeat('a', 64);
        $path = route('account-deletion.confirm.show', ['token' => $token]);

        DB::table('account_deletion_requests')->insert([
            'user_id' => $user->id,
            'requested_email' => $user->email,
            'confirmation_token_hash' => hash('sha256', $token),
            'confirmation_expires_at' => now()->addHours(24),
            'email_sent_at' => null,
            'status' => 'confirmation_sent',
            'version' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get($path)->assertGone();
        $this->post($path, ['lang' => 'en'])->assertGone();

        $this->assertDatabaseHas('account_deletion_requests', [
            'user_id' => $user->id,
            'status' => 'confirmation_sent',
            'confirmed_at' => null,
            'email_sent_at' => null,
        ]);
    }
}
