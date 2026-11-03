<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('opportunities', 'slug')) {
            Schema::table('opportunities', fn (Blueprint $t) => $t->string('slug', 190)->nullable());
        }
        if (! Schema::hasIndex('opportunities', 'opportunities_slug_unique')) {
            Schema::table('opportunities', fn (Blueprint $t) => $t->unique('slug'));
        }
        if (! Schema::hasColumn('opportunities', 'seo_updated_at')) {
            Schema::table('opportunities', fn (Blueprint $t) => $t->timestamp('seo_updated_at')->nullable());
        }
        // Query builder avoids observers, notifications and timestamp/financial writes.
        // Keep the generator here immutable for repeatable future installations.
        DB::table('opportunities')->whereNull('slug')->orderBy('id')->chunkById(200, function ($rows): void {
            foreach ($rows as $row) {
                $text = preg_replace('/[\p{M}\x{0640}]+/u', '', mb_strtolower(strip_tags($row->title))) ?? '';
                $base = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text) ?? '';
                $base = trim(mb_substr(trim($base, '-'), 0, 130), '-');
                do {
                    $slug = ($base ?: 'opportunity').'-'.strtolower(Str::random(10));
                } while (DB::table('opportunities')->where('slug', $slug)->exists());
                DB::table('opportunities')->where('id', $row->id)->whereNull('slug')->update(['slug' => $slug]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $t): void {
            $t->dropUnique('opportunities_slug_unique');
            $t->dropColumn(['slug', 'seo_updated_at']);
        });
    }
};
