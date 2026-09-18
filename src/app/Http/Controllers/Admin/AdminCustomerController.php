<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $customers = Customer::query()
            ->withCount([
                'users',
                'quotations',
                'shipments',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('company_name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
        ]);
    }

    /**
     * Show the specified customer.
     */
    public function show(Customer $customer): View
    {
        $customer->load([
            'users',
            'quotations',
            'shipments',
        ]);

        return view('admin.customers.show', [
            'customer' => $customer,
        ]);
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => [
                'required',
                'string',
                'max:255',
            ],

            'contact_person' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'country_code' => [
                'required',
                'string',
                'size:2',
            ],

            'city' => [
                'nullable',
                'string',
                'max:255',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['country_code'] =
            strtoupper($validated['country_code']);

        $validated['is_active'] =
            $request->boolean('is_active');

        $customer = Customer::create($validated);

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with(
                'success',
                'Customer created successfully.'
            );
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): View
    {
        return view('admin.customers.edit', [
            'customer' => $customer,
        ]);
    }

    /**
     * Update the specified customer.
     */
    public function update(
        Request $request,
        Customer $customer
    ): RedirectResponse {
        $validated = $request->validate([
            'company_name' => [
                'required',
                'string',
                'max:255',
            ],

            'contact_person' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'country_code' => [
                'required',
                'string',
                'size:2',
            ],

            'city' => [
                'nullable',
                'string',
                'max:255',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['country_code'] =
            strtoupper($validated['country_code']);

        $validated['is_active'] =
            $request->boolean('is_active');

        $customer->update($validated);

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with(
                'success',
                'Customer updated successfully.'
            );
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(
        Customer $customer
    ): RedirectResponse {
        $customer->delete();

        return redirect()
            ->route('admin.customers.index')
            ->with(
                'success',
                'Customer deleted successfully.'
            );
    }
}
