<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates all independent reference/lookup tables.
 * These have no FK dependencies on each other.
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- Sections (hierarchical, was "categories") ---
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('sections')->cascadeOnDelete();
            $table->string('name_en');
            $table->string('name_ru')->nullable();
            $table->string('name_he')->nullable();
            $table->string('slug');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
        });

        // --- Content Types ---
        Schema::create('content_types', function (Blueprint $table) {
            $table->id('id_content_type');
            $table->string('name_en');
            $table->string('name_ru')->nullable();
            $table->string('name_he')->nullable();
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('folder_name')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // --- Publishers ---
        Schema::create('publishers', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_ru')->nullable();
            $table->string('name_he')->nullable();
            $table->string('slug')->unique();
            $table->string('website')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // --- Genres ---
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_ru')->nullable();
            $table->string('name_he')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // --- Authors ---
        Schema::create('authors', function (Blueprint $table) {
            $table->id('id_author');
            $table->string('author');
            $table->string('author_low')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('author_low');
        });

        // --- Themes ---
        Schema::create('themes', function (Blueprint $table) {
            $table->id('id_theme');
            $table->string('theme');
            $table->string('theme_low')->nullable();
            $table->timestamps();

            $table->index('theme_low');
        });

        // --- Issue Types (legacy, single-field) ---
        Schema::create('issue_types', function (Blueprint $table) {
            $table->id('id_issue_type');
            $table->string('issue_type');
            $table->timestamps();
        });

        // --- Magazines (legacy, single-field) ---
        Schema::create('magazines', function (Blueprint $table) {
            $table->id('id_magazine');
            $table->string('magazine');
            $table->timestamps();
        });

        // --- Parts (legacy, single-field) ---
        Schema::create('parts', function (Blueprint $table) {
            $table->id('id_part');
            $table->string('part');
            $table->timestamps();
        });

        // --- Library Paths ---
        Schema::create('library_paths', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_verified_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // --- Extraction Rules ---
        Schema::create('extraction_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_type_id');
            $table->string('format', 20);
            $table->integer('priority')->default(0);
            $table->string('pattern_type', 50);
            $table->text('pattern')->nullable();
            $table->string('target_field', 100);
            $table->boolean('enabled')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('content_type_id')->references('id_content_type')->on('content_types')->cascadeOnDelete();
            $table->index(['content_type_id', 'enabled', 'priority'], 'er_content_enabled_priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extraction_rules');
        Schema::dropIfExists('library_paths');
        Schema::dropIfExists('parts');
        Schema::dropIfExists('magazines');
        Schema::dropIfExists('issue_types');
        Schema::dropIfExists('themes');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('genres');
        Schema::dropIfExists('publishers');
        Schema::dropIfExists('content_types');
        Schema::dropIfExists('sections');
    }
};
