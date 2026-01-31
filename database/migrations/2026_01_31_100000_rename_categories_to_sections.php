<?php

declare(strict_types=1);

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
        // 1. Drop FK and indexes on category_publication
        Schema::table('category_publication', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['publication_id']);
            $table->dropUnique(['category_id', 'publication_id']);
            $table->dropIndex(['category_id', 'publication_id']);
        });

        // 2. Drop FK and indexes on categories
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['slug']);
        });

        // 3. Rename tables
        Schema::rename('categories', 'sections');
        Schema::rename('category_publication', 'section_publication');

        // 4. Rename column in pivot table
        Schema::table('section_publication', function (Blueprint $table) {
            $table->renameColumn('category_id', 'section_id');
        });

        // 5. Recreate FK and indexes on sections
        Schema::table('sections', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('sections')->onDelete('cascade');
            $table->index('parent_id');
            $table->index('slug');
        });

        // 6. Recreate FK, indexes, and unique constraint on section_publication
        Schema::table('section_publication', function (Blueprint $table) {
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
            $table->foreign('publication_id')->references('id_publication')->on('publications')->onDelete('cascade');
            $table->unique(['section_id', 'publication_id']);
            $table->index(['section_id', 'publication_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop FK and indexes on section_publication
        Schema::table('section_publication', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropForeign(['publication_id']);
            $table->dropUnique(['section_id', 'publication_id']);
            $table->dropIndex(['section_id', 'publication_id']);
        });

        // 2. Drop FK and indexes on sections
        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['slug']);
        });

        // 3. Rename column back
        Schema::table('section_publication', function (Blueprint $table) {
            $table->renameColumn('section_id', 'category_id');
        });

        // 4. Rename tables back
        Schema::rename('sections', 'categories');
        Schema::rename('section_publication', 'category_publication');

        // 5. Recreate FK and indexes on categories
        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
            $table->index('parent_id');
            $table->index('slug');
        });

        // 6. Recreate FK, indexes, and unique constraint on category_publication
        Schema::table('category_publication', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('publication_id')->references('id_publication')->on('publications')->onDelete('cascade');
            $table->unique(['category_id', 'publication_id']);
            $table->index(['category_id', 'publication_id']);
        });
    }
};
