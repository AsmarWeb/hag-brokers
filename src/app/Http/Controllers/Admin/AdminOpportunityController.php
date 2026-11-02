<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Opportunity;
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

    /** Read-only platform oversight. */
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
}
