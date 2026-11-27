<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table): void {
            $table->foreignId('deal_id')->nullable()->constrained('deals')->restrictOnDelete();
            $table->foreignId('deal_document_id')->nullable()->unique()
                ->constrained('deal_documents')->restrictOnDelete();
            $table->foreignId('freight_rate_booking_id')->nullable()
                ->constrained('freight_rate_bookings')->restrictOnDelete();
            $table->foreignId('freight_booking_offer_id')->nullable()
                ->constrained('freight_booking_offers')->restrictOnDelete();
            $table->foreignId('freight_sailing_id')->nullable()
                ->constrained('freight_sailings')->restrictOnDelete();
            $table->char('fixture_recap_sha256', 64)->nullable();
            $table->json('fixture_recap_snapshot')->nullable();
            $table->char('fixture_recap_snapshot_sha256', 64)->nullable();
            $table->index(['deal_id', 'created_at'], 'shipments_deal_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table): void {
            $table->dropIndex('shipments_deal_created_idx');
            $table->dropForeign(['freight_sailing_id']);
            $table->dropForeign(['freight_booking_offer_id']);
            $table->dropForeign(['freight_rate_booking_id']);
            $table->dropForeign(['deal_document_id']);
            $table->dropUnique(['deal_document_id']);
            $table->dropForeign(['deal_id']);
            $table->dropColumn([
                'freight_sailing_id',
                'freight_booking_offer_id',
                'freight_rate_booking_id',
                'deal_document_id',
                'deal_id',
                'fixture_recap_sha256',
                'fixture_recap_snapshot',
                'fixture_recap_snapshot_sha256',
            ]);
        });
    }
};
