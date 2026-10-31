<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_deliveries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quotation_id')
                ->constrained('quotations')
                ->cascadeOnDelete();

            $table->foreignId('document_id')
                ->unique()
                ->constrained('documents')
                ->restrictOnDelete();

            $table->foreignId('document_version_id')
                ->constrained('document_versions')
                ->restrictOnDelete();

            $table->string('file_sha256', 64);
            $table->string('recipient_email');
            $table->string('status', 20)->default('queued')->index();

            $table->foreignId('requested_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('last_error')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_deliveries');
    }
};
