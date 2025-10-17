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
        // Skip FULLTEXT index creation on SQLite (only supported on MySQL)
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Add FULLTEXT index to publications table for title search
        Schema::table('publications', function (Blueprint $table) {
            $table->fullText(['title', 'title_low'], 'publications_title_fulltext');
        });

        // Add FULLTEXT index to authors table for author name search
        Schema::table('authors', function (Blueprint $table) {
            $table->fullText(['author', 'author_low'], 'authors_name_fulltext');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip FULLTEXT index drop on SQLite (only supported on MySQL)
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('publications', function (Blueprint $table) {
            $table->dropIndex('publications_title_fulltext');
        });

        Schema::table('authors', function (Blueprint $table) {
            $table->dropIndex('authors_name_fulltext');
        });
    }
};
