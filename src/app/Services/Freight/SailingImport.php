<?php

namespace App\Services\Freight;

use App\Models\Carrier;
use App\Models\FreightSailing;
use App\Models\Vessel;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use SplFileObject;

final class SailingImport
{
    public const HEADERS = [
        'carrier_code', 'vessel_imo', 'origin_unlocode', 'destination_unlocode',
        'voyage_number', 'departure_at', 'arrival_at', 'cargo_cutoff_at', 'container_types',
    ];

    public function read(string $path): array
    {
        $file = new SplFileObject($path);
        $header = preg_replace('/^\xEF\xBB\xBF/', '', trim($file->fgets()));
        $delimiter = str_contains($header, ';') ? ';' : ',';
        if (str_getcsv($header, $delimiter, '"', '') !== self::HEADERS) {
            $this->fail('header');
        }

        $rows = [];
        while (! $file->eof()) {
            $cells = $file->fgetcsv($delimiter, '"', '');
            if ($cells === false || $cells === [null]) {
                continue;
            }
            if (count($cells) !== count(self::HEADERS) || ! mb_check_encoding($cells, 'UTF-8')) {
                $this->fail('row_format', count($rows) + 1);
            }
            $rows[] = array_combine(self::HEADERS, array_map(trim(...), $cells));
            if (count($rows) > 100) {
                $this->fail('row_limit');
            }
        }
        if (! $rows) {
            $this->fail('empty');
        }

        return $rows;
    }

    public function plan(array $rows, SailingCatalog $catalog, bool $lock = false): array
    {
        $plan = [];
        $seen = [];
        // Take carrier locks in a consistent order to serialize competing imports.
        $carriers = Carrier::query()->whereIn('code', array_unique(array_column($rows, 'carrier_code')))
            ->where('is_active', true)->orderBy('id')->when($lock, fn ($q) => $q->lockForUpdate())->get()->keyBy('code');

        foreach ($rows as $index => $row) {
            $number = $index + 1;
            $types = array_map(trim(...), explode('|', $row['container_types']));
            sort($types);
            $validator = Validator::make($row, [
                'voyage_number' => ['required', 'string', 'max:60'],
                'departure_at' => ['required', 'date_format:Y-m-d H:i'],
                'arrival_at' => ['required', 'date_format:Y-m-d H:i', 'after:departure_at'],
                'cargo_cutoff_at' => ['required', 'date_format:Y-m-d H:i', 'before:departure_at'],
                'vessel_imo' => ['required', 'regex:/^\d{7}$/'],
                'origin_unlocode' => ['required', 'regex:/^[A-Z]{2}[A-Z0-9]{3}$/'],
                'destination_unlocode' => ['required', 'different:origin_unlocode', 'regex:/^[A-Z]{2}[A-Z0-9]{3}$/'],
            ]);
            if ($validator->fails() || array_diff($types, FreightSailing::CONTAINER_TYPES) || $types !== array_values(array_unique($types))) {
                $this->fail('row_format', $number);
            }

            $carrier = $carriers->get($row['carrier_code']);
            $vessel = Vessel::where('imo', $row['vessel_imo'])->when($lock, fn ($q) => $q->lockForUpdate())->first();
            if (! $carrier || ! $vessel || (int) $vessel->carrier_id !== $carrier->id || mb_strlen($vessel->name) > 120) {
                $this->fail('row_catalog', $number);
            }

            $ports = $catalog->ports()->whereIn('unlocode', [$row['origin_unlocode'], $row['destination_unlocode']])
                ->orderBy('id')->when($lock, fn ($q) => $q->lockForUpdate())->get()->keyBy('unlocode');
            if (! $ports->has($row['origin_unlocode']) || ! $ports->has($row['destination_unlocode'])) {
                $this->fail('row_catalog', $number);
            }

            $route = [
                'carrier_id' => $carrier->id,
                'origin_port_id' => $ports[$row['origin_unlocode']]->id,
                'destination_port_id' => $ports[$row['destination_unlocode']]->id,
            ];
            $voyage = mb_strtolower($row['voyage_number']);
            $key = implode('|', [...array_values($route), $voyage]);
            $matches = FreightSailing::where($route)->whereRaw('LOWER(voyage_number) = ?', [$voyage])
                ->when($lock, fn ($q) => $q->lockForUpdate())->limit(2)->get();
            if (isset($seen[$key]) || $matches->count() > 1 || $matches->first()?->status === 'cancelled') {
                $this->fail('row_conflict', $number);
            }
            $seen[$key] = true;
            $existing = $matches->first();

            $data = $route + [
                'voyage_number' => $existing?->voyage_number ?? $row['voyage_number'],
                'vessel_id' => $vessel->id,
                'vessel_name' => $vessel->name,
                'container_types' => $types,
                'status' => 'published',
            ];
            foreach (['departure_at', 'arrival_at', 'cargo_cutoff_at'] as $field) {
                $data[$field] = CarbonImmutable::createFromFormat('!Y-m-d H:i', $row[$field], 'Asia/Dubai')->utc()->toDateTimeString();
            }
            if ($data['cargo_cutoff_at'] <= now()->utc()->toDateTimeString()) {
                $this->fail('row_past', $number);
            }

            $target = $existing ? clone $existing : new FreightSailing;
            $target->fill($data);
            $plan[] = [
                'data' => $data,
                'id' => $existing?->id,
                'before' => $existing?->getRawOriginal(),
                'action' => ! $existing ? 'new' : ($target->isDirty() ? 'update' : 'unchanged'),
                'label' => [$carrier->name, $vessel->name, $row['origin_unlocode'], $row['destination_unlocode']],
            ];
        }

        return $plan;
    }

    private function fail(string $key, ?int $row = null): never
    {
        throw ValidationException::withMessages(['schedule' => __('sailing_import.'.$key, ['row' => $row])]);
    }
}
