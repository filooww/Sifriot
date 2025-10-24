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
        Schema::create('folder_scan_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users', 'id');
            $table->string('folder_path');
            $table->json('scan_options');
            $table->enum('status', ['pending', 'processing', 'paused', 'completed', 'cancelled', 'failed'])->default('pending');
            $table->integer('total_files_found')->default(0);
            $table->integer('files_registered')->default(0);
            $table->integer('files_skipped')->default(0);
            $table->integer('files_failed')->default(0);
            $table->integer('processing_time_seconds')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folder_scan_jobs');
    }
};
