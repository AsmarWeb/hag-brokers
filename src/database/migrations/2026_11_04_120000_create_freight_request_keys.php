<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freight_request_keys', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $t->uuid('request_key');
            $t->char('payload_hash', 64);
            $t->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $t->timestamp('created_at');
            $t->unique(['customer_id', 'request_key'], 'freight_customer_key_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freight_request_keys');
    }
};
