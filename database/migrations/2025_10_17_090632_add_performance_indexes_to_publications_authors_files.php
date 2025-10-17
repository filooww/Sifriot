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
            $table->index('title', 'idx_publications_title');
            $table->index('_del_mark', 'idx_publications_del_mark');
        });

        Schema::table('authors', function (Blueprint $table) {
            $table->index('author_low', 'idx_authors_author_low');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->index(['id_publication', 'file_name'], 'idx_files_publication_filename');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropIndex('idx_publications_title');
            $table->dropIndex('idx_publications_del_mark');
        });

        Schema::table('authors', function (Blueprint $table) {
            $table->dropIndex('idx_authors_author_low');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex('idx_files_publication_filename');
        });
    }
};
