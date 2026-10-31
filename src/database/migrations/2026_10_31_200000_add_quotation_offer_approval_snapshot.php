<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('approved_offer_id')
                ->nullable()
                ->constrained('quotation_offers')
                ->nullOnDelete();

            $table->decimal('approved_carrier_amount', 12, 2)->nullable();
            $table->decimal('approved_customer_amount', 12, 2)->nullable();
            $table->decimal('approved_margin', 12, 2)->nullable();
            $table->string('approved_currency', 3)->nullable();

            $table->foreignId('offer_approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('offer_approved_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_offer_id');
            $table->dropConstrainedForeignId('offer_approved_by');
            $table->dropColumn([
                'approved_carrier_amount',
                'approved_customer_amount',
                'approved_margin',
                'approved_currency',
                'offer_approved_at',
            ]);
        });
    }
};
