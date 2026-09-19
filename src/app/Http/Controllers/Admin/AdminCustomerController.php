<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query()
            ->withCount([
                'users',
                'quotations',
                'shipments',
            ]);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
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

        $customers = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('admin.customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => [
                'required',
                'string',
                'max:255',
            ],

            'contact_person' => [
                'nullable',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
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

        $validated['country_code'] = strtoupper(
            $validated['country_code']
        );

        $validated['is_active'] = $request->boolean('is_active');

        Customer::create($validated);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): View
    {
        $customer->load([
            'users',
            'quotations.originPort',
            'quotations.destinationPort',
            'shipments.originPort',
            'shipments.destinationPort',
            'shipments.carrier',
            'shipments.vessel',
        ]);

        return view(
            'admin.customers.show',
            compact('customer')
        );
    }

    public function edit(Customer $customer): View
    {
        return view(
            'admin.customers.edit',
            compact('customer')
        );
    }

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
                'nullable',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
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

        $validated['country_code'] = strtoupper(
            $validated['country_code']
        );

        $validated['is_active'] = $request->boolean('is_active');

        $customer->update($validated);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Protect customers with related data
        |--------------------------------------------------------------------------
        */

        if ($customer->users()->exists()) {
            return redirect()
                ->route('admin.customers.index')
                ->with(
                    'error',
                    'This customer cannot be deleted because it has users assigned.'
                );
        }

        if ($customer->quotations()->exists()) {
            return redirect()
                ->route('admin.customers.index')
                ->with(
                    'error',
                    'This customer cannot be deleted because it has quotations.'
                );
        }

        if ($customer->shipments()->exists()) {
            return redirect()
                ->route('admin.customers.index')
                ->with(
                    'error',
                    'This customer cannot be deleted because it has shipments.'
                );
        }

        $customer->delete();

        return redirect()
            ->route('admin.customers.index')
            ->with(
                'success',
                'Customer deleted successfully.'
            );
    }
}
