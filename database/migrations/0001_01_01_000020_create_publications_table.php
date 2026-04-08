<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->id('id_publication');

            // FKs to reference tables (nullable -- not all pubs have these)
            $table->unsignedBigInteger('id_part')->nullable();
            $table->unsignedBigInteger('id_issue_type')->nullable();
            $table->unsignedBigInteger('id_magazine')->nullable();
            $table->unsignedBigInteger('content_type_id')->nullable();

            // Core fields
            $table->string('title')->nullable();
            $table->string('title_low')->nullable();
            $table->char('issue_year', 4)->nullable();
            $table->date('upload_date');
            $table->string('status')->default('published');
            $table->text('description')->nullable();
            $table->string('original_folder_path')->nullable();
            $table->unsignedBigInteger('word_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('id_part')->references('id_part')->on('parts')->nullOnDelete();
            $table->foreign('id_issue_type')->references('id_issue_type')->on('issue_types')->nullOnDelete();
            $table->foreign('id_magazine')->references('id_magazine')->on('magazines')->nullOnDelete();
            $table->foreign('content_type_id')->references('id_content_type')->on('content_types')->nullOnDelete();

            // Performance indexes
            $table->index('status');
            $table->index('issue_year');
            $table->index('content_type_id');
            $table->index('title_low');

            // FULLTEXT for search
            $table->fullText('title', 'ft_publications_title');
            $table->fullText('description', 'ft_publications_description');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
