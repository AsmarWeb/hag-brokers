<?php

namespace App\Services\AccountDeletion;

use App\Models\CompanyMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class AccountDeletionFulfillmentService
{
    public function complete(int $requestId, User $admin): void
    {
        DB::transaction(function () use ($requestId, $admin): void {
            $deletionRequest = DB::table('account_deletion_requests')
                ->where('id', $requestId)
                ->lockForUpdate()
                ->first();

            abort_unless($deletionRequest, 404);

            if ($deletionRequest->status !== 'email_confirmed') {
                throw new ConflictHttpException(
                    'Only email-confirmed deletion requests can be completed.'
                );
            }

            $user = User::query()
                ->whereKey($deletionRequest->user_id)
                ->lockForUpdate()
                ->first();

            if (! $user) {
                throw new ConflictHttpException(
                    'The account no longer exists.'
                );
            }

            if ($user->hasRole(['admin', 'super-admin'])) {
                throw new ConflictHttpException(
                    'Administrator accounts cannot be deleted through this workflow.'
                );
            }

            if (CompanyMember::query()
                ->where('user_id', $user->id)
                ->where('role', 'owner')
                ->exists()) {
                throw new ConflictHttpException(
                    'The account owns a company workspace and cannot be deleted.'
                );
            }

            $user->delete();

            DB::table('account_deletion_requests')
                ->where('id', $requestId)
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'user_id' => null,
                    'version' => $deletionRequest->version + 1,
                    'updated_at' => now(),
                ]);
        });
    }
}
