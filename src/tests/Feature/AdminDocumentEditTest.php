<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laratrust\Models\Role;
use Tests\TestCase;

class AdminDocumentEditTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        $user = User::factory()->create();

        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );

        $user->addRole($role);

        return $user;
    }

    private function createDocument(User $user): Document
    {
        Storage::fake('local');

        Storage::disk('local')->put(
            'documents/test.pdf',
            '%PDF-1.4 test'
        );

        return Document::create([
            'name' => 'Test Document',
            'type' => 'commercial_invoice',
            'status' => 'active',
            'documentable_type' => 'customer',
            'documentable_id' => 1,
            'shipment_id' => null,
            'file_path' => 'documents/test.pdf',
            'original_name' => 'test.pdf',
            'uploaded_by' => $user->id,
        ]);
    }

    public function test_admin_can_open_document_edit_page(): void
    {
        $admin = $this->createAdmin();
        $document = $this->createDocument($admin);

        $response = $this->actingAs($admin)
            ->get(route('admin.documents.edit', $document));

        $response->assertOk();
        $response->assertViewIs('admin.documents.edit');
        $response->assertViewHas('document', function ($value) use ($document) {
            return $value->id === $document->id;
        });
    }

    public function test_regular_user_cannot_edit_document(): void
    {
        $admin = $this->createAdmin();
        $document = $this->createDocument($admin);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.documents.edit', $document))
            ->assertForbidden();
    }
}
