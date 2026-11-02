<?php

namespace App\Http\Controllers;

use App\Services\Platform\CompanyMarketplaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request, CompanyMarketplaceService $marketplace): View
    {
        // Share public eligibility with browsing and detail pages; publication
        // does not require or imply documentary verification.
        $opportunities = $marketplace->publicQuery()
            ->with(['media', 'originPort', 'destinationPort'])
            ->latest('published_at')
            ->latest('id')
            ->limit(6)
            ->get();

        return view('welcome', compact('opportunities'));
    }
}
