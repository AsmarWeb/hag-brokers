<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Notification::fake();
        $user = User::factory()->create();
        $user->addRole(Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']));
        return $user;
    }

    public function test_admin_can_open_user_forms_and_create_account_with_role(): void
    {
        $admin = $this->admin();
        $role = Role::firstOrCreate(['name' => 'sales'], ['display_name' => 'Sales']);
        $this->actingAs($admin)->get(route('admin.users.create'))->assertOk();
        $this->post(route('admin.users.store'), [
            'name' => 'New Team Member', 'email' => 'new-team@example.test', 'phone' => '+971501111111',
            'password' => 'Strong-password-123!', 'password_confirmation' => 'Strong-password-123!',
            'roles' => [$role->id],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $target = User::where('email', 'new-team@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('Strong-password-123!', $target->password));
        $this->assertTrue($target->roles()->whereKey($role->id)->exists());
        $this->assertFalse($target->hasVerifiedEmail());
        Notification::assertSentTo($target, VerifyEmail::class);
        $this->get(route('admin.users.show', $target))->assertOk();
        $this->get(route('admin.users.edit', $target))->assertOk();
    }

    public function test_duplicate_email_and_mismatched_password_are_rejected(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Invalid account', 'email' => $admin->email,
            'password' => 'Strong-password-123!', 'password_confirmation' => 'Different-password-456!',
        ])->assertSessionHasErrors(['email', 'password']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_updating_name_with_blank_password_preserves_existing_assignments(): void
    {
        $admin = $this->admin();

        $customer = Customer::create([
            'company_name' => 'Partial Update Test Customer',
        ]);
        $role = Role::firstOrCreate(
            ['name' => 'partial-update-role'],
            ['display_name' => 'Partial Update Role']
        );
        $permission = Permission::firstOrCreate(
            ['name' => 'partial-update-permission'],
            ['display_name' => 'Partial Update Permission']
        );

        $target = User::factory()->create(['customer_id' => $customer->id]);
        $target->syncRoles([$role->id]);
        $target->syncPermissions([$permission->id]);

        $oldHash = $target->password;

        $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => 'Updated Team Member',
            'email' => $target->email,
            'password' => '',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $updated = $target->fresh();

        $this->assertSame('Updated Team Member', $updated->name);
        $this->assertSame($oldHash, $updated->password);
        $this->assertSame($customer->id, $updated->customer_id);
        $this->assertTrue($updated->roles()->whereKey($role->id)->exists());
        $this->assertTrue($updated->permissions()->whereKey($permission->id)->exists());
    }

    public function test_admin_can_update_own_profile_without_resubmitting_roles(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => 'Updated Administrator',
            'email' => $admin->email,
            'password' => '',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('Updated Administrator', $admin->fresh()->name);
        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    public function test_admin_email_change_resets_verification_and_sends_a_new_link(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['email' => 'existing@example.test']);

        $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => 'New.Address@Example.Test',
            'phone' => $target->phone,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $target->refresh();
        $this->assertSame('new.address@example.test', $target->email);
        $this->assertNull($target->email_verified_at);
        Notification::assertSentTo($target, VerifyEmail::class);
    }

    public function test_admin_cannot_create_users_with_disposable_email_domains(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Disposable User',
            'email' => 'new@mailinator.com',
            'phone' => '+971501111111',
            'password' => 'Strong-password-123!',
            'password_confirmation' => 'Strong-password-123!',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_administrator_cannot_remove_own_admin_access_or_delete_self(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'roles' => [],
        ])->assertSessionHasErrors('roles');

        $this->assertTrue($admin->fresh()->hasRole('admin'));

        $this->delete(route('admin.users.destroy', $admin))->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_edit_form_markers_allow_intentionally_clearing_roles_and_permissions(): void
    {
        $admin = $this->admin();

        $role = Role::firstOrCreate(
            ['name' => 'clear-test-role'],
            ['display_name' => 'Clear Test Role']
        );
        $permission = Permission::firstOrCreate(
            ['name' => 'clear-test-permission'],
            ['display_name' => 'Clear Test Permission']
        );

        $target = User::factory()->create();
        $target->syncRoles([$role->id]);
        $target->syncPermissions([$permission->id]);

        $this->assertTrue($target->roles()->whereKey($role->id)->exists());
        $this->assertTrue($target->permissions()->whereKey($permission->id)->exists());

        $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'roles_present' => '1',
            'permissions_present' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $updated = $target->fresh();

        $this->assertFalse($updated->roles()->whereKey($role->id)->exists());
        $this->assertFalse($updated->permissions()->whereKey($permission->id)->exists());
    }

    public function test_admin_can_delete_unlinked_test_account(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();
        $this->actingAs($admin)->delete(route('admin.users.destroy', $target))
            ->assertRedirect(route('admin.users.index'))->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_admin_can_block_and_unblock_a_user_and_revoke_api_tokens(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();
        $token = $target->createToken('mobile:test');

        $this->actingAs($admin)->patch(route('admin.users.block', $target), [
            'blocked_reason' => 'Policy violation',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertNotNull($target->fresh()->blocked_at);
        $this->assertSame('Policy violation', $target->fresh()->blocked_reason);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);

        $this->patch(route('admin.users.unblock', $target))
            ->assertRedirect()->assertSessionHas('success');

        $this->assertNull($target->fresh()->blocked_at);
        $this->assertNull($target->fresh()->blocked_reason);
    }

    public function test_admin_cannot_block_themselves_or_another_administrator(): void
    {
        $admin = $this->admin();
        $otherAdmin = $this->admin();

        $this->actingAs($admin)->patch(route('admin.users.block', $admin))
            ->assertSessionHas('error');
        $this->patch(route('admin.users.block', $otherAdmin))
            ->assertSessionHas('error');

        $this->assertNull($admin->fresh()->blocked_at);
        $this->assertNull($otherAdmin->fresh()->blocked_at);
    }

    public function test_blocked_user_cannot_sign_in(): void
    {
        $user = User::factory()->create([
            'email' => 'blocked@example.test',
            'password' => Hash::make('Strong-password-123!'),
            'blocked_at' => now(),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Strong-password-123!',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => $user->email]);
    }

    public function test_successful_web_login_records_the_client_ip(): void
    {
        $user = User::factory()->create();

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.25'])
            ->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'last_login_ip' => '203.0.113.25',
        ]);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_blocked_user_is_logged_out_of_existing_web_sessions(): void
    {
        $user = User::factory()->create(['blocked_at' => now()]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_regular_user_cannot_create_edit_or_delete_accounts(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();
        $this->actingAs($user)->get(route('admin.users.create'))->assertForbidden();
        $this->post(route('admin.users.store'), [])->assertForbidden();
        $this->get(route('admin.users.edit', $target))->assertForbidden();
        $this->put(route('admin.users.update', $target), [])->assertForbidden();
        $this->delete(route('admin.users.destroy', $target))->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }
}
