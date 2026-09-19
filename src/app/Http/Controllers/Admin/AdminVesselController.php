<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Vessel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminVesselController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $vessels = Vessel::query()
            ->with('carrier')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('imo', 'like', "%{$search}%")
                        ->orWhere('mmsi', 'like', "%{$search}%")
                        ->orWhere('call_sign', 'like', "%{$search}%")
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

    public function create(): View
    {
        $carriers = Carrier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.vessels.create', [
            'carriers' => $carriers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'carrier_id' => [
                'nullable',
                'integer',
                'exists:carriers,id',
            ],
            'name' => ['required', 'string', 'max:255'],
            'imo' => [
                'nullable',
                'string',
                'max:50',
                'unique:vessels,imo',
            ],
            'mmsi' => [
                'nullable',
                'string',
                'max:50',
                'unique:vessels,mmsi',
            ],
            'call_sign' => ['nullable', 'string', 'max:100'],
            'flag' => ['nullable', 'string', 'max:100'],
            'vessel_type' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'course' => ['nullable', 'numeric', 'between:0,360'],
            'destination' => ['nullable', 'string', 'max:255'],
            'eta' => ['nullable', 'date'],
            'last_position_at' => ['nullable', 'date'],
            'is_in_gulf' => ['nullable', 'boolean'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $validated['is_in_gulf'] = $request->boolean('is_in_gulf');

        Vessel::create($validated);

        return redirect()
            ->route('admin.vessels.index')
            ->with('success', 'Vessel created successfully.');
    }

    public function edit(Vessel $vessel): View
    {
        $carriers = Carrier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.vessels.edit', [
            'vessel' => $vessel,
            'carriers' => $carriers,
        ]);
    }

    public function update(
        Request $request,
        Vessel $vessel
    ): RedirectResponse {
        $validated = $request->validate([
            'carrier_id' => [
                'nullable',
                'integer',
                'exists:carriers,id',
            ],
            'name' => ['required', 'string', 'max:255'],
            'imo' => [
                'nullable',
                'string',
                'max:50',
                'unique:vessels,imo,' . $vessel->id,
            ],
            'mmsi' => [
                'nullable',
                'string',
                'max:50',
                'unique:vessels,mmsi,' . $vessel->id,
            ],
            'call_sign' => ['nullable', 'string', 'max:100'],
            'flag' => ['nullable', 'string', 'max:100'],
            'vessel_type' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'course' => ['nullable', 'numeric', 'between:0,360'],
            'destination' => ['nullable', 'string', 'max:255'],
            'eta' => ['nullable', 'date'],
            'last_position_at' => ['nullable', 'date'],
            'is_in_gulf' => ['nullable', 'boolean'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $validated['is_in_gulf'] = $request->boolean('is_in_gulf');

        $vessel->update($validated);

        return redirect()
            ->route('admin.vessels.index')
            ->with('success', 'Vessel updated successfully.');
    }

    public function destroy(Vessel $vessel): RedirectResponse
    {
        $vessel->delete();

        return redirect()
            ->route('admin.vessels.index')
            ->with('success', 'Vessel deleted successfully.');
    }
}
