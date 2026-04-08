<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_publication');

            // File identity
            $table->string('file_name');
            $table->string('file_name_low')->nullable();
            $table->string('file_description')->nullable();
            $table->char('file_issue_year', 4)->nullable();
            $table->integer('ord_num')->default(0);

            // File storage
            $table->string('file_source')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->string('file_type', 20)->nullable()->comment('cover, content, etc.');
            $table->string('file_path')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // FK + unique constraint (old composite key semantics)
            $table->foreign('id_publication')
                ->references('id_publication')
                ->on('publications')
                ->cascadeOnDelete();

            $table->unique(['id_publication', 'file_name'], 'files_publication_file_unique');
            $table->index('file_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
