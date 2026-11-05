<?php

namespace App\Http\Controllers;

use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\PublicExperience;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request, CompanyMarketplaceService $marketplace)
    {
        // Share public eligibility with browsing and detail pages; publication
        // does not require or imply documentary verification.
        $opportunities = $marketplace->publicQuery()
            ->with(['media', 'originPort', 'destinationPort'])
            ->latest('published_at')
            ->latest('id')
            ->limit(6)
            ->get();

        $experience = app(PublicExperience::class);

        return response()->view('welcome', compact('opportunities') + [
            'maritimeMap' => $experience->map(),
            'androidRelease' => $experience->download(),
            'mapEnabled' => (bool) $experience->settings()['map_enabled'],
        ])->header('Cache-Control', 'no-store');
    }
}
