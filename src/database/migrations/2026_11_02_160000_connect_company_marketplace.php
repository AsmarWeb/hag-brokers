<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $t): void {
            $t->foreignId('company_workspace_id')->nullable()->constrained('company_workspaces')->restrictOnDelete();
            $t->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('marketplace_state', 20)->nullable()->index();
        });
        Schema::table('opportunity_interests', function (Blueprint $t): void {
            $t->foreignId('company_workspace_id')->nullable()->constrained('company_workspaces')->restrictOnDelete();
            $t->unique(['opportunity_id', 'company_workspace_id'], 'opportunity_company_interest_unique');
        });
        Schema::create('company_marketplace_requests', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('company_workspace_id')->constrained('company_workspaces')->restrictOnDelete();
            $t->uuid('request_key');
            $t->string('action', 20);
            $t->string('request_hash', 64);
            $t->unsignedBigInteger('resource_id');
            $t->string('usage_month', 7);
            $t->dateTime('created_at');
            $t->unique(['company_workspace_id', 'request_key']);
            $t->index(['company_workspace_id', 'action', 'usage_month'], 'company_marketplace_usage');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_marketplace_requests');
        Schema::table('opportunity_interests', function (Blueprint $t): void {
            $t->dropUnique('opportunity_company_interest_unique');
            $t->dropConstrainedForeignId('company_workspace_id');
        });
        Schema::table('opportunities', function (Blueprint $t): void {
            $t->dropConstrainedForeignId('company_workspace_id');
            $t->dropConstrainedForeignId('submitted_by');
            $t->dropColumn('marketplace_state');
        });
    }
};
