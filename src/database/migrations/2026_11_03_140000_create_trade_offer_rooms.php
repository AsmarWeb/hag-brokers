<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_offer_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('offer_id')->constrained('company_trade_offers')->restrictOnDelete();
            $t->foreignId('workspace_id')->constrained('company_workspaces')->restrictOnDelete();
            $t->unsignedBigInteger('author_id');
            $t->string('author_name');
            $t->uuid('request_key');
            $t->char('request_hash', 64);
            $t->text('body');
            $t->timestamp('created_at');
            $t->unique(['offer_id', 'workspace_id', 'request_key'], 'trade_msg_request_unique');
            $t->index(['offer_id', 'id'], 'trade_msg_room_index');
        });
        Schema::create('trade_offer_reads', function (Blueprint $t) {
            $t->id();
            $t->foreignId('offer_id')->constrained('company_trade_offers')->restrictOnDelete();
            $t->foreignId('workspace_id')->constrained('company_workspaces')->restrictOnDelete();
            $t->unsignedBigInteger('last_message_id');
            $t->unsignedBigInteger('read_by');
            $t->timestamp('read_at');
            $t->unique(['offer_id', 'workspace_id'], 'trade_room_read_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_offer_reads');
        Schema::dropIfExists('trade_offer_messages');
    }
};
