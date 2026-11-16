<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freight_rate_booking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freight_rate_booking_id')
                ->constrained('freight_rate_bookings')
                ->restrictOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_key', 60);
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32)->nullable();
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['freight_rate_booking_id', 'created_at'], 'freight_booking_events_timeline_idx');
            $table->index(['event_key', 'created_at'], 'freight_booking_events_type_idx');
        });

        // Preserve a visible timeline for requests that existed before this audit table.
        DB::table('freight_rate_bookings')
            ->orderBy('id')
            ->chunkById(200, function ($bookings): void {
                foreach ($bookings as $booking) {
                    DB::table('freight_rate_booking_events')->insert([
                        'freight_rate_booking_id' => $booking->id,
                        'actor_id' => null,
                        'event_key' => 'request_submitted',
                        'from_status' => null,
                        'to_status' => $booking->status,
                        'note' => 'Timeline entry created during audit-history rollout.',
                        'metadata' => json_encode(['backfilled' => true]),
                        'created_at' => $booking->submitted_at ?? $booking->created_at,
                        'updated_at' => $booking->submitted_at ?? $booking->created_at,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('freight_rate_booking_events');
    }
};
