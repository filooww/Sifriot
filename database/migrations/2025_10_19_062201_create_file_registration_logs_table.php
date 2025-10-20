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
        Schema::create('file_registration_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('publication_id')->nullable();
            $table->string('file_path')->unique();
            $table->enum('registration_source', ['manual_registration', 'admin_upload', 'bulk_scan']);
            $table->unsignedBigInteger('folder_scan_job_id')->nullable();
            $table->boolean('metadata_auto_extracted')->default(false);
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('registered_by');
            $table->timestamps();

            $table->foreign('publication_id')->references('id_publication')->on('publications')->onDelete('set null');
            $table->foreign('registered_by')->references('id')->on('users')->onDelete('cascade');

            $table->index('file_path');
            $table->index('registration_source');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_registration_logs');
    }
};
