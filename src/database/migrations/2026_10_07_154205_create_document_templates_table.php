<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            /*
             * shipment
             * quotation
             * customer
             * booking
             * invoice
             * generic
             */
            $table->string('entity_type')->default('generic');

            $table->string('document_type')->nullable();

            /*
             * Blade/HTML template content
             */
            $table->longText('content');

            /*
             * Optional CSS used by PDF renderer
             */
            $table->longText('styles')->nullable();

            $table->string('header')->nullable();
            $table->string('footer')->nullable();

            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('version')->default(1);

            $table->json('metadata')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('entity_type');
            $table->index('document_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
