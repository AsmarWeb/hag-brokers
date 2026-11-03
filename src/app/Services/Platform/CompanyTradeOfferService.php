<?php

namespace App\Services\Platform;

use App\Models\CompanyMember;
use App\Models\CompanyTradeOffer;
use App\Models\CompanyTradeOfferEvent;
use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\User;
use App\Notifications\AdminPlatformNotification;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CompanyTradeOfferService
{
    public const TYPES = ['buy', 'service_request', 'procurement'];

    public const FIELDS = ['opportunity_id', 'opportunity_interest_id', 'sender_workspace_id', 'publisher_workspace_id', 'submitted_by', 'quantity', 'unit', 'unit_price', 'base_amount', 'currency', 'tax_basis', 'delivery_days', 'valid_until', 'payment_terms', 'notes', 'snapshot', 'attachments'];

    private function fail(string $key): never
    {
        throw ValidationException::withMessages(['offer' => __('rfq.errors.'.$key)]);
    }

    public function canonical(mixed $v): mixed
    {
        if (! is_array($v)) {
            return $v;
        }if (! array_is_list($v)) {
            ksort($v);
        }foreach ($v as &$item) {
            $item = $this->canonical($item);
        }

return $v;
    }

    public function digest(array $v): string
    {
        return hash('sha256', json_encode($this->canonical($v), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function verify(CompanyTradeOffer $offer): bool
    {
        $values = [];
        foreach (self::FIELDS as $field) {
            $values[$field] = $field === 'valid_until' ? $offer->valid_until->format('Y-m-d') : $offer->$field;
        }

        return hash_equals($offer->content_sha256, $this->digest($values));
    }

    public function authorize(User $user, CompanyTradeOffer $offer, bool $admin = false): ?CompanyWorkspace
    {
        if ($admin) {
            abort_unless($user->hasRole(['admin', 'super-admin']), 403);

            return null;
        }
        $w = app(CompanyMarketplaceService::class)->actor($user, true);
        abort_unless(in_array((int) $w->id, [(int) $offer->sender_workspace_id, (int) $offer->publisher_workspace_id], true), 404);

        return $w;
    }

    public function eligible(Opportunity $o): bool
    {
        return in_array($o->opportunity_type, self::TYPES, true) && $o->company_workspace_id && app(CompanyMarketplaceService::class)->isPublic($o);
    }

    private function notify(int $workspaceId, string $type, CompanyTradeOffer $offer): void
    {
        $w = CompanyWorkspace::findOrFail($workspaceId);
        $members = CompanyMember::where('company_workspace_id', $w->id)->whereIn('role', ['owner', 'manager'])->select('user_id');
        foreach (User::where('customer_id', $w->customer_id)->whereIn('id', $members)->get() as $recipient) {
            if (! $recipient->hasRole(['admin', 'super-admin'])) {
                $recipient->notify(new AdminPlatformNotification(type: 'rfq.'.$type, title: 'تحديث عرض تجاري / Commercial offer update', message: $offer->snapshot['title'], url: '/company/trade-offers/'.$offer->id, extra: ['offer_id' => $offer->id, 'action' => $type]));
            }
        }
    }

    public function submit(User $actor, Opportunity $opportunity, array $data, array $uploads = []): CompanyTradeOffer
    {
        $market = app(CompanyMarketplaceService::class);
        $workspace = $market->actor($actor, true);
        $data['delivery_days'] = (int) $data['delivery_days'];
        $data['notes'] = $data['notes'] ?? null;
        $data['quantity'] = (string) BigDecimal::of($data['quantity'])->toScale(3, RoundingMode::UNNECESSARY);
        $data['unit_price'] = (string) BigDecimal::of($data['unit_price'])->toScale(2, RoundingMode::UNNECESSARY);
        $total = BigDecimal::of($data['unit_price'])->multipliedBy($data['quantity'])->toScale(2, RoundingMode::HALF_UP);
        if ($total->isLessThanOrEqualTo('0') || $total->isGreaterThan('9999999999999999.99')) {
            $this->fail('amount');
        }
        $fileHashes = [];
        foreach ($uploads as $file) {
            $fileHashes[] = ['sha256' => hash_file('sha256', $file->getRealPath()), 'name' => $file->getClientOriginalName(), 'size' => $file->getSize()];
        }
        $hash = $this->digest(['opportunity_id' => $opportunity->id, 'data' => $data, 'files' => $fileHashes]);
        $written = [];
        try {
            return DB::transaction(function () use ($actor, $opportunity, $data, $uploads, $workspace, $hash, $total, &$written, $market) {
                CompanyWorkspace::whereKey($workspace->id)->lockForUpdate()->firstOrFail();
                $w = $market->actor($actor, true);
                $o = Opportunity::whereKey($opportunity->id)->lockForUpdate()->firstOrFail();
                $existing = CompanyTradeOffer::where('sender_workspace_id', $w->id)->where('request_key', $data['request_key'])->first();
                if ($existing) {
                    if (! $this->verify($existing)) {
                        $this->fail('integrity');
                    }if (! hash_equals($existing->request_hash, $hash)) {
                        $this->fail('replay');
                    }

return $existing;
                }
                abort_unless($this->eligible($o), 404);
                abort_if((int) $o->company_workspace_id === (int) $w->id, 403);
                if (CompanyTradeOffer::where('opportunity_id', $o->id)->where('sender_workspace_id', $w->id)->exists()) {
                    $this->fail('existing');
                }
                $interest = $market->apply($actor, $o, ['role' => 'supplier', 'message' => __('rfq.application_message'), 'currency' => $data['currency'], 'available_capital' => null], $data['request_key']);
                if (in_array($interest->status, ['rejected', 'withdrawn'], true)) {
                    $this->fail('interest');
                }
                $files = [];
                foreach ($uploads as $file) {
                    $path = $file->storeAs('offers', (string) Str::uuid().'.'.$file->guessExtension(), 'rfq_private');
                    $written[] = $path;
                    $files[] = ['path' => $path, 'sha256' => hash_file('sha256', $file->getRealPath()), 'name' => mb_substr($file->getClientOriginalName(), 0, 180), 'size' => $file->getSize()];
                }
                $publisher = CompanyWorkspace::findOrFail($o->company_workspace_id);
                $terms = array_intersect_key($data, array_flip(['quantity', 'unit', 'unit_price', 'currency', 'tax_basis', 'delivery_days', 'valid_until', 'payment_terms', 'notes']));
                $terms += ['notes' => null];
                $values = $terms + ['opportunity_id' => $o->id, 'opportunity_interest_id' => $interest->id, 'sender_workspace_id' => $w->id, 'publisher_workspace_id' => $publisher->id, 'submitted_by' => $actor->id, 'base_amount' => (string) $total, 'snapshot' => ['title' => $o->title, 'type' => $o->opportunity_type, 'publisher' => Customer::findOrFail($publisher->customer_id)->company_name, 'sender' => Customer::findOrFail($w->customer_id)->company_name], 'attachments' => $files];
                $offer = CompanyTradeOffer::create($values + ['request_key' => $data['request_key'], 'request_hash' => $hash, 'content_sha256' => $this->digest($values), 'status' => 'submitted', 'version' => 0]);
                CompanyTradeOfferEvent::create(['company_trade_offer_id' => $offer->id, 'actor_id' => $actor->id, 'actor_name' => $actor->name, 'action' => 'submitted', 'reason' => __('rfq.submitted_event'), 'version' => 0, 'created_at' => now()]);
                $this->notify($publisher->id, 'submitted', $offer);

                return $offer;
            });
        } catch (\Throwable $e) {
            foreach ($written as $path) {
                Storage::disk('rfq_private')->delete($path);
            }throw $e;
        }
    }

    public function decide(User $actor, CompanyTradeOffer $offer, array $data): void
    {
        $w = $this->authorize($actor, $offer);
        if (! in_array($data['action'], ['selected', 'declined', 'withdrawn'], true)) {
            $this->fail('state');
        }
        DB::transaction(function () use ($actor, $offer, $data, $w) {
            CompanyWorkspace::whereKey($w->id)->lockForUpdate()->firstOrFail();
            app(CompanyMarketplaceService::class)->actor($actor, true);
            $o = Opportunity::whereKey($offer->opportunity_id)->lockForUpdate()->firstOrFail();
            $q = CompanyTradeOffer::whereKey($offer->id)->lockForUpdate()->firstOrFail();
            if (! $this->verify($q)) {
                $this->fail('integrity');
            }if ($q->version !== (int) $data['version']) {
                $this->fail('version');
            }
            if ($data['action'] === 'withdrawn') {
                abort_unless((int) $q->sender_workspace_id === (int) $w->id, 403);
            } else {
                abort_unless((int) $q->publisher_workspace_id === (int) $w->id, 403);
            }
            if (! in_array($q->status, ['submitted', 'selected'], true) || ($q->status === 'selected' && $data['action'] === 'selected')) {
                $this->fail('state');
            }
            if ($data['action'] === 'selected') {
                if (! $this->eligible($o) || $q->valid_until->format('Y-m-d') < now('Asia/Dubai')->toDateString()) {
                    $this->fail('expired');
                }
                $sender = CompanyWorkspace::find($q->sender_workspace_id);
                if (! $sender || $sender->status !== 'active' || ! Customer::whereKey($sender->customer_id)->where('is_active', true)->exists()) {
                    $this->fail('expired');
                }
                if (CompanyTradeOffer::where('opportunity_id', $o->id)->where('status', 'selected')->whereKeyNot($q->id)->exists()) {
                    $this->fail('selected');
                }
            }
            $q->update(['status' => $data['action'], 'version' => $q->version + 1]);
            CompanyTradeOfferEvent::create(['company_trade_offer_id' => $q->id, 'actor_id' => $actor->id, 'actor_name' => $actor->name, 'action' => $data['action'], 'reason' => $data['reason'], 'version' => $q->version, 'created_at' => now()]);
            $target = (int) $w->id === (int) $q->sender_workspace_id ? $q->publisher_workspace_id : $q->sender_workspace_id;
            $this->notify($target,$data['action'],$q);
        });
    }
}
