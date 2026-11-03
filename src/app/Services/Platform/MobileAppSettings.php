<?php

namespace App\Services\Platform;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MobileAppSettings
{
    public function defaults(): array
    {
        return [
            'app_name' => 'HAG Brokers',
            'welcome_title_ar' => 'بحر من الفرص. عالم من الأعمال.',
            'welcome_title_en' => 'A sea of opportunity. A world of business.',
            'welcome_body_ar' => 'وساطة الشحن البحري والفرص التجارية، من الإمارات إلى العالم.',
            'welcome_body_en' => 'Sea freight brokerage and commercial opportunities, from the UAE to the world.',
            'home_title_ar' => 'اكتشف فرصتك القادمة',
            'home_title_en' => 'Discover your next opportunity',
            'home_body_ar' => 'تواصل مع الشركات واكتشف فرص التجارة والأصول البحرية.',
            'home_body_en' => 'Connect with companies. Discover trade and marine assets.',
            'announcement_ar' => '', 'announcement_en' => '',
            'welcome_enabled' => true,
            'show_marine' => true,
            'categories' => OpportunityCatalog::CATEGORIES,
            'welcome_image' => null, 'home_image' => null,
        ];
    }

    public function stored(): array
    {
        $row = DB::table('mobile_app_settings')->where('id', 1)->first();

        return ['version' => (int) ($row->version ?? 0), 'content' => array_replace($this->defaults(), $row ? json_decode($row->content, true, 512, JSON_THROW_ON_ERROR) : [])];
    }

    public function publicContent(): array
    {
        $s = $this->stored();
        $content = array_intersect_key($s['content'], $this->defaults());
        $content['categories'] = array_values(array_intersect($content['categories'], OpportunityCatalog::CATEGORIES));
        foreach (['welcome_image', 'home_image'] as $key) {
            $content[$key] = $content[$key] && str_starts_with($content[$key], 'mobile-app/') ? Storage::disk('public')->url($content[$key]) : null;
        }

        return ['version' => $s['version'], ...$content];
    }
}
