<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunity_moderations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opportunity_id')->unique()->constrained()->restrictOnDelete();
            $t->boolean('hidden')->default(false);
            $t->unsignedInteger('version')->default(0);
            $t->timestamps();
        });
        Schema::create('opportunity_moderation_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opportunity_id')->constrained()->restrictOnDelete();
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('action', 20);
            $t->text('reason');
            $t->boolean('was_hidden');
            $t->boolean('is_hidden');
            $t->unsignedInteger('version');
            $t->timestamp('created_at');
            $t->unique(['opportunity_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_moderation_events');
        Schema::dropIfExists('opportunity_moderations');
    }
};
