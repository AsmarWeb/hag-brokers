<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();

            $table->string('company_name')->nullable();
            $table->string('company_name_ar')->nullable();

            $table->string('logo_path')->nullable();
            $table->string('stamp_path')->nullable();
            $table->string('signature_path')->nullable();

            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();

            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('po_box')->nullable();

            $table->string('website')->nullable();

            $table->string('tax_number')->nullable();
            $table->string('license_number')->nullable();

            $table->string('header_color')->nullable();
            $table->string('footer_text')->nullable();

            $table->json('social_links')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
