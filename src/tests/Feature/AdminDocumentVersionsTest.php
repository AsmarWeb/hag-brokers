<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laratrust\Models\Role;
use Tests\TestCase;

class AdminDocumentVersionsTest extends TestCase
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

    public function test_admin_can_view_document_version_history(): void
    {
        $document = Document::create([
            'name' => 'Shipping Contract',
            'type' => 'contract',
            'status' => 'active',
            'file_path' => 'documents/contract.pdf',
            'original_name' => 'contract.pdf',
        ]);

        $document->versions()->create([
            'version' => 1,
            'file_path' => 'documents/contract.pdf',
            'original_name' => 'contract.pdf',
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.documents.versions', $document));

        $response->assertOk();
        $response->assertSee('Shipping Contract');
        $response->assertSee('contract.pdf');
    }

    public function test_regular_user_cannot_view_version_history(): void
    {
        $document = Document::create([
            'name' => 'Private Contract',
            'type' => 'contract',
            'status' => 'active',
            'file_path' => 'documents/private.pdf',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.documents.versions', $document))
            ->assertForbidden();
    }
}
