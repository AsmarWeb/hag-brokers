<?php

namespace App\Services\Revenue;

use App\Models\Deal;
use App\Models\DealAgreement;
use App\Models\DealCommission;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DealApprovalService
{
    public function approve(
        Deal $deal,
        DealAgreement $agreement,
        int $actorId
    ): DealCommission {
        return DB::transaction(function () use ($deal, $agreement, $actorId) {
            $lockedDeal = Deal::query()
                ->whereKey($deal->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $lockedAgreement = DealAgreement::query()
                ->whereKey($agreement->getKey())
                ->where('deal_id', $lockedDeal->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedDeal->status !== 'draft' ||
                $lockedDeal->approved_at !== null ||
                $lockedAgreement->status !== 'draft') {
                throw ValidationException::withMessages([
                    'agreement' => 'Only a draft agreement on a draft deal can be approved.',
                ]);
            }

            if ($lockedDeal->agreements()
                ->where('status', 'approved')
                ->exists()) {
                throw ValidationException::withMessages([
                    'agreement' => 'An approved agreement already exists.',
                ]);
            }

            if ($lockedDeal->commissions()->exists()) {
                throw ValidationException::withMessages([
                    'agreement' => 'A commission record already exists.',
                ]);
            }

            $kind = (string) $lockedAgreement->fee_kind;
            $calculator = new CommissionCalculator();

            $basis = (string) $lockedDeal->deal_amount;
            $cost = $lockedDeal->supplier_cost === null
                ? null
                : (string) $lockedDeal->supplier_cost;

            if ($kind === 'percent') {
                if ($lockedAgreement->commission_bps === null) {
                    throw ValidationException::withMessages([
                        'agreement' => 'Missing commission rate.',
                    ]);
                }

                $rate = (int) $lockedAgreement->commission_bps;

                if ($rate < 0 || $rate > 10000) {
                    throw ValidationException::withMessages([
                        'agreement' => 'Invalid commission rate.',
                    ]);
                }

                $amount = $calculator->percent($basis, $rate);
            } elseif ($kind === 'fixed') {
                if ($lockedAgreement->fixed_fee === null) {
                    throw ValidationException::withMessages([
                        'agreement' => 'Missing fixed fee.',
                    ]);
                }

                $rate = null;
                $amount = $calculator->fixed(
                    (string) $lockedAgreement->fixed_fee
                );
            } elseif ($kind === 'spread') {
                if ($lockedDeal->collection_model !== 'full_service' ||
                    $cost === null) {
                    throw ValidationException::withMessages([
                        'agreement' => 'Spread requires full-service collection and supplier cost.',
                    ]);
                }

                $rate = null;
                $amount = $calculator->spread($basis, $cost);
            } else {
                throw ValidationException::withMessages([
                    'agreement' => 'Unsupported fee type.',
                ]);
            }

            if (! preg_match('/^[A-Z]{3}$/', (string) $lockedDeal->currency)) {
                throw ValidationException::withMessages([
                    'agreement' => 'Invalid deal currency.',
                ]);
            }

            if (trim((string) $lockedAgreement->terms) === '') {
                throw ValidationException::withMessages([
                    'agreement' => 'Agreement terms are required.',
                ]);
            }

            $snapshot = (new FinancialSnapshotBuilder())->version2(
                $lockedDeal,
                $lockedAgreement,
                $amount,
                $rate
            );

            $lockedAgreement->forceFill([
                'status' => 'approved',
                'integrity_version' => 2,
                'content_sha256' => hash('sha256', $snapshot),
                'approved_by' => $actorId,
                'approved_at' => now(),
            ])->save();

            $commission = $lockedDeal->commissions()->create([
                'deal_agreement_id' => $lockedAgreement->getKey(),
                'currency' => $lockedDeal->currency,
                'basis_amount' => $calculator->fixed($basis),
                'rate_bps' => $rate,
                'amount' => $amount,
                'status' => 'pending',
                'earned_at' => null,
            ]);

            $lockedDeal->forceFill([
                'status' => 'approved',
                'approved_at' => now(),
            ])->save();

            $auditPayload = [
                'agreement_id' => $lockedAgreement->getKey(),
                'agreement_version' => $lockedAgreement->version,
                'commission_id' => $commission->getKey(),
                'commission_amount' => $amount,
                'currency' => $lockedDeal->currency,
                'agreement_sha256' => $lockedAgreement->content_sha256,
            ];

            $serialized = json_encode(
                $auditPayload,
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );

            \App\Models\DealAudit::create([
                'deal_id' => $lockedDeal->getKey(),
                'actor_id' => $actorId,
                'event' => 'agreement.approved',
                'payload' => $auditPayload,
                'payload_sha256' => hash('sha256', $serialized),
            ]);

            return $commission;
        }, 3);
    }
}
