<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Platform\MobileAppSettings;

class MobileAppConfigController extends Controller
{
    public function __invoke(MobileAppSettings $settings)
    {
        return response()->json(['data' => $settings->publicContent()]);
    }
}
