<?php

namespace App\Services\Seo;

use Illuminate\Support\Str;

final class OpportunitySlug
{
    public static function make(string $title): string
    {
        $text = preg_replace('/[\p{M}\x{0640}]+/u', '', mb_strtolower(strip_tags($title))) ?? '';
        $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text) ?? '';
        $base = trim(mb_substr(trim($text, '-'), 0, 130), '-');

        return ($base ?: 'opportunity').'-'.strtolower(Str::random(10));
    }
}
