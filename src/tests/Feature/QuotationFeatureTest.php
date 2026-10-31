<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Quotation;
use App\Models\Role;
use App\Notifications\AdminPlatformNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QuotationFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function customerUser(string $suffix): User
    {
        $customer = Customer::create([
            'company_name' => "Company {$suffix}",
            'contact_person' => "Contact {$suffix}",
            'email' => "company-{$suffix}@example.test",
            'country_code' => 'AE',
            'is_active' => true,
        ]);

        return User::factory()->create([
            'customer_id' => $customer->id,
            'role' => 'customer',
        ]);
    }

    private function port(string $name): int
    {
        return (int) DB::table('ports')->insertGetId([
            'name' => $name,
            'country_code' => 'AE',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function validPayload(): array
    {
        return [
            'origin_port_id' => $this->port('Origin Port'),
            'destination_port_id' => $this->port('Destination Port'),
            'cargo_type' => 'General cargo',
            'container_type' => '40HC',
            'quantity' => 2,
            'weight' => 1250.50,
        ];
    }

    private function quotationFor(User $user, array $ports, string $reference): Quotation
    {
        return Quotation::create([
            'customer_id' => $user->customer_id,
            'origin_port_id' => $ports['origin_port_id'],
            'destination_port_id' => $ports['destination_port_id'],
            'cargo_type' => 'General cargo',
            'container_type' => '40HC',
            'quantity' => 1,
            'reference_no' => $reference,
        ]);
    }

    public function test_guest_cannot_access_customer_quotation_pages(): void
    {
        $this->get(route('quotations.index'))->assertRedirect(route('login'));
        $this->get(route('quotations.create'))->assertRedirect(route('login'));
    }

    public function test_customer_can_submit_quotation_and_it_is_assigned_to_their_company(): void
    {
        $user = $this->customerUser('one');
        $payload = $this->validPayload();

        $response = $this->actingAs($user)->post(route('quotations.store'), $payload);

        $response->assertSessionHasNoErrors();
        $quotation = Quotation::query()->sole();
        $response->assertRedirect(route('quotations.show', $quotation));
        $this->assertSame($user->customer_id, $quotation->customer_id);
        $this->assertSame('pending', $quotation->status);
        $this->assertMatchesRegularExpression(
            '/^QT-\d{4}-[A-F0-9]{32}$/',
            $quotation->reference_no
        );
        $this->assertSame(2, $quotation->quantity);
    }

    public function test_customer_cannot_override_company_id_on_submission(): void
    {
        $user = $this->customerUser('one');
        $other = $this->customerUser('two');
        $payload = $this->validPayload();
        $payload['customer_id'] = $other->customer_id;
        $payload['status'] = 'approved';
        $payload['total_charge'] = 1;

        $this->actingAs($user)
            ->post(route('quotations.store'), $payload)
            ->assertSessionHasNoErrors();

        $quotation = Quotation::query()->sole();
        $this->assertSame($user->customer_id, $quotation->customer_id);
        $this->assertSame('pending', $quotation->status);
        $this->assertNull($quotation->total_charge);
    }

    public function test_invalid_quotation_is_rejected_without_creating_record(): void
    {
        $user = $this->customerUser('one');

        $this->actingAs($user)->post(route('quotations.store'), [
            'origin_port_id' => 999999,
            'destination_port_id' => 999998,
            'cargo_type' => '',
            'container_type' => '',
            'quantity' => 0,
            'weight' => -1,
            'preferred_date' => 'not-a-date',
        ])->assertSessionHasErrors([
            'origin_port_id',
            'destination_port_id',
            'cargo_type',
            'container_type',
            'quantity',
            'weight',
            'preferred_date',
        ]);

        $this->assertDatabaseCount('quotations', 0);
    }

    public function test_customer_can_view_own_quotation_but_not_another_customers(): void
    {
        $owner = $this->customerUser('one');
        $other = $this->customerUser('two');
        $ports = $this->validPayload();
        $quotation = $this->quotationFor($owner, $ports, 'QT-2026-00001');

        $this->actingAs($owner)
            ->get(route('quotations.show', $quotation))
            ->assertOk();

        $this->actingAs($other)
            ->get(route('quotations.show', $quotation))
            ->assertForbidden();
    }

    public function test_customer_index_only_contains_their_own_quotations(): void
    {
        $owner = $this->customerUser('one');
        $other = $this->customerUser('two');
        $ports = $this->validPayload();
        $own = $this->quotationFor($owner, $ports, 'QT-2026-00001');
        $foreign = $this->quotationFor($other, $ports, 'QT-2026-00002');

        $response = $this->actingAs($owner)->get(route('quotations.index'));

        $response->assertOk()->assertViewHas('quotations', function ($paginator) use ($own, $foreign) {
            $ids = $paginator->getCollection()->pluck('id')->all();
            return in_array($own->id, $ids, true) && ! in_array($foreign->id, $ids, true);
        });
    }

    public function test_same_origin_and_destination_is_rejected(): void
    {
        $user = $this->customerUser('same-port');
        $payload = $this->validPayload();
        $payload['destination_port_id'] = $payload['origin_port_id'];

        $this->actingAs($user)
            ->post(route('quotations.store'), $payload)
            ->assertSessionHasErrors('destination_port_id');

        $this->assertDatabaseCount('quotations', 0);
    }

    public function test_user_without_customer_cannot_submit_quotation(): void
    {
        $user = User::factory()->create([
            'customer_id' => null,
        ]);

        $payload = $this->validPayload();

        $this->actingAs($user)
            ->post(route('quotations.store'), $payload)
            ->assertSessionHasErrors('customer');

        $this->assertDatabaseCount('quotations', 0);
    }



    public function test_admin_receives_new_quotation_notification(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );

        $admin->addRole($role);

        $customer = $this->customerUser('notify-admin');
        $payload = $this->validPayload();

        $this->actingAs($customer)
            ->post(route('quotations.store'), $payload)
            ->assertSessionHasNoErrors();

        Notification::assertSentTo(
            $admin,
            AdminPlatformNotification::class,
            fn ($notification) =>
                $notification->type === 'quotation.created'
        );
    }

    public function test_customer_receives_status_change_notification_only_once(): void
    {
        Notification::fake();

        $customer = $this->customerUser('notify-status');
        $payload = $this->validPayload();

        $quotation = $this->quotationFor(
            $customer,
            $payload,
            'QT-2026-STATUS-TEST'
        );

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );

        $admin->addRole($role);

        $this->actingAs($admin)
            ->patch(route('admin.quotations.status', $quotation), [
                'status' => 'processing',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('processing', $quotation->fresh()->status);

        Notification::assertSentToTimes(
            $customer,
            AdminPlatformNotification::class,
            1
        );

        $this->actingAs($admin)
            ->patch(route('admin.quotations.status', $quotation), [
                'status' => 'processing',
            ])
            ->assertSessionHasNoErrors();

        Notification::assertSentToTimes(
            $customer,
            AdminPlatformNotification::class,
            1
        );
    }

    public function test_admin_quotation_dashboard_receives_summary_statistics(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );

        $admin->addRole($role);

        $this->actingAs($admin)
            ->get(route('admin.quotations.index'))
            ->assertOk()
            ->assertViewHas('quotationStats', function ($stats) {
                return isset(
                    $stats['total'],
                    $stats['pending'],
                    $stats['processing'],
                    $stats['approved'],
                    $stats['completed']
                );
            });
    }


}
