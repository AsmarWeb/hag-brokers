<?php
namespace App\Services\Revenue;
use InvalidArgumentException;
/** Strict two-decimal integer-minor-unit arithmetic; currencies with other minor units need a separate adapter. */
final class CommissionCalculator {
    public static function minorUnits(string $amount): int {
        if (!preg_match('/^(0|[1-9][0-9]{0,13})(\.[0-9]{1,2})?$/D', $amount)) {
            throw new InvalidArgumentException('Invalid 2-decimal monetary amount.');
        }
        [$whole,$fraction] = array_pad(explode('.',$amount,2),2,'');
        $minor = ((int)$whole * 100) + (int)str_pad($fraction,2,'0');
        if ($minor < 0 || $minor > 900000000000000) throw new InvalidArgumentException('Amount too large.');
        return $minor;
    }
    public static function format(int $minor): string {
        if ($minor < 0) throw new InvalidArgumentException('Negative amount.');
        return intdiv($minor,100).'.'.str_pad((string)($minor%100),2,'0',STR_PAD_LEFT);
    }
    /** Half-up rounding, no float arithmetic. */
    public function percent(string $basis, int $bps): string {
        if ($bps < 0 || $bps > 10000) throw new InvalidArgumentException('Invalid bps.');
        $minor=self::minorUnits($basis);
        return self::format(intdiv($minor*$bps+5000,10000));
    }
    public function fixed(string $fee): string { return self::format(self::minorUnits($fee)); }
    public function spread(string $customerAmount, string $supplierCost): string {
        $difference=self::minorUnits($customerAmount)-self::minorUnits($supplierCost);
        if ($difference < 0) throw new InvalidArgumentException('Supplier cost exceeds customer amount.');
        return self::format($difference);
    }
    public function calculate(string $kind,string $basis,?int $bps=null,?string $fixed=null,?string $supplierCost=null): string {
        return match($kind) {
            'percent' => $this->percent($basis,$bps ?? throw new InvalidArgumentException('bps required')),
            'fixed' => $this->fixed($fixed ?? throw new InvalidArgumentException('fixed fee required')),
            'spread' => $this->spread($basis,$supplierCost ?? throw new InvalidArgumentException('supplier cost required')),
            default => throw new InvalidArgumentException('Unknown fee kind'),
        };
    }
}
