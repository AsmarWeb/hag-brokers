<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_plans', function (Blueprint $t): void {
            $t->id();
            $t->string('code')->unique();
            $t->string('name_ar');
            $t->string('name_en');
            $t->unsignedInteger('monthly_amount_minor');
            $t->string('currency', 3)->default('AED');
            $t->unsignedInteger('seats');
            $t->unsignedInteger('active_opportunities');
            $t->unsignedInteger('monthly_applications');
            $t->boolean('is_available')->default(true);
            $t->timestamps();
        });
        Schema::create('company_workspaces', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('customer_id')->unique()->constrained('customers')->restrictOnDelete();
            $t->string('status')->default('active');
            $t->timestamps();
        });
        Schema::create('company_members', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('company_workspace_id')->constrained('company_workspaces')->restrictOnDelete();
            $t->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $t->string('role');
            $t->timestamps();
        });
        Schema::create('company_access_grants', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('company_workspace_id')->constrained('company_workspaces')->restrictOnDelete();
            $t->string('source');
            $t->json('plan_snapshot');
            $t->dateTime('expires_at')->nullable();
            $t->text('reason');
            $t->unsignedBigInteger('created_by');
            $t->dateTime('created_at');
        });
        Schema::create('company_platform_audits', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('company_workspace_id')->constrained('company_workspaces')->restrictOnDelete();
            $t->string('action');
            $t->unsignedBigInteger('actor_id');
            $t->json('details');
            $t->dateTime('created_at');
        });
    }

    public function down(): void
    {
        foreach (['company_platform_audits', 'company_access_grants', 'company_members', 'company_workspaces', 'platform_plans'] as $name) {
            Schema::dropIfExists($name);
        }
    }
};
