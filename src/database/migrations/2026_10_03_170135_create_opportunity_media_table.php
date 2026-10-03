<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunity_media', function (Blueprint $table) {
            $table->id();

            $table->foreignId('opportunity_id')
                ->constrained('opportunities')
                ->cascadeOnDelete();

            $table->string('type')->index();

            $table->string('path');

            $table->string('mime_type')->nullable();

            $table->unsignedBigInteger('size')->nullable();

            $table->string('original_name')->nullable();

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->boolean('is_primary')
                ->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_media');
    }
};
