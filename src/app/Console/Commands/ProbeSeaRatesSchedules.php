<?php

namespace App\Console\Commands;

use App\Services\Freight\SailingCatalog;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class ProbeSeaRatesSchedules extends Command
{
    protected $signature = 'freight:searates-probe {origin=AEJEA} {destination=SADMM}';

    protected $description = 'Check SeaRates Ship Schedules access for an enabled HAG port pair';

    public function handle(SailingCatalog $catalog): int
    {
        $key = config('freight_sailings.searates_key');
        if (! is_string($key) || trim($key) === '') {
            $this->error('Configure SEARATES_SCHEDULES_API_KEY on the server.');

            return self::FAILURE;
        }

        $origin = strtoupper((string) $this->argument('origin'));
        $destination = strtoupper((string) $this->argument('destination'));
        if ($origin === $destination || ! preg_match('/^[A-Z0-9]{5}$/D', $origin)
            || ! preg_match('/^[A-Z0-9]{5}$/D', $destination)
            || $catalog->ports()->whereIn('unlocode', [$origin, $destination])->count() !== 2) {
            $this->error('Use two different enabled ports within the HAG search region.');

            return self::FAILURE;
        }

        try {
            $response = Http::acceptJson()->withHeaders(['X-API-KEY' => $key])
                ->withoutRedirecting()->connectTimeout(4)->timeout(12)
                ->get('https://schedules.searates.com/api/v2/schedules/by-points', [
                    'origin' => $origin,
                    'destination' => $destination,
                    'from_date' => now('Asia/Dubai')->toDateString(),
                    'weeks' => 2,
                    'cargo_type' => 'GC',
                    'sort' => 'DEP',
                    'direct_only' => 'true',
                    'multimodal' => 'false',
                ]);
        } catch (ConnectionException) {
            $this->error('SeaRates connection failed or timed out.');

            return self::FAILURE;
        }

        $this->line('SeaRates HTTP status: '.$response->status());
        if (! $response->successful() || ! is_array($response->json())) {
            $this->error('SeaRates did not return a successful JSON response.');

            return self::FAILURE;
        }

        $this->info('JSON response received. Confirm route coverage and field mapping with SeaRates before importing.');

        return self::SUCCESS;
    }
}
