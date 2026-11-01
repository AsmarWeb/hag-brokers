<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('deal_commission_id')->unique()->constrained('deal_commissions')->restrictOnDelete();
            $table->foreignId('deal_id')->constrained('deals')->restrictOnDelete();
            $table->foreignId('deal_agreement_id')->constrained('deal_agreements')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->string('status', 24)->default('draft')->index();
            $table->char('currency', 3);
            $table->decimal('amount', 18, 2);
            $table->string('source_agreement_sha256', 64);
            $table->json('snapshot');
            $table->string('snapshot_sha256', 64);
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['customer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_drafts');
    }
};
