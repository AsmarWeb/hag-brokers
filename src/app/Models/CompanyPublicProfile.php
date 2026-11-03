<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyPublicProfile extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_published' => 'boolean'];

    public function text(string $field): string
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';

        return (string) ($this->getAttribute($field.'_'.$locale) ?: $this->getAttribute($field.'_'.($locale === 'ar' ? 'en' : 'ar')));
    }

    public function completion(): array
    {
        return [
            'identity' => (bool) ($this->name_ar || $this->name_en),
            'story' => (bool) (($this->name_ar && mb_strlen(trim($this->about_ar ?? '')) >= 80) || ($this->name_en && mb_strlen(trim($this->about_en ?? '')) >= 80)),
            'sector' => (bool) $this->sector,
            'location' => (bool) ($this->country && $this->city),
            'specialties' => (bool) $this->specialties,
            'markets' => (bool) $this->markets,
        ];
    }

    public function ready(): bool
    {
        $steps = $this->completion();

        return $steps['identity'] && $steps['story'] && $steps['sector'] && $steps['location'];
    }
}
