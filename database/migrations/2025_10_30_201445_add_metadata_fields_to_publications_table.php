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
            // Extracted Metadata Fields
            $table->json('extracted_author_names')->nullable()->after('title_low');
            $table->unsignedInteger('extracted_publication_year')->nullable()->after('extracted_author_names');
            $table->string('extracted_publisher')->nullable()->after('extracted_publication_year');
            $table->string('extracted_isbn', 20)->nullable()->after('extracted_publisher');
            $table->string('extracted_doi')->nullable()->after('extracted_isbn');

            // Metadata Status Tracking
            $table->enum('metadata_source', ['manual', 'extracted', 'hybrid'])->default('manual')->after('extracted_doi');
            $table->decimal('metadata_confidence_avg', 3, 2)->nullable()->after('metadata_source');
            $table->timestamp('metadata_confirmed_at')->nullable()->after('metadata_confidence_avg');

            // History/Backup
            $table->json('metadata_previous_values')->nullable()->after('metadata_confirmed_at');

            // Index for metadata status queries
            $table->index(['metadata_source', 'metadata_confirmed_at'], 'idx_publications_metadata_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            // Drop index first
            $table->dropIndex('idx_publications_metadata_status');

            // Drop columns
            $table->dropColumn([
                'extracted_author_names',
                'extracted_publication_year',
                'extracted_publisher',
                'extracted_isbn',
                'extracted_doi',
                'metadata_source',
                'metadata_confidence_avg',
                'metadata_confirmed_at',
                'metadata_previous_values',
            ]);
        });
    }
};
