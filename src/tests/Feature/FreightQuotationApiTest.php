<?php

namespace Tests\Feature;

use App\Models\CompanyMember;
use App\Models\Customer;
use App\Models\Port;
use App\Models\Quotation;
use App\Models\User;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class FreightQuotationApiTest extends TestCase
{
    use RefreshDatabase;

    private function company(): array
    {
        $this->artisan('platform:install-plans');
        $c = Customer::create(['company_name' => 'Freight client', 'is_active' => true]);
        $u = User::factory()->create(['customer_id' => $c->id]);

        return [$u, app(CompanyWorkspaceService::class)->activateNewRegistration($c, $u)];
    }

    private function device(User $u, array $scope = ['mobile:v1']): void
    {
        auth()->forgetGuards();
        $this->withHeader('Authorization', 'Bearer '.$u->createToken('mobile:freight', $scope, now()->addDay())->plainTextToken);
    }

    private function payload(): array
    {
        $a = Port::create(['name' => 'Jebel Ali', 'is_active' => true]);
        $b = Port::create(['name' => 'Hamburg', 'is_active' => true]);

        return ['request_key' => (string) Str::uuid(), 'origin_port_id' => $a->id, 'destination_port_id' => $b->id, 'cargo_type' => 'Equipment', 'container_type' => '40HC', 'quantity' => 2, 'weight' => '1200.50', 'preferred_date' => '2027-01-12'];
    }

    public function test_exact_company_request_retry_and_financial_isolation(): void
    {
        Notification::fake();
        [$u] = $this->company();
        $this->device($u);
        $d = $this->payload();
        $id = $this->postJson('/api/v1/freight/quotations', $d)->assertCreated()->assertJsonPath('data.status', 'pending')->assertJsonMissingPath('data.broker_margin')->json('data.id');
        $this->postJson('/api/v1/freight/quotations', $d)->assertOk()->assertJsonPath('data.id', $id)->assertJsonPath('meta.created', false);
        $this->postJson('/api/v1/freight/quotations', array_replace($d, ['quantity' => 3]))->assertUnprocessable()->assertJsonValidationErrors('request_key');
        $this->assertDatabaseCount('quotations', 1);
        $this->assertDatabaseCount('freight_request_keys', 1);
        $this->assertDatabaseHas('quotations', ['id' => $id, 'customer_id' => $u->customer_id, 'quantity' => 2, 'total_charge' => null]);
        foreach (['deals', 'issued_invoices', 'invoice_payments', 'company_billing_receipts'] as $table) {
            if (Schema::hasTable($table)) {
                $this->assertDatabaseCount($table, 0);
            }
        }
    }

    public function test_cross_company_and_suspension_are_enforced(): void
    {
        [$u,$w] = $this->company();
        $this->device($u);
        $d = $this->payload();
        $id = $this->postJson('/api/v1/freight/quotations', $d)->assertCreated()->json('data.id');
        [$other] = $this->company();
        $this->device($other);
        $this->getJson('/api/v1/freight/quotations')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/freight/quotations/'.$id)->assertNotFound();
        $this->device($u);
        $w->update(['status' => 'suspended']);
        $this->getJson('/api/v1/freight/quotations')->assertForbidden();
        $this->getJson('/api/v1/freight/ports')->assertForbidden();
        $this->postJson('/api/v1/freight/quotations', $d)->assertForbidden();
    }

    public function test_guest_individual_member_unverified_and_wrong_scope_are_blocked(): void
    {
        $this->getJson('/api/v1/freight/quotations')->assertUnauthorized();
        $u = User::factory()->create(['customer_id' => null]);
        $this->device($u);
        $this->getJson('/api/v1/freight/quotations')->assertForbidden();
        auth()->forgetGuards();
        $this->actingAs($u)->get('/quotations')->assertRedirect(route('company.onboarding.show'));
        [$leader,$w] = $this->company();
        $member = User::factory()->create(['customer_id' => $leader->customer_id]);
        CompanyMember::create(['user_id' => $member->id, 'company_workspace_id' => $w->id, 'role' => 'member']);
        $this->device($member);
        $this->getJson('/api/v1/freight/quotations')->assertForbidden();
        $leader->forceFill(['email_verified_at' => null])->save();
        $this->device($leader);
        $this->getJson('/api/v1/freight/ports')->assertForbidden();
        $this->device($member, ['other']);
        $this->getJson('/api/v1/freight/quotations')->assertForbidden();
    }

    public function test_validation_and_privilege_injection_do_not_write(): void
    {
        [$u] = $this->company();
        $this->device($u);
        $d = $this->payload();
        $this->postJson('/api/v1/freight/quotations', array_replace($d, ['destination_port_id' => $d['origin_port_id']]))->assertUnprocessable();
        Port::find($d['destination_port_id'])->update(['is_active' => false]);
        $this->postJson('/api/v1/freight/quotations', $d)->assertUnprocessable();
        Port::find($d['destination_port_id'])->update(['is_active' => true]);
        foreach (['customer_id', 'status', 'total_charge', 'broker_margin', 'reference_no'] as $field) {
            $this->postJson('/api/v1/freight/quotations', $d + [$field => '1'])->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->postJson('/api/v1/freight/quotations', array_replace($d, ['quantity' => 10001, 'weight' => -1, 'preferred_date' => 'bad']))->assertUnprocessable();
        $this->assertDatabaseCount('quotations', 0);
        $this->assertDatabaseCount('freight_request_keys', 0);
    }

    public function test_manager_can_read_bounded_private_safe_responses(): void
    {
        [$u,$w] = $this->company();
        $this->device($u);
        $d = $this->payload();
        $id = $this->postJson('/api/v1/freight/quotations', $d)->assertCreated()->json('data.id');
        Quotation::find($id)->update(['total_charge' => 9000]);
        $manager = User::factory()->create(['customer_id' => $u->customer_id]);
        CompanyMember::create(['user_id' => $manager->id, 'company_workspace_id' => $w->id, 'role' => 'manager']);
        $this->device($manager);
        $this->getJson('/api/v1/freight/quotations/'.$id.'?lang=ar')->assertOk()->assertHeader('Content-Language', 'ar')->assertJsonMissingPath('data.total_charge')->assertJsonMissingPath('data.customer_id');
        $this->getJson('/api/v1/freight/quotations?per_page=1000')->assertUnprocessable();
        $this->getJson('/api/v1/freight/ports?search=Jebel')->assertOk()->assertJsonCount(1, 'data')->assertJsonMissingPath('data.0.latitude');
        $this->getJson('/api/v1/freight/ports?search=%25')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_failed_receipt_rolls_back_request(): void
    {
        [$u] = $this->company();
        $this->device($u);
        $d = $this->payload();
        DB::statement("CREATE TRIGGER freight_fail BEFORE INSERT ON freight_request_keys BEGIN SELECT RAISE(ABORT, 'fail'); END");
        $this->postJson('/api/v1/freight/quotations', $d)->assertStatus(500);
        $this->assertDatabaseCount('quotations',0);
        $this->assertDatabaseCount('freight_request_keys',0);
    }
}
