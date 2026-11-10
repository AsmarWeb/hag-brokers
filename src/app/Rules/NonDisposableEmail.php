<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NonDisposableEmail implements ValidationRule
{
    /**
     * Return whether the address belongs to a configured disposable mailbox domain.
     */
    public static function isDisposable(string $email): bool
    {
        $parts = explode('@', strtolower(trim($email)));

        if (count($parts) !== 2 || $parts[1] === '') {
            return false;
        }

        $domain = rtrim($parts[1], '.');
        $blockedDomains = config('auth_security.disposable_email_domains', []);

        foreach ($blockedDomains as $blockedDomain) {
            $blockedDomain = strtolower(trim((string) $blockedDomain));

            if ($blockedDomain !== '' && ($domain === $blockedDomain || str_ends_with($domain, '.'.$blockedDomain))) {
                return true;
            }
        }

        return false;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && self::isDisposable($value)) {
            $fail(__('auth.disposable_email'));
        }
    }
}
