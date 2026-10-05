<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
{
    /**
     * Show the public "Bring a Deal" form.
     */
    public function create(): View
    {
        return view('leads.create');
    }

    /**
     * Store a new lead submitted from the public website.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'company_name' => [
                'nullable',
                'string',
                'max:200',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:50',
            ],

            'deal_type' => [
                'required',
                'string',
                'max:50',
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
                'min:20',
                'max:10000',
            ],

            'estimated_value' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'currency' => [
                'required',
                'string',
                'size:3',
            ],

            'country' => [
                'nullable',
                'string',
                'max:100',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'preferred_contact' => [
                'nullable',
                'string',
                'max:50',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Prepare data for the Lead / CRM system
        |--------------------------------------------------------------------------
        |
        | The public form uses friendly field names such as "name" and
        | "description", while the admin CRM uses "contact_person" and "notes".
        |
        */

        $companyName = $validated['company_name'] ?? null;

        /*
         * If the person does not provide a company name, use the person's
         * name as the lead/company display name so the Admin panel always
         * has something meaningful to display.
         */
        if (empty($companyName)) {
            $companyName = $validated['name'];
        }

        /*
         * Build structured notes containing the actual deal information.
         *
         * This allows the existing Admin Lead pages to display the complete
         * information without requiring additional database columns.
         */
        $notes = [];

        $notes[] = 'Deal Title: ' . $validated['title'];
        $notes[] = 'Deal Type: ' . $validated['deal_type'];

        if (!empty($validated['category'])) {
            $notes[] = 'Category: ' . $validated['category'];
        }

        $notes[] = '';
        $notes[] = 'Description:';
        $notes[] = $validated['description'];

        if (!empty($validated['estimated_value'])) {
            $notes[] = '';
            $notes[] = 'Estimated Value: '
                . number_format((float) $validated['estimated_value'], 2)
                . ' '
                . strtoupper($validated['currency']);
        }

        if (!empty($validated['preferred_contact'])) {
            $notes[] = '';
            $notes[] = 'Preferred Contact: ' . $validated['preferred_contact'];
        }

        /*
         * Create the Lead using the same fields expected by
         * AdminLeadController.
         */
        Lead::create([
            'user_id' => auth()->id(),

            'company_name' => $companyName,

            'contact_person' => $validated['name'],

            'email' => $validated['email'] ?? null,

            'phone' => $validated['phone'],

            'city' => $validated['city'] ?? null,

            'source' => 'website',

            'status' => 'new',

            'assigned_to' => null,

            'notes' => implode("\n", $notes),
        ]);

        return redirect()
            ->route('leads.create')
            ->with(
                'success',
                app()->getLocale() === 'ar'
                    ? 'تم استلام تفاصيل الصفقة بنجاح. سيتواصل معك فريق HAG Brokers قريباً.'
                    : 'Your deal details have been received successfully. HAG Brokers will contact you shortly.'
            );
    }
}
