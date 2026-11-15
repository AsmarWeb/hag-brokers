<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_workspace_closure_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_workspace_id')->constrained('company_workspaces')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            // Deliberately not a foreign key: personal-account deletion must remain possible after closure.
            $table->unsignedBigInteger('owner_id');
            $table->string('email');
            $table->char('token_hash', 64)->nullable()->unique();
            $table->dateTime('token_expires_at')->nullable();
            $table->dateTime('email_sent_at')->nullable();
            $table->dateTime('email_verified_at')->nullable();
            $table->dateTime('owner_confirmed_at')->nullable();
            $table->string('status', 32)->default('email_pending');
            $table->string('previous_workspace_status', 32)->nullable();
            $table->boolean('previous_customer_active')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_reason')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->index(['company_workspace_id', 'status'], 'workspace_closure_status_idx');
            $table->index(['owner_id', 'status'], 'workspace_closure_owner_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_workspace_closure_requests');
    }
};
