<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freight_sailings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('origin_port_id')->constrained('ports')->restrictOnDelete();
            $table->foreignId('destination_port_id')->constrained('ports')->restrictOnDelete();
            $table->foreignId('carrier_id')->constrained()->restrictOnDelete();
            $table->string('vessel_name', 120);
            $table->string('voyage_number', 60);
            $table->dateTime('departure_at');
            $table->dateTime('arrival_at');
            $table->dateTime('cargo_cutoff_at');
            $table->json('container_types');
            $table->string('status', 20)->default('draft');
            $table->timestamps();
            $table->index(['origin_port_id', 'destination_port_id', 'status', 'departure_at'], 'freight_sailings_route_idx');
            $table->unique(['carrier_id', 'origin_port_id', 'destination_port_id', 'voyage_number', 'departure_at'], 'freight_sailings_voyage_uq');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('freight_sailing_id')->nullable()->constrained()->restrictOnDelete();
            $table->json('sailing_snapshot')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('freight_sailing_id');
            $table->dropColumn('sailing_snapshot');
        });
        Schema::dropIfExists('freight_sailings');
    }
};
