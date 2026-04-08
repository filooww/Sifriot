<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * All many-to-many pivot tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        // author_publication
        Schema::create('author_publication', function (Blueprint $table) {
            $table->unsignedBigInteger('id_author');
            $table->unsignedBigInteger('id_publication');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->primary(['id_author', 'id_publication']);
            $table->foreign('id_author')->references('id_author')->on('authors')->cascadeOnDelete();
            $table->foreign('id_publication')->references('id_publication')->on('publications')->cascadeOnDelete();
        });

        // publication_theme
        Schema::create('publication_theme', function (Blueprint $table) {
            $table->unsignedBigInteger('id_publication');
            $table->unsignedBigInteger('id_theme');
            $table->timestamps();

            $table->primary(['id_publication', 'id_theme']);
            $table->foreign('id_publication')->references('id_publication')->on('publications')->cascadeOnDelete();
            $table->foreign('id_theme')->references('id_theme')->on('themes')->cascadeOnDelete();
        });

        // section_publication
        Schema::create('section_publication', function (Blueprint $table) {
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->unsignedBigInteger('publication_id');
            $table->timestamps();

            $table->primary(['section_id', 'publication_id']);
            $table->foreign('publication_id')->references('id_publication')->on('publications')->cascadeOnDelete();
        });

        // publisher_publication
        Schema::create('publisher_publication', function (Blueprint $table) {
            $table->foreignId('publisher_id')->constrained('publishers')->cascadeOnDelete();
            $table->unsignedBigInteger('publication_id');
            $table->timestamps();

            $table->primary(['publisher_id', 'publication_id']);
            $table->foreign('publication_id')->references('id_publication')->on('publications')->cascadeOnDelete();
        });

        // genre_publication
        Schema::create('genre_publication', function (Blueprint $table) {
            $table->id();
            $table->foreignId('genre_id')->constrained('genres')->cascadeOnDelete();
            $table->unsignedBigInteger('publication_id');
            $table->timestamps();

            $table->foreign('publication_id')->references('id_publication')->on('publications')->cascadeOnDelete();
            $table->index('publication_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genre_publication');
        Schema::dropIfExists('publisher_publication');
        Schema::dropIfExists('section_publication');
        Schema::dropIfExists('publication_theme');
        Schema::dropIfExists('author_publication');
    }
};
