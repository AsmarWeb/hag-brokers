<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_public_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->unique('company_profile_workspace_unique')->constrained('company_workspaces')->restrictOnDelete();
            $table->string('slug', 80)->unique('company_profile_slug_unique');
            $table->string('name_ar', 160)->nullable();
            $table->string('name_en', 160)->nullable();
            $table->text('about_ar')->nullable();
            $table->text('about_en')->nullable();
            $table->string('sector', 60)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->text('specialties')->nullable();
            $table->string('markets', 300)->nullable();
            $table->string('website', 500)->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_public_profiles');
    }
};
