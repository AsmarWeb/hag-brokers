<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vessel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminVesselController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $vessels = Vessel::query()
            ->with([
                'carrier',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('imo', 'like', "%{$search}%")
                        ->orWhere('mmsi', 'like', "%{$search}%")
                        ->orWhereHas('carrier', function ($carrier) use ($search) {
                            $carrier->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.vessels.index', [
            'vessels' => $vessels,
        ]);
    }
}
