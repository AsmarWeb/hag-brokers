<?php

namespace App\Services\Platform;

use App\Models\CompanyTradeOffer;
use App\Models\TradeOfferRead;
use App\Models\User;
use App\Notifications\AdminPlatformNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\PersonalAccessToken;

final class MobilePushService
{
    public const EVENTS = ['rfq.submitted', 'rfq.selected', 'rfq.declined', 'rfq.withdrawn', 'rfq.message'];

    public function ready(): bool
    {
        return (bool) config('mobile_push.enabled') && preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', (string) config('mobile_push.project_id')) && (bool) config('mobile_push.access_token');
    }

    public function eligible($device, $n, bool $forDelivery = true): bool
    {
        if (! $device || ! $n || ! $device->enabled || $device->project_id !== config('mobile_push.project_id') || ! in_array($n->data['type'] ?? '', self::EVENTS, true) || $n->type !== AdminPlatformNotification::class || $n->notifiable_type !== (new User)->getMorphClass() || (int) $n->notifiable_id !== (int) $device->user_id || ($forDelivery && $n->read_at) || $n->created_at->lt(now()->subDay()) || $n->created_at->lt($device->enabled_at)) {
            return false;
        }
        $token = PersonalAccessToken::find($device->access_token_id);
        $u = User::find($device->user_id);
        if (! $token || ! $u || ! $token->can('mobile:v1') || (int) $token->tokenable_id !== (int) $u->id || $token->tokenable_type !== $u->getMorphClass() || ($token->expires_at && $token->expires_at->isPast())) {
            return false;
        }
        try {
            $w = app(CompanyMarketplaceService::class)->actor($u, true);
        } catch (\Throwable) {
            return false;
        }
        $offer = CompanyTradeOffer::find($n->data['offer_id'] ?? 0);
        if (! $offer || ! in_array((int) $w->id, [(int) $offer->publisher_workspace_id, (int) $offer->sender_workspace_id], true)) {
            return false;
        }
        $message = ($n->data['type'] ?? '') === 'rfq.message';
        if (($message && ! $device->messages) || (! $message && ! $device->offers)) {
            return false;
        }
        if ($forDelivery && $message && TradeOfferRead::where('offer_id', $offer->id)->where('workspace_id', $w->id)->where('last_message_id', '>=', (int) ($n->data['message_id'] ?? PHP_INT_MAX))->exists()) {
            return false;
        }

        return true;
    }

