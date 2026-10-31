<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laratrust\Models\Role;
use Tests\TestCase;

class AdminDocumentPdfRenderingTest extends TestCase
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

    public function test_pdf_preview_renders_without_saving_document(): void
    {
        $customer = Customer::query()->create([
            'company_name' => 'PDF Test Company',
        ]);

        $template = DocumentTemplate::query()->create([
            'name' => 'Test Invoice',
            'slug' => 'test-invoice-preview',
            'document_type' => 'invoice',
            'entity_type' => 'customer',
            'content' => '<h1>Test Invoice</h1>',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.documents.pdf.preview'), [
                'template_id' => $template->id,
                'document_type' => 'invoice',
                'entity_type' => 'customer',
                'entity_id' => $customer->id,
                'orientation' => 'portrait',
            ]);

        $response->assertOk();

        $this->assertStringContainsString(
            'application/pdf',
            $response->headers->get('Content-Type')
        );

        $this->assertStringStartsWith(
            '%PDF-',
            $response->getContent()
        );

        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('document_versions', 0);
    }
}
