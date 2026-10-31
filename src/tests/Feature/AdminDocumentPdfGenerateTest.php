<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laratrust\Models\Role;
use Tests\TestCase;

class AdminDocumentPdfGenerateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
        );

        Storage::fake('local');
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

    private function payload(): array
    {
        $customer = Customer::query()->create([
            'company_name' => 'HAG PDF Test Customer',
        ]);

        $template = DocumentTemplate::query()->create([
            'name' => 'Test Invoice',
            'slug' => 'test-invoice-generate',
            'document_type' => 'invoice',
            'entity_type' => 'customer',
            'content' => '<h1>HAG Brokers Invoice</h1>',
            'is_active' => true,
        ]);

        return [
            'template_id' => $template->id,
            'document_type' => 'invoice',
            'entity_type' => 'customer',
            'entity_id' => $customer->id,
            'file_name' => 'HAG-Test-Invoice',
            'orientation' => 'portrait',
        ];
    }

    public function test_admin_can_generate_pdf_with_first_version(): void
    {
        $payload = $this->payload();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.documents.pdf.generate'), $payload);

        $response->assertRedirect(route('admin.documents.index'));

        $this->assertDatabaseCount('documents', 1);
        $this->assertDatabaseCount('document_versions', 1);

        $document = Document::query()->firstOrFail();

        $this->assertSame('active', $document->status);
        $this->assertSame('invoice', $document->type);
        $this->assertSame('HAG-Test-Invoice.pdf', $document->original_name);

        $this->assertSame(
            $payload['entity_id'],
            (int) $document->documentable_id
        );

        $this->assertSame(
            1,
            (int) $document->versions()->firstOrFail()->version
        );

        Storage::disk('local')->assertExists($document->file_path);

        $this->assertStringStartsWith(
            '%PDF-',
            Storage::disk('local')->get($document->file_path)
        );
    }

    public function test_regular_user_cannot_generate_pdf(): void
    {
        $payload = $this->payload();

        $this->actingAs(User::factory()->create())
            ->post(route('admin.documents.pdf.generate'), $payload)
            ->assertForbidden();

        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('document_versions', 0);
    }

    public function test_failed_pdf_storage_does_not_create_database_records(): void
    {
        $payload = $this->payload();

        $disk = \Mockery::mock(
            \Illuminate\Contracts\Filesystem\Filesystem::class
        );

        $disk->shouldReceive('put')
            ->once()
            ->andReturn(false);

        Storage::shouldReceive('disk')
            ->once()
            ->with('local')
            ->andReturn($disk);

        $this->withoutExceptionHandling();

        $this->actingAs($this->admin());

        $caught = null;

        try {
            $this->post(
                route('admin.documents.pdf.generate'),
                $payload
            );
        } catch (\RuntimeException $exception) {
            $caught = $exception;
        }

        $this->assertInstanceOf(
            \RuntimeException::class,
            $caught,
            'Expected a storage failure exception.'
        );

        $this->assertSame(
            'Unable to store generated PDF.',
            $caught->getMessage()
        );

        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('document_versions', 0);
    }



    public function test_database_failure_removes_generated_pdf(): void
    {
        $payload = $this->payload();

        $this->actingAs($this->admin());
        $this->withoutExceptionHandling();

        \Illuminate\Support\Facades\DB::listen(function ($query) {
            if (
                str_contains(
                    strtolower($query->sql),
                    'insert into "documents"'
                )
            ) {
                throw new \RuntimeException(
                    'Simulated document database failure.'
                );
            }
        });

        $caught = null;

        try {
            $this->post(
                route('admin.documents.pdf.generate'),
                $payload
            );
        } catch (\RuntimeException $exception) {
            $caught = $exception;
        }

        $this->assertInstanceOf(
            \RuntimeException::class,
            $caught
        );

        $this->assertSame(
            'Simulated document database failure.',
            $caught->getMessage()
        );

        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('document_versions', 0);

        $this->assertSame(
            [],
            Storage::disk('local')->allFiles('documents/generated'),
            'Generated PDF was not cleaned up.'
        );
    }


}
