<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')
                ->constrained('deals')->restrictOnDelete();
            $table->foreignId('actor_id')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 64);
            $table->json('payload');
            $table->string('payload_sha256', 64);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['deal_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_audits');
    }
};
