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

            // =====================================================
            // CRM Relationships
            // =====================================================

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


            // =====================================================
            // Basic Opportunity Information
            // =====================================================

            $table->string('title');

            $table->string('opportunity_type')
                ->default('business')
                ->index();

            $table->string('category')
                ->nullable()
                ->index();

            $table->text('description')
                ->nullable();


            // =====================================================
            // Freight / Cargo Information
            // =====================================================

            $table->string('cargo_type')
                ->nullable();

            $table->string('container_type')
                ->nullable();

            $table->foreignId('origin_port_id')
                ->nullable()
                ->constrained('ports')
                ->nullOnDelete();

            $table->foreignId('destination_port_id')
                ->nullable()
                ->constrained('ports')
                ->nullOnDelete();


            // =====================================================
            // Location
            // =====================================================

            $table->string('country')
                ->nullable()
                ->index();

            $table->string('city')
                ->nullable();


            // =====================================================
            // Financial Information
            // =====================================================

            $table->decimal('estimated_value', 15, 2)
                ->nullable();

            $table->decimal('required_capital', 15, 2)
                ->nullable();

            $table->decimal('min_investment', 15, 2)
                ->nullable();

            $table->string('currency', 3)
                ->default('USD');


            // =====================================================
            // Commission
            // =====================================================

            $table->string('commission_type')
                ->default('percentage');

            $table->decimal('commission_rate', 8, 2)
                ->nullable();


            // =====================================================
            // CRM Pipeline
            // =====================================================

            $table->string('stage')
                ->default('new')
                ->index();

            $table->unsignedTinyInteger('probability')
                ->default(0);


            // =====================================================
            // Marketplace Visibility & Verification
            // =====================================================

            $table->string('visibility')
                ->default('private')
                ->index();

            $table->string('verification_status')
                ->default('pending')
                ->index();


            // =====================================================
            // Dates
            // =====================================================

            $table->date('expected_close_date')
                ->nullable()
                ->index();

            $table->timestamp('published_at')
                ->nullable();

            $table->timestamp('expires_at')
                ->nullable()
                ->index();


            // =====================================================
            // Notes
            // =====================================================

            $table->text('notes')
                ->nullable();


            // =====================================================
            // Timestamps
            // =====================================================

            $table->timestamps();


            // =====================================================
            // Additional Indexes
            // =====================================================

            $table->index([
                'customer_id',
                'stage'
            ]);

            $table->index([
                'assigned_to',
                'stage'
            ]);
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
