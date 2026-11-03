<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_trade_offers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opportunity_id')->constrained()->restrictOnDelete();
            $t->foreignId('opportunity_interest_id')->unique()->constrained('opportunity_interests')->restrictOnDelete();
            $t->foreignId('sender_workspace_id')->constrained('company_workspaces')->restrictOnDelete();
            $t->foreignId('publisher_workspace_id')->constrained('company_workspaces')->restrictOnDelete();
            $t->unsignedBigInteger('submitted_by');
            $t->uuid('request_key');
            $t->char('request_hash', 64);
            $t->string('quantity', 24);
            $t->string('unit', 24);
            $t->string('unit_price', 32);
            $t->string('base_amount', 32);
            $t->char('currency', 3);
            $t->string('tax_basis', 24);
            $t->unsignedSmallInteger('delivery_days');
            $t->date('valid_until');
            $t->text('payment_terms');
            $t->text('notes')->nullable();
            $t->json('snapshot');
            $t->json('attachments');
            $t->char('content_sha256', 64);
            $t->string('status', 20)->default('submitted');
            $t->unsignedInteger('version')->default(0);
            $t->timestamps();
            $t->unique(['sender_workspace_id', 'request_key'], 'trade_offer_request_unique');
            $t->index(['opportunity_id', 'status'], 'trade_offer_status');
        });
        Schema::create('company_trade_offer_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_trade_offer_id')->constrained('company_trade_offers')->restrictOnDelete();
            $t->unsignedBigInteger('actor_id');
            $t->string('actor_name');
            $t->string('action', 20);
            $t->text('reason');
            $t->unsignedInteger('version');
            $t->timestamp('created_at');
            $t->unique(['company_trade_offer_id', 'version'], 'trade_offer_event_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_trade_offer_events');
        Schema::dropIfExists('company_trade_offers');
    }
};
