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
            // Drop the index on metadata status FIRST (before dropping columns it depends on)
            try {
                $table->dropIndex('idx_publications_metadata_status');
            } catch (\Exception $e) {
                // Index might not exist, safe to ignore
            }

            // Drop metadata tracking columns if they exist
            // Confidence tracking now stays in FileMetadata
            // Status tracking moved to FileMetadata.status enum
            $columns = ['metadata_source', 'metadata_confidence_avg', 'metadata_confirmed_at'];
            $existingColumns = Schema::getColumnListing('publications');
            $columnsToDelete = array_intersect($columns, $existingColumns);

            if (! empty($columnsToDelete)) {
                $table->dropColumn($columnsToDelete);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->enum('metadata_source', ['manual', 'extracted', 'hybrid'])->default('manual')->after('extracted_doi');
            $table->decimal('metadata_confidence_avg', 3, 2)->nullable()->after('metadata_source');
            $table->timestamp('metadata_confirmed_at')->nullable()->after('metadata_confidence_avg');
            $table->index(['metadata_source', 'metadata_confirmed_at'], 'idx_publications_metadata_status');
        });
    }
};
