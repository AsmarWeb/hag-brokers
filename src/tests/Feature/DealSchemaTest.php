<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
class DealSchemaTest extends TestCase {
    use RefreshDatabase;
    public function test_revenue_tables_exist(): void {
        foreach (['deals','deal_agreements','deal_commissions'] as $table) {
            $this->assertTrue(Schema::hasTable($table),$table);
        }
        $this->assertTrue(Schema::hasColumns('deals',['collection_model','quotation_id','opportunity_id','supplier_cost','platform_revenue']));
    }
}
