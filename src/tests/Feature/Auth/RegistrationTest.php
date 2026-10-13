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
}
