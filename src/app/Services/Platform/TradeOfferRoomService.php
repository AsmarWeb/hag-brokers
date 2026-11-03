<?php

namespace App\Services\Platform;

use App\Models\CompanyMember;
use App\Models\CompanyTradeOffer;
use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\TradeOfferMessage;
use App\Models\TradeOfferRead;
use App\Models\User;
use App\Notifications\AdminPlatformNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class TradeOfferRoomService
{
    public function authorize(User $user, CompanyTradeOffer $offer, bool $admin = false): ?CompanyWorkspace
    {
        $w = app(CompanyTradeOfferService::class)->authorize($user, $offer, $admin);
        abort_unless(app(CompanyTradeOfferService::class)->verify($offer), 409);

        return $w;
    }

    public function writable(CompanyTradeOffer $offer): bool
    {
        if (! in_array($offer->status, ['submitted', 'selected'], true) || ! app(CompanyTradeOfferService::class)->eligible($offer->opportunity)) {
            return false;
        }
        $ids = [$offer->sender_workspace_id, $offer->publisher_workspace_id];

        return CompanyWorkspace::whereIn('id', $ids)->where('status', 'active')->whereIn('customer_id', Customer::where('is_active', true)->select('id'))->count() === 2;
    }

    private function locked(User $user, CompanyTradeOffer $offer): array
    {
        $w = $this->authorize($user, $offer);
        CompanyWorkspace::whereKey($w->id)->lockForUpdate()->firstOrFail();
        Opportunity::whereKey($offer->opportunity_id)->lockForUpdate()->firstOrFail();
        $q = CompanyTradeOffer::whereKey($offer->id)->lockForUpdate()->firstOrFail();

        return [$this->authorize($user, $q), $q];
    }

    public function send(User $user, CompanyTradeOffer $offer, array $data): TradeOfferMessage
    {
        return DB::transaction(function () use ($user, $offer, $data) {
            [$w,$q] = $this->locked($user, $offer);
            $body = trim(str_replace(["\r\n", "\r"], "\n", $data['body']));
            if ($body === '') {
                throw ValidationException::withMessages(['body' => __('trade_room.empty_body')]);
            }
            $hash = hash('sha256', $user->id."\n".$body);
            $existing = TradeOfferMessage::where('offer_id', $q->id)->where('workspace_id', $w->id)->where('request_key', $data['request_key'])->first();
            if ($existing) {
                if (! hash_equals($existing->request_hash, $hash)) {
                    throw ValidationException::withMessages(['body' => __('trade_room.replay')]);
                }

return $existing;
            }
            if (! $this->writable($q)) {
                throw ValidationException::withMessages(['body' => __('trade_room.read_only')]);
            }
            $message = TradeOfferMessage::create(['offer_id' => $q->id, 'workspace_id' => $w->id, 'author_id' => $user->id, 'author_name' => $user->name, 'request_key' => $data['request_key'], 'request_hash' => $hash, 'body' => $body, 'created_at' => now()]);
            $targetId = (int) $w->id === (int) $q->sender_workspace_id ? $q->publisher_workspace_id : $q->sender_workspace_id;
            $target = CompanyWorkspace::findOrFail($targetId);
            $members = CompanyMember::where('company_workspace_id', $target->id)->whereIn('role', ['owner', 'manager'])->select('user_id');
            foreach (User::where('customer_id', $target->customer_id)->whereIn('id', $members)->get() as $recipient) {
                if ($recipient->hasRole(['admin', 'super-admin'])) {
                    continue;
                }$recipient->notify(new AdminPlatformNotification(type: 'rfq.message', title: 'رسالة تفاوض جديدة / New negotiation message', message: $q->snapshot['title'], url: '/company/trade-offers/'.$q->id.'/room', extra: ['offer_id' => $q->id, 'message_id' => $message->id]));
            }

            return $message;
        }, 3);
    }

    public function markRead(User $user, CompanyTradeOffer $offer, int $upTo): void
    {
        DB::transaction(function () use ($user, $offer, $upTo) {
            [$w,$q] = $this->locked($user, $offer);
            abort_unless(TradeOfferMessage::where('offer_id', $q->id)->whereKey($upTo)->exists(), 404);
            $read = TradeOfferRead::where('offer_id', $q->id)->where('workspace_id', $w->id)->lockForUpdate()->first();
            if ($read && $read->last_message_id >= $upTo) {
                return;
            }
            $read ??= new TradeOfferRead(['offer_id' => $q->id, 'workspace_id' => $w->id]);
            $read->fill(['last_message_id' => $upTo, 'read_by' => $user->id, 'read_at' => now()])->save();
        }, 3);
    }
}
