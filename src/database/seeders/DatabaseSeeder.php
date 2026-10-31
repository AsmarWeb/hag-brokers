<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the HAG Brokers database.
     */
    public function run(): void
    {
        // 1. Roles, permissions, and initial accounts.
        $this->call(LaratrustSeeder::class);

        // 2. Reference data for sea freight operations.
        $this->call(PortSeeder::class);

        // 3. Default company settings.
        $this->call(CompanySettingSeeder::class);

        // 4. Document templates.
        $this->call(DocumentTemplateSeeder::class);

        $this->command?->info(
            'HAG Brokers database seeding completed successfully.'
        );
    }
}
