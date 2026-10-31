<?php

namespace App\Services\Revenue;

use App\Models\Deal;
use App\Models\DealAgreement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DealAgreementService
{
    public function createDraft(Deal $deal, array $data): DealAgreement
    {
        return DB::transaction(function () use ($deal, $data) {
            $locked = Deal::query()
                ->whereKey($deal->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'draft') {
                throw ValidationException::withMessages([
                    'deal' => 'Only draft deals can receive new agreements.',
                ]);
            }

            $kind = $data['fee_kind'];

            if ($kind === 'spread' &&
                ($locked->collection_model !== 'full_service' ||
                 $locked->supplier_cost === null)) {
                throw ValidationException::withMessages([
                    'fee_kind' => 'Spread requires a full-service deal with supplier cost.',
                ]);
            }

            if ($kind === 'percent' && ! isset($data['commission_bps'])) {
                throw ValidationException::withMessages([
                    'commission_bps' => 'Commission rate is required.',
                ]);
            }

            if ($kind === 'fixed' && ! isset($data['fixed_fee'])) {
                throw ValidationException::withMessages([
                    'fixed_fee' => 'Fixed fee is required.',
                ]);
            }

            $calculator = new CommissionCalculator();

            if ($kind === 'fixed') {
                $calculator->fixed((string) $data['fixed_fee']);
            }

            if ($kind === 'percent') {
                $calculator->percent(
                    (string) $locked->deal_amount,
                    (int) $data['commission_bps']
                );
            }

            if ($kind === 'spread') {
                $calculator->spread(
                    (string) $locked->deal_amount,
                    (string) $locked->supplier_cost
                );
            }

            if ($locked->agreements()
                ->where('status', 'draft')
                ->exists()) {
                throw ValidationException::withMessages([
                    'agreement' => 'Complete or cancel the existing draft first.',
                ]);
            }

            $version = (int) $locked->agreements()->max('version') + 1;

            return $locked->agreements()->create([
                'version' => $version,
                'status' => 'draft',
                'fee_kind' => $kind,
                'commission_bps' => $kind === 'percent'
                    ? (int) $data['commission_bps']
                    : null,
                'fixed_fee' => $kind === 'fixed'
                    ? $calculator->fixed((string) $data['fixed_fee'])
                    : null,
                'terms' => $data['terms'],
                'content_sha256' => null,
                'approved_by' => null,
                'approved_at' => null,
            ]);
        }, 3);
    }
}
