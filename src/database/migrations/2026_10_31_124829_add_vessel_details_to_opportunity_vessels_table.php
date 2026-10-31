<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunity_vessels', function (Blueprint $table) {
            $table->foreignId('opportunity_id')
                ->nullable()
                ->unique()
                ->constrained('opportunities')
                ->cascadeOnDelete();

            $table->string('listing_type', 30)->default('sale');
            $table->string('vessel_category', 40)->default('yacht');
            $table->string('vessel_type', 80)->nullable();
            $table->string('vessel_name')->nullable();
            $table->string('builder')->nullable();
            $table->unsignedSmallInteger('year_built')->nullable();

            $table->decimal('length_m', 10, 2)->nullable();
            $table->decimal('beam_m', 10, 2)->nullable();
            $table->decimal('draft_m', 10, 2)->nullable();
            $table->decimal('gross_tonnage', 14, 2)->nullable();
            $table->decimal('deadweight_tonnage', 14, 2)->nullable();

            $table->string('imo_number', 7)->nullable();
            $table->string('flag_state', 100)->nullable();
            $table->string('classification_society', 150)->nullable();

            $table->unsignedSmallInteger('cabins')->nullable();
            $table->unsignedSmallInteger('guest_capacity')->nullable();
            $table->unsignedSmallInteger('crew_capacity')->nullable();

            $table->string('engine_make')->nullable();
            $table->unsignedInteger('engine_hours')->nullable();

            $table->string('condition', 30)->nullable();
            $table->boolean('price_on_request')->default(false);

            $table->index(['vessel_category', 'listing_type']);
        });
    }

    public function down(): void
    {
        Schema::table('opportunity_vessels', function (Blueprint $table) {
            $table->dropForeign(['opportunity_id']);
            $table->dropUnique(['opportunity_id']);
            $table->dropIndex(['vessel_category', 'listing_type']);

            $table->dropColumn([
                'opportunity_id', 'listing_type', 'vessel_category',
                'vessel_type', 'vessel_name', 'builder', 'year_built',
                'length_m', 'beam_m', 'draft_m', 'gross_tonnage',
                'deadweight_tonnage', 'imo_number', 'flag_state',
                'classification_society', 'cabins', 'guest_capacity',
                'crew_capacity', 'engine_make', 'engine_hours',
                'condition', 'price_on_request'
            ]);
        });
    }
};
