<?php

namespace App\Http\Controllers;

use App\Models\CompanyTradeOffer;
use App\Models\TradeOfferMessage;
use App\Models\TradeOfferRead;
use App\Services\Platform\TradeOfferRoomService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TradeOfferRoomController extends Controller
{
    private function render(Request $request, CompanyTradeOffer $offer, TradeOfferRoomService $service, bool $admin)
    {
        $w = $service->authorize($request->user(), $offer, $admin);
        $messages = TradeOfferMessage::where('offer_id', $offer->id)->latest('id')->paginate(30)->withQueryString();
        $reads = TradeOfferRead::where('offer_id', $offer->id)->get()->keyBy('workspace_id');
        $otherId = $w ? ((int) $w->id === (int) $offer->sender_workspace_id ? $offer->publisher_workspace_id : $offer->sender_workspace_id) : null;
        $otherRead = $otherId ? ($reads[$otherId]->last_message_id ?? 0) : 0;
        $ownRead = $w ? ($reads[$w->id]->last_message_id ?? 0) : 0;
        $unread = $w ? TradeOfferMessage::where('offer_id', $offer->id)->where('workspace_id', '!=', $w->id)->where('id', '>', $ownRead)->count() : 0;

        $writable = ! $admin && $service->writable($offer);
        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('company.trade-offers.messages', compact('offer', 'messages', 'w', 'admin', 'otherRead'))->render(),
                'writable' => $writable,
                'unread' => $unread,
            ])->header('Cache-Control', 'private, no-store');
        }

        return response()->view('company.trade-offers.room', compact('offer', 'messages', 'w', 'admin', 'otherRead', 'unread') + ['writable' => ! $admin && $service->writable($offer), 'requestKey' => (string) Str::uuid()])->header('Cache-Control', 'private, no-store');
    }

    public function show(Request $request, CompanyTradeOffer $offer, TradeOfferRoomService $service)
    {
        return $this->render($request, $offer, $service, false);
    }

    public function adminShow(Request $request, CompanyTradeOffer $offer, TradeOfferRoomService $service)
    {
        return $this->render($request, $offer, $service, true);
    }

    public function store(Request $request, CompanyTradeOffer $offer, TradeOfferRoomService $service)
    {
        $service->authorize($request->user(), $offer);
        $data = $request->validate(['request_key' => ['required', 'uuid'], 'body' => ['required', 'string', 'min:1', 'max:4000']]);
        $service->send($request->user(), $offer, $data);

        if ($request->expectsJson()) {
            return response()->json(['sent' => true, 'request_key' => (string) Str::uuid()])->header('Cache-Control', 'private, no-store');
        }

        return redirect()->route('company.trade-offers.room', $offer)->with('success', __('trade_room.sent'));
    }

    public function read(Request $request, CompanyTradeOffer $offer, TradeOfferRoomService $service)
    {
        $service->authorize($request->user(), $offer);
        $data = $request->validate(['up_to' => ['required', 'integer', 'min:1']]);
        $service->markRead($request->user(), $offer, (int) $data['up_to']);

        return redirect()->route('company.trade-offers.room', $offer)->with('success', __('trade_room.read_saved'));
    }
}
