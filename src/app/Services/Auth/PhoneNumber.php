<?php

namespace App\\Services\\Auth;

final class PhoneNumber
{
    /**
     * Convert a local phone number and ISO country selection into E.164.
     * Already-normalized E.164 values remain supported for API compatibility.
     */
    public static function normalize(mixed $localNumber, mixed $countryIso = null): ?string
    {
        if (! is_string($localNumber)) {
            return null;
        }

        $localNumber = trim($localNumber);

        if (str_starts_with($localNumber, '+')) {
            $international = '+'.preg_replace('/\\D+/', '', substr($localNumber, 1));

            return preg_match('/^\\+[1-9][0-9]{7,14}$/D', $international) ? $international : null;
        }

        if (! is_string($countryIso) || $countryIso === '') {
            return null;
        }

        $country = config('phone.countries.'.strtoupper(trim($countryIso)));

        if (! is_array($country) || empty($country['dial_code'])) {
            return null;
        }

        $digits = preg_replace('/\\D+/', '', $localNumber);

        if (! is_string($digits) || $digits === '') {
            return null;
        }

        // Remove national trunk prefix (for example, 0 in UAE 050 numbers).
        $digits = ltrim($digits, '0');
        $dialDigits = ltrim((string) $country['dial_code'], '+');
        $number = $dialDigits.$digits;

        if (! preg_match('/^[1-9][0-9]{7,14}$/D', $number)) {
            return null;
        }

        return '+'.$number;
    }

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

    public static function nationalNumber(?string $phone): string
    {
        if (! $phone) {
            return '';
        }

        $country = config('phone.countries.'.self::countryFor($phone));

        if (! is_array($country)) {
            return preg_replace('/\\D+/', '', $phone);
        }

        return ltrim(substr($phone, strlen($country['dial_code'])), '0');
    }
}
