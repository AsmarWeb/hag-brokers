<?php

namespace App\Services\Freight;

use App\Models\FreightSailing;
use App\Models\Port;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

final class SailingCatalog
{
    public function ports(): Builder
    {
        return Port::query()->where('is_active', true)->whereNotNull('unlocode')
            ->where(fn ($query) => $query->whereIn('country_code', config('freight_sailings.countries'))
                ->orWhereIn('unlocode', config('freight_sailings.nearby_ports')));
    }

    public function available(): Builder
    {
        return FreightSailing::query()->where('status', 'published')
            ->where('cargo_cutoff_at', '>', now()->utc())
            ->where('departure_at', '>', now()->utc())
            ->whereHas('carrier', fn ($query) => $query->where('is_active', true))
            ->whereIn('origin_port_id', $this->ports()->select('id'))
            ->whereIn('destination_port_id', $this->ports()->select('id'))
            ->with(['originPort', 'destinationPort', 'carrier']);
    }

    public function snapshot(FreightSailing $sailing): array
    {
        return [
            'source' => 'hag_partner_schedule',
            'voyage_number' => $sailing->voyage_number,
            'vessel_name' => $sailing->vessel_name,
            'carrier' => $sailing->carrier->name,
            'origin' => $sailing->originPort->only(['id', 'name', 'unlocode']),
            'destination' => $sailing->destinationPort->only(['id', 'name', 'unlocode']),
            'departure_at' => $sailing->departure_at->utc()->toIso8601String(),
            'arrival_at' => $sailing->arrival_at->utc()->toIso8601String(),
            'cargo_cutoff_at' => $sailing->cargo_cutoff_at->utc()->toIso8601String(),
            'container_types' => $sailing->container_types,
            'availability' => 'on_request',
        ];
    }

    public function revision(FreightSailing $sailing): string
    {
        return hash('sha256', json_encode($this->snapshot($sailing), JSON_THROW_ON_ERROR));
    }

    public function forQuotation(array $payload): array
    {
        $sailing = $this->available()->whereKey($payload['freight_sailing_id'])->lockForUpdate()->first();
        if (! $sailing
            || ! hash_equals($this->revision($sailing), $payload['sailing_revision'])
            || (int) $sailing->origin_port_id !== $payload['origin_port_id']
            || (int) $sailing->destination_port_id !== $payload['destination_port_id']
            || ! in_array($payload['container_type'], $sailing->container_types, true)
            || (! empty($payload['preferred_date'])
                && ($payload['preferred_date'] < now('Asia/Dubai')->toDateString()
                    || $payload['preferred_date'] > $sailing->cargo_cutoff_at->setTimezone('Asia/Dubai')->toDateString()))) {
            throw ValidationException::withMessages(['freight_sailing_id' => __('sailings.invalid_selection')]);
        }

        return $this->snapshot($sailing);
    }
}
