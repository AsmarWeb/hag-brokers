<?php

namespace App\Services\Auth;

final class PhoneNumber
{
    /**
     * Convert a local number and an ISO country selection into E.164.
     */
    public static function normalize(mixed $localNumber, mixed $countryIso): ?string
    {
        if (! is_string($localNumber) || ! is_string($countryIso)) {
            return null;
        }

        $country = config('phone.countries.'.strtoupper(trim($countryIso)));

        if (! is_array($country) || empty($country['dial_code'])) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $localNumber);

        if (! is_string($digits) || $digits === '') {
            return null;
        }

        // National trunk prefixes (for example, 050 in the UAE) are not part of E.164.
        $digits = ltrim($digits, '0');
        $dialDigits = ltrim((string) $country['dial_code'], '+');
        $number = $dialDigits.$digits;

        if (! preg_match('/^[1-9][0-9]{7,14}$/D', $number)) {
            return null;
        }

        return '+'.$number;
    }

    /**
     * Return the ISO country that best matches an existing E.164 number.
     */
    public static function countryFor(?string $phone): string
    {
        if (! $phone) {
            return 'AE';
        }

        $countries = config('phone.countries', []);
        uasort($countries, fn (array $a, array $b) => strlen($b['dial_code']) <=> strlen($a['dial_code']));

        foreach ($countries as $iso => $country) {
            if (str_starts_with($phone, $country['dial_code'])) {
                return $iso;
            }
        }

        return 'AE';
    }
}
