<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_deletion_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('reviewed_at')->nullable();
            $t->string('status', 20)->default('pending');
            $t->unsignedInteger('version')->default(1);
            $t->timestamps();
        });
        Schema::create('platform_legal_documents', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 20)->unique();
            $t->string('title_ar');
            $t->string('title_en');
            $t->text('body_ar');
            $t->text('body_en');
            $t->boolean('published')->default(false);
            $t->unsignedInteger('version')->default(1);
            $t->timestamps();
        });
        Schema::create('platform_legal_audits', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 20);
            $t->unsignedInteger('version');
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->longText('snapshot');
            $t->timestamp('created_at');
            $t->unique(['slug', 'version'], 'legal_slug_version_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_legal_audits');
        Schema::dropIfExists('platform_legal_documents');
        Schema::dropIfExists('account_deletion_requests');
    }
};
