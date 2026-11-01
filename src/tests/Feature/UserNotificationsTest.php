<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function createNotification(User $user): string
    {
        $id = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $id,
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id' => $user->getKey(),
            'data' => json_encode([
                'title' => 'Test notification',
                'message' => 'Testing HAG Brokers notifications',
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    public function test_guest_cannot_view_notifications(): void
    {
        $this->get(route('notifications.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_view_notifications_page(): void
    {
        $user = User::factory()->create();

        $this->createNotification($user);

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Test notification');
    }

    public function test_user_can_mark_own_notification_as_read(): void
    {
        $user = User::factory()->create();
        $id = $this->createNotification($user);

        $this->actingAs($user)
            ->patch(route('notifications.markRead', $id))
            ->assertRedirect();

        $this->assertNotNull(
            DB::table('notifications')->where('id', $id)->value('read_at')
        );
    }

    public function test_user_can_mark_all_own_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $ownId = $this->createNotification($user);
        $otherId = $this->createNotification($other);

        $this->actingAs($user)
            ->patch(route('notifications.markAllRead'))
            ->assertRedirect();

        $this->assertNotNull(
            DB::table('notifications')->where('id', $ownId)->value('read_at')
        );

        $this->assertNull(
            DB::table('notifications')->where('id', $otherId)->value('read_at')
        );
    }

    public function test_user_cannot_access_other_users_notification(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $id = $this->createNotification($other);

        $this->actingAs($user)
            ->get(route('notifications.show', $id))
            ->assertNotFound();

        $this->actingAs($user)
            ->patch(route('notifications.markRead', $id))
            ->assertNotFound();

        $this->actingAs($user)
            ->delete(route('notifications.destroy', $id))
            ->assertNotFound();

        $this->assertDatabaseHas('notifications', ['id' => $id]);
    }

    public function test_user_can_delete_own_notification(): void
    {
        $user = User::factory()->create();
        $id = $this->createNotification($user);

        $this->actingAs($user)
            ->delete(route('notifications.destroy', $id))
            ->assertRedirect();

        $this->assertDatabaseMissing('notifications', ['id' => $id]);
    }

    public function test_notification_redirects_to_same_origin_url(): void
    {
        config()->set('app.url', 'https://hagbrokers.net');

        $user = User::factory()->create();
        $id = $this->createNotification($user);

        DB::table('notifications')->where('id', $id)->update([
            'data' => json_encode([
                'type' => 'account.welcome',
                'title' => 'Welcome',
                'message' => 'Welcome to HAG Brokers',
                'url' => 'https://hagbrokers.net/dashboard',
            ]),
        ]);

        $this->actingAs($user)
            ->get(route('notifications.show', $id))
            ->assertRedirect('/dashboard');

        $this->assertNotNull(
            DB::table('notifications')->where('id', $id)->value('read_at')
        );
    }

    public function test_notification_rejects_external_redirect(): void
    {
        config()->set('app.url', 'https://hagbrokers.net');

        $user = User::factory()->create();
        $id = $this->createNotification($user);

        DB::table('notifications')->where('id', $id)->update([
            'data' => json_encode([
                'type' => 'admin.message',
                'title' => 'External link',
                'message' => 'Test external redirect',
                'url' => 'https://example.com/phishing',
            ]),
        ]);

        $this->actingAs($user)
            ->get(route('notifications.show', $id))
            ->assertRedirect(route('notifications.index'));
    }
}
