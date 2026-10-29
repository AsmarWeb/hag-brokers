<?php

namespace Tests\Feature;

use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DocumentPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_can_be_saved_for_each_supported_type(): void
    {
        foreach (['customer', 'shipment', 'quotation'] as $type) {
            $document = Document::create([
                'name' => 'Test ' . $type,
                'type' => 'other',
                'status' => 'active',
                'documentable_type' => $type,
                'documentable_id' => 100,
                'shipment_id' => null,
                'file_path' => 'documents/test.pdf',
                'original_name' => 'test.pdf',
            ]);

            $this->assertDatabaseHas('documents', [
                'id' => $document->id,
                'documentable_type' => $type,
                'documentable_id' => 100,
                'shipment_id' => null,
            ]);

            $this->assertSame(
                $type,
                $document->fresh()->documentable_type
            );
        }

        $this->assertSame(3, DB::table('documents')->count());
    }
}
