<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', fn(Blueprint $t) => [
            $t->id(),
            $t->string('company_name'),
            $t->string('contact_person')->nullable(),
            $t->string('email')->nullable(),
            $t->string('phone')->nullable(),
            $t->timestamps(),
        ]);

        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('customer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $t->string('role')->default('customer');
            $t->string('phone')->nullable();
            $t->boolean('is_active')->default(true);
        });

        Schema::create('ports', fn(Blueprint $t) => [
            $t->id(),
            $t->string('name'),
            $t->string('country')->nullable(),

            $t->string('unlocode', 10)->unique(),
            $t->string('country_code', 3)->nullable(),
            $t->string('port_code', 20)->nullable(),
            $t->string('timezone')->nullable(),

            $t->decimal('latitude', 10, 7)->nullable(),
            $t->decimal('longitude', 10, 7)->nullable(),

            $t->boolean('is_gulf')->default(false),
            $t->boolean('is_uae')->default(false),
            $t->boolean('is_active')->default(true),

            $t->timestamps(),
        ]);

        Schema::create('vessels', fn(Blueprint $t) => [
            $t->id(),
            $t->string('name'),

            $t->string('imo')->nullable()->unique(),
            $t->string('mmsi')->nullable()->unique(),

            $t->string('flag')->nullable(),
            $t->string('call_sign')->nullable(),
            $t->string('vessel_type')->nullable(),

            $t->unsignedSmallInteger('built_year')->nullable(),

            $t->decimal('latitude', 10, 7)->nullable(),
            $t->decimal('longitude', 10, 7)->nullable(),

            $t->timestamp('last_position_at')->nullable(),

            $t->string('status')->default('active'),

            $t->timestamps(),
        ]);

        Schema::create('shipments', function (Blueprint $t) {
            $t->id();

            $t->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $t->string('shipment_no')->unique();
            $t->string('booking_no')->nullable()->unique();
            $t->string('bl_no')->nullable();

            $t->foreignId('origin_port_id')
                ->constrained('ports');

            $t->foreignId('destination_port_id')
                ->constrained('ports');

            $t->foreignId('vessel_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $t->string('cargo_type')->nullable();
            $t->string('container_type')->nullable();

            $t->unsignedInteger('quantity')->default(1);

            $t->decimal('weight', 12, 2)->nullable();
            $t->string('weight_unit')->default('KG');

            $t->string('status')->default('pending');

            $t->date('preferred_date')->nullable();
            $t->date('etd')->nullable();
            $t->date('eta')->nullable();

            $t->timestamp('actual_departure')->nullable();
            $t->timestamp('actual_arrival')->nullable();

            $t->text('notes')->nullable();

            $t->timestamps();

            $t->index(['customer_id', 'status']);
        });

        Schema::create('containers', function (Blueprint $t) {
            $t->id();

            $t->foreignId('shipment_id')
                ->constrained()
                ->cascadeOnDelete();

            $t->string('container_no')->unique();

            $t->string('type')->nullable();
            $t->string('size')->nullable();
            $t->string('iso_code')->nullable();

            $t->string('seal_no')->nullable();

            $t->decimal('weight', 12, 2)->nullable();

            $t->string('status')->default('pending');

            $t->timestamps();
        });

        Schema::create('shipment_events', function (Blueprint $t) {
            $t->id();

            $t->foreignId('shipment_id')
                ->constrained()
                ->cascadeOnDelete();

            $t->foreignId('port_id')
                ->nullable()
                ->constrained('ports')
                ->nullOnDelete();

            $t->string('event_type');
            $t->string('location')->nullable();
            $t->text('description')->nullable();

            $t->timestamp('event_date');

            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();

            $t->string('source')->nullable();
            $t->json('metadata')->nullable();

            $t->timestamps();

            $t->index(['shipment_id', 'event_date']);
        });

        Schema::create('documents', function (Blueprint $t) {
            $t->id();

            $t->foreignId('shipment_id')
                ->constrained()
                ->cascadeOnDelete();

            $t->string('type');
            $t->string('name');
            $t->string('file_path');
            $t->string('original_name')->nullable();

            $t->string('mime_type')->nullable();
            $t->unsignedInteger('version')->default(1);
            $t->string('status')->default('active');

            $t->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $t->text('notes')->nullable();

            $t->timestamps();
        });

        Schema::create('quotations', function (Blueprint $t) {
            $t->id();

            $t->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $t->foreignId('origin_port_id')
                ->constrained('ports');

            $t->foreignId('destination_port_id')
                ->constrained('ports');

            $t->string('cargo_type');
            $t->string('container_type');

            $t->unsignedInteger('quantity')->default(1);

            $t->decimal('weight', 12, 2)->nullable();

            $t->date('preferred_date')->nullable();

            $t->decimal('total_charge', 14, 2)->nullable();
            $t->string('currency', 3)->default('USD');

            $t->string('status')->default('pending');

            $t->timestamp('valid_until')->nullable();

            $t->text('notes')->nullable();

            $t->string('reference_no')->unique();

            $t->timestamps();
        });

        Schema::create('notifications', fn(Blueprint $t) => [
            $t->id(),
            $t->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete(),

            $t->string('type'),
            $t->string('title'),
            $t->text('message'),

            $t->boolean('is_read')->default(false),

            $t->json('data')->nullable(),

            $t->timestamps(),
        ]);
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach (
            [
                'notifications',
                'quotations',
                'documents',
                'shipment_events',
                'containers',
                'shipments',
                'vessels',
                'ports',
                'customers',
            ] as $table
        ) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }
};
