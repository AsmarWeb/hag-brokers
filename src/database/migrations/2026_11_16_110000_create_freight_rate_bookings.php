<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freight_rate_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('rate_id')->nullable()->constrained('freight_rates')->nullOnDelete();
            $table->foreignId('source_id')->nullable()->constrained('freight_rate_sources')->nullOnDelete();
            $table->foreignId('carrier_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('request_key');
            $table->char('payload_hash', 64);
            $table->string('status', 32)->default('request_pending')->index();
            $table->char('currency', 3);
            $table->decimal('supplier_total', 14, 2);
            $table->decimal('customer_total', 14, 2);
            $table->decimal('commission_total', 14, 2);
            $table->json('rate_snapshot');
            $table->string('carrier_reference', 120)->nullable();
            $table->text('status_note')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'request_key'], 'freight_rate_booking_customer_key_uq');
            $table->index(['customer_id', 'status', 'created_at'], 'freight_rate_booking_customer_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freight_rate_bookings');
    }
};
