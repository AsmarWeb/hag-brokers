<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deal_agreements', function (Blueprint $table) {
            $table->unsignedTinyInteger('integrity_version')
                ->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('deal_agreements', function (Blueprint $table) {
            $table->dropColumn('integrity_version');
        });
    }
};
