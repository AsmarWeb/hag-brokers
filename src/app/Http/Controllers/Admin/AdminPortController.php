<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Port;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPortController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $ports = Port::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhere('port_code', 'like', "%{$search}%")
                        ->orWhere('unlocode', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.ports.index', [
            'ports' => $ports,
        ]);
    }

    public function create(): View
    {
        return view('admin.ports.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'port_code' => ['nullable', 'string', 'max:50'],
            'unlocode' => ['nullable', 'string', 'max:20', 'unique:ports,unlocode'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_gulf' => ['nullable', 'boolean'],
            'is_uae' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['country_code'] = $validated['country_code']
            ? strtoupper($validated['country_code'])
            : null;

        $validated['is_gulf'] = $request->boolean('is_gulf');
        $validated['is_uae'] = $request->boolean('is_uae');
        $validated['is_active'] = $request->boolean('is_active');

        Port::create($validated);

        return redirect()
            ->route('admin.ports.index')
            ->with('success', 'Port created successfully.');
    }

    public function edit(Port $port): View
    {
        return view('admin.ports.edit', [
            'port' => $port,
        ]);
    }

    public function update(
        Request $request,
        Port $port
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'port_code' => ['nullable', 'string', 'max:50'],
            'unlocode' => [
                'nullable',
                'string',
                'max:20',
                'unique:ports,unlocode,' . $port->id,
            ],
            'timezone' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_gulf' => ['nullable', 'boolean'],
            'is_uae' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['country_code'] = $validated['country_code']
            ? strtoupper($validated['country_code'])
            : null;

        $validated['is_gulf'] = $request->boolean('is_gulf');
        $validated['is_uae'] = $request->boolean('is_uae');
        $validated['is_active'] = $request->boolean('is_active');

        $port->update($validated);

        return redirect()
            ->route('admin.ports.index')
            ->with('success', 'Port updated successfully.');
    }

    public function destroy(Port $port): RedirectResponse
    {
        $port->delete();

        return redirect()
            ->route('admin.ports.index')
            ->with('success', 'Port deleted successfully.');
    }
}
