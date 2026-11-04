<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\MobilePushService;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class MobilePushController extends Controller
{
    private function device(Request $r)
    {
        return DB::table('mobile_push_devices')->where('user_id', $r->user()->id)->where('access_token_id', $r->user()->currentAccessToken()->id)->first();
    }

    public function settings(Request $r, CompanyMarketplaceService $market, MobilePushService $push)
    {
        $market->actor($r->user(), true);
        $d = $this->device($r);

        return response()->json(['data' => ['available' => $push->ready(), 'project_id' => config('mobile_push.project_id'),
            'device' => $d ? ['binding' => $d->binding_key, 'enabled' => (bool) $d->enabled, 'offers' => (bool) $d->offers, 'messages' => (bool) $d->messages, 'locale' => $d->locale] : null]]);
    }

    public function register(Request $r, CompanyMarketplaceService $market, MobilePushService $push)
    {
        $market->actor($r->user(), true);
        abort_unless($push->ready(), 503);
        $d = $r->validate(['expo_token' => ['required', 'string', 'max:255', 'regex:/^(ExpoPushToken|ExponentPushToken)\[[A-Za-z0-9_-]+\]$/D'],
            'project_id' => ['required', 'uuid'], 'offers' => ['required', 'boolean'], 'messages' => ['required', 'boolean'], 'locale' => ['required', 'in:ar,en']]);
        abort_unless(hash_equals((string) config('mobile_push.project_id'), $d['project_id']), 422);
        $binding = DB::transaction(function () use ($r, $d, $market) {
            $u = User::whereKey($r->user()->id)->lockForUpdate()->firstOrFail();
            $market->actor($u, true);
            $hash = hash('sha256', $d['expo_token']);
            $old = DB::table('mobile_push_devices')->where('token_hash', $hash)->lockForUpdate()->first();
            $same = $old && $old->project_id === config('mobile_push.project_id') && (int) $old->user_id === (int) $u->id && (int) $old->access_token_id === (int) $r->user()->currentAccessToken()->id;
            $reset = ! $same || ! $old->enabled || (! $old->offers && $d['offers']) || (! $old->messages && $d['messages']);
            if (! $same && DB::table('mobile_push_devices')->where('user_id', $u->id)->count() >= 10) {
                throw ValidationException::withMessages(['device' => ['Revoke an old device before registering another.']]);
            }
            DB::table('mobile_push_devices')->where('access_token_id', $r->user()->currentAccessToken()->id)->when($old, fn ($q) => $q->where('id', '!=', $old->id))->delete();
            $key = $reset ? (string) Str::uuid() : $old->binding_key;
            $values = ['project_id' => config('mobile_push.project_id'), 'user_id' => $u->id, 'access_token_id' => $r->user()->currentAccessToken()->id, 'expo_token' => Crypt::encryptString($d['expo_token']),
                'binding_key' => $key, 'enabled' => true, 'offers' => $d['offers'], 'messages' => $d['messages'], 'locale' => $d['locale'],
                'enabled_at' => $reset ? now() : $old->enabled_at, 'last_seen_at' => now(), 'updated_at' => now()];
            if ($old) {
                DB::table('mobile_push_devices')->where('id', $old->id)->update($values);
            } else {
                DB::table('mobile_push_devices')->insert($values + ['token_hash' => $hash, 'created_at' => now()]);
            }

            return $key;
        });

        return response()->json(['data' => ['binding' => $binding]]);
    }

    public function remove(Request $r)
    {
        DB::table('mobile_push_devices')->where('user_id', $r->user()->id)->where('access_token_id', $r->user()->currentAccessToken()->id)->delete();

        return response()->noContent();
    }

    public function resolve(Request $r, CompanyMarketplaceService $market, MobilePushService $push)
    {
        $market->actor($r->user(), true);
        $d = $r->validate(['binding' => ['required', 'uuid'], 'notice_id' => ['required', 'uuid']]);
        $device = $this->device($r);
        abort_unless($device && $device->enabled && hash_equals($device->binding_key, $d['binding']), 404);
        $n = DatabaseNotification::find($d['notice_id']);
        // Already-read notices may still be opened, but membership, ownership and event checks remain mandatory.
        abort_unless($push->eligible($device, $n, false), 404);

        return response()->json(['data' => ['name' => $n->data['type'] === 'rfq.message' ? 'room' : 'offer', 'id' => (int) $n->data['offer_id']]]);
    }
}
