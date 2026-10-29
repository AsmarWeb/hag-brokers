<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Quotation;
use App\Models\Shipment;
use Illuminate\Database\Eloquent\Relations\Relation;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DocumentRelationshipsTest extends TestCase
{
    public static function documentableTypes(): array
    {
        return [
            'customer' => ['customer', Customer::class],
            'shipment' => ['shipment', Shipment::class],
            'quotation' => ['quotation', Quotation::class],
        ];
    }

    #[DataProvider('documentableTypes')]
    public function test_document_type_maps_to_expected_model(
        string $type,
        string $modelClass
    ): void {
        $this->assertSame(
            $modelClass,
            Relation::getMorphedModel($type)
        );

        $model = new $modelClass();

        $this->assertSame($type, $model->getMorphClass());
    }

    public function test_document_has_polymorphic_relationship(): void
    {
        $document = new Document();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\MorphTo::class,
            $document->documentable()
        );
    }

    public function test_document_has_versions_relationship(): void
    {
        $document = new Document();

        $relationship = $document->versions();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $relationship
        );

        $this->assertInstanceOf(
            DocumentVersion::class,
            $relationship->getRelated()
        );
    }
}
