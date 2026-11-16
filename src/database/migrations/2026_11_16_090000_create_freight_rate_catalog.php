<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freight_rate_sources', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name');
            $table->string('type', 30)->default('manual'); // hag, carrier, partner, provider, manual
            $table->string('tier', 20)->default('free'); // free, trial, paid, internal
            $table->json('capabilities')->nullable();
            $table->longText('credentials')->nullable(); // encrypted by the model cast
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_checked_at')->nullable();
            $table->string('health_status', 20)->default('unknown');
            $table->text('health_message')->nullable();
            $table->timestamps();
        });

        Schema::create('freight_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('freight_rate_sources')->restrictOnDelete();
            $table->foreignId('carrier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('freight_sailing_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('origin_port_id')->constrained('ports')->restrictOnDelete();
            $table->foreignId('destination_port_id')->constrained('ports')->restrictOnDelete();
            $table->string('container_type', 20);
            $table->unsignedInteger('min_quantity')->default(1);
            $table->unsignedInteger('max_quantity')->nullable();
            $table->decimal('supplier_unit_cost', 12, 2);
            $table->decimal('customer_unit_price', 12, 2);
            $table->char('currency', 3)->default('USD');
            $table->json('surcharge_items')->nullable();
            $table->json('included_charges')->nullable();
            $table->json('excluded_charges')->nullable();
            $table->string('availability', 24)->default('on_request'); // on_request, limited, available
            $table->string('booking_mode', 24)->default('request'); // request, manual_confirmation, instant_api
            $table->string('rate_type', 24)->default('manual'); // spot_rate, contract_rate, partner_rate, manual_rate
            $table->string('status', 20)->default('draft')->index();
            $table->string('source_reference', 120)->nullable();
            $table->timestamp('quoted_at')->nullable();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('terms')->nullable();
            $table->timestamps();

            $table->index(['origin_port_id', 'destination_port_id', 'container_type', 'status'], 'freight_rates_search_idx');
            $table->index(['status', 'valid_from', 'expires_at'], 'freight_rates_validity_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freight_rates');
        Schema::dropIfExists('freight_rate_sources');
    }
};
