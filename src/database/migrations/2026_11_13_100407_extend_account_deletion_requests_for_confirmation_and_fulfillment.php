<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_deletion_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
        });

        Schema::table('account_deletion_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();

            $table->string('requested_email')->nullable();
            $table->char('confirmation_token_hash', 64)->nullable()->unique();
            $table->timestamp('confirmation_expires_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('email_sent_at')->nullable();

            $table->text('rejection_reason')->nullable();
            $table->text('retention_reason')->nullable();
            $table->timestamp('completed_at')->nullable();
        });

        Schema::table('account_deletion_requests', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unique('user_id');
        });

        DB::table('account_deletion_requests')
            ->whereNull('requested_email')
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->each(function (object $request): void {
                $email = DB::table('users')
                    ->where('id', $request->user_id)
                    ->value('email');

                if ($email) {
                    DB::table('account_deletion_requests')
                        ->where('id', $request->id)
                        ->update(['requested_email' => $email]);
                }
            });
    }

    public function down(): void
    {
        if (DB::table('account_deletion_requests')->whereNull('user_id')->exists()) {
            throw new RuntimeException(
                'Cannot reverse account deletion migration while requests without users exist.'
            );
        }

        Schema::table('account_deletion_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropUnique(['confirmation_token_hash']);

            $table->dropColumn([
                'requested_email',
                'confirmation_token_hash',
                'confirmation_expires_at',
                'confirmed_at',
                'email_sent_at',
                'rejection_reason',
                'retention_reason',
                'completed_at',
            ]);
        });

        Schema::table('account_deletion_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->unique('user_id');
        });
    }
};
