<?php

namespace Tests\Feature\Auth;

use App\Models\CompanyMember;
use App\Models\CompanyWorkspace;
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

    public function test_registration_creates_a_personal_account_without_creating_a_company_or_phone(): void
    {
        $response = $this->post('/register', $this->registrationPayload('personal@example.test'));

        $response->assertSessionHasNoErrors()->assertRedirect(route('login'));
        $user = User::where('email', 'personal@example.test')->firstOrFail();

        $this->assertSame('Personal User', $user->name);
        $this->assertNull($user->customer_id);
        $this->assertNull($user->phone);
        $this->assertTrue($user->phone_verification_required);
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertGuest();
        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('company_workspaces', 0);
        $this->assertDatabaseCount('company_members', 0);
    }

    public function test_new_personal_account_receives_welcome_notification(): void
    {
        $this->post('/register', $this->registrationPayload('welcome@example.test'))
            ->assertSessionHasNoErrors();

        $user = User::where('email', 'welcome@example.test')->firstOrFail();
        $welcome = $user->notifications()->where('data->type', 'account.welcome')->get();

        $this->assertCount(1, $welcome);
        $this->assertSame('Welcome to HAG Brokers', $welcome->first()->data['title']);
        $this->assertNull($welcome->first()->read_at);
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_registration_notifies_admin_and_welcomes_user_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->addRole(\App\Models\Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        ));

        $this->post('/register', $this->registrationPayload('notification@example.test'))
            ->assertSessionHasNoErrors();

        $user = User::where('email', 'notification@example.test')->firstOrFail();
        $this->assertSame(1, $user->notifications()->where('data->type', 'account.welcome')->count());
        $this->assertSame(1, $admin->notifications()->where('data->type', 'user.created')->count());
    }

    public function test_disposable_email_domains_are_rejected_before_account_creation(): void
    {
        $this->post('/register', $this->registrationPayload('signup@mailinator.com'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_company_and_phone_fields_are_not_accepted_during_personal_registration(): void
    {
        $payload = $this->registrationPayload('legacy-payload@example.test');
        $payload['company_name'] = 'Must not be created';
        $payload['phone'] = '+971501234567';

        $this->post('/register', $payload)->assertSessionHasErrors(['company_name', 'phone']);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_email_verified_user_can_sign_in_before_phone_setup(): void
    {
        $user = User::factory()->create([
            'email' => 'verified@example.test',
            'password' => bcrypt('Strong-password-123!'),
            'email_verified_at' => now(),
            'phone' => null,
            'phone_verification_required' => true,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Strong-password-123!',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_unverified_email_user_cannot_sign_in(): void
    {
        $user = User::factory()->create([
            'email' => 'unverified@example.test',
            'password' => bcrypt('Strong-password-123!'),
            'email_verified_at' => null,
            'phone' => null,
            'phone_verification_required' => true,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Strong-password-123!',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    private function registrationPayload(string $email): array
    {
        return [
            'name' => 'Personal User',
            'email' => $email,
            'password' => 'Strong-password-123!',
            'password_confirmation' => 'Strong-password-123!',
        ];
    }
}
