<?php

namespace App\Services\Platform;

use App\Jobs\SendCompanyMail;
use App\Mail\CompanyActivityMail;
use App\Models\CompanyMailDelivery;
use App\Models\CompanyMailPreference;
use App\Models\CompanyMember;
use App\Models\CompanyTradeOffer;
use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\TradeOfferRead;
use App\Models\User;
use App\Notifications\AdminPlatformNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class CompanyMailService
{
    public const EVENTS = ['rfq.submitted', 'rfq.selected', 'rfq.declined', 'rfq.withdrawn', 'rfq.message'];

    public function ready(): bool
    {
        $url = parse_url((string) config('app.url'));
        $mailer = config('company_mail.mailer');

        return config('company_mail.enabled') && (! config('queue.connections.database.connection') || config('queue.connections.database.connection') === config('database.default')) && isset($url['host']) && ! isset($url['user']) && ! isset($url['pass'])
            && (app()->environment('testing') || (($url['scheme'] ?? '') === 'https' && config("mail.mailers.$mailer.transport") === 'smtp' && config("mail.mailers.$mailer.host")))
            && filter_var(config('mail.from.address'), FILTER_VALIDATE_EMAIL);
    }

    public function recipient(int $userId, int $workspaceId): ?User
    {
        $w = CompanyWorkspace::whereKey($workspaceId)->where('status', 'active')->first();
        if (! $w || ! Customer::whereKey($w->customer_id)->where('is_active', true)->exists()) {
            return null;
        }
        $user = User::whereKey($userId)->where('customer_id', $w->customer_id)->first();
        if (! $user || ! $user->hasVerifiedEmail() || ! filter_var($user->email, FILTER_VALIDATE_EMAIL) || $user->hasRole(['admin', 'super-admin'])) {
            return null;
        }

        return CompanyMember::where('company_workspace_id', $w->id)->where('user_id', $user->id)->whereIn('role', ['owner', 'manager'])->exists() ? $user : null;
    }

    private function permitted(CompanyMailPreference $p, string $event): bool
    {
        return $p->enabled && ($event === 'rfq.message' ? $p->messages : $p->offers);
    }

    public function collect(): int
    {
        if (! $this->ready()) {
            return 0;
        }
        $count = 0;
        // Finite catch-up window; never replay the historical notification archive.
        DatabaseNotification::where('type', AdminPlatformNotification::class)->where('notifiable_type', (new User)->getMorphClass())->where('created_at', '>=', now()->subHours(48))
            ->orderBy('id')->chunk(200, function ($notifications) use (&$count) {
                foreach ($notifications as $n) {
                    $event = $n->data['type'] ?? '';
                    if (! in_array($event, self::EVENTS, true)) {
                        continue;
                    }
                    $member = CompanyMember::where('user_id', $n->notifiable_id)->first();
                    if (! $member) {
                        continue;
                    }
                    $p = CompanyMailPreference::where('workspace_id', $member->company_workspace_id)->first();
                    if (! $p || ! $this->permitted($p, $event) || ! $p->enabled_at || $n->created_at->lt($p->enabled_at) || ! $this->recipient((int) $n->notifiable_id, (int) $p->workspace_id)) {
                        continue;
                    }
                    $offer = CompanyTradeOffer::find($n->data['offer_id'] ?? 0);
                    if (! $offer || ! in_array((int) $p->workspace_id, [(int) $offer->publisher_workspace_id, (int) $offer->sender_workspace_id], true)) {
                        continue;
                    }
                    $d = CompanyMailDelivery::firstOrCreate(['notification_id' => $n->id], ['workspace_id' => $p->workspace_id, 'user_id' => $n->notifiable_id, 'event_type' => $event, 'offer_id' => $offer->id, 'status' => 'pending', 'next_attempt_at' => now()]);
                    if ($d->wasRecentlyCreated) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    public function enqueue(): int
    {
        if (! $this->ready()) {
            return 0;
        }
        $count = 0;
        CompanyMailDelivery::where('status', 'pending')->where('attempts', '<', 3)->where(fn ($q) => $q->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now()))->where(fn ($q) => $q->whereNull('queued_at')->orWhere('queued_at', '<=', now()->subMinutes(10)))->orderBy('id')->limit(200)->get()->each(function ($d) use (&$count) {
            DB::transaction(function () use ($d, &$count) {
                $claimed = CompanyMailDelivery::whereKey($d->id)->where('status', 'pending')->where(fn ($q) => $q->whereNull('queued_at')->orWhere('queued_at', '<=', now()->subMinutes(10)))->update(['queued_at' => now()]);
                if ($claimed) {
                    SendCompanyMail::dispatch($d->id)->onConnection('database')->onQueue('company-mail');
                    $count++;
                }
            }, 3);
        });

        return $count;
    }

    public function deliver(int $id): void
    {
        if (! $this->ready()) {
            return;
        }
        $d = DB::transaction(function () use ($id) {
            $d = CompanyMailDelivery::whereKey($id)->lockForUpdate()->first();
            if (! $d || $d->status !== 'pending' || $d->attempts >= 3 || $d->next_attempt_at?->isFuture()) {
                return null;
            }
            $d->update(['status' => 'processing', 'attempts' => $d->attempts + 1]);

            return $d;
        }, 3);
        if (! $d) {
            return;
        }
        $p = CompanyMailPreference::where('workspace_id', $d->workspace_id)->first();
        $user = $this->recipient($d->user_id, $d->workspace_id);
        $n = DatabaseNotification::find($d->notification_id);
        $offer = CompanyTradeOffer::find($d->offer_id);
        if (! in_array($d->event_type, self::EVENTS, true) || ! $p || ! $this->permitted($p, $d->event_type) || ! $user || ! $n || (int) $n->notifiable_id !== (int) $d->user_id || $n->notifiable_type !== (new User)->getMorphClass() || $n->type !== AdminPlatformNotification::class || ($n->data['type'] ?? '') !== $d->event_type || (int) ($n->data['offer_id'] ?? 0) !== (int) $d->offer_id || $n->created_at->lt(now()->subHours(48)) || $n->read_at || ! $p->enabled_at || $n->created_at->lt($p->enabled_at) || ! $offer || ! in_array((int) $d->workspace_id, [(int) $offer->sender_workspace_id, (int) $offer->publisher_workspace_id], true)) {
            $d->update(['status' => 'skipped', 'last_error' => 'no_longer_eligible']);

            return;
        }
        if ($d->event_type === 'rfq.message' && TradeOfferRead::where('offer_id', $d->offer_id)->where('workspace_id', $d->workspace_id)->where('last_message_id', '>=', (int) ($n->data['message_id'] ?? PHP_INT_MAX))->exists()) {
            $d->update(['status' => 'skipped', 'last_error' => 'already_read']);

            return;
        }
        // No database transaction is held across the SMTP network request.
        try {
            $mailer = config('company_mail.mailer');
            if (config("mail.mailers.$mailer.transport") === 'smtp') {
                config(["mail.mailers.$mailer.timeout" => 20]);
            }
            Mail::mailer($mailer)->to($user->email)->send(new CompanyActivityMail($d->event_type, $d->offer_id, $p->locale));
        } catch (\Throwable $e) {
            // Keep provider bodies, credentials and recipient details out of the delivery log.
            $d->update(['status' => $d->attempts >= 3 ? 'failed' : 'pending', 'next_attempt_at' => now()->addMinutes(5 * $d->attempts), 'queued_at' => null, 'last_error' => 'delivery_failed']);

            return;
        }
        // A failure recording success leaves processing for review, never an automatic resend.
        $d->update(['status' => 'sent', 'sent_at' => now(), 'last_error' => null]);
    }
}
