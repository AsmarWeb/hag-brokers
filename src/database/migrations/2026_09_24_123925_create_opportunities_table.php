<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('contacts')
                ->nullOnDelete();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title');

            $table->string('cargo_type')->nullable();
            $table->string('container_type')->nullable();

            $table->foreignId('origin_port_id')
                ->nullable()
                ->constrained('ports')
                ->nullOnDelete();

            $table->foreignId('destination_port_id')
                ->nullable()
                ->constrained('ports')
                ->nullOnDelete();

            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->string('currency', 3)->default('USD');

            $table->string('stage')->default('new')->index();

            $table->unsignedTinyInteger('probability')
                ->default(0);

            $table->date('expected_close_date')->nullable()->index();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['customer_id', 'stage']);
            $table->index(['assigned_to', 'stage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
