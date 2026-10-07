<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminCompanySettingController extends Controller
{
    /**
     * Company branding/settings page.
     */
    public function edit()
    {
        $company = CompanySetting::firstOrCreate([]);

        return view('admin.company.edit', compact('company'));
    }

    /**
     * Update company settings and branding.
     */
    public function update(Request $request)
    {
        $company = CompanySetting::firstOrCreate([]);

        $data = $request->validate([
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_name_ar' => ['nullable', 'string', 'max:255'],

            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],

            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'po_box' => ['nullable', 'string', 'max:100'],

            'website' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'license_number' => ['nullable', 'string', 'max:100'],

            'header_color' => ['nullable', 'string', 'max:20'],
            'footer_text' => ['nullable', 'string'],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'stamp' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'signature' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        foreach (['logo', 'stamp', 'signature'] as $field) {

            if ($request->hasFile($field)) {

                $oldPath = $company->{$field . '_path'};

                if ($oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }

                $data[$field . '_path'] =
                    $request->file($field)->store('company', 'public');
            }
        }

        unset(
            $data['logo'],
            $data['stamp'],
            $data['signature']
        );

        $company->update($data);

        return back()->with(
            'success',
            'Company settings and branding updated successfully.'
        );
    }
}
