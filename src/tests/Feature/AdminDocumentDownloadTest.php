<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laratrust\Models\Role;
use Tests\TestCase;

class AdminDocumentDownloadTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();

        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );

        $user->addRole($role);

        return $user;
    }

    private function document(string $path): Document
    {
        return Document::create([
            'name' => 'Test Document',
            'type' => 'contract',
            'status' => 'active',
            'file_path' => $path,
            'original_name' => 'contract.pdf',
        ]);
    }

    public function test_admin_can_download_document(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put(
            'documents/contract.pdf',
            'PDF test content'
        );

        $document = $this->document('documents/contract.pdf');

        $response = $this->actingAs($this->admin())
            ->get(route('admin.documents.download', $document));

        $response->assertOk();

        $this->assertStringContainsString(
            'attachment',
            $response->headers->get('content-disposition')
        );
    }

    public function test_regular_user_cannot_download_document(): void
    {
        Storage::fake('local');

        $document = $this->document('documents/contract.pdf');

        $this->actingAs(User::factory()->create())
            ->get(route('admin.documents.download', $document))
            ->assertForbidden();
    }

    public function test_missing_document_file_returns_404(): void
    {
        Storage::fake('local');

        $document = $this->document('documents/missing.pdf');

        $this->actingAs($this->admin())
            ->get(route('admin.documents.download', $document))
            ->assertNotFound();
    }

    public function test_document_cannot_access_file_outside_documents(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put(
            'private-secret.txt',
            'Sensitive information'
        );

        $document = $this->document('../private-secret.txt');

        $this->actingAs($this->admin())
            ->get(route('admin.documents.download', $document))
            ->assertNotFound();
    }
}
