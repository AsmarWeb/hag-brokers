<?php

namespace App\Services\Revenue;

use App\Models\Deal;
use App\Models\DealCommission;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

final class FinancialReport
{
    public function deals(array $filters): Builder
    {
        $query = Deal::query()->where('status', 'approved')->whereNotNull('approved_at');
        if (!empty($filters['customer_id'])) $query->where('customer_id', $filters['customer_id']);
        if (!empty($filters['currency'])) $query->where('currency', $filters['currency']);
        if (!empty($filters['date_from'])) {
            $query->where('approved_at', '>=', CarbonImmutable::parse($filters['date_from'])->startOfDay());
        }
        if (!empty($filters['date_to'])) {
            $query->where('approved_at', '<', CarbonImmutable::parse($filters['date_to'])->addDay()->startOfDay());
        }
        if (!empty($filters['commission_status'])) {
            $query->whereHas('commissions', function ($commissions) use ($filters) {
                $commissions->where('status', $filters['commission_status']);
            });
        }
        return $query;
    }

    public function summary(Builder $deals, array $filters = []): array
    {
        $currencies = [];
        foreach ((clone $deals)->select(['id', 'currency', 'deal_amount'])->lazyById(500) as $deal) {
            $currency = (string) $deal->currency;
            $currencies[$currency] ??= $this->emptyRow();
            $currencies[$currency]['deal_count']++;
            $currencies[$currency]['deal_value'] = ReportMoney::add(
                $currencies[$currency]['deal_value'], (string) $deal->deal_amount
            );
        }
        // Commission amounts are grouped by their own stored currency. Never convert or mix currencies.
        $commissions = DealCommission::whereIn('deal_id', (clone $deals)->select('deals.id'));
        if (!empty($filters['commission_status'])) $commissions->where('status', $filters['commission_status']);
        foreach ($commissions->lazyById(500) as $commission) {
            $currency = (string) $commission->currency;
            $currencies[$currency] ??= $this->emptyRow();
            $status = in_array($commission->status, ['pending', 'earned', 'reversed'], true)
                ? $commission->status : 'other';
            $currencies[$currency][$status] = ReportMoney::add(
                $currencies[$currency][$status], (string) $commission->amount
            );
            $currencies[$currency]['commission_count']++;
        }
        ksort($currencies);
        return $currencies;
    }

    private function emptyRow(): array
    {
        return [
            'deal_count' => 0, 'commission_count' => 0,
            'deal_value' => '0.00', 'pending' => '0.00', 'earned' => '0.00',
            'reversed' => '0.00', 'other' => '0.00',
        ];
    }
}
