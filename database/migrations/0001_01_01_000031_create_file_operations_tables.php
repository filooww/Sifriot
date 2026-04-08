<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * File metadata extraction, folder scanning, and registration logs.
 */
return new class extends Migration
{
    public function up(): void
    {
        // File metadata (extracted metadata for review/approval)
        Schema::create('file_metadatas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('publication_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('status', 30)->default('pending');
            $table->json('extracted_data')->nullable();
            $table->string('extraction_method', 50)->nullable();
            $table->json('confidence_scores')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('extracted_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->foreign('publication_id')
                ->references('id_publication')
                ->on('publications')
                ->nullOnDelete();

            $table->index('status');
            $table->index(['publication_id', 'file_name'], 'fm_pub_file');
        });

        // Folder scan jobs
        Schema::create('folder_scan_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('folder_path');
            $table->json('scan_options')->nullable();
            $table->string('status', 30)->default('pending');
            $table->integer('total_files_found')->default(0);
            $table->integer('files_registered')->default(0);
            $table->integer('files_skipped')->default(0);
            $table->integer('files_failed')->default(0);
            $table->float('processing_time_seconds', 10, 2)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        // File registration logs
        Schema::create('file_registration_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('publication_id')->nullable();
            $table->string('file_path');
            $table->string('registration_source', 50)->default('manual');
            $table->foreignId('folder_scan_job_id')->nullable()->constrained('folder_scan_jobs')->cascadeOnDelete();
            $table->boolean('metadata_auto_extracted')->default(false);
            $table->string('status', 30)->default('pending');
            $table->text('error_message')->nullable();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('publication_id')->references('id_publication')->on('publications')->nullOnDelete();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_registration_logs');
        Schema::dropIfExists('folder_scan_jobs');
        Schema::dropIfExists('file_metadatas');
    }
};
