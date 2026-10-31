<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('deal_agreements', function(Blueprint $t) {
            $t->id();
            $t->foreignId('deal_id')->constrained('deals')->restrictOnDelete();
            $t->unsignedInteger('version');
            $t->string('status', 24)->default('draft');
            $t->string('fee_kind', 24); // percent | fixed | spread
            $t->unsignedInteger('commission_bps')->nullable(); // 100 bps = 1%
            $t->decimal('fixed_fee', 18, 2)->nullable();
            $t->text('terms')->nullable();
            $t->string('content_sha256',64)->nullable();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->timestamps();
            $t->unique(['deal_id','version']);
        });
        Schema::create('deal_commissions', function(Blueprint $t) {
            $t->id();
            $t->foreignId('deal_id')->constrained('deals')->restrictOnDelete();
            $t->foreignId('deal_agreement_id')->unique()->constrained('deal_agreements')->restrictOnDelete();
            $t->char('currency', 3);
            $t->decimal('basis_amount',18,2);
            $t->unsignedInteger('rate_bps')->nullable();
            $t->decimal('amount',18,2);
            $t->string('status',24)->default('pending'); // pending | earned | reversed
            $t->timestamp('earned_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('deal_commissions');
        Schema::dropIfExists('deal_agreements');
    }
};
