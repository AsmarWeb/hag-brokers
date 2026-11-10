<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carrier_invitations', function (Blueprint $table) {
            $table->dateTime('revoked_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('carrier_invitations', function (Blueprint $table) {
            $table->dropColumn('revoked_at');
        });
    }
};
