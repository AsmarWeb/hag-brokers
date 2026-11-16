<?php

namespace App\Services\Freight;

use App\Models\FreightRate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class FreightRateSearch
{
    public function query(array $filters): Builder
    {
        return FreightRate::query()
            ->with(['source:id,code,name,type', 'carrier:id,name,code', 'sailing:id,vessel_name,voyage_number,departure_at,arrival_at',
                'originPort:id,name,unlocode', 'destinationPort:id,name,unlocode'])
            ->where('freight_rates.status', 'published')
            ->whereHas('source', fn (Builder $q) => $q->where('is_active', true))
            ->where(fn (Builder $q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->when(isset($filters['origin_port_id']), fn (Builder $q) => $q->where('origin_port_id', $filters['origin_port_id']))
            ->when(isset($filters['destination_port_id']), fn (Builder $q) => $q->where('destination_port_id', $filters['destination_port_id']))
            ->when(isset($filters['container_type']), fn (Builder $q) => $q->where('container_type', $filters['container_type']))
            ->where('currency', strtoupper($filters['currency']))
            ->when(isset($filters['carrier_id']), fn (Builder $q) => $q->where('carrier_id', $filters['carrier_id']))
            ->when(isset($filters['ready_on']), function (Builder $q) use ($filters) {
                $q->whereHas('sailing', fn (Builder $s) => $s->whereDate('departure_at', '>=', $filters['ready_on']));
            })
            ->when(isset($filters['quantity']), fn (Builder $q) => $q->where('min_quantity', '<=', $filters['quantity'])
                ->where(fn (Builder $inner) => $inner->whereNull('max_quantity')->orWhere('max_quantity', '>=', $filters['quantity'])));
    }

    public function results(array $filters): Collection
    {
        $sort = $filters['sort'] ?? 'best_value';
        if (! in_array($sort, ['lowest_price', 'fastest', 'best_value'], true)) {
            throw ValidationException::withMessages(['sort' => 'Unsupported freight rate sort order.']);
        }

        $rates = $this->query($filters)->get();
        $quantity = max(1, (int) ($filters['quantity'] ?? 1));

        $items = $rates->map(function (FreightRate $rate) use ($quantity) {
            $unitPrice = (float) $rate->customer_unit_price;
            $surcharges = collect($rate->surcharge_items ?? [])->sum(fn ($item) => (float) ($item['customer_amount'] ?? $item['amount'] ?? 0));
            $total = round(($unitPrice * $quantity) + $surcharges, 2);
            $departure = $rate->sailing?->departure_at;
            $arrival = $rate->sailing?->arrival_at;
            $transitDays = $departure && $arrival ? (int) $departure->diffInDays($arrival) : null;

            return [
                'id' => $rate->id,
                'source' => ['code' => $rate->source->code, 'name' => $rate->source->name, 'type' => $rate->source->type],
                'carrier' => $rate->carrier ? ['id' => $rate->carrier->id, 'name' => $rate->carrier->name] : null,
                'route' => [
                    'origin' => ['id' => $rate->originPort->id, 'name' => $rate->originPort->name, 'unlocode' => $rate->originPort->unlocode],
                    'destination' => ['id' => $rate->destinationPort->id, 'name' => $rate->destinationPort->name, 'unlocode' => $rate->destinationPort->unlocode],
                ],
                'container_type' => $rate->container_type,
                'quantity' => $quantity,
                'unit_price' => number_format($unitPrice, 2, '.', ''),
                'surcharges' => $rate->surcharge_items ?? [],
                'total_price' => number_format($total, 2, '.', ''),
                'currency' => $rate->currency,
                'departure_at' => $departure?->toIso8601String(),
                'arrival_at' => $arrival?->toIso8601String(),
                'transit_days' => $transitDays,
                'vessel_name' => $rate->sailing?->vessel_name,
                'voyage_number' => $rate->sailing?->voyage_number,
                'availability' => $rate->availability,
                'booking_mode' => $rate->booking_mode,
                'rate_type' => $rate->rate_type,
                'quoted_at' => $rate->quoted_at?->toIso8601String(),
                'expires_at' => $rate->expires_at?->toIso8601String(),
                'included_charges' => $rate->included_charges ?? [],
                'excluded_charges' => $rate->excluded_charges ?? [],
                'terms' => $rate->terms,
            ];
        });

        if ($sort === 'lowest_price') {
            return $items->sortBy(fn ($item) => [(float) $item['total_price'], $item['transit_days'] ?? PHP_INT_MAX])->values();
        }
        if ($sort === 'fastest') {
            return $items->sortBy(fn ($item) => [$item['transit_days'] ?? PHP_INT_MAX, (float) $item['total_price']])->values();
        }

        // Balanced ranking: normalize price and transit time within this currency's result set.
        if ($items->isEmpty()) {
            return $items;
        }
        $prices = $items->pluck('total_price')->map(fn ($value) => (float) $value);
        $knownTransit = $items->pluck('transit_days')->filter(fn ($value) => $value !== null);
        $minPrice = $prices->min();
        $maxPrice = $prices->max();
        $minTransit = $knownTransit->isNotEmpty() ? $knownTransit->min() : 0;
        $maxTransit = $knownTransit->isNotEmpty() ? $knownTransit->max() : 0;

        return $items->sortBy(function ($item) use ($minPrice, $maxPrice, $minTransit, $maxTransit) {
            $priceScore = $maxPrice > $minPrice
                ? (((float) $item['total_price'] - $minPrice) / ($maxPrice - $minPrice))
                : 0.0;
            $transit = $item['transit_days'];
            $transitScore = $transit === null ? 1.0 : ($maxTransit > $minTransit
                ? (($transit - $minTransit) / ($maxTransit - $minTransit))
                : 0.0);

            return ($priceScore + $transitScore) / 2;
        })->values();
    }
}
