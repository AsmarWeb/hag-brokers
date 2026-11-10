<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use App\Notifications\MobileVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AccountCompletionTest extends TestCase
{
    use RefreshDatabase;

    private function device(User $u, array $scope = ['mobile:v1']): string
    {
        auth()->forgetGuards();
        $t = $u->createToken('mobile:test', $scope, now()->addDay());
        $this->withHeader('Authorization', 'Bearer '.$t->plainTextToken);

        return (string) $t->accessToken->id;
    }

    private function account(User $u): array
    {
        $this->device($u);

        return $this->getJson('/api/v1/auth/account')->assertOk()->json('data');
    }

    private function admin(): User
    {
        $u = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
        $u->addRole($role);

        return $u;
    }

    public function test_name_save_is_scoped_and_email_requires_password_and_reverification(): void
    {
        Notification::fake();
        $c = Customer::create(['company_name' => 'Immutable business', 'is_active' => true]);
        $u = User::factory()->create(['customer_id' => $c->id, 'password' => Hash::make('Current-pass-123')]);
        $other = $u->createToken('mobile:other', ['mobile:v1']);
        $record = $this->account($u);
        $payload = ['name' => 'New Name', 'email' => $u->email, 'revision' => $record['revision']];
        $a = $this->putJson('/api/v1/auth/account', $payload)->assertOk()->assertJsonPath('data.user.name', 'New Name')->assertJsonPath('data.user.email_verified', true)->assertJsonPath('meta.email_changed', false)->json('data');
        $payload = ['name' => 'New Name', 'email' => 'NEW@example.test', 'revision' => $a['revision']];
        $this->putJson('/api/v1/auth/account', $payload)->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->assertSame($u->email, $u->fresh()->email);
        $this->putJson('/api/v1/auth/account', $payload + ['current_password' => 'Current-pass-123'])->assertOk()->assertJsonPath('data.user.email', 'new@example.test')->assertJsonPath('data.user.email_verified', false);
        $this->assertNull($u->fresh()->email_verified_at);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $other->accessToken->id]);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        auth()->forgetGuards();
        $this->getJson('/api/v1/me')->assertUnauthorized();
        Notification::assertSentTo($u, MobileVerifyEmail::class);
        $this->assertDatabaseHas('customers', ['id' => $c->id, 'company_name' => 'Immutable business']);
        $this->assertSame($c->id, $u->fresh()->customer_id);
    }

    public function test_account_email_cannot_be_changed_to_a_disposable_domain(): void
    {
        $u = User::factory()->create();
        $record = $this->account($u);

        $this->putJson('/api/v1/auth/account', [
            'name' => $u->name,
            'email' => 'fake@mailinator.com',
            'revision' => $record['revision'],
            'current_password' => 'password',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertSame($u->email, $u->fresh()->email);
        $this->assertTrue($u->fresh()->hasVerifiedEmail());
    }

    public function test_stale_email_duplicate_and_privilege_fields_are_rejected(): void
    {
        $u = User::factory()->create();
        $other = User::factory()->create();
        $a = $this->account($u);
        $d = ['name' => 'Changed', 'email' => $u->email, 'revision' => $a['revision']];
        $this->putJson('/api/v1/auth/account', $d + ['role' => 'admin'])->assertUnprocessable();
        $this->putJson('/api/v1/auth/account', array_replace($d, ['email' => $other->email]))->assertUnprocessable()->assertJsonValidationErrors('email');
        $u->update(['name' => 'Web edit']);
        $this->putJson('/api/v1/auth/account', $d)->assertConflict();
        $this->assertSame('Web edit', $u->fresh()->name);
    }

    public function test_deletion_request_is_password_confirmed_idempotent_and_never_erases_business_or_tokens(): void
    {
        $u = User::factory()->create(['password' => Hash::make('Current-pass-123')]);
        $token = $this->device($u);
        $this->getJson('/api/v1/auth/deletion-request')->assertOk()->assertJsonPath('data', null);
        $this->postJson('/api/v1/auth/deletion-request', ['current_password' => 'wrong', 'confirm' => true])->assertUnprocessable();
        $this->postJson('/api/v1/auth/deletion-request', ['current_password' => 'Current-pass-123', 'confirm' => false])->assertUnprocessable();
        $d = ['current_password' => 'Current-pass-123', 'confirm' => true];
        $this->postJson('/api/v1/auth/deletion-request', $d)->assertCreated()->assertJsonPath('data.status', 'pending');
        $this->postJson('/api/v1/auth/deletion-request', $d)->assertStatus(429);
        $this->travel(61)->seconds();
        $this->postJson('/api/v1/auth/deletion-request', $d)->assertOk()->assertJsonPath('meta.created', false);
        $this->assertDatabaseCount('account_deletion_requests', 1);
        $this->assertDatabaseHas('users', ['id' => $u->id]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $token]);
        $this->assertStringNotContainsString('Current-pass-123', json_encode(DB::table('account_deletion_requests')->first()));
        $other = User::factory()->create();
        $this->device($other);
        $this->getJson('/api/v1/auth/deletion-request')->assertJsonPath('data', null);
    }

    public function test_auth_scope_and_administrative_separation(): void
    {
        $this->getJson('/api/v1/auth/account')->assertUnauthorized();
        $u = User::factory()->create();
        $this->device($u, ['other']);
        $this->getJson('/api/v1/auth/account')->assertForbidden();
        $admin = $this->admin();
        $this->device($admin);
        $this->getJson('/api/v1/auth/account')->assertForbidden();
        auth()->forgetGuards();
        $this->actingAs($u)->get('/admin/account-center')->assertForbidden();
    }

    public function test_admin_publishes_bilingual_escaped_policies_and_stale_changes_do_not_overwrite(): void
    {
        $admin = $this->admin();
        $d = ['version' => 0, 'published' => false, 'title_ar' => 'الخصوصية', 'title_en' => 'Privacy', 'body_ar' => 'نص عربي <script>bad()</script>', 'body_en' => 'English policy <script>bad()</script>'];
        $this->actingAs($admin)->put('/admin/account-center/legal/privacy', $d)->assertSessionHasNoErrors();
        $this->getJson('/api/v1/legal/privacy')->assertNotFound();
        $this->put('/admin/account-center/legal/privacy', array_replace($d, ['version' => 1, 'published' => true]))->assertSessionHasNoErrors();
        $this->getJson('/api/v1/legal/privacy?lang=ar')->assertOk()->assertJsonPath('data.title', 'الخصوصية')->assertJsonPath('data.version', 2);
        $this->get('/legal/privacy?lang=en')->assertOk()->assertDontSee('<script>bad()</script>', false)->assertSee('&lt;script&gt;', false);
        $this->put('/admin/account-center/legal/privacy', $d)->assertConflict();
        $this->assertDatabaseCount('platform_legal_audits', 2);
        $this->get('/admin/account-center')->assertOk();
    }

    public function test_admin_review_is_explicit_versioned_and_does_not_claim_deletion(): void
    {
        $u = User::factory()->create(['password' => Hash::make('Current-pass-123')]);
        $this->device($u);
        $this->postJson('/api/v1/auth/deletion-request', ['current_password' => 'Current-pass-123', 'confirm' => true])->assertCreated();
        $row = DB::table('account_deletion_requests')->first();
        auth()->forgetGuards();
        $this->actingAs($this->admin());
        $this->post('/admin/account-center/requests/'.$row->id.'/review', ['version' => 1])->assertRedirect();
        $this->post('/admin/account-center/requests/'.$row->id.'/review', ['version' => 1])->assertConflict();
        $this->device($u);
        $this->getJson('/api/v1/auth/deletion-request')->assertJsonPath('data.status', 'reviewing');
        $this->assertDatabaseHas('users', ['id' => $u->id]);
    }

    public function test_failed_policy_audit_rolls_back_document(): void
    {
        $this->actingAs($this->admin());
        DB::statement("CREATE TRIGGER legal_fail BEFORE INSERT ON platform_legal_audits BEGIN SELECT RAISE(ABORT, 'fail'); END");
        $this->put('/admin/account-center/legal/terms',['version' => 0, 'published' => true, 'title_ar' => 'شروط', 'title_en' => 'Terms', 'body_ar' => 'محتوى', 'body_en' => 'Content'])->assertStatus(500);
        $this->assertDatabaseCount('platform_legal_documents',0);
    }
}
