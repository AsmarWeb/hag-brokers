<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laratrust\Models\Role;
use Tests\TestCase;

class AdminDocumentVersionConflictTest extends TestCase
{
    use RefreshDatabase;

    public function test_unique_version_conflict_returns_validation_error_and_preserves_file(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create();

        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );

        $admin->addRole($role);

        $oldPath = 'documents/original.pdf';

        Storage::disk('local')->put($oldPath, '%PDF-1.4 original');

        $document = Document::create([
            'name' => 'Shipping Contract',
            'type' => 'contract',
            'status' => 'active',
            'file_path' => $oldPath,
            'original_name' => 'original.pdf',
        ]);

        $document->versions()->create([
            'version' => 1,
            'file_path' => $oldPath,
            'original_name' => 'original.pdf',
        ]);

        // Force the next insert to conflict with the existing v1.
        DB::connection()->getPdo()->exec(
            "CREATE TRIGGER force_duplicate_version
             AFTER INSERT ON document_versions
             BEGIN
                 UPDATE document_versions
                 SET version = 1
                 WHERE id = NEW.id;
             END"
        );

        try {
            $response = $this->actingAs($admin)
                ->from(route('admin.documents.versions', $document))
                ->post(
                    route('admin.documents.versions.store', $document),
                    [
                        'file' => UploadedFile::fake()->create(
                            'new-contract.pdf',
                            10,
                            'application/pdf'
                        ),
                    ]
                );

            $response->assertRedirect(
                route('admin.documents.versions', $document)
            );

            $response->assertSessionHasErrors('file');
        } finally {
            DB::connection()->getPdo()->exec(
                'DROP TRIGGER IF EXISTS force_duplicate_version'
            );
        }

        $document->refresh();

        $this->assertSame($oldPath, $document->file_path);
        $this->assertSame(1, $document->versions()->count());

        $this->assertSame(
            [$oldPath],
            Storage::disk('local')->allFiles('documents')
        );
    }
}
