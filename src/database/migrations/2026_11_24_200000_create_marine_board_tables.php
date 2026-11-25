<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marine_cargo_listings', function (Blueprint $table) {
            $table->id();
            $table->string('public_reference', 24)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Contact/company fields are private and must never be selected for public cards.
            $table->string('company_name', 200);
            $table->string('contact_name', 150);
            $table->string('email', 255);
            $table->string('phone', 50);
            $table->string('cargo_type', 80);
            $table->text('description');
            $table->text('public_description')->nullable();
            $table->decimal('quantity', 15, 3);
            $table->foreignId('loading_port_id')->constrained('ports')->restrictOnDelete();
            $table->foreignId('discharging_port_id')->constrained('ports')->restrictOnDelete();
            $table->date('laycan_from');
            $table->date('laycan_to');
            $table->date('readiness_date')->nullable();
            $table->string('status', 24)->default('pending_review')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('terms_accepted_at');
            $table->timestamps();
            $table->index(['status', 'laycan_from', 'laycan_to'], 'marine_cargo_public_board_idx');
            $table->index(['loading_port_id', 'discharging_port_id'], 'marine_cargo_route_idx');
        });

        Schema::create('marine_vessel_listings', function (Blueprint $table) {
            $table->id();
            $table->string('public_reference', 24)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Owner/operator contact details remain private to HAG reviewers.
            $table->string('company_name', 200);
            $table->string('contact_name', 150);
            $table->string('email', 255);
            $table->string('phone', 50);
            $table->string('vessel_type', 80);
            $table->decimal('dwt_capacity', 15, 2);
            $table->foreignId('current_port_id')->nullable()->constrained('ports')->nullOnDelete();
            $table->string('current_position', 180)->nullable();
            $table->date('open_date');
            $table->string('preferred_trading_area', 255);
            $table->string('status', 24)->default('pending_review')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('terms_accepted_at');
            $table->timestamps();
            $table->index(['status', 'open_date'], 'marine_vessel_public_board_idx');
            $table->index(['current_port_id', 'open_date'], 'marine_vessel_position_idx');
        });

        Schema::create('marine_board_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('public_reference', 24)->unique();
            $table->foreignId('cargo_listing_id')->nullable()->constrained('marine_cargo_listings')->restrictOnDelete();
            $table->foreignId('vessel_listing_id')->nullable()->constrained('marine_vessel_listings')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->string('company_name', 200);
            $table->string('email', 255);
            $table->string('phone', 50);
            $table->text('message');
            $table->string('status', 24)->default('new')->index();
            $table->timestamp('terms_accepted_at');
            $table->timestamps();
            $table->index(['status', 'created_at'], 'marine_board_inquiry_queue_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marine_board_inquiries');
        Schema::dropIfExists('marine_vessel_listings');
        Schema::dropIfExists('marine_cargo_listings');
    }
};
