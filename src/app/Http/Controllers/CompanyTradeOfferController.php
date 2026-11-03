<?php

namespace App\Http\Controllers;

use App\Models\CompanyTradeOffer;
use App\Models\Opportunity;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\CompanyTradeOfferService;
use App\Services\Platform\TradeOfferAttachments;
use App\Services\Platform\TradeOfferInput;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        [$data, $uploads] = app(TradeOfferInput::class)->submission($request);
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

        return app(TradeOfferAttachments::class)->download($offer, $file, $service);
    }

    public function download(Request $request, CompanyTradeOffer $offer, int $file, CompanyTradeOfferService $service)
    {
        return $this->attachment($request, $offer, $file, $service, false);
    }

    public function adminDownload(Request $request, CompanyTradeOffer $offer, int $file, CompanyTradeOfferService $service)
    {
        return $this->attachment($request, $offer, $file, $service, true);
    }
}
