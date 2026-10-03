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
        Schema::create('opportunity_interests', function (Blueprint $table) {

            $table->id();

            // =====================================================
            // Opportunity
            // =====================================================

            $table->foreignId('opportunity_id')
                ->constrained('opportunities')
                ->cascadeOnDelete();


            // =====================================================
            // User
            // =====================================================

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();


            // =====================================================
            // User Role in the Opportunity
            // =====================================================

            $table->string('role')
                ->nullable()
                ->index();


            // =====================================================
            // Financial Capacity
            // =====================================================

            $table->decimal('available_capital', 15, 2)
                ->nullable();

            $table->string('currency', 3)
                ->default('USD');


            // =====================================================
            // User Message
            // =====================================================

            $table->text('message')
                ->nullable();


            // =====================================================
            // Interest Status
            // =====================================================

            $table->string('status')
                ->default('pending')
                ->index();


            // =====================================================
            // Review Information
            // =====================================================

            $table->timestamp('reviewed_at')
                ->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            // =====================================================
            // Timestamps
            // =====================================================

            $table->timestamps();


            // =====================================================
            // Prevent Duplicate Interest
            // =====================================================

            $table->unique([
                'opportunity_id',
                'user_id',
            ]);


            // =====================================================
            // Performance Index
            // =====================================================

            $table->index([
                'opportunity_id',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunity_interests');
    }
};
