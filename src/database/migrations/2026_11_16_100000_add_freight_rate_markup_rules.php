<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('freight_rate_sources', function (Blueprint $table) {
            $table->decimal('markup_percent', 7, 3)->default(0);
            $table->decimal('markup_fixed', 12, 2)->default(0);
        });
        Schema::table('freight_rates', function (Blueprint $table) {
            $table->decimal('markup_percent', 7, 3)->nullable();
            $table->decimal('markup_fixed', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('freight_rates', fn (Blueprint $table) => $table->dropColumn(['markup_percent', 'markup_fixed']));
        Schema::table('freight_rate_sources', fn (Blueprint $table) => $table->dropColumn(['markup_percent', 'markup_fixed']));
    }
};
