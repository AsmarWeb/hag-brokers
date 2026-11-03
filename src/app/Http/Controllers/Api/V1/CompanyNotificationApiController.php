<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CompanyTradeOffer;
use App\Models\Opportunity;
use App\Notifications\AdminPlatformNotification;
use App\Services\Platform\CompanyMarketplaceService;
use Illuminate\Http\Request;

class CompanyNotificationApiController extends Controller
{
    private function query(Request $r, CompanyMarketplaceService $market)
    {
        $w = $market->actor($r->user(), true);
        $offers = CompanyTradeOffer::where(fn ($q) => $q->where('publisher_workspace_id', $w->id)->orWhere('sender_workspace_id', $w->id))->select('id');
        $opps = Opportunity::where('company_workspace_id', $w->id)->select('id');

        return $r->user()->notifications()->where('type', AdminPlatformNotification::class)->where(function ($q) use ($offers, $opps) {
            $q->where(fn ($q) => $q->whereIn('data->type', ['rfq.submitted', 'rfq.selected', 'rfq.declined', 'rfq.withdrawn', 'rfq.message'])->whereIn('data->offer_id', $offers))
                ->orWhere(fn ($q) => $q->whereIn('data->type', ['marketplace.moderation.hide', 'marketplace.moderation.restore'])->whereIn('data->opportunity_id', $opps));
        });
    }

    public function index(Request $r, CompanyMarketplaceService $market)
    {
        $d = $r->validate(['per_page' => ['nullable', 'integer', 'between:1,50'], 'page' => ['nullable', 'integer', 'between:1,10000'], 'unread' => ['nullable', 'boolean']]);
        $base = $this->query($r, $market);
        $unread = (clone $base)->whereNull('read_at')->count();
        if ($d['unread'] ?? false) {
            $base->whereNull('read_at');
        }
        $p = $base->orderByDesc('created_at')->orderByDesc('id')->paginate($d['per_page'] ?? 20);

        return response()->json(['data' => $p->getCollection()->map(fn ($n) => ['id' => $n->id, 'type' => $n->data['type'],
            'title' => app()->getLocale() === 'ar' ? 'تحديث تجاري' : 'Commercial update', 'offer_id' => isset($n->data['offer_id']) ? (int) $n->data['offer_id'] : null,
            'opportunity_id' => isset($n->data['opportunity_id']) ? (int) $n->data['opportunity_id'] : null, 'read_at' => $n->read_at?->utc()->toIso8601String(), 'created_at' => $n->created_at?->utc()->toIso8601String()]),
            'meta' => ['current_page' => $p->currentPage(), 'per_page' => $p->perPage(), 'total' => $p->total(), 'last_page' => $p->lastPage(), 'unread' => $unread]]);
    }

    public function read(Request $r, string $notificationId, CompanyMarketplaceService $market)
    {
        $n = $this->query($r, $market)->whereKey($notificationId)->firstOrFail();
        $n->markAsRead();

        return response()->noContent();
    }
}
