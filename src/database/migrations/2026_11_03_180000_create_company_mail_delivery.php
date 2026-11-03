<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_mail_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->unique('company_mail_pref_workspace')->constrained('company_workspaces')->restrictOnDelete();
            $table->boolean('enabled')->default(false);
            $table->boolean('offers')->default(true);
            $table->boolean('messages')->default(true);
            $table->string('locale', 2)->default('ar');
            $table->timestamp('enabled_at')->nullable();
            $table->timestamps();
        });
        Schema::create('company_mail_deliveries', function (Blueprint $table) {
            $table->id();
            $table->uuid('notification_id')->unique('company_mail_notification_unique');
            $table->foreignId('workspace_id')->constrained('company_workspaces')->restrictOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->string('event_type', 40);
            $table->unsignedBigInteger('offer_id');
            $table->string('status', 20)->default('pending');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('next_attempt_at')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('last_error', 40)->nullable();
            $table->timestamps();
            $table->index(['status', 'next_attempt_at'], 'company_mail_pending_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_mail_deliveries');
        Schema::dropIfExists('company_mail_preferences');
    }
};
