<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Port;
use App\Models\Quotation;
use App\Models\Role;
use App\Models\User;
use App\Notifications\AdminPlatformNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class QuotationOperationsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );

        $user->addRole($role);

        return $user;
    }

    private function quotation(): Quotation
    {
        $customer = Customer::create([
            'company_name' => 'Operations Test',
            'country_code' => 'AE',
        ]);

        $origin = Port::create(['name' => 'Origin Test', 'country_code' => 'AE']);
        $destination = Port::create(['name' => 'Destination Test', 'country_code' => 'AE']);

        return Quotation::create([
            'customer_id' => $customer->id,
            'origin_port_id' => $origin->id,
            'destination_port_id' => $destination->id,
            'cargo_type' => 'General',
            'container_type' => '40ft',
            'quantity' => 1,
            'reference_no' => 'QT-OPS-' . uniqid(),
        ]);
    }

    public function test_admin_assigns_staff_and_creates_history(): void
    {
        Notification::fake();

        $admin = $this->admin();
        $staff = $this->admin();
        $quotation = $this->quotation();

        $this->actingAs($admin)
            ->patch(route('admin.quotations.assign', $quotation), [
                'assigned_to' => $staff->id,
            ])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertEquals($staff->id, $quotation->fresh()->assigned_to);

        $this->assertDatabaseHas('quotation_history', [
            'quotation_id' => $quotation->id,
            'event' => 'assigned',
            'new_value' => (string) $staff->id,
        ]);

        Notification::assertSentTo(
            $staff,
            AdminPlatformNotification::class,
            fn ($notification) => $notification->type === 'quotation.assigned'
        );
    }

    public function test_status_change_is_recorded_once(): void
    {
        Notification::fake();

        $admin = $this->admin();
        $quotation = $this->quotation();

        $this->actingAs($admin)
            ->patch(route('admin.quotations.status', $quotation), [
                'status' => 'processing',
            ])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->patch(route('admin.quotations.status', $quotation), [
                'status' => 'processing',
            ])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseCount('quotation_history', 1);

        $this->assertDatabaseHas('quotation_history', [
            'quotation_id' => $quotation->id,
            'event' => 'status_changed',
            'old_value' => 'pending',
            'new_value' => 'processing',
        ]);
    }

    public function test_customer_cannot_assign_quotation(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $quotation = $this->quotation();
        $staff = $this->admin();

        $this->actingAs($customer)
            ->patch(route('admin.quotations.assign', $quotation), [
                'assigned_to' => $staff->id,
            ])
            ->assertForbidden();

        $this->assertNull($quotation->fresh()->assigned_to);
    }
}