    public function collect(): int
    {
        if (! $this->ready()) {
            return 0;
        }$count = 0;
        DatabaseNotification::where('type', AdminPlatformNotification::class)->whereNull('read_at')->where('created_at', '>=', now()->subDay())->orderByDesc('created_at')->limit(200)->get()->each(function ($n) use (&$count) {
            DB::table('mobile_push_devices')->where('user_id', $n->notifiable_id)->where('enabled', true)->get()->each(function ($d) use ($n, &$count) {
                if ($this->eligible($d, $n)) {
                    $count += DB::table('mobile_push_deliveries')->insertOrIgnore(['device_id' => $d->id, 'notification_id' => $n->id, 'binding_key' => $d->binding_key, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
                }
            });
        });

        return $count;
    }

    public function deliver(int $id): void
    {
        if (! $this->ready()) {
            return;
        }
        $claimed = DB::table('mobile_push_deliveries')->where('id', $id)->where('status', 'pending')->update(['status' => 'processing', 'updated_at' => now()]);
        if (! $claimed) {
            return;
        }
        $row = DB::table('mobile_push_deliveries')->find($id);
        if (! $row) {
            return;
        }
        $device = DB::table('mobile_push_devices')->find($row->device_id);
        $n = DatabaseNotification::find($row->notification_id);
        if (! $device || $device->binding_key !== $row->binding_key || ! $this->eligible($device, $n)) {
            $this->status($id, 'skipped', 'no_longer_eligible');

            return;
        }
        $ar = $device->locale === 'ar';
        // Only opaque binding and notice IDs leave the server; no private content or business identifiers.
        try {
            $response = Http::withToken(config('mobile_push.access_token'))->acceptJson()->connectTimeout(3)->timeout(8)->post('https://exp.host/--/api/v2/push/send', [[
                'to' => Crypt::decryptString($device->expo_token), 'title' => 'HAG Brokers',
                'body' => $ar ? 'لديك تحديث جديد. افتح التطبيق للاطلاع عليه.' : 'You have a new update. Open the app to view it.',
                'data' => ['scope' => 'hag-push-v1', 'binding' => $device->binding_key, 'notice_id' => $n->id],
                'channelId' => 'business-updates', 'ttl' => 3600,
            ]]);
            $ticket = $response->json('data.0');
            if ($response->successful() && is_array($ticket) && ($ticket['status'] ?? '') === 'ok' && is_string($ticket['id'] ?? null) && strlen($ticket['id']) <= 255) {
                DB::table('mobile_push_deliveries')->where('id', $id)->update(['status' => 'ticketed', 'ticket_id' => $ticket['id'], 'sent_at' => now(), 'updated_at' => now()]);

                return;
            }
            if (($ticket['details']['error'] ?? '') === 'DeviceNotRegistered') {
                $this->disable($device);
                $this->status($id, 'failed', 'device_not_registered');

                return;
            }
            $this->status($id, 'uncertain', 'send_not_confirmed');
        } catch (\Throwable) {
            $this->status($id, 'uncertain', 'send_not_confirmed');
        }
        // Uncertain sends are not retried automatically: the provider may already have accepted them.
    }

    private function disable($device): void
    {
        DB::table('mobile_push_devices')->where('id', $device->id)->where('binding_key', $device->binding_key)->update(['enabled' => false, 'updated_at' => now()]);
    }

    private function status(int $id, string $state, ?string $error = null): void
    {
        DB::table('mobile_push_deliveries')->where('id', $id)->update(['status' => $state, 'last_error' => $error, 'updated_at' => now()]);
    }

    public function receipts(): int
    {
        if (! $this->ready()) {
            return 0;
        }
        $rows = DB::table('mobile_push_deliveries')->where('status', 'ticketed')->where('sent_at', '<=', now()->subMinutes(15))->orderBy('id')->limit(100)->get();
        if ($rows->isEmpty()) {
            return 0;
        }
        $fresh = $rows->filter(fn ($r) => $r->sent_at >= now()->subDay()->toDateTimeString());
        foreach ($rows as $row) {
            if ($row->sent_at < now()->subDay()->toDateTimeString()) {
                $this->status($row->id, 'uncertain', 'receipt_expired');
            }
        }
        if ($fresh->isEmpty()) {
            return 0;
        }
        try {
            $response = Http::withToken(config('mobile_push.access_token'))->acceptJson()->connectTimeout(3)->timeout(8)->post('https://exp.host/--/api/v2/push/getReceipts', ['ids' => $fresh->pluck('ticket_id')->values()->all()]);
            if (! $response->successful() || ! is_array($data = $response->json('data'))) {
                return 0;
            }
            $count = 0;
            foreach ($fresh as $row) {
                $receipt = $data[$row->ticket_id] ?? null;
                if (! is_array($receipt)) {
                    continue;
                }
                if (($receipt['status'] ?? '') === 'ok') {
                    $this->status($row->id, 'provider_accepted');
                    $count++;
                } elseif (($receipt['status'] ?? '') === 'error') {
                    if (($receipt['details']['error'] ?? '') === 'DeviceNotRegistered') {
                        $device = DB::table('mobile_push_devices')->find($row->device_id);
                        if ($device && $device->binding_key === $row->binding_key) {
                            $this->disable($device);
                        }
                    }
                    $this->status($row->id, 'failed', 'provider_rejected');
                    $count++;
                }
            }

            return $count;
        } catch (\Throwable) {
            return 0;
        }
    }
}
