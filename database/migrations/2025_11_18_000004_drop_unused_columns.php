<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            // Drop completely unused columns
            $table->dropColumn([
                'add_int',    // Completely unused in business logic
                'add_char',   // Migrated to 'description' column
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->integer('add_int')->default(0)->after('_del_mark');
            $table->string('add_char')->default('')->after('add_int');
        });
    }
};
