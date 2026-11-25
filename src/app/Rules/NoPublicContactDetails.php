<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class NoPublicContactDetails implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $text = (string) $value;
        $containsEmail = preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/iu', $text) === 1;
        $containsUrl = preg_match('/(?:https?:\/\/|www\.|\b(?:[A-Z0-9-]+\.)+[A-Z]{2,}\b)/iu', $text) === 1;
        $containsPhoneNumber = preg_match('/(?<!\d)\+?\d(?:[\s().-]*\d){6,}(?!\d)/u', $text) === 1;

        if ($containsEmail || $containsUrl || $containsPhoneNumber) {
            $fail(__('maritime_board.public_text_contact_error'));
        }
    }
}
