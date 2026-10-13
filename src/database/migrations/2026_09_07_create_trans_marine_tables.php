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

        Schema::create('customers', function (Blueprint $t) {
            $t->id();

            $t->string('company_name');

            $t->string('contact_person')
                ->nullable();

            $t->string('email')
                ->nullable()
                ->index();

            $t->string('phone')
                ->nullable();

            $t->string('country_code', 2)
                ->default('AE')
                ->index();

            $t->string('city')
                ->nullable();

            $t->boolean('is_active')
                ->default(true)
                ->index();

            $t->timestamps();
        });


        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $t->string('role')
                ->default('customer')
                ->index();

            $t->string('phone')
                ->nullable();
        });


        /*
        |--------------------------------------------------------------------------
        | Ports
        |--------------------------------------------------------------------------
        */

        Schema::create('ports', function (Blueprint $t) {
            $t->id();

            $t->string('name');

            $t->string('country')
                ->nullable();

            $t->string('country_code', 2)
                ->nullable()
                ->index();

            $t->string('port_code')
                ->nullable()
                ->index();

            $t->string('unlocode')
                ->nullable()
                ->unique();

            $t->string('timezone')
                ->nullable();

            $t->decimal('latitude', 10, 7)
                ->nullable();

            $t->decimal('longitude', 10, 7)
                ->nullable();

            $t->boolean('is_gulf')
                ->default(false)
                ->index();

            $t->boolean('is_uae')
                ->default(false)
                ->index();

            $t->boolean('is_active')
                ->default(true)
                ->index();

            $t->timestamps();
        });


        /*
        |--------------------------------------------------------------------------
        | Vessels
        |--------------------------------------------------------------------------
        */

        Schema::create('vessels', function (Blueprint $t) {
            $t->id();

            $t->string('name');

            $t->string('imo')
                ->nullable()
                ->unique();

            $t->string('mmsi')
                ->nullable()
                ->unique();

            $t->string('call_sign')
                ->nullable();

            $t->string('flag')
                ->nullable();

            $t->string('vessel_type')
                ->nullable();

            $t->decimal('latitude', 10, 7)
                ->nullable();

            $t->decimal('longitude', 10, 7)
                ->nullable();

            $t->decimal('speed', 8, 2)
                ->nullable();

            $t->decimal('course', 8, 2)
                ->nullable();

            $t->string('destination')
                ->nullable();

            $t->timestamp('eta')
                ->nullable();

            $t->timestamp('last_position_at')
                ->nullable();

            $t->boolean('is_in_gulf')
                ->default(false)
                ->index();

            $t->string('status')
                ->default('active')
                ->index();

            $t->timestamps();
        });


        /*
        |--------------------------------------------------------------------------
        | Shipments
        |--------------------------------------------------------------------------
        */

        Schema::create('shipments', function (Blueprint $t) {
            $t->id();

            $t->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $t->string('shipment_no')
                ->unique();

            $t->string('booking_no')
                ->nullable()
                ->unique();

            $t->foreignId('origin_port_id')
                ->constrained('ports')
                ->restrictOnDelete();

            $t->foreignId('destination_port_id')
                ->constrained('ports')
                ->restrictOnDelete();

            $t->foreignId('vessel_id')
                ->nullable()
                ->constrained('vessels')
                ->nullOnDelete();

            $t->string('cargo_type')
                ->nullable();

            $t->string('container_type')
                ->nullable();

            $t->unsignedInteger('quantity')
                ->default(1);

            $t->decimal('weight', 12, 2)
                ->nullable();

            $t->string('status')
                ->default('pending')
                ->index();

            $t->date('preferred_date')
                ->nullable();

            $t->timestamp('etd')
                ->nullable();

            $t->timestamp('eta')
                ->nullable();

            $t->timestamp('actual_departure')
                ->nullable();

            $t->timestamp('actual_arrival')
                ->nullable();

            $t->timestamps();

            $t->index([
                'customer_id',
                'status',
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Containers
        |--------------------------------------------------------------------------
        */

        Schema::create('containers', function (Blueprint $t) {
            $t->id();

            $t->foreignId('shipment_id')
                ->constrained('shipments')
                ->cascadeOnDelete();

            $t->string('container_no')
                ->unique();

            $t->string('type')
                ->nullable();

            $t->string('size')
                ->nullable();

            $t->string('seal_no')
                ->nullable();

            $t->string('status')
                ->default('pending')
                ->index();

            $t->string('tracking_provider')
                ->nullable();

            $t->timestamp('last_tracked_at')
                ->nullable();

            $t->timestamps();
        });


        /*
        |--------------------------------------------------------------------------
        | Shipment Events
        |--------------------------------------------------------------------------
        */

        Schema::create('shipment_events', function (Blueprint $t) {
            $t->id();

            $t->foreignId('shipment_id')
                ->constrained('shipments')
                ->cascadeOnDelete();

            $t->string('event_type')
                ->index();

            $t->string('location')
                ->nullable();

            $t->text('description')
                ->nullable();

            $t->timestamp('event_date');

            $t->decimal('latitude', 10, 7)
                ->nullable();

            $t->decimal('longitude', 10, 7)
                ->nullable();

            $t->string('source')
                ->nullable();

            $t->timestamps();

            $t->index([
                'shipment_id',
                'event_date',
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Documents
        |--------------------------------------------------------------------------
        */

        Schema::create('documents', function (Blueprint $t) {
            $t->id();

            $t->foreignId('shipment_id')
                ->constrained('shipments')
                ->cascadeOnDelete();

            $t->string('type');

            $t->string('name');

            $t->string('file_path');

            $t->string('original_name')
                ->nullable();

            $t->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $t->timestamps();
        });


        /*
        |--------------------------------------------------------------------------
        | Quotations
        |--------------------------------------------------------------------------
        */

        Schema::create('quotations', function (Blueprint $t) {
            $t->id();

            $t->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $t->foreignId('origin_port_id')
                ->constrained('ports')
                ->restrictOnDelete();

            $t->foreignId('destination_port_id')
                ->constrained('ports')
                ->restrictOnDelete();

            $t->string('cargo_type');

            $t->string('container_type');

            $t->unsignedInteger('quantity')
                ->default(1);

            $t->decimal('weight', 12, 2)
                ->nullable();

            $t->date('preferred_date')
                ->nullable();

            $t->string('currency', 3)
                ->default('USD');

            $t->decimal('base_freight', 14, 2)
                ->nullable();

            $t->decimal('origin_charges', 14, 2)
                ->nullable();

            $t->decimal('destination_charges', 14, 2)
                ->nullable();

            $t->decimal('documentation_fee', 14, 2)
                ->nullable();

            $t->decimal('other_charges', 14, 2)
                ->nullable();

            $t->decimal('broker_margin', 14, 2)
                ->nullable();

            $t->decimal('customer_total', 14, 2)
                ->nullable();

            $t->decimal('total_charge', 14, 2)
                ->nullable();

            $t->string('status')
                ->default('pending')
                ->index();

            $t->string('reference_no')
                ->unique();

            $t->timestamps();

            $t->index([
                'customer_id',
                'status',
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */

        Schema::create('notifications', function (Blueprint $t) {
            $t->id();

            $t->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $t->string('type');

            $t->string('title');

            $t->text('message');

            $t->boolean('is_read')
                ->default(false)
                ->index();

            $t->json('data')
                ->nullable();

            $t->timestamps();
        });
    }


    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Drop application tables
        |--------------------------------------------------------------------------
        */

        Schema::dropIfExists('notifications');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('shipment_events');
        Schema::dropIfExists('containers');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('vessels');
        Schema::dropIfExists('ports');


        /*
        |--------------------------------------------------------------------------
        | Remove customer fields from users
        |--------------------------------------------------------------------------
        */

        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('customer_id');
            $t->dropColumn([
                'role',
                'phone',
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Drop customers
        |--------------------------------------------------------------------------
        */

        Schema::dropIfExists('customers');
    }
};
