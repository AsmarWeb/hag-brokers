<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCarrierController extends Controller
{
    /**
     * Display carriers.
     */
    public function index(Request $request): View
    {
        $query = Carrier::query()
            ->withCount([
                'vessels',
                'quotationOffers',
                'shipments',
            ]);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%")
                    ->orWhere('country_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();

            if ($status === 'active') {
                $query->where('is_active', true);
            }

            if ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $carriers = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.carriers.index', compact('carriers'));
    }


    /**
     * Show create form.
     */
    public function create(): View
    {
        return view('admin.carriers.create');
    }


    /**
     * Store a new carrier.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'nullable',
                'string',
                'max:255',
                'unique:carriers,code',
            ],

            'country' => [
                'nullable',
                'string',
                'max:255',
            ],

            'country_code' => [
                'nullable',
                'string',
                'size:2',
            ],

            'website' => [
                'nullable',
                'url',
                'max:255',
            ],

            'api_provider' => [
                'nullable',
                'string',
                'max:255',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['country_code'] = !empty($validated['country_code'])
            ? strtoupper($validated['country_code'])
            : null;

        $validated['is_active'] = $request->boolean('is_active');

        Carrier::create($validated);

        return redirect()
            ->route('admin.carriers.index')
            ->with('success', 'Carrier created successfully.');
    }


    /**
     * Display a carrier.
     */
    public function show(Carrier $carrier): View
    {
        $carrier->load([
            'vessels',
            'quotationOffers',
            'shipments',
        ]);

        return view('admin.carriers.show', compact('carrier'));
    }


    /**
     * Show edit form.
     */
    public function edit(Carrier $carrier): View
    {
        return view('admin.carriers.edit', compact('carrier'));
    }


    /**
     * Update carrier.
     */
    public function update(
        Request $request,
        Carrier $carrier
    ): RedirectResponse {

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('carriers', 'code')
                    ->ignore($carrier->id),
            ],

            'country' => [
                'nullable',
                'string',
                'max:255',
            ],

            'country_code' => [
                'nullable',
                'string',
                'size:2',
            ],

            'website' => [
                'nullable',
                'url',
                'max:255',
            ],

            'api_provider' => [
                'nullable',
                'string',
                'max:255',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['country_code'] = !empty($validated['country_code'])
            ? strtoupper($validated['country_code'])
            : null;

        $validated['is_active'] = $request->boolean('is_active');

        $carrier->update($validated);

        return redirect()
            ->route('admin.carriers.index')
            ->with('success', 'Carrier updated successfully.');
    }


    /**
     * Delete carrier.
     */
    public function destroy(Carrier $carrier): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Protect historical relationships
        |--------------------------------------------------------------------------
        */

        if ($carrier->vessels()->exists()) {
            return redirect()
                ->route('admin.carriers.index')
                ->with(
                    'error',
                    'This carrier cannot be deleted because it has vessels assigned.'
                );
        }

        if ($carrier->quotationOffers()->exists()) {
            return redirect()
                ->route('admin.carriers.index')
                ->with(
                    'error',
                    'This carrier cannot be deleted because it has quotation offers.'
                );
        }

        if ($carrier->shipments()->exists()) {
            return redirect()
                ->route('admin.carriers.index')
                ->with(
                    'error',
                    'This carrier cannot be deleted because it has shipments.'
                );
        }

        $carrier->delete();

        return redirect()
            ->route('admin.carriers.index')
            ->with('success', 'Carrier deleted successfully.');
    }
}
