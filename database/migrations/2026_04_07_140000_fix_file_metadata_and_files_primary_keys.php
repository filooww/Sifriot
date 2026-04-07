<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * - Add publication_id FK to file_metadatas, drop file_id string column
     * - Add auto-increment id PK to files, keep unique on (id_publication, file_name)
     */
    public function up(): void
    {
        // === TASK 1: file_metadatas table ===

        Schema::table('file_metadatas', function (Blueprint $table) {
            // Add publication_id column (nullable so existing rows don't break immediately)
            $table->unsignedBigInteger('publication_id')->nullable()->after('id');

            // Add foreign key constraint
            $table->foreign('publication_id')
                ->references('id_publication')
                ->on('publications')
                ->nullOnDelete();
        });

        // Migrate existing file_id data: extract publication_id from "123-filename.pdf" format
        // We use raw SQL for efficiency and correctness
        DB::statement("
            UPDATE file_metadatas
            SET publication_id = CAST(SUBSTRING_INDEX(file_id, '-', 1) AS UNSIGNED)
            WHERE file_id IS NOT NULL AND file_id != ''
        ");

        // Now drop the old file_id column
        Schema::table('file_metadatas', function (Blueprint $table) {
            $table->dropColumn('file_id');
        });

        // === TASK 2: files table ===

        // Drop the old composite primary key first
        // MySQL stores composite PK as a single constraint named "PRIMARY"
        // We need to drop it and add an auto-increment id column

        Schema::table('files', function (Blueprint $table) {
            // Drop existing composite primary key
            $table->dropPrimary();
        });

        Schema::table('files', function (Blueprint $table) {
            // Add auto-increment id as new primary key
            $table->unsignedBigInteger('id', true)->first();

            // Add unique constraint on the old composite key columns
            $table->unique(['id_publication', 'file_name'], 'files_publication_file_unique');

            // Add FK for id_publication with cascade delete
            $table->foreign('id_publication')
                ->references('id_publication')
                ->on('publications')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // === Reverse TASK 2: files table ===

        Schema::table('files', function (Blueprint $table) {
            // Drop the FK and unique constraint
            $table->dropForeign(['id_publication']);
            $table->dropUnique('files_publication_file_unique');
        });

        Schema::table('files', function (Blueprint $table) {
            // Drop the auto-increment id column
            $table->dropColumn('id');
        });

        Schema::table('files', function (Blueprint $table) {
            // Restore composite primary key
            $table->primary(['id_publication', 'file_name']);
        });

        // === Reverse TASK 1: file_metadatas table ===

        Schema::table('file_metadatas', function (Blueprint $table) {
            // Re-add file_id column
            $table->string('file_id')->nullable()->after('id');
        });

        // Restore file_id data from publication_id + file_name
        DB::statement("
            UPDATE file_metadatas
            SET file_id = CONCAT(publication_id, '-', file_name)
            WHERE publication_id IS NOT NULL
        ");

        Schema::table('file_metadatas', function (Blueprint $table) {
            // Drop the FK and column
            $table->dropForeign(['publication_id']);
            $table->dropColumn('publication_id');
        });
    }
};
