<?php

namespace App\Services\Platform;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MobileAppSettings
{
    public function defaults(): array
    {
        $cards = [];

        foreach (OpportunityCatalog::CATEGORIES as $index => $code) {
            $cards[$code] = [
                'code' => $code,
                'title_ar' => __('trade.categories.'.$code, [], 'ar'),
                'title_en' => __('trade.categories.'.$code, [], 'en'),
                'description_ar' => '',
                'description_en' => '',
                'image' => null,
                'enabled' => true,
                'featured' => in_array($code, ['food', 'agriculture', 'machinery', 'electronics'], true),
                'sort_order' => $index + 1,
            ];
        }

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
            'announcement_ar' => '',
            'announcement_en' => '',
            'welcome_enabled' => true,
            'show_marine' => true,
            'categories' => OpportunityCatalog::CATEGORIES,
            'category_cards' => $cards,
            'welcome_image' => null,
            'home_image' => null,
        ];
    }

    public function stored(): array
    {
        $row = DB::table('mobile_app_settings')->where('id', 1)->first();
        $content = array_replace(
            $this->defaults(),
            $row ? json_decode($row->content, true, 512, JSON_THROW_ON_ERROR) : []
        );

        // Backfill newly introduced category-card fields for older saved configurations.
        $defaults = $this->defaults()['category_cards'];
        $savedCards = is_array($content['category_cards'] ?? null) ? $content['category_cards'] : [];
        foreach ($defaults as $code => $card) {
            $content['category_cards'][$code] = array_replace(
                $card,
                is_array($savedCards[$code] ?? null) ? $savedCards[$code] : []
            );
        }

        return ['version' => (int) ($row->version ?? 0), 'content' => $content];
    }

    public function publicContent(): array
    {
        $s = $this->stored();
        $content = array_intersect_key($s['content'], $this->defaults());
        $content['categories'] = array_values(array_intersect($content['categories'], OpportunityCatalog::CATEGORIES));

        foreach (['welcome_image', 'home_image'] as $key) {
            $path = $content[$key] ?? null;
            $content[$key] = is_string($path) && str_starts_with($path, 'mobile-app/')
                ? Storage::disk('public')->url($path)
                : null;
        }

        $cards = [];
        foreach ($content['category_cards'] as $code => $card) {
            if (! in_array($code, OpportunityCatalog::CATEGORIES, true) || empty($card['enabled'])) {
                continue;
            }

            $path = $card['image'] ?? null;
            $card['image'] = is_string($path) && str_starts_with($path, 'mobile-app/categories/')
                ? Storage::disk('public')->url($path)
                : null;
            $cards[] = $card;
        }

        usort($cards, fn ($a, $b) => ((int) $a['sort_order'] <=> (int) $b['sort_order'])
            ?: strcmp($a['code'], $b['code']));

        $content['category_cards'] = array_values($cards);

        return ['version' => $s['version'], ...$content];
    }
}
