<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_billing_accounts', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('company_workspace_id')->unique()->constrained('company_workspaces')->restrictOnDelete();
            $t->uuid('customer_key')->unique();
            $t->string('stripe_customer_id')->nullable()->unique();
            $t->string('stripe_subscription_id')->nullable()->unique();
            $t->uuid('attempt_key')->nullable();
            $t->string('plan_code')->nullable();
            $t->json('plan_snapshot')->nullable();
            $t->string('price_id')->nullable();
            $t->string('checkout_session_id')->nullable()->unique();
            $t->text('checkout_url')->nullable();
            $t->dateTime('checkout_expires_at')->nullable();
            $t->string('status')->default('none');
            $t->dateTime('paid_until')->nullable();
            $t->boolean('cancel_at_period_end')->default(false);
            $t->dateTime('last_synced_at')->nullable();
            $t->timestamps();
        });
        Schema::create('company_billing_events', function (Blueprint $t): void {
            $t->id();
            $t->string('stripe_event_id')->unique();
            $t->string('type');
            $t->string('payload_sha256', 64);
            $t->dateTime('created_at');
        });
        Schema::create('company_billing_receipts', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('company_billing_account_id')->constrained('company_billing_accounts')->restrictOnDelete();
            $t->string('stripe_invoice_id')->unique();
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
        Schema::dropIfExists('company_billing_receipts');
        Schema::dropIfExists('company_billing_events');
        Schema::dropIfExists('company_billing_accounts');
    }
};
