<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laratrust\Models\Role;
use Tests\TestCase;

class AdminDocumentPdfCreatePageTest extends TestCase
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

    public function test_admin_can_open_pdf_create_page(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.documents.pdf.create'))
            ->assertOk()
            ->assertViewIs('admin.documents.pdf-create')
            ->assertViewHasAll([
                'templates',
                'customers',
                'shipments',
                'quotations',
            ]);
    }

    public function test_regular_user_cannot_open_pdf_create_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.documents.pdf.create'))
            ->assertForbidden();
    }
}
