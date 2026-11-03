<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_live_billing_accounts', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('company_workspace_id')->unique('live_bill_workspace_unique')->constrained('company_workspaces')->restrictOnDelete();
            $t->uuid('customer_key')->unique('live_bill_customer_key_unique');
            $t->string('stripe_customer_id')->nullable()->unique('live_bill_stripe_customer_id_uq');
            $t->string('stripe_subscription_id')->nullable()->unique('live_bill_stripe_subscription_id_uq');
            $t->uuid('attempt_key')->nullable();
            $t->string('plan_code')->nullable();
            $t->json('plan_snapshot')->nullable();
            $t->string('price_id')->nullable();
            $t->string('checkout_session_id')->nullable()->unique('live_bill_checkout_session_id_uq');
            $t->text('checkout_url')->nullable();
            $t->dateTime('checkout_expires_at')->nullable();
            $t->string('status')->default('none');
            $t->unsignedBigInteger('sync_revision')->default(0);
            $t->dateTime('paid_until')->nullable();
            $t->boolean('cancel_at_period_end')->default(false);
            $t->dateTime('last_synced_at')->nullable();
            $t->timestamps();
        });
        Schema::create('company_live_billing_events', function (Blueprint $t): void {
            $t->id();
            $t->string('stripe_event_id')->unique('live_bill_stripe_event_id_uq');
            $t->string('type');
            $t->string('payload_sha256', 64);
            $t->dateTime('created_at');
        });
        Schema::create('company_live_billing_receipts', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('company_billing_account_id')->constrained('company_live_billing_accounts', indexName: 'live_bill_receipt_account_fk')->restrictOnDelete();
            $t->string('stripe_invoice_id')->unique('live_bill_stripe_invoice_id_uq');
            $t->string('stripe_subscription_id');
            $t->unsignedBigInteger('amount_paid_minor');
            $t->string('currency', 3);
            $t->dateTime('paid_until');
            $t->json('plan_snapshot');
            $t->dateTime('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_live_billing_receipts');
        Schema::dropIfExists('company_live_billing_events');
        Schema::dropIfExists('company_live_billing_accounts');
    }
};
