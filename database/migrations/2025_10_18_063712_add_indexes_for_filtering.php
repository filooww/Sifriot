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
            // Add index on upload_date for date range queries (using upload_date instead of publication_date)
            $table->index('upload_date', 'idx_publications_upload_date');

            // Add index on word_count for text size range queries
            $table->index('word_count', 'idx_publications_word_count');

            // Add index on status for publication status filtering (if column exists)
            // Note: This will be added if the status column exists in the publications table
            if (Schema::hasColumn('publications', 'status')) {
                $table->index('status', 'idx_publications_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropIndex('idx_publications_upload_date');
            $table->dropIndex('idx_publications_word_count');

            if (Schema::hasColumn('publications', 'status')) {
                $table->dropIndex('idx_publications_status');
            }
        });
    }
};
