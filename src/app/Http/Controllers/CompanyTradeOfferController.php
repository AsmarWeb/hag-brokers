<?php

namespace App\Http\Controllers;

use App\Models\CompanyTradeOffer;
use App\Models\Opportunity;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\CompanyTradeOfferService;
use App\Services\Platform\OpportunityCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CompanyTradeOfferController extends Controller
{
    private function page(string $name, array $data)
    {
        return response()->view($name, $data)->header('Cache-Control', 'private, no-store');
    }

    public function index(Request $request, CompanyMarketplaceService $market)
    {
        $w = $market->actor($request->user(), true);
        $filters = $request->validate(['direction' => ['nullable', 'in:received,sent']]);
        $query = CompanyTradeOffer::query()->where(fn ($q) => $q->where('publisher_workspace_id', $w->id)->orWhere('sender_workspace_id', $w->id));
        if (($filters['direction'] ?? '') === 'received') {
            $query->where('publisher_workspace_id', $w->id);
        }if (($filters['direction'] ?? '') === 'sent') {
            $query->where('sender_workspace_id', $w->id);
        }

        return $this->page('company.trade-offers.index', ['offers' => $query->latest('id')->paginate(15)->withQueryString(), 'workspace' => $w, 'opportunity' => null, 'admin' => false]);
    }

    public function comparison(Request $request, Opportunity $opportunity, CompanyMarketplaceService $market)
    {
        $w = $market->actor($request->user(), true);
        abort_unless((int) $opportunity->company_workspace_id === (int) $w->id, 404);

        return $this->page('company.trade-offers.index', ['offers' => CompanyTradeOffer::where('opportunity_id', $opportunity->id)->latest('id')->paginate(15)->withQueryString(), 'workspace' => $w, 'opportunity' => $opportunity, 'admin' => false]);
    }

    public function adminComparison(Opportunity $opportunity)
    {
        return $this->page('company.trade-offers.index', ['offers' => CompanyTradeOffer::where('opportunity_id', $opportunity->id)->latest('id')->paginate(15)->withQueryString(), 'workspace' => null, 'opportunity' => $opportunity, 'admin' => true]);
    }

    public function create(Request $request, Opportunity $opportunity, CompanyMarketplaceService $market, CompanyTradeOfferService $service)
    {
        $w = $market->actor($request->user(), true);
        abort_unless($service->eligible($opportunity), 404);
        abort_if((int) $opportunity->company_workspace_id === (int) $w->id, 403);
        if ($existing = CompanyTradeOffer::where('opportunity_id', $opportunity->id)->where('sender_workspace_id', $w->id)->first()) {
            return redirect()->route('company.trade-offers.show', $existing);
        }

        return $this->page('company.trade-offers.create', ['opportunity' => $opportunity, 'requestKey' => (string) Str::uuid()]);
    }

    public function store(Request $request, Opportunity $opportunity, CompanyMarketplaceService $market, CompanyTradeOfferService $service)
    {
        $market->actor($request->user(), true);
        $data = $request->validate([
            'request_key' => ['required', 'uuid'], 'quantity' => ['required', 'regex:/^\d+(?:\.\d{1,3})?$/D', 'decimal:0,3', 'gt:0', 'max:999999999.999'],
            'unit' => ['required', Rule::in(OpportunityCatalog::UNITS)], 'unit_price' => ['required', 'regex:/^\d+(?:\.\d{1,2})?$/D', 'decimal:0,2', 'gt:0', 'max:999999999.99'],
            'currency' => ['required', Rule::in(OpportunityCatalog::CURRENCIES)], 'tax_basis' => ['required', 'in:exclusive,inclusive,not_specified'],
            'delivery_days' => ['required', 'integer', 'between:0,3650'], 'valid_until' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.now('Asia/Dubai')->toDateString(), 'before_or_equal:'.now('Asia/Dubai')->addDays(90)->toDateString()],
            'payment_terms' => ['required', 'string', 'min:10', 'max:1000'], 'notes' => ['nullable', 'string', 'max:3000'],
            'attachments' => ['nullable', 'array', 'max:3'], 'attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'confirmed' => ['required', 'accepted'],
        ]);
        $uploads = $request->file('attachments', []);
        if (collect($uploads)->sum(fn ($file) => $file->getSize()) > 10 * 1024 * 1024) {
            throw ValidationException::withMessages(['attachments' => __('rfq.files_help')]);
        }
        unset($data['attachments'],$data['confirmed']);
        $offer = $service->submit($request->user(), $opportunity, $data, $uploads);

        return redirect()->route('company.trade-offers.show', $offer)->with('success', __('rfq.sent'));
    }

    private function render(Request $request, CompanyTradeOffer $offer, CompanyTradeOfferService $service, bool $admin)
    {
        $w = $service->authorize($request->user(), $offer, $admin);
        $integrity = $service->verify($offer);
        $canSelect = ! $admin && $integrity && $offer->status === 'submitted' && $offer->valid_until->format('Y-m-d') >= now('Asia/Dubai')->toDateString() && $service->eligible($offer->opportunity);

        return $this->page('company.trade-offers.show', ['offer' => $offer, 'workspace' => $w, 'admin' => $admin, 'integrity' => $integrity, 'canSelect' => $canSelect, 'events' => $offer->events()->latest('version')->paginate(15)]);
    }

    public function show(Request $request, CompanyTradeOffer $offer, CompanyTradeOfferService $service)
    {
        return $this->render($request, $offer, $service, false);
    }

    public function adminShow(Request $request, CompanyTradeOffer $offer, CompanyTradeOfferService $service)
    {
        return $this->render($request, $offer, $service, true);
    }

    public function decide(Request $request, CompanyTradeOffer $offer, CompanyTradeOfferService $service)
    {
        $service->authorize($request->user(), $offer);
        $data = $request->validate(['action' => ['required', 'in:selected,declined,withdrawn'], 'version' => ['required', 'integer', 'min:0'], 'reason' => ['required', 'string', 'min:10', 'max:1000'], 'confirmed' => ['required', 'accepted']]);
        $service->decide($request->user(), $offer, $data);

        return redirect()->route('company.trade-offers.show', $offer)->with('success', __('rfq.updated'));
    }

    private function attachment(Request $request, CompanyTradeOffer $offer, int $file, CompanyTradeOfferService $service, bool $admin)
    {
        $service->authorize($request->user(), $offer, $admin);
        abort_unless($service->verify($offer), 409);
        $attachment = $offer->attachments[$file] ?? null;
        abort_unless($attachment && str_starts_with($attachment['path'], 'offers/'), 404);
        $disk = Storage::disk('rfq_private');
        abort_unless($disk->exists($attachment['path']), 404);
        abort_unless(hash_equals($attachment['sha256'], hash_file('sha256', $disk->path($attachment['path']))), 409);

        return $disk->download($attachment['path'], 'offer-'.$offer->id.'-attachment-'.($file + 1).'.'.pathinfo($attachment['path'], PATHINFO_EXTENSION), ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    public function download(Request $request, CompanyTradeOffer $offer, int $file, CompanyTradeOfferService $service)
    {
        return $this->attachment($request,$offer,$file,$service,false);
    }

    public function adminDownload(Request $request,CompanyTradeOffer $offer,int $file,CompanyTradeOfferService $service)
    {
        return $this->attachment($request,$offer,$file,$service,true);
    }
}
