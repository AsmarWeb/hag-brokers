<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('event_uuid')->unique();
            $table->foreignId('issued_invoice_id')->constrained('issued_invoices')->restrictOnDelete();
            $table->string('kind', 16);
            $table->foreignId('reverses_id')->nullable()->unique()->constrained('invoice_payments')->restrictOnDelete();
            $table->uuid('request_key');
            $table->string('request_hash', 64);
            $table->string('receipt_reference_hash', 64)->nullable();
            // Strings retain exact cents in SQLite as well as MySQL.
            $table->string('amount', 32);
            $table->string('currency', 3);
            $table->date('paid_on');
            $table->string('method', 24);
            $table->string('reference', 120);
            $table->text('notes')->nullable();
            $table->text('reason')->nullable();
            // Retain attribution even after a user account is removed.
            $table->unsignedBigInteger('recorded_by');
            $table->string('recorded_by_name');
            $table->dateTime('recorded_at');
            $table->json('snapshot');
            $table->string('snapshot_sha256', 64);
            $table->unique(['issued_invoice_id', 'request_key']);
            $table->unique(['issued_invoice_id', 'receipt_reference_hash'], 'invoice_receipt_reference_unique');
            $table->index(['issued_invoice_id', 'id']);
        });
    }

    public function down(): void { Schema::dropIfExists('invoice_payments'); }
};
