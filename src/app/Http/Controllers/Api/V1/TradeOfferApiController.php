<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CompanyTradeOffer;
use App\Models\Opportunity;
use App\Models\TradeOfferMessage;
use App\Models\TradeOfferRead;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\CompanyTradeOfferService;
use App\Services\Platform\TradeOfferAttachments;
use App\Services\Platform\TradeOfferInput;
use App\Services\Platform\TradeOfferRoomService;
use Illuminate\Http\Request;

class TradeOfferApiController extends Controller
{
    private function page(Request $r): array
    {
        return $r->validate(['per_page' => ['nullable', 'integer', 'between:1,50'], 'page' => ['nullable', 'integer', 'between:1,10000']]);
    }

    private function meta($p): array
    {
        return ['current_page' => $p->currentPage(), 'per_page' => $p->perPage(), 'total' => $p->total(), 'last_page' => $p->lastPage()];
    }

    private function offer(Request $r, string $id, CompanyTradeOfferService $service): CompanyTradeOffer
    {
        $q = CompanyTradeOffer::findOrFail($id);
        $service->authorize($r->user(), $q);

        return $q;
    }

    private function resource(CompanyTradeOffer $q, CompanyTradeOfferService $service): array
    {
        if (! $service->verify($q)) {
            return ['id' => $q->id, 'integrity' => false];
        }
        $files = [];
        foreach ($q->attachments as $i => $f) {
            $files[] = ['index' => $i, 'size' => $f['size'], 'download_path' => '/api/v1/company/trade-offers/'.$q->id.'/attachments/'.$i];
        }

        return ['id' => $q->id, 'opportunity_id' => $q->opportunity_id, 'sender_workspace_id' => $q->sender_workspace_id, 'publisher_workspace_id' => $q->publisher_workspace_id, 'status' => $q->status, 'version' => $q->version, 'integrity' => true,
            'quantity' => (string) $q->quantity, 'unit' => $q->unit, 'unit_price' => (string) $q->unit_price, 'base_amount' => (string) $q->base_amount, 'currency' => $q->currency,
            'tax_basis' => $q->tax_basis, 'delivery_days' => $q->delivery_days, 'valid_until' => $q->valid_until->format('Y-m-d'), 'payment_terms' => $q->payment_terms, 'notes' => $q->notes,
            'snapshot' => array_intersect_key((array) $q->snapshot, array_flip(['title', 'type', 'publisher', 'sender'])), 'attachments' => $files];
    }

    public function index(Request $r, CompanyMarketplaceService $market, CompanyTradeOfferService $service)
    {
        $w = $market->actor($r->user(), true);
        $d = $this->page($r) + $r->validate(['direction' => ['nullable', 'in:received,sent']]);
        $q = CompanyTradeOffer::where(fn ($q) => $q->where('publisher_workspace_id', $w->id)->orWhere('sender_workspace_id', $w->id));
        if (($d['direction'] ?? '') === 'received') {
            $q->where('publisher_workspace_id', $w->id);
        }if (($d['direction'] ?? '') === 'sent') {
            $q->where('sender_workspace_id', $w->id);
        }
        $p = $q->latest('id')->paginate($d['per_page'] ?? 20);

        return response()->json(['data' => $p->getCollection()->map(fn ($q) => $this->resource($q, $service)), 'meta' => $this->meta($p)]);
    }

    public function comparison(Request $r, string $opportunityId, CompanyMarketplaceService $market, CompanyTradeOfferService $service)
    {
        $w = $market->actor($r->user(), true);
        $o = Opportunity::where('company_workspace_id', $w->id)->whereKey($opportunityId)->firstOrFail();
        $d = $this->page($r);
        $p = CompanyTradeOffer::where('opportunity_id', $o->id)->latest('id')->paginate($d['per_page'] ?? 20);

        return response()->json(['data' => $p->getCollection()->map(fn ($q) => $this->resource($q, $service)), 'meta' => $this->meta($p)]);
    }

    public function store(Request $r, string $opportunityId, CompanyMarketplaceService $market, CompanyTradeOfferService $service, TradeOfferInput $input)
    {
        $market->actor($r->user(), true);
        $o = Opportunity::findOrFail($opportunityId);
        $r->validate(array_fill_keys(['sender_workspace_id', 'publisher_workspace_id', 'submitted_by', 'opportunity_id', 'base_amount', 'snapshot', 'status', 'version', 'content_sha256'], ['prohibited']));
        [$d,$files] = $input->submission($r);
        $q = $service->submit($r->user(), $o, $d, $files);
        $created = $q->wasRecentlyCreated;

        return response()->json(['data' => $this->resource($q->fresh(), $service), 'meta' => ['created' => $created]], $created ? 201 : 200);
    }

