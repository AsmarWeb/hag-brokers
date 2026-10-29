<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laratrust\Models\Role;
use Tests\TestCase;

class AdminDocumentVersionUploadTest extends TestCase
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

    public function test_admin_can_upload_v2_without_losing_v1(): void
    {
        Storage::fake('local');

        $oldPath = 'documents/contract-v1.pdf';

        Storage::disk('local')->put(
            $oldPath,
            "%PDF-1.4\nOld contract\n%%EOF"
        );

        $document = Document::create([
            'name' => 'Shipping Contract',
            'type' => 'contract',
            'status' => 'active',
            'file_path' => $oldPath,
            'original_name' => 'contract-v1.pdf',
        ]);

        $document->versions()->create([
            'version' => 1,
            'file_path' => $oldPath,
            'original_name' => 'contract-v1.pdf',
        ]);

        $response = $this->actingAs($this->admin())
            ->post(
                route('admin.documents.versions.store', $document),
                [
                    'file' => UploadedFile::fake()->create(
                        'contract-v2.pdf',
                        100,
                        'application/pdf'
                    ),
                ]
            );

        $response->assertRedirect(
            route('admin.documents.versions', $document)
        );

        $document->refresh();

        $this->assertSame(2, $document->versions()->count());
        $this->assertSame(2, $document->version);

        $v1 = $document->versions()
            ->where('version', 1)
            ->firstOrFail();

        $v2 = $document->versions()
            ->where('version', 2)
            ->firstOrFail();

        $this->assertSame($oldPath, $v1->file_path);
        $this->assertSame($v2->file_path, $document->file_path);
        $this->assertNotSame($v1->file_path, $v2->file_path);
        $this->assertSame('contract-v2.pdf', $v2->original_name);

        Storage::disk('local')->assertExists([
            $v1->file_path,
            $v2->file_path,
        ]);
    }

    public function test_regular_user_cannot_upload_new_version(): void
    {
        Storage::fake('local');

        $document = Document::create([
            'name' => 'Private Contract',
            'type' => 'contract',
            'status' => 'active',
            'file_path' => 'documents/private.pdf',
        ]);

        $this->actingAs(User::factory()->create())
            ->post(
                route('admin.documents.versions.store', $document),
                [
                    'file' => UploadedFile::fake()->create(
                        'unauthorized.pdf',
                        10,
                        'application/pdf'
                    ),
                ]
            )
            ->assertForbidden();

        $this->assertSame(0, $document->versions()->count());
    }

    public function test_invalid_file_cannot_create_new_version(): void
    {
        Storage::fake('local');

        $document = Document::create([
            'name' => 'Contract',
            'type' => 'contract',
            'status' => 'active',
            'file_path' => 'documents/contract.pdf',
        ]);

        $this->actingAs($this->admin())
            ->post(
                route('admin.documents.versions.store', $document),
                [
                    'file' => UploadedFile::fake()->create(
                        'malware.exe',
                        10,
                        'application/octet-stream'
                    ),
                ]
            )
            ->assertSessionHasErrors('file');

        $this->assertSame(0, $document->versions()->count());
        $this->assertSame(
            'documents/contract.pdf',
            $document->fresh()->file_path
        );
    }
}
