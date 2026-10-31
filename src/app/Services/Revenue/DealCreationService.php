<?php

namespace App\Services\Revenue;

use App\Models\Deal;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class DealCreationService
{
    public function fromApprovedQuotation(
        Quotation $quotation,
        int $actorId
    ): Deal {
        return DB::transaction(function () use ($quotation, $actorId) {
            $source = Quotation::query()
                ->whereKey($quotation->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($source->approved_offer_id === null ||
                $source->offer_approved_at === null ||
                $source->approved_customer_amount === null ||
                $source->approved_carrier_amount === null ||
                $source->approved_currency === null) {
                throw ValidationException::withMessages([
                    'quotation' => 'An approved carrier offer is required.',
                ]);
            }

            if (Deal::query()
                ->where('quotation_id', $source->getKey())
                ->exists()) {
                throw ValidationException::withMessages([
                    'quotation' => 'A deal already exists for this quotation.',
                ]);
            }

            $currency = strtoupper((string) $source->approved_currency);

            if (! preg_match('/^[A-Z]{3}$/', $currency)) {
                throw ValidationException::withMessages([
                    'quotation' => 'Invalid approved quotation currency.',
                ]);
            }

            $calculator = new CommissionCalculator();

            $customerAmount = $calculator->fixed(
                (string) $source->approved_customer_amount
            );

            $carrierCost = $calculator->fixed(
                (string) $source->approved_carrier_amount
            );

            $margin = $calculator->spread(
                $customerAmount,
                $carrierCost
            );

            return Deal::query()->create([
                'reference' => 'HAG-DEAL-' . Str::upper(Str::random(16)),
                'customer_id' => $source->customer_id,
                'quotation_id' => $source->getKey(),
                'opportunity_id' => null,
                'collection_model' => 'full_service',
                'status' => 'draft',
                'currency' => $currency,
                'deal_amount' => $customerAmount,
                'supplier_cost' => $carrierCost,
                'platform_revenue' => $margin,
                'created_by' => $actorId,
                'approved_at' => null,
            ]);
        }, 3);
    }
}
