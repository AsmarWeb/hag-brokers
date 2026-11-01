<?php

namespace Tests\Feature\Auth;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_new_company_can_register(): void
    {
        $response = $this->post('/register', [
            'company_name' => 'Test Trading LLC',
            'contact_person' => 'Test User',
            'phone' => '+971501234567',
            'country_code' => 'AE',
            'city' => 'Dubai',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors();

        $user = User::where('email', 'test@example.com')->firstOrFail();

        $customer = Customer::findOrFail($user->customer_id);

        $this->assertSame('Test Trading LLC', $customer->company_name);
        $this->assertSame('Test User', $user->name);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_new_company_receives_welcome_notification(): void
    {
        $this->post('/register', [
            'company_name' => 'Welcome Trading LLC',
            'contact_person' => 'Welcome User',
            'phone' => '+971501234567',
            'country_code' => 'AE',
            'city' => 'Dubai',
            'email' => 'welcome@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasNoErrors();

        $user = User::where('email', 'welcome@example.test')->firstOrFail();

        $welcome = $user->notifications()
            ->where('data->type', 'account.welcome')
            ->get();

        $this->assertCount(1, $welcome);
        $this->assertSame('Welcome to HAG Brokers', $welcome->first()->data['title']);
        $this->assertNull($welcome->first()->read_at);
    }

    public function test_registration_notifies_admin_and_welcomes_customer_once(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $admin->addRole(
            \App\Models\Role::firstOrCreate(
                ['name' => 'admin'],
                ['display_name' => 'Administrator']
            )
        );

        $response = $this->post('/register', [
            'company_name' => 'Notification Test LLC',
            'contact_person' => 'Notification Customer',
            'phone' => '+971501234567',
            'country_code' => 'AE',
            'city' => 'Dubai',
            'email' => 'notification-customer@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors();

        $customer = User::where(
            'email',
            'notification-customer@example.test'
        )->firstOrFail();

        $this->assertSame(
            1,
            $customer->notifications()
                ->where('data->type', 'account.welcome')
                ->count()
        );

        $this->assertSame(
            1,
            $admin->notifications()
                ->where('data->type', 'user.created')
                ->count()
        );
    }
}
