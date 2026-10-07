<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    /**
     * Seed the application's company settings.
     */
    public function run(): void
    {
        CompanySetting::updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'HAG Brokers',

                'company_name_ar' => 'رأس الخليج العربي للوساطة التجارية',

                'email' => 'info@hagbrokers.net',

                'phone' => null,

                'whatsapp' => null,

                'address' => null,

                'city' => 'Dubai',

                'country' => 'United Arab Emirates',

                'po_box' => null,

                'website' => 'https://hagbrokers.net',

                'tax_number' => null,

                'license_number' => null,

                'header_color' => '#06101d',

                'footer_text' =>
                'HAG Brokers — Sea Freight Solutions',

                'social_links' => [
                    'linkedin' => null,
                    'instagram' => null,
                    'facebook' => null,
                    'x' => null,
                ],

                'metadata' => [
                    'industry' => 'Sea Freight Brokerage',

                    'business_type' =>
                    'Commercial Brokerage',

                    'tagline' =>
                    'Sea Freight Solutions',

                    'tagline_ar' =>
                    'حلول الشحن البحري',

                    'primary_color' =>
                    '#22d3ee',

                    'secondary_color' =>
                    '#06101d',

                    'timezone' =>
                    'Asia/Dubai',

                    'default_language' =>
                    'en',

                    'supported_languages' => [
                        'en',
                        'ar',
                    ],
                ],
            ]
        );

        $this->command?->info(
            'Company settings seeded successfully.'
        );
    }
}
