<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Platform\MobileAppSettings;
use Illuminate\Http\Request;

class MobileAppConfigController extends Controller
{
    public function __invoke(Request $request, MobileAppSettings $settings)
    {
        $content = $settings->publicContent();
        $assetOrigin = $request->getSchemeAndHttpHost();

        $rewriteLocalAsset = static function ($url) use ($assetOrigin) {
            if (! is_string($url) || $url === '') {
                return $url;
            }

            $parts = parse_url($url);

            if (! is_array($parts)) {
                return $url;
            }

            $host = strtolower($parts['host'] ?? '');

            if (! in_array($host, ['localhost', '127.0.0.1'], true)) {
                return $url;
            }

            return $assetOrigin
                . ($parts['path'] ?? '/')
                . (isset($parts['query']) ? '?' . $parts['query'] : '');
        };

        foreach (['welcome_image', 'home_image'] as $key) {
            $content[$key] = $rewriteLocalAsset($content[$key] ?? null);
        }

        foreach ($content['category_cards'] ?? [] as $index => $card) {
            $content['category_cards'][$index]['image'] = $rewriteLocalAsset(
                $card['image'] ?? null
            );
        }

        return response()->json(['data' => $content]);
    }
}
