<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            // Add description column (migrate from add_char)
            $table->string('description', 255)->nullable()->after('add_char');
        });

        // Migrate add_char to description for non-empty values
        DB::table('publications')
            ->whereNotNull('add_char')
            ->where('add_char', '!=', '')
            ->update([
                'description' => DB::raw('add_char'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
