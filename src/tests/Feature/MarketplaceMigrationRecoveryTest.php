<?php
namespace Tests\Feature;
use App\Models\Customer;
use App\Models\User;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;
class MarketplaceMigrationRecoveryTest extends TestCase {
 use RefreshDatabase;
 private function migration() { return require database_path('migrations/2026_11_02_160000_connect_company_marketplace.php'); }
 public function test_repeated_up_preserves_existing_rows_and_unique_index(): void {
  $this->artisan('platform:install-plans');
  $c=Customer::create(['company_name'=>'Migration company','is_active'=>true]);$u=User::factory()->create(['customer_id'=>$c->id]);$w=app(CompanyWorkspaceService::class)->activateNewRegistration($c,$u);
  DB::table('company_marketplace_requests')->insert(['company_workspace_id'=>$w->id,'request_key'=>(string)Str::uuid(),'action'=>'publish','request_hash'=>str_repeat('a',64),'resource_id'=>7,'usage_month'=>'2026-11','created_at'=>now()]);
  $before=DB::table('company_marketplace_requests')->first();
  $this->migration()->up();$this->migration()->up();
  $this->assertEquals($before,DB::table('company_marketplace_requests')->first());
  $this->assertTrue(Schema::hasIndex('company_marketplace_requests',['company_workspace_id','request_key'],'unique'));
 }
 public function test_partial_table_without_indexes_or_foreign_key_is_completed_without_deleting_rows(): void {
  $this->artisan('platform:install-plans');$c=Customer::create(['company_name'=>'Recovery company','is_active'=>true]);$u=User::factory()->create(['customer_id'=>$c->id]);$w=app(CompanyWorkspaceService::class)->activateNewRegistration($c,$u);
  Schema::drop('company_marketplace_requests');
  Schema::create('company_marketplace_requests',function(Blueprint $t){$t->id();$t->unsignedBigInteger('company_workspace_id');$t->uuid('request_key');$t->string('action',20);$t->string('request_hash',64);$t->unsignedBigInteger('resource_id');$t->string('usage_month',7);$t->dateTime('created_at');});
  DB::table('company_marketplace_requests')->insert(['company_workspace_id'=>$w->id,'request_key'=>(string)Str::uuid(),'action'=>'publish','request_hash'=>str_repeat('b',64),'resource_id'=>8,'usage_month'=>'2026-11','created_at'=>now()]);
  $before=DB::table('company_marketplace_requests')->first();$this->migration()->up();
  $this->assertEquals($before,DB::table('company_marketplace_requests')->first());
  $this->assertTrue(Schema::hasIndex('company_marketplace_requests',['company_workspace_id','request_key'],'unique'));
  $this->assertTrue(Schema::hasIndex('company_marketplace_requests',['company_workspace_id','action','usage_month']));
  $this->assertTrue(collect(Schema::getForeignKeys('company_marketplace_requests'))->contains(fn($key)=>$key['columns']===['company_workspace_id']));
 }
}
