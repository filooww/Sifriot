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
            $table->enum('status', ['published', 'pending', 'hidden'])
                  ->default('published')
                  ->after('upload_date')
                  ->comment('Publication status: published (visible to all), pending (awaiting review), hidden (not visible)');

            $table->index('status', 'idx_publications_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropIndex('idx_publications_status');
            $table->dropColumn('status');
        });
    }
};
