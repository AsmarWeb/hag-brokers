<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laratrust\Models\Role;
use Tests\TestCase;

class AdminDocumentVersionDownloadTest extends TestCase
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
            'name' => 'Shipping Contract',
            'type' => 'contract',
            'status' => 'active',
            'file_path' => $path,
            'original_name' => basename($path),
        ]);
    }

    public function test_admin_can_download_document_version(): void
    {
        Storage::fake('local');

        $path = 'documents/contract-v1.pdf';

        Storage::disk('local')->put($path, 'Version 1 content');

        $document = $this->document($path);

        $version = $document->versions()->create([
            'version' => 1,
            'file_path' => $path,
            'original_name' => 'contract-v1.pdf',
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.documents.versions.download', [
                $document,
                $version->id,
            ]));

        $response->assertOk();

        $this->assertStringContainsString(
            'attachment',
            $response->headers->get('content-disposition')
        );
    }

    public function test_admin_cannot_download_version_from_another_document(): void
    {
        Storage::fake('local');

        $documentA = $this->document('documents/a.pdf');
        $documentB = $this->document('documents/b.pdf');

        $versionB = $documentB->versions()->create([
            'version' => 1,
            'file_path' => 'documents/b.pdf',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.documents.versions.download', [
                $documentA,
                $versionB->id,
            ]))
            ->assertNotFound();
    }

    public function test_regular_user_cannot_download_document_version(): void
    {
        Storage::fake('local');

        $document = $this->document('documents/private.pdf');

        $version = $document->versions()->create([
            'version' => 1,
            'file_path' => 'documents/private.pdf',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.documents.versions.download', [
                $document,
                $version->id,
            ]))
            ->assertForbidden();
    }

    public function test_missing_version_file_returns_404(): void
    {
        Storage::fake('local');

        $document = $this->document('documents/missing.pdf');

        $version = $document->versions()->create([
            'version' => 1,
            'file_path' => 'documents/missing.pdf',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.documents.versions.download', [
                $document,
                $version->id,
            ]))
            ->assertNotFound();
    }
}
