<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class DocumentMigrationRollbackTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): object
    {
        return require database_path(
            'migrations/2026_10_27_200200_extend_documents_for_polymorphic_links.php'
        );
    }

    public function test_rollback_refuses_documents_without_shipments(): void
    {
        DB::table('documents')->insert([
            'name' => 'Customer Document',
            'type' => 'contract',
            'file_path' => 'documents/customer.pdf',
            'shipment_id' => null,
            'documentable_type' => 'customer',
            'documentable_id' => 1,
            'status' => 'active',
        ]);

        try {
            $this->migration()->down();

            $this->fail('Rollback should have been rejected.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString(
                'Cannot roll back document relationships',
                $exception->getMessage()
            );
        }

        $this->assertTrue(
            Schema::hasColumn('documents', 'documentable_type')
        );

        $this->assertSame(1, DB::table('documents')->count());
    }

    public function test_rollback_restores_original_schema_when_documents_are_empty(): void
    {
        $this->migration()->down();

        $this->assertFalse(
            Schema::hasColumn('documents', 'documentable_type')
        );

        $this->assertFalse(
            Schema::hasColumn('documents', 'documentable_id')
        );

        $this->assertFalse(
            Schema::hasColumn('documents', 'status')
        );

        $this->assertTrue(
            Schema::hasColumn('documents', 'shipment_id')
        );
    }

    public function test_rollback_restores_shipment_id_not_null_constraint(): void
    {
        $this->migration()->down();

        $this->assertTrue(
            Schema::hasColumn('documents', 'shipment_id')
        );

        $columns = DB::select("PRAGMA table_info('documents')");

        $shipmentColumn = collect($columns)->first(
            fn ($column) => $column->name === 'shipment_id'
        );

        $this->assertNotNull($shipmentColumn);

        $this->assertSame(
            1,
            (int) $shipmentColumn->notnull,
            'shipment_id must be NOT NULL after rollback.'
        );
    }

}
