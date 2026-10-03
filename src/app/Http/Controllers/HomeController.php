<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the public homepage.
     */
    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | Public Opportunities
        |--------------------------------------------------------------------------
        |
        | نعرض فقط الفرص التي:
        | - public
        | - verified
        | - published
        | - غير منتهية
        |
        */

        $opportunities = Opportunity::query()
            ->with([
                'media',
                'originPort',
                'destinationPort',
            ])
            ->where('visibility', 'public')
            ->where('verification_status', 'verified')
            ->whereNotNull('published_at')
            ->where(function ($query) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('published_at')
            ->limit(6)
            ->get();

        return view('welcome', [
            'opportunities' => $opportunities,
        ]);
    }
}
