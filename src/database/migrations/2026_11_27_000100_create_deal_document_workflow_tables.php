<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('deal_id')->constrained('deals')->restrictOnDelete();
            $table->string('reference', 48)->unique();
            $table->string('type', 40);
            $table->string('title', 180);
            $table->string('language', 5)->default('en');
            $table->string('status', 32)->default('draft');
            $table->json('content');
            $table->json('financial_snapshot')->nullable();
            $table->char('financial_snapshot_sha256', 64)->nullable();
            $table->char('document_sha256', 64)->nullable();
            $table->string('template_version', 20)->default('1.0');
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['deal_id', 'status']);
            $table->index(['type', 'status']);
        });

        Schema::create('deal_document_parties', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('deal_document_id')->constrained('deal_documents')->restrictOnDelete();
            $table->string('role_key', 40);
            $table->string('company_name', 255);
            $table->string('contact_name', 255);
            $table->string('email', 255);
            $table->unsignedSmallInteger('signing_order')->default(1);
            $table->string('status', 20)->default('pending');
            $table->string('signed_by_name', 255)->nullable();
            $table->string('signed_by_title', 160)->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('signature_method', 40)->nullable();
            $table->string('signature_evidence_path', 500)->nullable();
            $table->char('signature_evidence_sha256', 64)->nullable();
            $table->foreignId('signature_received_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['deal_document_id', 'role_key']);
            $table->index(['deal_document_id', 'status']);
        });

        Schema::create('deal_document_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('deal_document_id')->constrained('deal_documents')->restrictOnDelete();
            $table->unsignedInteger('version');
            $table->json('snapshot');
            $table->char('snapshot_sha256', 64);
            $table->foreignId('actor_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['deal_document_id', 'version']);
            $table->index(['deal_document_id', 'created_at']);
        });

        Schema::create('deal_document_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('deal_document_id')->constrained('deal_documents')->restrictOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('event', 64);
            $table->json('payload');
            $table->char('payload_sha256', 64);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['deal_document_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_document_events');
        Schema::dropIfExists('deal_document_versions');
        Schema::dropIfExists('deal_document_parties');
        Schema::dropIfExists('deal_documents');
    }
};
