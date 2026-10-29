<?php

namespace Tests\Feature;

use App\Models\Document;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentVersionUniqueConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_document_cannot_have_duplicate_version_numbers(): void
    {
        $document = Document::create([
            'name' => 'Shipping Contract',
            'type' => 'contract',
            'status' => 'active',
            'file_path' => 'documents/contract.pdf',
        ]);

        $document->versions()->create([
            'version' => 1,
            'file_path' => 'documents/v1.pdf',
        ]);

        $this->expectException(QueryException::class);

        $document->versions()->create([
            'version' => 1,
            'file_path' => 'documents/duplicate-v1.pdf',
        ]);
    }

    public function test_different_documents_can_have_same_version_number(): void
    {
        $documentA = Document::create([
            'name' => 'Contract A',
            'type' => 'contract',
            'status' => 'active',
            'file_path' => 'documents/a.pdf',
        ]);

        $documentB = Document::create([
            'name' => 'Contract B',
            'type' => 'contract',
            'status' => 'active',
            'file_path' => 'documents/b.pdf',
        ]);

        $documentA->versions()->create([
            'version' => 1,
            'file_path' => 'documents/a-v1.pdf',
        ]);

        $documentB->versions()->create([
            'version' => 1,
            'file_path' => 'documents/b-v1.pdf',
        ]);

        $this->assertDatabaseCount('document_versions', 2);
    }
}
