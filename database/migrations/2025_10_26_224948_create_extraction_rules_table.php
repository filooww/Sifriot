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
        Schema::create('extraction_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_type_id');
            $table->foreign('content_type_id')
                ->references('id')->on('content_types')
                ->onDelete('cascade');
            $table->string('format', 20); // pdf, epub, docx, etc.
            $table->integer('priority')->default(0); // Lower = higher priority
            $table->enum('pattern_type', ['regex', 'delimiter', 'field_mapping', 'xpath'])->default('regex');
            $table->text('pattern'); // Regex pattern, delimiter, field name, or XPath
            $table->string('target_field', 50); // title, author, publisher, isbn, doi, year
            $table->boolean('enabled')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index(['content_type_id', 'format']);
            $table->index(['content_type_id', 'priority']);
            $table->index(['enabled']);

            // Unique constraint (with shorter name to stay under MySQL 64 char limit)
            $table->unique(['content_type_id', 'format', 'target_field', 'priority'], 'uq_extraction_rule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extraction_rules');
    }
};
