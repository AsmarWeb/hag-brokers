<?php

namespace Tests\Feature;

use App\Models\Port;
use Database\Seeders\PortSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PortSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_new_uae_and_iraqi_ports_without_duplicates(): void
    {
        $this->seed(PortSeeder::class);

        $this->assertDatabaseHas('ports', [
            'name' => 'Port of Fujairah',
            'country_code' => 'AE',
            'unlocode' => 'AEFJR',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('ports', [
            'name' => 'Port of Khorfakkan',
            'country_code' => 'AE',
            'unlocode' => 'AEKLF',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('ports', [
            'name' => 'Umm Qasr Port',
            'country_code' => 'IQ',
            'unlocode' => 'IQUQR',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('ports', [
            'name' => 'Khor Al Zubair Port',
            'country_code' => 'IQ',
            'unlocode' => 'IQKAZ',
            'is_active' => true,
        ]);

        $alFaw = Port::query()
            ->where('country_code', 'IQ')
            ->where('name', 'Al Faw Grand Port')
            ->sole();

        $this->assertNull($alFaw->unlocode);
        $this->assertNull($alFaw->port_code);
        $this->assertFalse((bool) $alFaw->is_active);

        $this->assertSame(3, Port::query()->where('country_code', 'IQ')->count());

        $countAfterFirstRun = Port::count();

        $this->seed(PortSeeder::class);

        $this->assertSame($countAfterFirstRun, Port::count());
        $this->assertSame(
            1,
            Port::query()
                ->where('country_code', 'IQ')
                ->where('name', 'Al Faw Grand Port')
                ->count()
        );
    }
}
