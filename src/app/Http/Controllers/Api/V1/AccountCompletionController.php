<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\MobileVerifyEmail;
use App\Rules\NonDisposableEmail;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class AccountCompletionController extends Controller
{
    public static function revision(User $u): string
    {
        return hash('sha256', json_encode([$u->id, $u->name, $u->email, (string) $u->email_verified_at], JSON_THROW_ON_ERROR));
    }

    private function data(User $u): array
    {
        return ['user' => MobileAuthController::identity($u), 'revision' => self::revision($u)];
    }

    public function show(Request $r)
    {
        return response()->json(['data' => $this->data($r->user())]);
    }

    public function update(Request $r)
    {
        $r->merge(['email' => strtolower(trim((string) $r->input('email')))]);
        $d = $r->validate(['revision' => ['required', 'string', 'size:64'], 'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($r->user()->id), new NonDisposableEmail()],
            'current_password' => ['nullable', 'string', 'max:128'], 'customer_id' => ['prohibited'], 'role' => ['prohibited'], 'email_verified_at' => ['prohibited']]);
        try {
            [$u,$changed] = DB::transaction(function () use ($r, $d) {
                $u = User::whereKey($r->user()->id)->lockForUpdate()->firstOrFail();
                abort_unless(hash_equals(self::revision($u), $d['revision']), 409, 'Account changed. Reload before saving.');
                $changed = $d['email'] !== $u->email;
                if ($changed && ! Hash::check($d['current_password'] ?? '', $u->password)) {
                    throw ValidationException::withMessages(['current_password' => [app()->getLocale() === 'ar' ? 'أدخل كلمة المرور الحالية الصحيحة لتغيير البريد.' : 'The correct current password is required to change email.']]);
                }
                $u->name = $d['name'];
                $u->email = $d['email'];
                if ($changed) {
                    $u->email_verified_at = null;
                    $u->remember_token = null;
                }
                $u->save();
                if ($changed) {
                    $u->tokens()->delete();

                    DB::table('phone_verifications')
                        ->where('user_id', $u->getKey())
                        ->delete();
                }

                return [$u, $changed];
            });
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages(['email' => [app()->getLocale() === 'ar' ? 'هذا البريد غير متاح. اختر بريدًا آخر.' : 'This email is unavailable. Choose another email.']]);
        }
        $sent = false;
        if ($changed) {
            try {
                $u->notify(new MobileVerifyEmail);
                $sent = true;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json(['data' => $this->data($u), 'meta' => ['email_changed' => $changed, 'verification_requested' => $sent]]);
    }

    public function deletion(Request $r)
    {
        $row = DB::table('account_deletion_requests')->where('user_id', $r->user()->id)->first();

        return response()->json(['data' => $row ? ['status' => $row->status, 'requested_at' => Carbon::parse($row->created_at)->toIso8601String()] : null]);
    }

    public function requestDeletion(Request $r)
    {
        $d = $r->validate(['current_password' => ['required', 'string', 'max:128'], 'confirm' => ['required', 'accepted']]);
        [$row,$created] = DB::transaction(function () use ($r, $d) {
            $u = User::whereKey($r->user()->id)->lockForUpdate()->firstOrFail();
            if (! Hash::check($d['current_password'], $u->password)) {
                throw ValidationException::withMessages(['current_password' => [app()->getLocale() === 'ar' ? 'كلمة المرور الحالية غير صحيحة.' : 'The current password is incorrect.']]);
            }
            $row = DB::table('account_deletion_requests')->where('user_id', $u->id)->first();
            if ($row) {
                return [$row, false];
            }
            DB::table('account_deletion_requests')->insert(['user_id' => $u->id, 'status' => 'pending', 'version' => 1, 'created_at' => now(), 'updated_at' => now()]);

            return [DB::table('account_deletion_requests')->where('user_id', $u->id)->first(), true];
        });

        // A request is not deletion. Neither company records nor account access are removed here.
        return response()->json(['data' => ['status' => $row->status, 'requested_at' => Carbon::parse($row->created_at)->toIso8601String()], 'meta' => ['created' => $created]], $created ? 201 : 200);
    }
}
