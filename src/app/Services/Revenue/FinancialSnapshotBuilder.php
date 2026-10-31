<?php

namespace App\Services\Revenue;

use App\Models\Deal;
use App\Models\DealAgreement;

final class FinancialSnapshotBuilder
{
    /**
     * Legacy V1 canonical snapshot.
     *
     * Keep field order, JSON options and monetary
     * normalization unchanged for existing hashes.
     */
    public function legacy(
        Deal $deal,
        DealAgreement $agreement,
        string $commissionAmount,
        ?int $rate
    ): string {
        $calculator = new CommissionCalculator();

        $basis = $calculator->fixed(
            (string) $deal->deal_amount
        );

        $cost = $deal->supplier_cost === null
            ? null
            : $calculator->fixed(
                (string) $deal->supplier_cost
            );

        $fixedFee = $agreement->fixed_fee === null
            ? null
            : $calculator->fixed(
                (string) $agreement->fixed_fee
            );

        return json_encode([
            'deal_id' => $deal->getKey(),
            'agreement_id' => $agreement->getKey(),
            'version' => $agreement->version,
            'collection_model' => $deal->collection_model,
            'currency' => $deal->currency,
            'deal_amount' => $basis,
            'supplier_cost' => $cost,
            'fee_kind' => (string) $agreement->fee_kind,
            'commission_bps' => $rate,
            'fixed_fee' => $fixedFee,
            'terms' => $agreement->terms,
            'commission_amount' => $commissionAmount,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * V2 includes financial ownership and expected revenue fields.
     * The legacy method must remain unchanged.
     */
    public function version2(
        Deal $deal,
        DealAgreement $agreement,
        string $commissionAmount,
        ?int $rate
    ): string {
        $legacy = json_decode(
            $this->legacy($deal, $agreement, $commissionAmount, $rate),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $calculator = new CommissionCalculator();

        $snapshot = array_merge(
            ['snapshot_version' => 2],
            $legacy,
            [
                'customer_id' => $deal->customer_id,
                'quotation_id' => $deal->quotation_id,
                'opportunity_id' => $deal->opportunity_id,
                'platform_revenue' => $calculator->fixed(
                    (string) $deal->platform_revenue
                ),
            ]
        );

        return json_encode(
            $snapshot,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }

}
