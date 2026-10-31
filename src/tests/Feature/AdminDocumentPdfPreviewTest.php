<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laratrust\Models\Role;
use Tests\TestCase;

class AdminDocumentPdfPreviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
        );
    }

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

    public function test_regular_user_cannot_preview_pdf(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.documents.pdf.preview'))
            ->assertForbidden();
    }

    public function test_preview_rejects_invalid_template(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.documents.pdf.preview'), [
                'template_id' => 999999,
                'document_type' => 'invoice',
                'entity_type' => 'customer',
                'entity_id' => 1,
                'orientation' => 'portrait',
            ])
            ->assertNotFound();
    }

    public function test_preview_rejects_invalid_orientation(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.documents.pdf.preview'), [
                'template_id' => 1,
                'document_type' => 'invoice',
                'entity_type' => 'customer',
                'entity_id' => 1,
                'orientation' => 'diagonal',
            ])
            ->assertSessionHasErrors('orientation');
    }
}
