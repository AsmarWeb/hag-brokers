<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_app_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->json('content');
            $table->unsignedInteger('version')->default(0);
            $table->timestamps();
        });
        Schema::create('mobile_app_setting_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version');
            $table->json('content');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_app_setting_audits');
        Schema::dropIfExists('mobile_app_settings');
    }
};
