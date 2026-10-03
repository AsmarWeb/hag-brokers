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

            'title' => [
                'required',
                'string',
                'max:255',
            ],

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

            'expected_close_date' => [
                'nullable',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

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

            'title' => [
                'required',
                'string',
                'max:255',
            ],

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

            'expected_close_date' => [
                'nullable',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

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
