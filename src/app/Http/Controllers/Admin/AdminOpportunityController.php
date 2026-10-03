<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\Port;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOpportunityController extends Controller
{
    /**
     * Display a listing of opportunities.
     */
    public function index(Request $request): View
    {
        $query = Opportunity::with([
            'customer',
            'contact',
            'assignedTo',
            'originPort',
            'destinationPort',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('cargo_type', 'like', "%{$search}%")
                    ->orWhere('container_type', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")

                    ->orWhereHas('customer', function ($customer) use ($search) {
                        $customer->where(
                            'company_name',
                            'like',
                            "%{$search}%"
                        );
                    })

                    ->orWhereHas('contact', function ($contact) use ($search) {
                        $contact->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Customer Filter
        |--------------------------------------------------------------------------
        */
        if ($request->filled('customer_id')) {
            $query->where(
                'customer_id',
                $request->integer('customer_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Stage Filter
        |--------------------------------------------------------------------------
        */
        if ($request->filled('stage')) {
            $query->where(
                'stage',
                $request->string('stage')->toString()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Opportunity Type Filter
        |--------------------------------------------------------------------------
        */
        if ($request->filled('opportunity_type')) {
            $query->where(
                'opportunity_type',
                $request->string('opportunity_type')->toString()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Verification Filter
        |--------------------------------------------------------------------------
        */
        if ($request->filled('verification_status')) {
            $query->where(
                'verification_status',
                $request->string('verification_status')->toString()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Visibility Filter
        |--------------------------------------------------------------------------
        */
        if ($request->filled('visibility')) {
            $query->where(
                'visibility',
                $request->string('visibility')->toString()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Opportunities
        |--------------------------------------------------------------------------
        */
        $opportunities = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Customers for filter
        |--------------------------------------------------------------------------
        */
        $customers = Customer::where('is_active', true)
            ->orderBy('company_name')
            ->get();

        return view('admin.opportunities.index', compact(
            'opportunities',
            'customers'
        ));
    }


    /**
     * Show the form for creating a new opportunity.
     */
    public function create(): View
    {
        $customers = Customer::where('is_active', true)
            ->orderBy('company_name')
            ->get();

        $contacts = Contact::with('customer')
            ->orderBy('name')
            ->get();

        $users = User::orderBy('name')
            ->get();

        $ports = Port::where('is_active', true)
            ->orderBy('country')
            ->orderBy('name')
            ->get();

        return view('admin.opportunities.create', compact(
            'customers',
            'contacts',
            'users',
            'ports'
        ));
    }


    /**
     * Store a newly created opportunity.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([

            // =====================================================
            // CRM Relationships
            // =====================================================

            'customer_id' => [
                'required',
                'exists:customers,id',
            ],

            'contact_id' => [
                'nullable',
                'exists:contacts,id',
            ],

            'assigned_to' => [
                'nullable',
                'exists:users,id',
            ],


            // =====================================================
            // Basic Opportunity Information
            // =====================================================

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'opportunity_type' => [
                'required',
                'string',
                'max:50',
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
            ],


            // =====================================================
            // Freight
            // =====================================================

            'cargo_type' => [
                'nullable',
                'string',
                'max:255',
            ],

            'container_type' => [
                'nullable',
                'string',
                'max:255',
            ],

            'origin_port_id' => [
                'nullable',
                'exists:ports,id',
            ],

            'destination_port_id' => [
                'nullable',
                'exists:ports,id',
            ],


            // =====================================================
            // Location
            // =====================================================

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


            // =====================================================
            // Financial Information
            // =====================================================

            'estimated_value' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'required_capital' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'min_investment' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'currency' => [
                'required',
                'string',
                'size:3',
            ],


            // =====================================================
            // Commission
            // =====================================================

            'commission_type' => [
                'required',
                'string',
                'max:50',
            ],

            'commission_rate' => [
                'nullable',
                'numeric',
                'min:0',
            ],


            // =====================================================
            // CRM Pipeline
            // =====================================================

            'stage' => [
                'required',
                'string',
                'max:50',
            ],

            'probability' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],


            // =====================================================
            // Marketplace
            // =====================================================

            'visibility' => [
                'required',
                'in:private,public,invite_only',
            ],

            'verification_status' => [
                'required',
                'in:pending,under_review,verified,rejected',
            ],


            // =====================================================
            // Dates
            // =====================================================

            'expected_close_date' => [
                'nullable',
                'date',
            ],

            'published_at' => [
                'nullable',
                'date',
            ],

            'expires_at' => [
                'nullable',
                'date',
                'after_or_equal:published_at',
            ],


            // =====================================================
            // Notes
            // =====================================================

            'notes' => [
                'nullable',
                'string',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Automatically set published_at
        |--------------------------------------------------------------------------
        |
        | When an opportunity is public and verified, we can publish it.
        |
        */

        if (
            $validated['visibility'] === 'public'
            && $validated['verification_status'] === 'verified'
            && empty($validated['published_at'])
        ) {
            $validated['published_at'] = now();
        }


        Opportunity::create($validated);


        return redirect()
            ->route('admin.opportunities.index')
            ->with(
                'success',
                'Opportunity created successfully.'
            );
    }


    /**
     * Display the specified opportunity.
     */
    public function show(Opportunity $opportunity): View
    {
        $opportunity->load([
            'customer',
            'contact',
            'assignedTo',
            'originPort',
            'destinationPort',
            'interests.user',
            'interests.reviewer',
        ]);

        return view(
            'admin.opportunities.show',
            compact('opportunity')
        );
    }


    /**
     * Show the form for editing the specified opportunity.
     */
    public function edit(Opportunity $opportunity): View
    {
        $opportunity->load([
            'customer',
            'contact',
            'assignedTo',
            'originPort',
            'destinationPort',
        ]);

        $customers = Customer::where('is_active', true)
            ->orderBy('company_name')
            ->get();

        $contacts = Contact::with('customer')
            ->orderBy('name')
            ->get();

        $users = User::orderBy('name')
            ->get();

        $ports = Port::where('is_active', true)
            ->orderBy('country')
            ->orderBy('name')
            ->get();

        return view('admin.opportunities.edit', compact(
            'opportunity',
            'customers',
            'contacts',
            'users',
            'ports'
        ));
    }


    /**
     * Update the specified opportunity.
     */
    public function update(
        Request $request,
        Opportunity $opportunity
    ): RedirectResponse {

        $validated = $request->validate([

            // =====================================================
            // CRM Relationships
            // =====================================================

            'customer_id' => [
                'required',
                'exists:customers,id',
            ],

            'contact_id' => [
                'nullable',
                'exists:contacts,id',
            ],

            'assigned_to' => [
                'nullable',
                'exists:users,id',
            ],


            // =====================================================
            // Basic Opportunity Information
            // =====================================================

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'opportunity_type' => [
                'required',
                'string',
                'max:50',
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
            ],


            // =====================================================
            // Freight
            // =====================================================

            'cargo_type' => [
                'nullable',
                'string',
                'max:255',
            ],

            'container_type' => [
                'nullable',
                'string',
                'max:255',
            ],

            'origin_port_id' => [
                'nullable',
                'exists:ports,id',
            ],

            'destination_port_id' => [
                'nullable',
                'exists:ports,id',
            ],


            // =====================================================
            // Location
            // =====================================================

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


            // =====================================================
            // Financial Information
            // =====================================================

            'estimated_value' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'required_capital' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'min_investment' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'currency' => [
                'required',
                'string',
                'size:3',
            ],


            // =====================================================
            // Commission
            // =====================================================

            'commission_type' => [
                'required',
                'string',
                'max:50',
            ],

            'commission_rate' => [
                'nullable',
                'numeric',
                'min:0',
            ],


            // =====================================================
            // CRM Pipeline
            // =====================================================

            'stage' => [
                'required',
                'string',
                'max:50',
            ],

            'probability' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],


            // =====================================================
            // Marketplace
            // =====================================================

            'visibility' => [
                'required',
                'in:private,public,invite_only',
            ],

            'verification_status' => [
                'required',
                'in:pending,under_review,verified,rejected',
            ],


            // =====================================================
            // Dates
            // =====================================================

            'expected_close_date' => [
                'nullable',
                'date',
            ],

            'published_at' => [
                'nullable',
                'date',
            ],

            'expires_at' => [
                'nullable',
                'date',
                'after_or_equal:published_at',
            ],


            // =====================================================
            // Notes
            // =====================================================

            'notes' => [
                'nullable',
                'string',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Automatically manage published_at
        |--------------------------------------------------------------------------
        */

        if (
            $validated['visibility'] === 'public'
            && $validated['verification_status'] === 'verified'
            && empty($validated['published_at'])
        ) {
            $validated['published_at'] = now();
        }

        /*
        |--------------------------------------------------------------------------
        | Remove publication date when no longer public/verified
        |--------------------------------------------------------------------------
        */

        if (
            $validated['visibility'] !== 'public'
            || $validated['verification_status'] !== 'verified'
        ) {
            $validated['published_at'] = null;
        }


        $opportunity->update($validated);


        return redirect()
            ->route(
                'admin.opportunities.show',
                $opportunity
            )
            ->with(
                'success',
                'Opportunity updated successfully.'
            );
    }


    /**
     * Remove the specified opportunity.
     */
    public function destroy(
        Opportunity $opportunity
    ): RedirectResponse {
        $opportunity->delete();

        return redirect()
            ->route('admin.opportunities.index')
            ->with(
                'success',
                'Opportunity deleted successfully.'
            );
    }
}
