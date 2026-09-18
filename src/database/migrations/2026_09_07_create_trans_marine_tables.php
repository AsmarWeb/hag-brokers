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
        | Customers
        |--------------------------------------------------------------------------
        */
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('company_name');

            $table->string('contact_person')->nullable();

            $table->string('email')->nullable()->index();

            $table->string('phone')->nullable();

            $table->string('country_code', 2)
                ->default('AE')
                ->index();

            $table->string('city')->nullable();

            $table->boolean('is_active')
                ->default(true)
                ->index();

            $table->timestamps();
        });


        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */
        Schema::table('users', function (Blueprint $table) {

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table->string('role')
                ->default('customer')
                ->index();

            $table->string('phone')->nullable();
        });


        /*
        |--------------------------------------------------------------------------
        | Gulf Ports
        |--------------------------------------------------------------------------
        */
        Schema::create('ports', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('country')->nullable();

            $table->string('country_code', 2)
                ->nullable()
                ->index();

            $table->string('port_code')
                ->nullable()
                ->index();

            $table->string('unlocode')
                ->nullable()
                ->unique();

            $table->string('timezone')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();

            $table->decimal('longitude', 10, 7)->nullable();

            $table->boolean('is_gulf')
                ->default(false)
                ->index();

            $table->boolean('is_uae')
                ->default(false)
                ->index();

            $table->boolean('is_active')
                ->default(true)
                ->index();

            $table->timestamps();
        });


        /*
        |--------------------------------------------------------------------------
        | Carriers / Shipping Lines
        |--------------------------------------------------------------------------
        */
        Schema::create('carriers', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('code')
                ->nullable()
                ->unique();

            $table->string('country')->nullable();

            $table->string('country_code', 2)->nullable();

            $table->string('website')->nullable();

            $table->string('api_provider')->nullable();

            $table->boolean('is_active')
                ->default(true)
                ->index();

            $table->timestamps();
        });


        /*
        |--------------------------------------------------------------------------
        | Vessels
        |--------------------------------------------------------------------------
        */
        Schema::create('vessels', function (Blueprint $table) {

            $table->id();

            $table->foreignId('carrier_id')
                ->nullable()
                ->constrained('carriers')
                ->nullOnDelete();

            $table->string('name');

            $table->string('imo')
                ->nullable()
                ->unique();

            $table->string('mmsi')
                ->nullable()
                ->unique();

            $table->string('call_sign')->nullable();

            $table->string('flag')->nullable();

            $table->string('vessel_type')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();

            $table->decimal('longitude', 10, 7)->nullable();

            $table->decimal('speed', 8, 2)->nullable();

            $table->decimal('course', 8, 2)->nullable();

            $table->string('destination')->nullable();

            $table->timestamp('eta')->nullable();

            $table->timestamp('last_position_at')->nullable();

            $table->boolean('is_in_gulf')
                ->default(false)
                ->index();

            $table->string('status')
                ->default('active')
                ->index();

            $table->timestamps();

            $table->index([
                'is_in_gulf',
                'status'
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Vessel Positions
        |--------------------------------------------------------------------------
        */
        Schema::create('vessel_positions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('vessel_id')
                ->constrained('vessels')
                ->cascadeOnDelete();

            $table->decimal('latitude', 10, 7);

            $table->decimal('longitude', 10, 7);

            $table->decimal('speed', 8, 2)->nullable();

            $table->decimal('course', 8, 2)->nullable();

            $table->timestamp('recorded_at');

            $table->timestamps();

            $table->index([
                'vessel_id',
                'recorded_at'
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Port Calls
        |--------------------------------------------------------------------------
        */
        Schema::create('port_calls', function (Blueprint $table) {

            $table->id();

            $table->foreignId('vessel_id')
                ->constrained('vessels')
                ->cascadeOnDelete();

            $table->foreignId('port_id')
                ->constrained('ports')
                ->cascadeOnDelete();

            $table->timestamp('arrival_at')->nullable();

            $table->timestamp('departure_at')->nullable();

            $table->string('status')
                ->default('scheduled')
                ->index();

            $table->timestamps();

            $table->index([
                'vessel_id',
                'port_id'
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Quotations
        |--------------------------------------------------------------------------
        */
        Schema::create('quotations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->foreignId('origin_port_id')
                ->constrained('ports')
                ->restrictOnDelete();

            $table->foreignId('destination_port_id')
                ->constrained('ports')
                ->restrictOnDelete();

            $table->string('cargo_type');

            $table->string('container_type');

            $table->unsignedInteger('quantity')
                ->default(1);

            $table->decimal('weight', 12, 2)
                ->nullable();

            $table->date('preferred_date')
                ->nullable();

            $table->string('currency', 3)
                ->default('USD');

            $table->decimal('base_freight', 14, 2)
                ->nullable();

            $table->decimal('origin_charges', 14, 2)
                ->nullable();

            $table->decimal('destination_charges', 14, 2)
                ->nullable();

            $table->decimal('documentation_fee', 14, 2)
                ->nullable();

            $table->decimal('other_charges', 14, 2)
                ->nullable();

            $table->decimal('broker_margin', 14, 2)
                ->nullable();

            $table->decimal('customer_total', 14, 2)
                ->nullable();

            $table->decimal('total_charge', 14, 2)
                ->nullable();

            $table->string('status')
                ->default('pending')
                ->index();

            $table->string('reference_no')
                ->unique();

            $table->timestamps();

            $table->index([
                'customer_id',
                'status'
            ]);

            $table->index([
                'origin_port_id',
                'destination_port_id'
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Quotation Offers
        |--------------------------------------------------------------------------
        */
        Schema::create('quotation_offers', function (Blueprint $table) {

            $table->id();

            $table->foreignId('quotation_id')
                ->constrained('quotations')
                ->cascadeOnDelete();

            $table->foreignId('carrier_id')
                ->constrained('carriers')
                ->cascadeOnDelete();

            $table->foreignId('vessel_id')
                ->nullable()
                ->constrained('vessels')
                ->nullOnDelete();

            $table->decimal('carrier_amount', 14, 2);

            $table->decimal('customer_amount', 14, 2);

            $table->string('currency', 3)
                ->default('USD');

            $table->date('departure_date')
                ->nullable();

            $table->date('eta')
                ->nullable();

            $table->unsignedInteger('transit_days')
                ->nullable();

            $table->string('status')
                ->default('available')
                ->index();

            $table->timestamp('expires_at')
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $table->index([
                'quotation_id',
                'status'
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Shipments
        |--------------------------------------------------------------------------
        */
        Schema::create('shipments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->foreignId('carrier_id')
                ->nullable()
                ->constrained('carriers')
                ->nullOnDelete();

            $table->foreignId('quotation_id')
                ->nullable()
                ->constrained('quotations')
                ->nullOnDelete();

            $table->foreignId('vessel_id')
                ->nullable()
                ->constrained('vessels')
                ->nullOnDelete();

            $table->string('shipment_no')
                ->unique();

            $table->string('booking_no')
                ->nullable()
                ->unique();

            $table->foreignId('origin_port_id')
                ->constrained('ports')
                ->restrictOnDelete();

            $table->foreignId('destination_port_id')
                ->constrained('ports')
                ->restrictOnDelete();

            $table->string('cargo_type')
                ->nullable();

            $table->string('container_type')
                ->nullable();

            $table->unsignedInteger('quantity')
                ->default(1);

            $table->decimal('weight', 12, 2)
                ->nullable();

            $table->date('preferred_date')
                ->nullable();

            $table->date('etd')
                ->nullable();

            $table->date('eta')
                ->nullable();

            $table->timestamp('actual_departure')
                ->nullable();

            $table->timestamp('actual_arrival')
                ->nullable();

            $table->string('status')
                ->default('pending')
                ->index();

            $table->timestamps();

            $table->index([
                'customer_id',
                'status'
            ]);

            $table->index([
                'origin_port_id',
                'destination_port_id'
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Containers
        |--------------------------------------------------------------------------
        */
        Schema::create('containers', function (Blueprint $table) {

            $table->id();

            $table->foreignId('shipment_id')
                ->constrained('shipments')
                ->cascadeOnDelete();

            $table->string('container_no')
                ->unique();

            $table->string('type')
                ->nullable();

            $table->string('size')
                ->nullable();

            $table->string('seal_no')
                ->nullable();

            $table->string('status')
                ->default('pending')
                ->index();

            $table->string('tracking_provider')
                ->nullable();

            $table->timestamp('last_tracked_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'shipment_id',
                'status'
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Shipment Events
        |--------------------------------------------------------------------------
        */
        Schema::create('shipment_events', function (Blueprint $table) {

            $table->id();

            $table->foreignId('shipment_id')
                ->constrained('shipments')
                ->cascadeOnDelete();

            $table->string('event_type')
                ->index();

            $table->string('location')
                ->nullable();

            $table->foreignId('port_id')
                ->nullable()
                ->constrained('ports')
                ->nullOnDelete();

            $table->text('description')
                ->nullable();

            $table->timestamp('event_date');

            $table->decimal('latitude', 10, 7)
                ->nullable();

            $table->decimal('longitude', 10, 7)
                ->nullable();

            $table->string('source')
                ->nullable();

            $table->timestamps();

            $table->index([
                'shipment_id',
                'event_date'
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Documents
        |--------------------------------------------------------------------------
        */
        Schema::create('documents', function (Blueprint $table) {

            $table->id();

            $table->foreignId('shipment_id')
                ->constrained('shipments')
                ->cascadeOnDelete();

            $table->string('type');

            $table->string('name');

            $table->string('file_path');

            $table->string('original_name')
                ->nullable();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'shipment_id',
                'type'
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */
        Schema::create('notifications', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('type');

            $table->string('title');

            $table->text('message');

            $table->boolean('is_read')
                ->default(false)
                ->index();

            $table->json('data')
                ->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'is_read'
            ]);
        });
    }


    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Drop tables in reverse dependency order
        |--------------------------------------------------------------------------
        */

        Schema::dropIfExists('notifications');

        Schema::dropIfExists('documents');

        Schema::dropIfExists('shipment_events');

        Schema::dropIfExists('containers');

        Schema::dropIfExists('shipments');

        Schema::dropIfExists('quotation_offers');

        Schema::dropIfExists('quotations');

        Schema::dropIfExists('port_calls');

        Schema::dropIfExists('vessel_positions');

        Schema::dropIfExists('vessels');

        Schema::dropIfExists('carriers');

        Schema::dropIfExists('ports');


        /*
        |--------------------------------------------------------------------------
        | Remove fields from users
        |--------------------------------------------------------------------------
        */

        Schema::table('users', function (Blueprint $table) {

            $table->dropForeign([
                'customer_id'
            ]);

            $table->dropIndex([
                'role'
            ]);

            $table->dropColumn([
                'customer_id',
                'role',
                'phone'
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        Schema::dropIfExists('customers');
    }
};
