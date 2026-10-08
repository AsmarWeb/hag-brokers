<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Carriers
        |--------------------------------------------------------------------------
        */

        Schema::create('carriers', function (Blueprint $t) {
            $t->id();

            $t->string('name');

            $t->string('code')
                ->unique();

            $t->string('country')
                ->nullable();

            $t->string('country_code', 2)
                ->nullable();

            $t->string('website')
                ->nullable();

            $t->string('api_provider')
                ->nullable();

            $t->boolean('is_active')
                ->default(true)
                ->index();

            $t->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Add Carrier to Vessels
        |--------------------------------------------------------------------------
        */

        Schema::table('vessels', function (Blueprint $t) {
            $t->foreignId('carrier_id')
                ->nullable()
                ->after('id')
                ->constrained('carriers')
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | Add Carrier to Shipments
        |--------------------------------------------------------------------------
        */

        Schema::table('shipments', function (Blueprint $t) {
            $t->foreignId('carrier_id')
                ->nullable()
                ->after('vessel_id')
                ->constrained('carriers')
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | Quotation Offers
        |--------------------------------------------------------------------------
        */

        Schema::create('quotation_offers', function (Blueprint $t) {
            $t->id();

            $t->foreignId('quotation_id')
                ->constrained('quotations')
                ->cascadeOnDelete();

            $t->foreignId('carrier_id')
                ->constrained('carriers')
                ->cascadeOnDelete();

            $t->foreignId('vessel_id')
                ->nullable()
                ->constrained('vessels')
                ->nullOnDelete();

            $t->decimal('carrier_amount', 12, 2)
                ->nullable();

            $t->decimal('customer_amount', 12, 2)
                ->nullable();

            $t->string('currency', 3)
                ->default('USD');

            $t->date('departure_date')
                ->nullable();

            $t->date('eta')
                ->nullable();

            $t->unsignedInteger('transit_days')
                ->nullable();

            $t->string('status')
                ->default('pending')
                ->index();

            $t->timestamp('expires_at')
                ->nullable();

            $t->text('notes')
                ->nullable();

            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_offers');

        Schema::table('shipments', function (Blueprint $t) {
            $t->dropForeign(['carrier_id']);
            $t->dropColumn('carrier_id');
        });

        Schema::table('vessels', function (Blueprint $t) {
            $t->dropForeign(['carrier_id']);
            $t->dropColumn('carrier_id');
        });

        Schema::dropIfExists('carriers');
    }
};
