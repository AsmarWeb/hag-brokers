<?php

namespace App\Services\Revenue;

/** Exact, non-negative two-decimal report amounts; never convert money to float. */
final class ReportMoney
{
    public static function normalize(string $value): string
    {
        if (!preg_match('/^\d+(?:\.\d{1,2})?$/D', $value)) {
            throw new \InvalidArgumentException('Invalid non-negative report amount.');
        }
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        return (ltrim($whole, '0') ?: '0').'.'.str_pad($fraction, 2, '0');
    }

    public static function add(string $left, string $right): string
    {
        $a = str_replace('.', '', self::normalize($left));
        $b = str_replace('.', '', self::normalize($right));
        $i = strlen($a) - 1;
        $j = strlen($b) - 1;
        $carry = 0;
        $digits = '';
        while ($i >= 0 || $j >= 0 || $carry) {
            $value = ($i >= 0 ? (int) $a[$i--] : 0)
                + ($j >= 0 ? (int) $b[$j--] : 0) + $carry;
            $digits = (string) ($value % 10).$digits;
            $carry = intdiv($value, 10);
        }
        $digits = str_pad($digits, 3, '0', STR_PAD_LEFT);
        return self::normalize(substr($digits, 0, -2).'.'.substr($digits, -2));
    }

    public static function format(string $value): string
    {
        [$whole, $fraction] = explode('.', self::normalize($value));
        return preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', $whole).'.'.$fraction;
    }
}
