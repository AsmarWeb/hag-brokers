<?php

namespace App\Services\Revenue;

use App\Models\Deal;
use App\Models\DealAgreement;
use App\Models\DealCommission;
use App\Models\DealAudit;
use Throwable;

final class DealIntegrityVerifier
{
    /**
     * Read-only verification of an approved financial deal.
     *
     * @return array{valid: bool, issues: array<int, string>}
     */
    public function verify(Deal $deal): array
    {
        $issues = [];

        $deal = Deal::query()->find($deal->getKey());

        if (! $deal) {
            return $this->result(['deal_not_found']);
        }

        if ($deal->status !== 'approved' ||
            $deal->approved_at === null) {
            $issues[] = 'deal_not_approved';
        }

        $agreements = $deal->agreements()
            ->where('status', 'approved')->get();

        if ($agreements->count() !== 1) {
            $issues[] = 'approved_agreement_count_invalid';
        }

        $agreement = $agreements->first();

        if (! $agreement) {
            return $this->result($issues);
        }

        if ($agreement->approved_at === null ||
            $agreement->approved_by === null) {
            $issues[] = 'agreement_approval_metadata_invalid';
        }

        $commissions = $deal->commissions()->get();

        if ($commissions->count() !== 1) {
            $issues[] = 'commission_count_invalid';
        }

        $commission = $commissions->first();

        if (! $commission ||
            (string) $commission->deal_agreement_id !==
            (string) $agreement->getKey()) {
            $issues[] = 'commission_agreement_mismatch';
        }

        $calculator = new CommissionCalculator();

        try {
            $basis = $calculator->fixed(
                (string) $deal->deal_amount
            );

            $cost = $deal->supplier_cost === null
                ? null
                : $calculator->fixed(
                    (string) $deal->supplier_cost
                );

            $kind = (string) $agreement->fee_kind;
            $rate = null;

            if ($kind === 'percent') {
                if ($agreement->commission_bps === null) {
                    throw new \LogicException('Missing rate.');
                }

                $rate = (int) $agreement->commission_bps;

                if ($rate < 0 || $rate > 10000) {
                    throw new \LogicException('Invalid rate.');
                }

                $amount = $calculator->percent(
                    $basis, $rate
                );
            } elseif ($kind === 'fixed') {
                if ($agreement->fixed_fee === null) {
                    throw new \LogicException('Missing fixed fee.');
                }

                $amount = $calculator->fixed(
                    (string) $agreement->fixed_fee
                );
            } elseif ($kind === 'spread') {
                if ($deal->collection_model !== 'full_service' ||
                    $cost === null) {
                    throw new \LogicException('Invalid spread.');
                }

                $amount = $calculator->spread(
                    $basis, $cost
                );
            } else {
                throw new \LogicException('Unknown fee kind.');
            }

            if (! preg_match(
                '/^[A-Z]{3}$/',
                (string) $deal->currency
            )) {
                throw new \LogicException('Invalid currency.');
            }

            if (trim((string) $agreement->terms) === '') {
                throw new \LogicException('Missing terms.');
            }

            // Maintain the exact field order and encoding
            // used by DealApprovalService.
            $integrityVersion = (int) $agreement->integrity_version;

            if (! in_array($integrityVersion, [1, 2], true)) {
                throw new \LogicException(
                    'Unsupported integrity version.'
                );
            }

            $builder = new FinancialSnapshotBuilder();

            $snapshot = $integrityVersion === 1
                ? $builder->legacy(
                    $deal,
                    $agreement,
                    $amount,
                    $rate
                )
                : $builder->version2(
                    $deal,
                    $agreement,
                    $amount,
                    $rate
                );

            $expectedHash = hash('sha256', $snapshot);
            $storedHash = (string) $agreement->content_sha256;

            if (! hash_equals($expectedHash, $storedHash)) {
                $issues[] = 'agreement_hash_mismatch';
            }

            if ($commission) {
                if ((string) $commission->currency !==
                    (string) $deal->currency ||
                    (string) $commission->basis_amount !== $basis ||
                    (string) $commission->amount !== $amount ||
                    $commission->rate_bps !== $rate ||
                    $commission->status !== 'pending' ||
                    $commission->earned_at !== null) {
                    $issues[] = 'commission_snapshot_mismatch';
                }
            }
        } catch (Throwable $exception) {
            $issues[] = 'financial_snapshot_invalid';
        }

        $audits = DealAudit::query()
            ->where('deal_id', $deal->getKey())
            ->where('event', 'agreement.approved')
            ->get();

        if ($audits->count() !== 1) {
            $issues[] = 'approval_audit_count_invalid';
        }

        $audit = $audits->first();

        if ($audit) {
            try {
                $payload = $audit->payload;

                if (! is_array($payload)) {
                    throw new \LogicException('Invalid audit payload.');
                }

                $serialized = json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                );

                if (! hash_equals(
                    hash('sha256', $serialized),
                    (string) $audit->payload_sha256
                )) {
                    $issues[] = 'audit_hash_mismatch';
                }

                if ((string) ($payload['agreement_id'] ?? '') !==
                    (string) $agreement->getKey() ||
                    (string) ($payload['agreement_version'] ?? '') !==
                    (string) $agreement->version ||
                    (string) ($payload['agreement_sha256'] ?? '') !==
                    (string) $agreement->content_sha256 ||
                    (string) ($payload['commission_id'] ?? '') !==
                    (string) ($commission?->getKey() ?? '') ||
                    (string) ($payload['currency'] ?? '') !==
                    (string) $deal->currency ||
                    (string) ($audit->actor_id ?? '') !==
                    (string) $agreement->approved_by ||
                    ! $commission ||
                    ! isset($amount) ||
                    (string) ($payload['commission_amount'] ?? '') !==
                    (string) $amount) {
                    $issues[] = 'audit_payload_mismatch';
                }
            } catch (Throwable $exception) {
                $issues[] = 'audit_payload_invalid';
            }
        }

        return $this->result($issues);
    }

    private function result(array $issues): array
    {
        $issues = array_values(array_unique($issues));

        return [
            'valid' => count($issues) === 0,
            'issues' => $issues,
        ];
    }
}
