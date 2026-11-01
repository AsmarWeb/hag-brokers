<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Notifications\AdminPlatformNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_notifications_by_business_type(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $admin->addRole(
            Role::firstOrCreate(
                ['name' => 'admin'],
                ['display_name' => 'Administrator']
            )
        );

        $admin->notify(new AdminPlatformNotification(
            type: 'user.created',
            title: 'Registration Alpha',
            message: 'New company registered',
            url: '/admin/users'
        ));

        $admin->notify(new AdminPlatformNotification(
            type: 'quotation.created',
            title: 'Quotation Beta',
            message: 'New quotation received',
            url: '/admin/quotations'
        ));

        $this->actingAs($admin)
            ->get(route('admin.notifications.index', [
                'search' => 'user.created',
            ]))
            ->assertOk()
            ->assertSee('Registration Alpha')
            ->assertDontSee('Quotation Beta');

        $this->assertSame(
            2,
            $admin->notifications()->count()
        );
    }
}
