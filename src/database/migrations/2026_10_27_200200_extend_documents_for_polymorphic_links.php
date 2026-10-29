<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('documentable_type')->nullable();
            $table->unsignedBigInteger('documentable_id')->nullable();
            $table->string('status')->default('active');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('shipment_id')
                ->nullable()
                ->change();
        });

        DB::table('documents')
            ->whereNotNull('shipment_id')
            ->whereNull('documentable_type')
            ->update([
                'documentable_type' => 'shipment',
                'documentable_id' => DB::raw('shipment_id'),
            ]);
    }

    public function down(): void
    {
        // The original schema requires every document to have a shipment.
        // Refuse rollback if doing so would leave records without a shipment.
        if (DB::table('documents')->whereNull('shipment_id')->exists()) {
            throw new \RuntimeException(
                'Cannot roll back document relationships: '
                . 'some documents have no shipment_id. '
                . 'Reassign or archive them before rollback.'
            );
        }

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'documentable_type',
                'documentable_id',
                'status',
            ]);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('shipment_id')
                ->nullable(false)
                ->change();
        });
    }
};
