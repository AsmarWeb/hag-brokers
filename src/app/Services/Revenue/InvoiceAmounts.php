<?php

namespace App\Services\Revenue;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

final class InvoiceAmounts
{
    public function calculate(string $commission, string $basis, string $treatment, string $exchangeRate): array
    {
        if (!in_array($basis, ['exclusive', 'inclusive'], true) || !in_array($treatment, ['standard', 'zero'], true)) {
            throw new \InvalidArgumentException('Explicit tax basis and treatment required.');
        }
        $amount = BigDecimal::of(ReportMoney::normalize($commission));
        $rate = BigDecimal::of($exchangeRate)->toScale(8, RoundingMode::UNNECESSARY);
        if ($rate->isLessThanOrEqualTo('0')) throw new \InvalidArgumentException('Positive exchange rate required.');
        $vatRate = $treatment === 'standard' ? '0.05' : '0.00';
        if ($basis === 'exclusive') {
            $net = $amount;
            $vat = $net->multipliedBy($vatRate)->toScale(2, RoundingMode::HALF_UP);
            $gross = $net->plus($vat);
        } else {
            $gross = $amount;
            $net = $gross->dividedBy(BigDecimal::of('1')->plus($vatRate), 2, RoundingMode::HALF_UP);
            $vat = $gross->minus($net);
        }
        $netAed = $net->multipliedBy($rate)->toScale(2, RoundingMode::HALF_UP);
        $vatAed = $vat->multipliedBy($rate)->toScale(2, RoundingMode::HALF_UP);
        $grossAed = $netAed->plus($vatAed);
        $values = [
            'subtotal' => $net, 'tax_amount' => $vat, 'total' => $gross,
            'subtotal_aed' => $netAed, 'tax_aed' => $vatAed, 'total_aed' => $grossAed,
        ];
        foreach ($values as $value) {
            if ($value->isLessThan('0') || $value->isGreaterThan('9999999999999999.99')) {
                throw new \InvalidArgumentException('Invoice amount out of range.');
            }
        }
        return array_map(fn ($value) => (string) $value->toScale(2), $values)
            + ['exchange_rate' => (string) $rate, 'tax_rate_bps' => $treatment === 'standard' ? 500 : 0];
    }
}