    public function show(Request $r, string $offerId, CompanyTradeOfferService $service)
    {
        return response()->json(['data' => $this->resource($this->offer($r, $offerId, $service), $service)]);
    }

    public function decide(Request $r, string $offerId, CompanyTradeOfferService $service)
    {
        $q = $this->offer($r, $offerId, $service);
        $d = $r->validate(['action' => ['required', 'in:selected,declined,withdrawn'], 'version' => ['required', 'integer', 'min:0'], 'reason' => ['required', 'string', 'min:10', 'max:1000'], 'confirmed' => ['required', 'accepted']]);
        $service->decide($r->user(), $q, $d);

        return response()->json(['data' => $this->resource($q->fresh(), $service)]);
    }

    public function events(Request $r, string $offerId, CompanyTradeOfferService $service)
    {
        $q = $this->offer($r, $offerId, $service);
        abort_unless($service->verify($q), 409);
        $d = $this->page($r);
        $p = $q->events()->latest('version')->paginate($d['per_page'] ?? 20);

        return response()->json(['data' => $p->getCollection()->map(fn ($e) => ['version' => $e->version, 'action' => $e->action, 'reason' => $e->reason, 'actor_name' => $e->actor_name, 'created_at' => $e->created_at?->utc()->toIso8601String()]), 'meta' => $this->meta($p)]);
    }

    public function download(Request $r, string $offerId, int $file, CompanyTradeOfferService $service, TradeOfferAttachments $attachments)
    {
        return $attachments->download($this->offer($r, $offerId, $service), $file, $service);
    }

    private function message(TradeOfferMessage $m, int $own): array
    {
        return ['id' => $m->id, 'body' => $m->body, 'author_name' => $m->author_name, 'mine' => $m->workspace_id === $own, 'created_at' => $m->created_at->utc()->toIso8601String()];
    }

    public function messages(Request $r, string $offerId, CompanyTradeOfferService $offers, TradeOfferRoomService $room)
    {
        $q = $this->offer($r, $offerId, $offers);
        $w = $room->authorize($r->user(), $q);
        $d = $r->validate(['after_id' => ['nullable', 'integer', 'min:0', 'prohibits:before_id'], 'before_id' => ['nullable', 'integer', 'min:1', 'prohibits:after_id'], 'limit' => ['nullable', 'integer', 'between:1,50']]);
        $limit = $d['limit'] ?? 30;
        $query = TradeOfferMessage::where('offer_id', $q->id);
        $forward = isset($d['after_id']);
        if ($forward) {
            $query->where('id', '>', $d['after_id'])->orderBy('id');
        } else {
            if (isset($d['before_id'])) {
                $query->where('id', '<', $d['before_id']);
            }$query->orderByDesc('id');
        }
        $messages = $query->limit($limit + 1)->get();
        $more = $messages->count() > $limit;
        $messages = $messages->take($limit);
        if (! $forward) {
            $messages = $messages->reverse();
        }$messages = $messages->values();
        $own = (int) (TradeOfferRead::where('offer_id', $q->id)->where('workspace_id', $w->id)->value('last_message_id') ?? 0);
        $other = (int) (TradeOfferRead::where('offer_id', $q->id)->where('workspace_id', '!=', $w->id)->value('last_message_id') ?? 0);

        return response()->json(['data' => $messages->map(fn ($m) => $this->message($m, $w->id)), 'meta' => ['has_more' => $more, 'first_id' => $messages->first()?->id, 'last_id' => $messages->last()?->id],
            'room' => ['writable' => $room->writable($q), 'own_read_up_to' => $own, 'other_read_up_to' => $other, 'unread' => TradeOfferMessage::where('offer_id', $q->id)->where('workspace_id', '!=', $w->id)->where('id', '>', $own)->count()]]);
    }

    public function send(Request $r, string $offerId, CompanyTradeOfferService $offers, TradeOfferRoomService $room)
    {
        $q = $this->offer($r, $offerId, $offers);
        $w = $room->authorize($r->user(), $q);
        $d = $r->validate(['request_key' => ['required', 'uuid'], 'body' => ['required', 'string', 'min:1', 'max:4000']]);
        $m = $room->send($r->user(), $q, $d);

        return response()->json(['data' => $this->message($m, $w->id)]);
    }

    public function read(Request $r,string $offerId,CompanyTradeOfferService $offers,TradeOfferRoomService $room)
    {
        $q = $this->offer($r,$offerId,$offers);
        $d = $r->validate(['up_to' => ['required', 'integer', 'min:1']]);
        $room->markRead($r->user(),$q,(int) $d['up_to']);

        return response()->noContent();
    }
}
