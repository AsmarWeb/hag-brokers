<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_push_devices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('access_token_id')->constrained('personal_access_tokens')->cascadeOnDelete();
            $t->uuid('project_id');
            $t->char('token_hash', 64)->unique();
            $t->text('expo_token');
            $t->uuid('binding_key')->unique();
            $t->boolean('enabled')->default(true);
            $t->boolean('offers')->default(true);
            $t->boolean('messages')->default(true);
            $t->string('locale', 2)->default('ar');
            $t->timestamp('enabled_at');
            $t->timestamp('last_seen_at');
            $t->timestamps();
        });
        Schema::create('mobile_push_deliveries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('device_id')->constrained('mobile_push_devices')->cascadeOnDelete();
            $t->uuid('notification_id');
            $t->uuid('binding_key');
            $t->string('status', 24)->default('pending');
            $t->string('ticket_id')->nullable();
            $t->string('last_error', 50)->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();
            $t->unique(['device_id', 'notification_id', 'binding_key'], 'push_device_notice_binding_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_push_deliveries');
        Schema::dropIfExists('mobile_push_devices');
    }
};
