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

class AdminDocumentUpdateTest extends TestCase
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

    private function document(User $user, Customer $customer): Document
    {
        Storage::disk('local')->put('documents/original.pdf', '%PDF-1.4 original');

        $document = Document::create([
            'name' => 'Original Document',
            'type' => 'commercial_invoice',
            'status' => 'active',
            'documentable_type' => 'customer',
            'documentable_id' => $customer->id,
            'shipment_id' => null,
            'file_path' => 'documents/original.pdf',
            'original_name' => 'original.pdf',
            'uploaded_by' => $user->id,
        ]);

        $document->versions()->create([
            'version' => 1,
            'file_path' => 'documents/original.pdf',
            'original_name' => 'original.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 17,
            'uploaded_by' => $user->id,
        ]);

        return $document;
    }

    private function fields(Customer $customer): array
    {
        return [
            'name' => 'Updated Document',
            'type' => 'contract',
            'status' => 'draft',
            'documentable_type' => 'customer',
            'documentable_id' => $customer->id,
        ];
    }

    public function test_admin_updates_metadata_without_replacing_file(): void
    {
        Storage::fake('local');

        $admin = $this->admin();
        $customer = Customer::create(['company_name' => 'Test Logistics LLC']);
        $document = $this->document($admin, $customer);

        $this->actingAs($admin)
            ->put(route('admin.documents.update', $document), $this->fields($customer))
            ->assertRedirect(route('admin.documents.edit', $document));

        $document->refresh();

        $this->assertSame('Updated Document', $document->name);
        $this->assertSame('contract', $document->type);
        $this->assertSame('draft', $document->status);
        $this->assertSame('documents/original.pdf', $document->file_path);
        $this->assertSame(1, $document->versions()->count());

        Storage::disk('local')->assertExists('documents/original.pdf');
    }

    public function test_admin_uploads_new_version_without_losing_original(): void
    {
        Storage::fake('local');

        $admin = $this->admin();
        $customer = Customer::create(['company_name' => 'Test Logistics LLC']);
        $document = $this->document($admin, $customer);

        $this->actingAs($admin)
            ->put(route('admin.documents.update', $document), [
                ...$this->fields($customer),
                'file' => UploadedFile::fake()->create('replacement.pdf', 20, 'application/pdf'),
            ])
            ->assertRedirect(route('admin.documents.edit', $document));

        $document->refresh();

        $this->assertSame(2, $document->versions()->count());
        $this->assertSame(2, $document->versions()->max('version'));
        $this->assertNotSame('documents/original.pdf', $document->file_path);

        Storage::disk('local')->assertExists('documents/original.pdf');
        Storage::disk('local')->assertExists($document->file_path);
    }

    public function test_invalid_linked_record_is_rejected(): void
    {
        Storage::fake('local');

        $admin = $this->admin();
        $customer = Customer::create(['company_name' => 'Test Logistics LLC']);
        $document = $this->document($admin, $customer);

        $fields = $this->fields($customer);
        $fields['documentable_id'] = 999999;

        $this->actingAs($admin)
            ->from(route('admin.documents.edit', $document))
            ->put(route('admin.documents.update', $document), $fields)
            ->assertSessionHasErrors('documentable_id');

        $document->refresh();

        $this->assertSame('Original Document', $document->name);
        $this->assertSame(1, $document->versions()->count());
    }
}
