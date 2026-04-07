<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop dead legacy columns that have been replaced or were never used.
 *
 * Publications:
 *   - _del_mark: replaced by SoftDeletes trait
 *   - actuality: never used in any view or business logic
 *   - metadata_previous_values: only written by deleted artisan command, never read
 *
 * Files:
 *   - file_size (char(11)): duplicate of file_size_bytes (bigInteger)
 *   - file_volume: never used
 *   - file_number: never used
 *   - file_page: never used
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- publications table ---
        Schema::table('publications', function (Blueprint $table) {
            $existing = Schema::getColumnListing('publications');
            $toDrop = ['_del_mark', 'actuality', 'metadata_previous_values'];
            $drop = array_intersect($toDrop, $existing);

            if (!empty($drop)) {
                // Drop index on _del_mark first if it exists
                try {
                    $table->dropIndex('idx_publications_del_mark');
                } catch (\Exception $e) {
                    // Index may not exist, safe to ignore
                }
                $table->dropColumn($drop);
            }
        });

        // --- files table ---
        Schema::table('files', function (Blueprint $table) {
            $existing = Schema::getColumnListing('files');
            $toDrop = ['file_size', 'file_volume', 'file_number', 'file_page'];
            $drop = array_intersect($toDrop, $existing);

            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->tinyInteger('_del_mark')->default(0);
            $table->tinyInteger('actuality')->nullable()->default(0);
            $table->json('metadata_previous_values')->nullable();
        });

        Schema::table('files', function (Blueprint $table) {
            $table->char('file_size', 11)->default('');
            $table->char('file_volume', 5)->nullable();
            $table->char('file_number', 7)->nullable();
            $table->char('file_page', 9)->nullable();
        });
    }
};
