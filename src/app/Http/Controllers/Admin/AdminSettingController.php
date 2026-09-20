<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingController extends Controller
{
    /**
     * Display the admin settings page.
     */
    public function edit(): View
    {
        $setting = Setting::first();

        if (! $setting) {
            $setting = Setting::create([
                'maintenance_mode' => false,
                'phone' => null,
                'email' => null,
            ]);
        }

        return view('admin.settings.edit', [
            'setting' => $setting,
        ]);
    }

    /**
     * Update admin settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
        ]);

        $setting = Setting::first();

        if (! $setting) {
            $setting = new Setting();
        }

        $setting->maintenance_mode = $request->boolean('maintenance_mode');
        $setting->phone = $validated['phone'] ?? null;
        $setting->email = $validated['email'] ?? null;

        $setting->save();

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Settings updated successfully.');
    }
}
