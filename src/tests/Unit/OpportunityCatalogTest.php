<?php

namespace Tests\Unit;

use App\Services\Platform\OpportunityCatalog;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

final class OpportunityCatalogTest extends TestCase
{
    public function test_sea_freight_contains_the_new_cargo_subcategories(): void
    {
        $expected = [
            'dry_bulk',
            'breakbulk_general_cargo',
            'project_cargo',
            'containers',
            'liquid_petroleum_products',
        ];

        foreach ($expected as $subcategory) {
            $this->assertContains(
                $subcategory,
                OpportunityCatalog::SUBCATEGORIES['sea_freight']
            );

            $validator = Validator::make(
                ['commercial_details' => ['subcategory' => $subcategory]],
                OpportunityCatalog::detailRules('sea_freight')
            );

            $this->assertTrue(
                $validator->passes(),
                "The sea freight subcategory [{$subcategory}] should be valid."
            );
        }

        $this->assertContains('freight_services', OpportunityCatalog::SUBCATEGORIES['sea_freight']);
        $this->assertContains('marine_equipment', OpportunityCatalog::SUBCATEGORIES['sea_freight']);
    }

    public function test_new_cargo_subcategories_have_arabic_and_english_translations(): void
    {
        $translations = [
            'dry_bulk' => [
                'ar' => 'بضائع سائبة جافة',
                'en' => 'Dry bulk',
            ],
            'breakbulk_general_cargo' => [
                'ar' => 'بضائع عامة وقطع مفككة',
                'en' => 'Breakbulk / general cargo',
            ],
            'project_cargo' => [
                'ar' => 'شحنات المشاريع',
                'en' => 'Project cargo',
            ],
            'containers' => [
                'ar' => 'شحن الحاويات',
                'en' => 'Containerized cargo',
            ],
            'liquid_petroleum_products' => [
                'ar' => 'المنتجات النفطية السائلة',
                'en' => 'Liquid petroleum products',
            ],
        ];

        foreach ($translations as $key => $locales) {
            foreach ($locales as $locale => $expected) {
                $this->assertSame(
                    $expected,
                    Lang::get('marketplace.subcategories.'.$key, [], $locale)
                );
            }
        }
    }

    public function test_existing_vessel_classification_rules_are_preserved(): void
    {
        $marineSubcategories = [
            'yachts',
            'boats',
            'sailboats',
            'commercial_vessels',
        ];

        foreach ($marineSubcategories as $subcategory) {
            $this->assertTrue(
                OpportunityCatalog::isMarine('sell', 'marine', ['subcategory' => $subcategory])
            );

            $this->assertTrue(
                OpportunityCatalog::isMarine('buy', 'marine', ['subcategory' => $subcategory])
            );
        }

        $this->assertFalse(
            OpportunityCatalog::isMarine('sell', 'marine', ['subcategory' => 'marine_equipment'])
        );

        $this->assertTrue(
            OpportunityCatalog::isMarine('vessel_brokerage', null)
        );

        $expectedCategories = [
            'yachts' => 'yacht',
            'boats' => 'boat',
            'sailboats' => 'boat',
            'commercial_vessels' => 'commercial_vessel',
        ];

        foreach ($expectedCategories as $subcategory => $expectedCategory) {
            $valid = Validator::make(
                [
                    'vessel' => [
                        'listing_type' => 'sale',
                        'vessel_category' => $expectedCategory,
                    ],
                ],
                OpportunityCatalog::vesselRules(true, $subcategory)
            );

            $this->assertTrue(
                $valid->passes(),
                "The expected vessel category [{$expectedCategory}] should pass for [{$subcategory}]."
            );

            $wrongCategory = $expectedCategory === 'yacht' ? 'boat' : 'yacht';

            $invalid = Validator::make(
                [
                    'vessel' => [
                        'listing_type' => 'sale',
                        'vessel_category' => $wrongCategory,
                    ],
                ],
                OpportunityCatalog::vesselRules(true, $subcategory)
            );

            $this->assertFalse(
                $invalid->passes(),
                "The wrong vessel category should fail for [{$subcategory}]."
            );
        }
    }
}
