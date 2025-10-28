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
        Schema::create('file_metadatas', function (Blueprint $table) {
            $table->id();
            $table->string('file_id')->nullable(); // File reference (flexible for different file systems)
            $table->string('file_name'); // Original filename for reference
            $table->enum('status', ['pending', 'processed', 'failed', 'confirmed', 'rejected'])->default('pending');
            $table->json('extracted_data')->nullable(); // {title, authors[], publication_year, publisher, isbn, doi}
            $table->string('extraction_method')->nullable(); // pdf_extractor, epub_extractor, etc.
            $table->json('confidence_scores')->nullable(); // {title_confidence, author_confidence, ...}
            $table->text('error_message')->nullable(); // Error details for failed extractions
            $table->timestamp('extracted_at')->nullable(); // When extraction completed
            $table->timestamp('confirmed_at')->nullable(); // When admin confirmed
            $table->timestamp('rejected_at')->nullable(); // When admin rejected
            $table->timestamps();

            // Indexes
            $table->index('file_id');
            $table->index('status');
            $table->index('created_at');
            $table->index(['status', 'created_at']); // For queries filtering by status and date
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_metadatas');
    }
};
