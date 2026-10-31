<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\OpportunityInterest;
use App\Models\OpportunityMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
                'media',
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
        $validated = $request->validate([
            /*
        |--------------------------------------------------------------------------
        | Opportunity
        |--------------------------------------------------------------------------
        */
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

            /*
        |--------------------------------------------------------------------------
        | Logistics
        |--------------------------------------------------------------------------
        */
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

            /*
        |--------------------------------------------------------------------------
        | Financial
        |--------------------------------------------------------------------------
        */
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

            /*
        |--------------------------------------------------------------------------
        | Commission
        |--------------------------------------------------------------------------
        */
            'commission_type' => [
                'required',
                'in:percentage,fixed',
            ],

            'commission_rate' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            /*
        |--------------------------------------------------------------------------
        | Timeline
        |--------------------------------------------------------------------------
        */
            'expected_close_date' => [
                'nullable',
                'date',
            ],

            'expires_at' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],

            /*
        |--------------------------------------------------------------------------
        | Notes
        |--------------------------------------------------------------------------
        */
            'notes' => [
                'nullable',
                'string',
            ],

            /*
        |--------------------------------------------------------------------------
        | Media
        |--------------------------------------------------------------------------
        */
            'media' => [
                'nullable',
                'array',
                'max:10',
            ],

            'media.*' => [
                'file',
                'mimes:jpg,jpeg,png,webp,mp4,mov,webm',
                'max:20480',
            ],
        ]);


        // Vessel brokerage validation
        if ($request->input('opportunity_type') === 'vessel_brokerage') {
            $vessel = $request->validate([
                'vessel.listing_type' => 'required|in:sale,wanted,charter',
                'vessel.vessel_category' => 'required|in:yacht,commercial_vessel,boat',
                'vessel.vessel_type' => 'nullable|string|max:80',
                'vessel.vessel_name' => 'nullable|string|max:255',
                'vessel.builder' => 'nullable|string|max:255',
                'vessel.year_built' => 'nullable|integer|between:1800,2100',
                'vessel.length_m' => 'nullable|numeric|gt:0',
                'vessel.imo_number' => 'nullable|digits:7',
            ]);

            $validated['vessel'] = $vessel['vessel'];
        }

        $user = Auth::user();

        /*
    |--------------------------------------------------------------------------
    | Find / Create Customer
    |--------------------------------------------------------------------------
    */

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

        $storedMediaPaths = [];

        try {
            $opportunity = DB::transaction(function () use (
                $validated,
                $customer,
                $request,
                &$storedMediaPaths
            ) {
                /*
            |--------------------------------------------------------------------------
            | Create Opportunity
            |--------------------------------------------------------------------------
            */

                $opportunity = Opportunity::create([
                    'customer_id' => $customer->id,

                    'contact_id' => null,
                    'assigned_to' => null,

                    'title' => $validated['title'],

                    'opportunity_type' =>
                    $validated['opportunity_type'],

                    'category' =>
                    $validated['category'] ?? null,

                    'description' =>
                    $validated['description'],

                    'cargo_type' =>
                    $validated['cargo_type'] ?? null,

                    'container_type' =>
                    $validated['container_type'] ?? null,

                    'origin_port_id' =>
                    $validated['origin_port_id'] ?? null,

                    'destination_port_id' =>
                    $validated['destination_port_id'] ?? null,

                    'country' =>
                    $validated['country'] ?? null,

                    'city' =>
                    $validated['city'] ?? null,

                    'estimated_value' =>
                    $validated['estimated_value'] ?? null,

                    'required_capital' =>
                    $validated['required_capital'] ?? null,

                    'min_investment' =>
                    $validated['min_investment'] ?? null,

                    'currency' =>
                    strtoupper($validated['currency']),

                    'commission_type' =>
                    $validated['commission_type'],

                    'commission_rate' =>
                    $validated['commission_rate'] ?? null,

                    /*
                |--------------------------------------------------------------------------
                | Pipeline
                |--------------------------------------------------------------------------
                */

                    'stage' => 'new',

                    'probability' => 0,

                    /*
                |--------------------------------------------------------------------------
                | Marketplace Protection
                |--------------------------------------------------------------------------
                */

                    'visibility' => 'private',

                    'verification_status' => 'pending',

                    'expected_close_date' =>
                    $validated['expected_close_date'] ?? null,

                    'published_at' => null,

                    'expires_at' =>
                    $validated['expires_at'] ?? null,

                    'notes' =>
                    $validated['notes'] ?? null,
                ]);

                /*
            |--------------------------------------------------------------------------
            | Store Media
            |--------------------------------------------------------------------------
            */

                if ($request->hasFile('media')) {
                    $sortOrder = 0;
                    $primarySet = false;

                    foreach ($request->file('media') as $file) {
                        if (!$file->isValid()) {
                            continue;
                        }

                        $mimeType = $file->getMimeType();

                        $type = str_starts_with($mimeType, 'video/')
                            ? 'video'
                            : 'image';

                        $path = $file->store(
                            "opportunities/{$opportunity->id}",
                            'public'
                        );

                        $storedMediaPaths[] = $path;

                        OpportunityMedia::create([
                            'opportunity_id' => $opportunity->id,

                            'type' => $type,

                            'path' => $path,

                            'mime_type' => $mimeType,

                            'size' => $file->getSize(),

                            'original_name' =>
                            $file->getClientOriginalName(),

                            'sort_order' => $sortOrder++,

                            /*
                        |--------------------------------------------------------------------------
                        | First image becomes primary
                        |--------------------------------------------------------------------------
                        */

                            'is_primary' =>
                            $type === 'image' && !$primarySet,
                        ]);

                        if ($type === 'image' && !$primarySet) {
                            $primarySet = true;
                        }
                    }
                }


                if (
                    $validated['opportunity_type'] === 'vessel_brokerage'
                    && isset($validated['vessel'])
                ) {
                    $opportunity->vessel()->create($validated['vessel']);
                }

                return $opportunity;
            });
        } catch (\Throwable $e) {

            /*
        |--------------------------------------------------------------------------
        | Cleanup uploaded files if transaction fails
        |--------------------------------------------------------------------------
        */

            foreach ($storedMediaPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }

        return redirect()
            ->route('opportunities.index')
            ->with(
                'success',
                app()->getLocale() === 'ar'
                    ? 'تم إرسال الفرصة بنجاح، وستتم مراجعتها من فريق HAG Brokers قبل نشرها.'
                    : 'Your opportunity has been submitted successfully and will be reviewed by HAG Brokers before publication.'
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
            'media' => function ($query) {
                $query->orderBy('sort_order');
            },
        ]);

        return view(
            'opportunities.show',
            compact('opportunity')
        );
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
            'media',
        ]);

        $existingInterest = OpportunityInterest::where(
            'opportunity_id',
            $opportunity->id
        )
            ->where('user_id', Auth::id())
            ->first();

        return view(
            'opportunities.interest',
            compact(
                'opportunity',
                'existingInterest'
            )
        );
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
                ->route(
                    'opportunities.show',
                    $opportunity
                )
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

            'currency' =>
            strtoupper($validated['currency']),

            'message' =>
            $validated['message'],

            'status' => 'pending',

            'reviewed_at' => null,

            'reviewed_by' => null,
        ]);

        return redirect()
            ->route(
                'opportunities.show',
                $opportunity
            )
            ->with(
                'success',
                app()->getLocale() === 'ar'
                    ? 'تم إرسال اهتمامك بنجاح. سيقوم فريق HAG Brokers بمراجعة طلبك والتواصل معك.'
                    : 'Your interest has been submitted successfully. HAG Brokers will review your request and contact you.'
            );
    }
}
