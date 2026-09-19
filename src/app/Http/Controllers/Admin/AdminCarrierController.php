<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCarrierController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $carriers = Carrier::query()
            ->withCount([
                'vessels',
                'quotationOffers',
                'shipments',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.carriers.index', [
            'carriers' => $carriers,
        ]);
    }
}
