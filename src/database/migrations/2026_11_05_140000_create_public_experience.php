<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_experience_settings', function (Blueprint $t) {
            $t->unsignedTinyInteger('id')->primary();
            $t->unsignedInteger('version')->default(0);
            $t->boolean('download_enabled')->default(false);
            $t->boolean('map_enabled')->default(true);
            $t->string('android_url', 500)->nullable();
            $t->string('android_version', 40)->nullable();
            $t->date('released_on')->nullable();
            $t->timestamps();
        });
        Schema::create('opportunity_map_locations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opportunity_id')->unique()->constrained()->cascadeOnDelete();
            $t->unsignedInteger('version')->default(0);
            $t->decimal('latitude', 5, 2)->nullable();
            $t->decimal('longitude', 6, 2)->nullable();
            $t->string('label', 120)->nullable();
            $t->boolean('is_public')->default(false);
            $t->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('confirmed_at')->nullable();
            $t->timestamps();
        });
        Schema::create('public_experience_audits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('action', 40);
            $t->unsignedBigInteger('opportunity_id')->nullable();
            $t->unsignedInteger('version');
            $t->text('payload');
            $t->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_experience_audits');
        Schema::dropIfExists('opportunity_map_locations');
        Schema::dropIfExists('public_experience_settings');
    }
};
