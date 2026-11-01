<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
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
            'name' => 'New Team Member', 'email' => 'new-team@example.test',
            'password' => 'new-password-123', 'password_confirmation' => 'new-password-123',
            'roles' => [$role->id],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $target = User::where('email', 'new-team@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('new-password-123', $target->password));
        $this->assertTrue($target->roles()->whereKey($role->id)->exists());
        $this->get(route('admin.users.show', $target))->assertOk();
        $this->get(route('admin.users.edit', $target))->assertOk();
    }

    public function test_duplicate_email_and_mismatched_password_are_rejected(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Invalid account', 'email' => $admin->email,
            'password' => 'new-password-123', 'password_confirmation' => 'different-password',
        ])->assertSessionHasErrors(['email', 'password']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_updating_name_with_blank_password_keeps_existing_password(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();
        $oldHash = $target->password;
        $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => 'Updated Team Member', 'email' => $target->email, 'password' => '',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('Updated Team Member', $target->fresh()->name);
        $this->assertSame($oldHash, $target->fresh()->password);
    }

    public function test_administrator_cannot_remove_own_admin_access_or_delete_self(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email, 'roles' => [],
        ])->assertSessionHasErrors('roles');
        $this->delete(route('admin.users.destroy', $admin))->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_unlinked_test_account(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();
        $this->actingAs($admin)->delete(route('admin.users.destroy', $target))
            ->assertRedirect(route('admin.users.index'))->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
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
