<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_deletion_email_verifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('email', 254);
            $table->char('token_hash', 64)->nullable()->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_deletion_email_verifications');
    }
};
