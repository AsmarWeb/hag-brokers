<?php

namespace App\Http\Controllers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\CompanyMember;
use App\Models\Opportunity;
use App\Models\OpportunityInterest;
use App\Models\Port;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\MobileAppSettings;
use App\Services\Platform\OpportunityCatalog;
use App\Services\Platform\OpportunityInput;
use App\Services\Platform\OpportunitySubmission;
use App\Services\Seo\PublicSeo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class OpportunityController extends Controller
{
    public function index(
        Request $request,
        CompanyMarketplaceService $service,
        MobileAppSettings $settings
)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'type' => ['nullable', Rule::in(OpportunityCatalog::TYPES)],
            'category' => ['nullable', Rule::in(OpportunityCatalog::CATEGORIES)],
            'country' => ['nullable', 'string', 'max:100'],
            'subcategory' => ['nullable', Rule::in(OpportunityCatalog::SUBCATEGORIES[$request->input('category')] ?? [])],
            'vessel_category' => ['nullable', 'in:yacht,boat,commercial_vessel'],
            'currency' => [Rule::requiredIf($request->filled('min_price') || $request->filled('max_price') || in_array($request->input('sort'), ['price_asc', 'price_desc'], true)), 'nullable', Rule::in(OpportunityCatalog::CURRENCIES)],
            'min_price' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'max_price' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999999.99', Rule::when($request->filled('min_price'), 'gte:min_price')],
            'sort' => ['nullable', 'in:newest,oldest,price_asc,price_desc'],
        ]);
        $query = $service->publicQuery()->with(['customer', 'media', 'vessel']);
        if ($term = trim($filters['search'] ?? '')) {
            $query->where(fn ($q) => $q->where('title', 'like', '%'.$term.'%')->orWhere('description', 'like', '%'.$term.'%')->orWhere('commercial_details->product_name', 'like', '%'.$term.'%'));
        }
        foreach (['type' => 'opportunity_type', 'category' => 'category'] as $input => $field) {
            if ($filters[$input] ?? null) {
                $query->where($field, $filters[$input]);
            }
        }
        if ($country = trim($filters['country'] ?? '')) {
            if (in_array(mb_strtolower($country), ['ae', 'uae', 'united arab emirates', 'الإمارات العربية المتحدة'], true)) {
                $query->whereIn('country', ['AE', 'UAE', 'United Arab Emirates', 'الإمارات العربية المتحدة']);
            } else {
                $query->where('country', $country);
            }
        }
        if ($filters['subcategory'] ?? null) {
            $query->where('commercial_details->subcategory', $filters['subcategory']);
        }
        if ($filters['vessel_category'] ?? null) {
            $query->whereHas('vessel', fn ($q) => $q->where('vessel_category', $filters['vessel_category']));
        }
        if ($filters['currency'] ?? null) {
            $query->where('currency', $filters['currency']);
        }
        if (isset($filters['min_price'])) {
            $query->where('estimated_value', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('estimated_value', '<=', $filters['max_price']);
        }
        $sort = $filters['sort'] ?? 'newest';
        if (in_array($sort, ['price_asc', 'price_desc'], true)) {
            $query->orderByRaw('case when estimated_value is null then 1 else 0 end')->orderBy('estimated_value', $sort === 'price_asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('published_at', $sort === 'oldest' ? 'asc' : 'desc');
        }
        $opportunities = $query->orderByDesc('id')->paginate(12)->withQueryString();

        $categoryCards = $settings->publicContent()['category_cards'];
        $selectedCategory = collect($categoryCards)->firstWhere('code', $filters['category'] ?? null);

        return view('opportunities.index', compact(
            'opportunities',
            'categoryCards',
            'selectedCategory'
        ));
    }

    public function create(Request $request, CompanyMarketplaceService $service)
    {
        $this->pruneStagedPublicationMedia();
        $user = $request->user();
        if ($user) {
            if (! $user->hasVerifiedEmail()) {
                $request->session()->put('url.intended', route('opportunities.create'));
                return redirect()->route('verification.notice');
            }
            abort_if($user->isPlatformAdministrator(), 403);
            $member = CompanyMember::where('user_id', $user->id)->first();
            if ($member) {
                $service->actor($user, true);
            } elseif ($user->customer_id !== null) {
                abort(403);
            }
        }

        return view('opportunities.create', [
            'requestKey' => (string) Str::uuid(),
            'ports' => Port::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, CompanyMarketplaceService $service)
    {
        $data = app(OpportunityInput::class)->publicationData($request);
        $requestKey = (string) $request->input('request_key');
        $user = $request->user();
        $member = $user ? CompanyMember::where('user_id', $user->id)->first() : null;

        abort_if($user && $user->isPlatformAdministrator(), 403);
        if ($member && ! in_array($member->role, ['owner', 'manager'], true)) {
            abort(403);
        }
        if ($user && $member && $user->hasVerifiedEmail() && $user->hasCompletedPhoneSetup()) {
            $service->actor($user, true);
            app(OpportunitySubmission::class)->publish($request, $data, $requestKey, $service);
            return redirect()->route('company.marketplace')->with('success', __('market.published'));
        }
        if ($user && ! $member && $user->customer_id !== null) {
            // Do not silently reassign pre-existing legacy customer accounts to a new workspace.
            abort(403);
        }

        $token = (string) Str::uuid();
        $media = $this->stashPublicationMedia($request, $token);
        $drafts = $request->session()->get('pending_opportunity_publications', []);
        $drafts[$token] = [
            'data' => $data,
            'raw' => $request->except(['_token', 'media']),
            'request_key' => $requestKey,
            'media' => $media,
            'created_at' => now()->timestamp,
        ];
        $request->session()->put('pending_opportunity_publications', $drafts);

        return $this->redirectToPublicationDraft($request, $token);
    }

    public function resumePublication(Request $request, string $draft)
    {
        $pending = $this->pendingPublicationDraft($request, $draft);
        $user = $request->user();
        $url = route('opportunities.create.resume', ['draft' => $draft]);
        if (! $user->hasCompletedPhoneSetup()) {
            $request->session()->put('url.intended', $url);
            return redirect()->route('phone.verification.notice');
        }

        $member = CompanyMember::where('user_id', $user->id)->first();
        if (! $member) {
            abort_if($user->customer_id !== null, 403);
            $request->session()->put('url.intended', $url);
            return redirect()->route('company.onboarding.show');
        }
        $workspace = app(CompanyMarketplaceService::class)->actor($user, true);

        return response()->view('opportunities.create-resume', [
            'pending' => $pending,
            'data' => $pending['data'],
            'files' => $pending['media'],
            'workspace' => $workspace,
        ])->header('Cache-Control', 'private, no-store');
    }

    public function completePublication(Request $request, string $draft, CompanyMarketplaceService $service)
    {
        $pending = $this->pendingPublicationDraft($request, $draft);
        $user = $request->user();
        $url = route('opportunities.create.resume', ['draft' => $draft]);
        if (! $user->hasCompletedPhoneSetup()) {
            $request->session()->put('url.intended', $url);
            return redirect()->route('phone.verification.notice');
        }
        $member = CompanyMember::where('user_id', $user->id)->first();
        if (! $member) {
            abort_if($user->customer_id !== null, 403);
            $request->session()->put('url.intended', $url);
            return redirect()->route('company.onboarding.show');
        }
        $service->actor($user, true);

        $restored = [];
        foreach ($pending['media'] as $item) {
            $path = Storage::disk('local')->path($item['path']);
            abort_unless(is_file($path), 410, 'A file attached to this draft is no longer available.');
            $restored[] = new UploadedFile($path, $item['original_name'], $item['mime_type'], UPLOAD_ERR_OK, true);
        }
        $request->merge($pending['raw']);
        $request->files->set('media', $restored);
        $data = app(OpportunityInput::class)->publicationData($request);
        app(OpportunitySubmission::class)->publish($request, $data, $pending['request_key'], $service);

        Storage::disk('local')->deleteDirectory('pending-opportunity-publications/'.$draft);
        $this->forgetPublicationDraft($request, $draft);

        return redirect()->route('company.marketplace')->with('success', __('market.published'));
    }

    private function redirectToPublicationDraft(Request $request, string $token)
    {
        $url = route('opportunities.create.resume', ['draft' => $token]);
        $request->session()->put('url.intended', $url);
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }
        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }
        if (! $user->hasCompletedPhoneSetup()) {
            return redirect()->route('phone.verification.notice');
        }
        if (! CompanyMember::where('user_id', $user->id)->exists()) {
            return redirect()->route('company.onboarding.show');
        }
        return redirect()->to($url);
    }

    private function pruneStagedPublicationMedia(): void
    {
        // Opportunistically remove stale private attachments when the public form is visited.
        foreach (Storage::disk('local')->allDirectories('pending-opportunity-publications') as $directory) {
            $files = Storage::disk('local')->allFiles($directory);
            $latest = 0;
            foreach ($files as $filePath) {
                $latest = max($latest, Storage::disk('local')->lastModified($filePath));
            }
            if ($latest === 0 || $latest < now()->subDay()->timestamp) {
                Storage::disk('local')->deleteDirectory($directory);
            }
        }
    }

    private function stashPublicationMedia(Request $request, string $token): array
    {
        $this->pruneStagedPublicationMedia();
        $stored = [];
        try {
            foreach ($request->file('media', []) as $file) {
                $extension = $file->guessExtension() ?: 'bin';
                $name = (string) Str::uuid().'.'.$extension;
                $path = Storage::disk('local')->putFileAs('pending-opportunity-publications/'.$token, $file, $name);
                if (! $path) {
                    throw new \RuntimeException('Unable to save an attachment for the opportunity draft.');
                }
                $stored[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        } catch (\Throwable $exception) {
            Storage::disk('local')->deleteDirectory('pending-opportunity-publications/'.$token);
            throw $exception;
        }
        return $stored;
    }

    private function pendingPublicationDraft(Request $request, string $token): array
    {
        $pending = $request->session()->get('pending_opportunity_publications.'.$token);
        abort_unless(is_array($pending) && isset($pending['data'], $pending['raw'], $pending['request_key'], $pending['media'], $pending['created_at']), 404);
        $userId = (int) $request->user()->id;
        if (! isset($pending['bound_user_id'])) {
            $pending['bound_user_id'] = $userId;
            $drafts = $request->session()->get('pending_opportunity_publications', []);
            $drafts[$token] = $pending;
            $request->session()->put('pending_opportunity_publications', $drafts);
        }
        abort_unless((int) $pending['bound_user_id'] === $userId, 403);
        if ((int) $pending['created_at'] < now()->subDay()->timestamp) {
            Storage::disk('local')->deleteDirectory('pending-opportunity-publications/'.$token);
            $this->forgetPublicationDraft($request, $token);
            abort(410, 'This opportunity draft has expired.');
        }
        return $pending;
    }

    private function forgetPublicationDraft(Request $request, string $token): void
    {
        $drafts = $request->session()->get('pending_opportunity_publications', []);
        unset($drafts[$token]);
        $request->session()->put('pending_opportunity_publications', $drafts);
    }

        public function show(Request $request, Opportunity $opportunity, CompanyMarketplaceService $service)
    {
        abort_unless($service->isPublic($opportunity), 404);
        if (ctype_digit((string) $request->route()->originalParameter('opportunity'))) {
            $parameters = ['opportunity' => $opportunity];
            if (in_array($request->query('lang'), ['ar', 'en'], true)) {
                $parameters['lang'] = $request->query('lang');
            }

            return redirect(app(PublicSeo::class)->url('opportunities.show', $parameters), 301);
        }
        $opportunity->load(['originPort', 'destinationPort', 'media', 'customer', 'vessel']);

        return view($opportunity->isLuxuryMarineListing() ? 'opportunities.marine.show' : 'opportunities.show', compact('opportunity'));
    }

    public function interest(Request $request, Opportunity $opportunity, CompanyMarketplaceService $service)
    {
        abort_unless($service->isPublic($opportunity) && $opportunity->company_workspace_id, 404);

        $existingInterest = null;
        if ($request->user()) {
            $member = CompanyMember::where('user_id', $request->user()->id)->first();
            if ($member) {
                $workspace = $service->actor($request->user());
                abort_if((int) $opportunity->customer_id === (int) $workspace->customer_id, 403);
                $existingInterest = OpportunityInterest::where('opportunity_id', $opportunity->id)
                    ->where('company_workspace_id', $workspace->id)
                    ->first();
            }
        }

        return view('opportunities.interest', [
            'opportunity' => $opportunity,
            'existingInterest' => $existingInterest,
            'requestKey' => (string) Str::uuid(),
        ]);
    }

    /**
     * Save an application draft before authentication. The actual application is
     * created only after the user has authenticated and completed account setup.
     */
    public function storeInterest(Request $request, Opportunity $opportunity, CompanyMarketplaceService $service)
    {
        abort_unless($service->isPublic($opportunity), 404);

        $data = $request->validate([
            'request_key' => ['required', 'uuid'],
            'role' => ['required', 'string', 'max:100'],
            'available_capital' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'currency' => ['required', 'regex:/^[A-Za-z]{3}$/D'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $requestKey = $data['request_key'];
        unset($data['request_key']);
        $data['currency'] = strtoupper($data['currency']);

        // Legacy listings remain ineligible for new public applications. Existing
        // company members still receive the service's established validation error.
        if (! $opportunity->company_workspace_id
            && (! $request->user() || ! CompanyMember::where('user_id', $request->user()->id)->exists())) {
            abort(404);
        }

        // Existing verified company members have already completed the final
        // authentication step, so preserve their current one-submit experience.
        $user = $request->user();
        if ($user && $user->hasCompletedPhoneSetup() && CompanyMember::where('user_id', $user->id)->exists()) {
            $workspace = $service->actor($user);
            $service->apply($user, $opportunity, $data, $requestKey);

            return redirect()->route('company.marketplace')->with('success', __('market.delivered'));
        }

        $draftToken = (string) Str::uuid();
        $request->session()->put('pending_opportunity_applications.'.$draftToken, [
            'opportunity_id' => $opportunity->id,
            'request_key' => $requestKey,
            'data' => $data,
            'created_at' => now('UTC')->timestamp,
        ]);

        $resumeUrl = route('opportunities.interest.resume', ['draft' => $draftToken]);
        $request->session()->put('url.intended', $resumeUrl);

        if (! $request->user()) {
            return redirect()->route('login')->with(
                'status',
                app()->getLocale() === 'ar'
                    ? 'تم حفظ طلبك مؤقتاً. سجّل الدخول أو أنشئ حساباً شخصياً لإكمال الإرسال.'
                    : 'Your application has been saved. Sign in or create a personal account to complete submission.'
            );
        }

        return redirect()->to($resumeUrl);
    }

    /**
     * Resume the saved application after login. A new personal account is kept
     * separate from the company workspace; onboarding happens here, not at signup.
     */
    public function resumeInterest(Request $request, string $draft, CompanyMarketplaceService $service)
    {
        $pending = $this->pendingInterestDraft($request, $draft);
        $opportunity = Opportunity::findOrFail($pending['opportunity_id']);
        abort_unless($service->isPublic($opportunity) && $opportunity->company_workspace_id, 404);

        $resumeUrl = route('opportunities.interest.resume', ['draft' => $draft]);
        $user = $request->user();

        if (! $user->hasCompletedPhoneSetup()) {
            $request->session()->put('url.intended', $resumeUrl);

            return redirect()->route('phone.verification.notice');
        }

        if (! CompanyMember::where('user_id', $user->id)->exists()) {
            // Older customer-linked accounts without a workspace require support
            // migration; do not silently create or reassign a company record.
            abort_if($user->customer_id !== null, 403);
            $request->session()->put('url.intended', $resumeUrl);

            return redirect()->route('company.onboarding.show');
        }

        $workspace = $service->actor($user);
        abort_if((int) $opportunity->customer_id === (int) $workspace->customer_id, 403);

        $existingInterest = OpportunityInterest::where('opportunity_id', $opportunity->id)
            ->where('company_workspace_id', $workspace->id)
            ->first();

        return view('opportunities.interest-resume', [
            'opportunity' => $opportunity,
            'application' => $pending['data'],
            'existingInterest' => $existingInterest,
            'draftToken' => $draft,
        ]);
    }

    /** Final CSRF-protected confirmation; the company-level quota/idempotency stays in the existing service. */
    public function completeInterest(Request $request, string $draft, CompanyMarketplaceService $service)
    {
        $pending = $this->pendingInterestDraft($request, $draft);
        $opportunity = Opportunity::findOrFail($pending['opportunity_id']);
        abort_unless($service->isPublic($opportunity) && $opportunity->company_workspace_id, 404);

        $user = $request->user();
        if (! $user->hasCompletedPhoneSetup()) {
            $request->session()->put('url.intended', route('opportunities.interest.resume', ['draft' => $draft]));

            return redirect()->route('phone.verification.notice');
        }

        $workspace = $service->actor($user);
        abort_if((int) $opportunity->customer_id === (int) $workspace->customer_id, 403);

        $application = array_intersect_key($pending['data'], array_flip([
            'role', 'available_capital', 'currency', 'message',
        ]));

        $service->apply($user, $opportunity, $application, $pending['request_key']);
        $request->session()->forget('pending_opportunity_applications.'.$draft);

        return redirect()->route('company.marketplace')->with('success', __('market.delivered'));
    }

    private function pendingInterestDraft(Request $request, string $draft): array
    {
        $key = 'pending_opportunity_applications.'.$draft;
        $pending = $request->session()->get($key);

        abort_unless(
            is_array($pending)
                && isset($pending['opportunity_id'], $pending['request_key'], $pending['data'], $pending['created_at'])
                && is_array($pending['data']),
            404
        );

        if ((int) $pending['created_at'] < now('UTC')->subHours(24)->timestamp) {
            $request->session()->forget($key);
            abort(410, 'This saved application has expired. Please start again.');
        }

        return $pending;
    }

}
