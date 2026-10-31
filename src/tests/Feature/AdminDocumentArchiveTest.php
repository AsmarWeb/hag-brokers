<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laratrust\Models\Role;
use Tests\TestCase;

class AdminDocumentArchiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
        );
    }

    public function test_guest_cannot_archive_document(): void
    {
        $response = $this->delete('/admin/documents/999');

        $response->assertRedirect();
    }

    public function test_archiving_document_preserves_record_and_versions(): void
    {
        $user = User::factory()->create();

        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );

        $user->addRole($role);

        $document = Document::query()->create([
            'name' => 'Test Document',
            'type' => 'invoice',
            'status' => 'active',
            'file_path' => 'documents/test.pdf',
            'original_name' => 'test.pdf',
            'uploaded_by' => $user->id,
        ]);

        $version = $document->versions()->create([
            'version' => 1,
            'file_path' => 'documents/test.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'uploaded_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('admin.documents.destroy', $document));

        $response->assertRedirect(route('admin.documents.index'));

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'archived',
        ]);

        $this->assertDatabaseHas('document_versions', [
            'id' => $version->id,
            'document_id' => $document->id,
        ]);
    }

    public function test_regular_user_cannot_archive_document(): void
    {
        $user = User::factory()->create();

        $document = Document::query()->create([
            'name' => 'Protected Document',
            'type' => 'contract',
            'status' => 'active',
            'file_path' => 'documents/protected.pdf',
            'original_name' => 'protected.pdf',
        ]);

        $this->actingAs($user)
            ->delete(route('admin.documents.destroy', $document))
            ->assertForbidden();

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'active',
        ]);
    }


}
