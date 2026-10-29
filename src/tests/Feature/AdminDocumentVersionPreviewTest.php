<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laratrust\Models\Role;
use Tests\TestCase;

class AdminDocumentVersionPreviewTest extends TestCase
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

    public function test_admin_can_preview_pdf_version(): void
    {
        Storage::fake('local');

        $path = 'documents/contract-v1.pdf';

        Storage::disk('local')->put(
            $path,
            "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF"
        );

        $document = $this->document($path);

        $version = $document->versions()->create([
            'version' => 1,
            'file_path' => $path,
            'original_name' => 'contract-v1.pdf',
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.documents.versions.preview', [
                $document,
                $version->id,
            ]));

        $response->assertOk();

        $this->assertStringContainsString(
            'inline',
            $response->headers->get('content-disposition')
        );

        $this->assertSame(
            'nosniff',
            $response->headers->get('X-Content-Type-Options')
        );
    }

    public function test_cannot_preview_version_of_another_document(): void
    {
        Storage::fake('local');

        $documentA = $this->document('documents/a.pdf');
        $documentB = $this->document('documents/b.pdf');

        $versionB = $documentB->versions()->create([
            'version' => 1,
            'file_path' => 'documents/b.pdf',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.documents.versions.preview', [
                $documentA,
                $versionB->id,
            ]))
            ->assertNotFound();
    }

    public function test_unsupported_version_cannot_be_previewed(): void
    {
        Storage::fake('local');

        $path = 'documents/data.txt';

        Storage::disk('local')->put($path, 'Plain text');

        $document = $this->document($path);

        $version = $document->versions()->create([
            'version' => 1,
            'file_path' => $path,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.documents.versions.preview', [
                $document,
                $version->id,
            ]))
            ->assertStatus(415);
    }

    public function test_regular_user_cannot_preview_version(): void
    {
        Storage::fake('local');

        $document = $this->document('documents/private.pdf');

        $version = $document->versions()->create([
            'version' => 1,
            'file_path' => 'documents/private.pdf',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.documents.versions.preview', [
                $document,
                $version->id,
            ]))
            ->assertForbidden();
    }
}
