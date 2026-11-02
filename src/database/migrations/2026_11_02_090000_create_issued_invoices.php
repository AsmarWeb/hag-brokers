<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->primary();
            $table->unsignedBigInteger('last_number')->default(0);
        });
        Schema::create('issued_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_draft_id')->unique()->constrained('invoice_drafts')->restrictOnDelete();
            $table->string('reference', 64)->unique();
            $table->unsignedSmallInteger('sequence_year');
            $table->unsignedBigInteger('sequence_number');
            $table->unique(['sequence_year', 'sequence_number']);
            $table->char('currency', 3);
            foreach (['subtotal', 'tax_amount', 'total', 'subtotal_aed', 'tax_aed', 'total_aed'] as $column) {
                // Exact decimal text avoids SQLite NUMERIC affinity converting large amounts to float.
                $table->string($column, 32);
            }
            $table->string('exchange_rate', 32);
            $table->date('supply_date');
            $table->timestamp('issued_at');
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('snapshot');
            $table->string('snapshot_sha256', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issued_invoices');
        Schema::dropIfExists('invoice_sequences');
    }
};
