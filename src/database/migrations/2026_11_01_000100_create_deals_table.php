<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('deals', function(Blueprint $t) {
            $t->id();
            $t->string('reference')->unique();
            $t->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $t->foreignId('quotation_id')->nullable()->unique()->constrained('quotations')->restrictOnDelete();
            $t->foreignId('opportunity_id')->nullable()->unique()->constrained('opportunities')->restrictOnDelete();
            $t->string('collection_model', 32); // commission_only | full_service
            $t->string('status', 32)->default('draft')->index();
            $t->char('currency', 3);
            $t->decimal('deal_amount', 18, 2); // face value, not necessarily revenue
            $t->decimal('supplier_cost', 18, 2)->nullable(); // internal, never customer-visible
            $t->decimal('platform_revenue', 18, 2)->default(0); // expected, not recognized revenue
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->timestamps();
            $t->index(['customer_id','status']);
        });
    }
    public function down(): void { Schema::dropIfExists('deals'); }
};
