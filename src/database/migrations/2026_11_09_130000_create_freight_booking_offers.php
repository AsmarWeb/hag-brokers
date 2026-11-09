<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freight_booking_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->unique()->constrained()->cascadeOnDelete();
            $table->uuid('revision');
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3);
            $table->text('terms')->nullable();
            $table->dateTime('expires_at');
            $table->dateTime('accepted_at')->nullable();
            $table->string('carrier_reference', 100)->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freight_booking_offers');
    }
};
