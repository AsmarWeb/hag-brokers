<?php

namespace Tests\Feature;

use App\Mail\AccountDeletionConfirmationMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccountDeletionAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Admin']
        );

        $user->addRole($role);

        return $user;
    }

    private function deletionRequest(User $user, string $status = 'reviewing'): int
    {
        return (int) DB::table('account_deletion_requests')->insertGetId([
            'user_id' => $user->id,
            'requested_email' => $user->email,
            'status' => $status,
            'version' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_admin_can_send_confirmation_email(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $requestId = $this->deletionRequest($user);

        $this->actingAs($this->admin())
            ->post("/admin/account-center/requests/{$requestId}/send-confirmation")
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(
            AccountDeletionConfirmationMail::class,
            fn ($mail) => $mail->hasTo($user->email)
        );

        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $requestId,
            'status' => 'confirmation_sent',
        ]);

        $this->assertNotNull(
            DB::table('account_deletion_requests')
                ->where('id', $requestId)
                ->value('email_sent_at')
        );
    }

    public function test_regular_user_cannot_send_confirmation_email(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $requestId = $this->deletionRequest($user);

        $this->actingAs($user)
            ->post("/admin/account-center/requests/{$requestId}/send-confirmation")
            ->assertForbidden();

        Mail::assertNothingSent();

        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $requestId,
            'status' => 'reviewing',
        ]);
    }

    public function test_admin_cannot_send_confirmation_for_pending_request(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $requestId = $this->deletionRequest($user, 'pending');

        $this->actingAs($this->admin())
            ->post("/admin/account-center/requests/{$requestId}/send-confirmation")
            ->assertRedirect()
            ->assertSessionHas('error');

        Mail::assertNothingSent();

        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $requestId,
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_reject_request_with_reason_and_invalidate_confirmation_link(): void
    {
        $user = User::factory()->create();
        $requestId = $this->deletionRequest($user, 'confirmation_sent');
        DB::table('account_deletion_requests')->where('id', $requestId)->update([
            'confirmation_token_hash' => hash('sha256', 'test-confirmation-token'),
            'confirmation_expires_at' => now()->addHour(),
        ]);

        $this->actingAs($this->admin())
            ->post("/admin/account-center/requests/{$requestId}/reject", [
                'version' => 2,
                'rejection_reason' => 'The request requires additional account verification.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $requestId,
            'status' => 'rejected',
            'rejection_reason' => 'The request requires additional account verification.',
            'confirmation_token_hash' => null,
            'confirmation_expires_at' => null,
            'version' => 3,
        ]);

        $this->get('/account-deletion/confirm/test-confirmation-token')->assertStatus(404);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_admin_rejection_requires_reason_and_rejects_stale_or_confirmed_requests(): void
    {
        $user = User::factory()->create();
        $requestId = $this->deletionRequest($user, 'reviewing');

        $this->actingAs($this->admin())
            ->post("/admin/account-center/requests/{$requestId}/reject", [
                'version' => 2,
                'rejection_reason' => '',
            ])
            ->assertSessionHasErrors('rejection_reason');

        $this->post("/admin/account-center/requests/{$requestId}/reject", [
            'version' => 1,
            'rejection_reason' => 'A valid reason for rejection.',
        ])->assertConflict();

        DB::table('account_deletion_requests')->where('id', $requestId)->update([
            'status' => 'email_confirmed',
        ]);

        $this->post("/admin/account-center/requests/{$requestId}/reject", [
            'version' => 2,
            'rejection_reason' => 'A valid reason for rejection.',
        ])->assertConflict();

        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $requestId,
            'status' => 'email_confirmed',
        ]);
    }


    public function test_admin_can_complete_confirmed_deletion_and_preserve_request(): void
    {
        $user = User::factory()->create();
        $requestId = $this->deletionRequest($user, 'email_confirmed');

        $this->actingAs($this->admin())
            ->post("/admin/account-center/requests/{$requestId}/complete-deletion")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);

        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $requestId,
            'user_id' => null,
            'status' => 'completed',
        ]);

        $this->assertNotNull(
            DB::table('account_deletion_requests')
                ->where('id', $requestId)
                ->value('completed_at')
        );
    }

    public function test_admin_cannot_complete_deletion_of_an_administrator_account(): void
    {
        $target = User::factory()->create();
        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Admin']
        );
        $target->addRole($role);

        $requestId = $this->deletionRequest($target, 'email_confirmed');

        $this->actingAs($this->admin())
            ->post("/admin/account-center/requests/{$requestId}/complete-deletion")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
        ]);

        $this->assertDatabaseHas('account_deletion_requests', [
            'id' => $requestId,
            'status' => 'email_confirmed',
        ]);
    }

}
