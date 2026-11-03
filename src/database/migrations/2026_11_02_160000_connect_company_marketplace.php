<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL commits DDL before later statements fail. Resume partially applied schemas.
        if (! Schema::hasColumn('opportunities', 'company_workspace_id')) {
            Schema::table('opportunities', fn (Blueprint $t) => $t->foreignId('company_workspace_id')->nullable()->constrained('company_workspaces')->restrictOnDelete());
        }
        if (! Schema::hasColumn('opportunities', 'submitted_by')) {
            Schema::table('opportunities', fn (Blueprint $t) => $t->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete());
        }
        if (! Schema::hasColumn('opportunities', 'marketplace_state')) {
            Schema::table('opportunities', fn (Blueprint $t) => $t->string('marketplace_state', 20)->nullable());
        }
        if (! Schema::hasIndex('opportunities', ['marketplace_state'])) {
            Schema::table('opportunities', fn (Blueprint $t) => $t->index('marketplace_state'));
        }
        if (! Schema::hasColumn('opportunity_interests', 'company_workspace_id')) {
            Schema::table('opportunity_interests', fn (Blueprint $t) => $t->foreignId('company_workspace_id')->nullable()->constrained('company_workspaces')->restrictOnDelete());
        }
        if (! Schema::hasIndex('opportunity_interests', ['opportunity_id', 'company_workspace_id'], 'unique')) {
            Schema::table('opportunity_interests', fn (Blueprint $t) => $t->unique(['opportunity_id', 'company_workspace_id'], 'opportunity_company_interest_unique'));
        }
        if (! Schema::hasTable('company_marketplace_requests')) {
            Schema::create('company_marketplace_requests', function (Blueprint $t): void {
                $t->id();
                $t->foreignId('company_workspace_id')->constrained('company_workspaces')->restrictOnDelete();
                $t->uuid('request_key');
                $t->string('action', 20);
                $t->string('request_hash', 64);
                $t->unsignedBigInteger('resource_id');
                $t->string('usage_month', 7);
                $t->dateTime('created_at');
            });
        }
        if (! Schema::hasIndex('company_marketplace_requests', ['company_workspace_id', 'request_key'], 'unique')) {
            Schema::table('company_marketplace_requests', fn (Blueprint $t) => $t->unique(['company_workspace_id', 'request_key'], 'company_marketplace_request_unique'));
        }
        if (! Schema::hasIndex('company_marketplace_requests', ['company_workspace_id', 'action', 'usage_month'])) {
            Schema::table('company_marketplace_requests', fn (Blueprint $t) => $t->index(['company_workspace_id', 'action', 'usage_month'], 'company_marketplace_usage'));
        }
        // The failed MySQL CREATE can leave its later foreign-key statement unapplied.
        $foreignKeys = Schema::getForeignKeys('company_marketplace_requests');
        if (! collect($foreignKeys)->contains(fn ($key) => $key['columns'] === ['company_workspace_id'])) {
            Schema::table('company_marketplace_requests', fn (Blueprint $t) => $t->foreign('company_workspace_id', 'company_marketplace_workspace_foreign')->references('id')->on('company_workspaces')->restrictOnDelete());
        }
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
