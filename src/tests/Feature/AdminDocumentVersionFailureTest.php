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

class AdminDocumentVersionFailureTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_version_insert_preserves_current_document_and_cleans_new_file(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create();

        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );

        $admin->addRole($role);

        $oldPath = 'documents/original.pdf';

        Storage::disk('local')->put(
            $oldPath,
            '%PDF-1.4 original'
        );

        $document = Document::create([
            'name' => 'Original Contract',
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

        DB::connection()->getPdo()->exec(
            "CREATE TRIGGER reject_document_version_insert
             BEFORE INSERT ON document_versions
             BEGIN
                 SELECT RAISE(ABORT, 'Simulated insert failure');
             END"
        );

        try {
            $this->withoutExceptionHandling();

            $this->actingAs($admin)->post(
                route('admin.documents.versions.store', $document),
                [
                    'file' => UploadedFile::fake()->create(
                        'new-contract.pdf',
                        10,
                        'application/pdf'
                    ),
                ]
            );
        } catch (\Illuminate\Database\QueryException $exception) {
            $this->assertStringContainsString(
                'Simulated insert failure',
                $exception->getMessage()
            );
        } finally {
            DB::connection()->getPdo()->exec(
                'DROP TRIGGER IF EXISTS reject_document_version_insert'
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
