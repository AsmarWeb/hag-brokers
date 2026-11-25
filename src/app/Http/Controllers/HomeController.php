<?php

namespace App\Http\Controllers;

use App\Services\Platform\MobileAppSettings;
use App\Services\Platform\PublicExperience;

class HomeController extends Controller
{
    public function index()
    {
        $experience = app(PublicExperience::class);

        $categoryCards = app(MobileAppSettings::class)->publicContent()['category_cards'] ?? [];

        return response()->view('welcome', compact('categoryCards') + [
            'maritimeMap' => $experience->map(),
            'androidRelease' => $experience->download(),
            'mapEnabled' => (bool) $experience->settings()['map_enabled'],
        ])->header('Cache-Control', 'no-store');
    }
}
