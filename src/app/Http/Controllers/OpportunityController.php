<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\OpportunityInterest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OpportunityController extends Controller
{
    /**
     * Display public opportunities.
     */
    public function index(Request $request): View
    {
        $query = Opportunity::query()
            ->with([
                'originPort',
                'destinationPort',
            ])
            ->where('visibility', 'public')
            ->where('verification_status', 'verified')
            ->whereNotNull('published_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();

            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('cargo_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where(
                'opportunity_type',
                $request->string('type')->trim()->toString()
            );
        }

        if ($request->filled('category')) {
            $query->where(
                'category',
                $request->string('category')->trim()->toString()
            );
        }

        if ($request->filled('country')) {
            $query->where(
                'country',
                $request->string('country')->trim()->toString()
            );
        }

        $opportunities = $query
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('opportunities.index', compact('opportunities'));
    }

    /**
     * Show create opportunity form.
     */
    public function create(): View
    {
        abort_unless(Auth::check(), 403);

        return view('opportunities.create');
    }

    /**
     * Store a new opportunity submitted by a user.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
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
                'required',
                'string',
                'min:20',
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

            'commission_type' => [
                'required',
                'in:percentage,fixed',
            ],

            'commission_rate' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'expected_close_date' => [
                'nullable',
                'date',
            ],

            'expires_at' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Find/Create Customer for authenticated user
        |--------------------------------------------------------------------------
        */

        $user = Auth::user();

        $customer = Customer::firstOrCreate(
            [
                'email' => $user->email,
            ],
            [
                'company_name' => $user->name,
                'contact_person' => $user->name,
                'email' => $user->email,
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Create Opportunity
        |--------------------------------------------------------------------------
        */

        $opportunity = Opportunity::create([
            'customer_id' => $customer->id,

            'contact_id' => null,
            'assigned_to' => null,

            'title' => $request->title,

            'opportunity_type' => $request->opportunity_type,
            'category' => $request->category,
            'description' => $request->description,

            'cargo_type' => $request->cargo_type,
            'container_type' => $request->container_type,

            'origin_port_id' => $request->origin_port_id,
            'destination_port_id' => $request->destination_port_id,

            'country' => $request->country,
            'city' => $request->city,

            'estimated_value' => $request->estimated_value,
            'required_capital' => $request->required_capital,
            'min_investment' => $request->min_investment,

            'currency' => strtoupper($request->currency),

            'commission_type' => $request->commission_type,
            'commission_rate' => $request->commission_rate,

            'stage' => 'new',
            'probability' => 0,

            /*
             * User submissions are never published immediately.
             */
            'visibility' => 'private',
            'verification_status' => 'pending',

            'expected_close_date' => $request->expected_close_date,

            'published_at' => null,

            'expires_at' => $request->expires_at,

            'notes' => $request->notes,
        ]);

        return redirect()
            ->route('opportunities.show', $opportunity)
            ->with(
                'success',
                app()->getLocale() === 'ar'
                    ? 'تم إرسال الفرصة بنجاح، وستتم مراجعتها من فريق HAG Brokers.'
                    : 'Your opportunity has been submitted and will be reviewed by HAG Brokers.'
            );
    }

    /**
     * Display opportunity details.
     */
    public function show(Opportunity $opportunity): View
    {
        /*
        |--------------------------------------------------------------------------
        | Public visibility protection
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $opportunity->visibility === 'public'
                && $opportunity->verification_status === 'verified'
                && $opportunity->published_at !== null
                && (
                    $opportunity->expires_at === null
                    || $opportunity->expires_at->isFuture()
                ),
            404
        );

        $opportunity->load([
            'originPort',
            'destinationPort',
        ]);

        return view('opportunities.show', compact('opportunity'));
    }

    /**
     * Show interest form.
     */
    public function interest(Opportunity $opportunity): View
    {
        abort_unless(
            $opportunity->visibility === 'public'
                && $opportunity->verification_status === 'verified'
                && $opportunity->published_at !== null
                && (
                    $opportunity->expires_at === null
                    || $opportunity->expires_at->isFuture()
                ),
            404
        );

        $opportunity->load([
            'originPort',
            'destinationPort',
        ]);

        $existingInterest = OpportunityInterest::where(
            'opportunity_id',
            $opportunity->id
        )
            ->where('user_id', Auth::id())
            ->first();

        return view('opportunities.interest', compact(
            'opportunity',
            'existingInterest'
        ));
    }

    /**
     * Store user's interest.
     */
    public function storeInterest(
        Request $request,
        Opportunity $opportunity
    ): RedirectResponse {
        abort_unless(
            $opportunity->visibility === 'public'
                && $opportunity->verification_status === 'verified'
                && $opportunity->published_at !== null
                && (
                    $opportunity->expires_at === null
                    || $opportunity->expires_at->isFuture()
                ),
            404
        );

        $validated = $request->validate([
            'role' => [
                'required',
                'string',
                'max:100',
            ],

            'available_capital' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'currency' => [
                'required',
                'string',
                'size:3',
            ],

            'message' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
        ]);

        /*
    |--------------------------------------------------------------------------
    | Prevent duplicate interest
    |--------------------------------------------------------------------------
    */

        $existingInterest = OpportunityInterest::where(
            'opportunity_id',
            $opportunity->id
        )
            ->where('user_id', Auth::id())
            ->first();

        if ($existingInterest) {
            return redirect()
                ->route('opportunities.show', $opportunity)
                ->with(
                    'info',
                    app()->getLocale() === 'ar'
                        ? 'لقد قمت بالفعل بإبداء اهتمامك بهذه الفرصة.'
                        : 'You have already expressed interest in this opportunity.'
                );
        }

        OpportunityInterest::create([
            'opportunity_id' => $opportunity->id,
            'user_id' => Auth::id(),

            'role' => $validated['role'],

            'available_capital' =>
            $validated['available_capital'] ?? null,

            'currency' => strtoupper($validated['currency']),

            'message' => $validated['message'],

            'status' => 'pending',

            'reviewed_at' => null,
            'reviewed_by' => null,
        ]);

        return redirect()
            ->route('opportunities.show', $opportunity)
            ->with(
                'success',
                app()->getLocale() === 'ar'
                    ? 'تم إرسال اهتمامك بنجاح. سيقوم فريق HAG Brokers بمراجعة طلبك والتواصل معك.'
                    : 'Your interest has been submitted successfully. HAG Brokers will review your request and contact you.'
            );
    }
}
