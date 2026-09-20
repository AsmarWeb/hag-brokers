<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $setting = Setting::first();

        if (
            $setting?->maintenance_mode &&
            !auth()->check()
        ) {
            return response()->view(
                'maintenance',
                ['setting' => $setting],
                503
            );
        }

        return $next($request);
    }
}
