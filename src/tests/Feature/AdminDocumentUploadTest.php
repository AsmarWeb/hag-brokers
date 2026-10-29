<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laratrust\Models\Role;
use Tests\TestCase;

class AdminDocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_document_and_create_first_version(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $role = Role::firstOrCreate([
            'name' => 'admin',
        ], [
            'display_name' => 'Administrator',
        ]);

        $user->addRole($role);

        $customer = Customer::create([
            'company_name' => 'Test Logistics LLC',
        ]);

        $response = $this->actingAs($user)->post(
            route('admin.documents.store'),
            [
                'name' => 'Test Contract',
                'type' => 'contract',
                'status' => 'active',
                'documentable_type' => 'customer',
                'documentable_id' => $customer->id,
                'file' => UploadedFile::fake()->create(
                    'contract.pdf',
                    100,
                    'application/pdf'
                ),
            ]
        );

        $response->assertRedirect(route('admin.documents.index'));

        $document = Document::query()->firstOrFail();

        $this->assertSame('customer', $document->documentable_type);
        $this->assertSame($customer->id, $document->documentable_id);

        Storage::disk('local')->assertExists($document->file_path);

        $this->assertDatabaseHas('document_versions', [
            'document_id' => $document->id,
            'version' => 1,
            'file_path' => $document->file_path,
        ]);
    }

    public function test_invalid_file_type_is_rejected(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $role = Role::firstOrCreate([
            'name' => 'admin',
        ], [
            'display_name' => 'Administrator',
        ]);

        $user->addRole($role);

        $customer = Customer::create([
            'company_name' => 'Test Logistics LLC',
        ]);

        $response = $this->actingAs($user)->post(
            route('admin.documents.store'),
            [
                'name' => 'Invalid File',
                'type' => 'other',
                'documentable_type' => 'customer',
                'documentable_id' => $customer->id,
                'file' => UploadedFile::fake()->create(
                    'malicious.exe',
                    100,
                    'application/octet-stream'
                ),
            ]
        );

        $response->assertSessionHasErrors('file');

        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('document_versions', 0);
    }

    public function test_regular_user_cannot_upload_document(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            route('admin.documents.store'),
            [
                'name' => 'Unauthorized Document',
                'type' => 'other',
                'documentable_type' => 'customer',
                'documentable_id' => 1,
                'file' => UploadedFile::fake()->create(
                    'test.pdf',
                    100,
                    'application/pdf'
                ),
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('document_versions', 0);
    }
}
