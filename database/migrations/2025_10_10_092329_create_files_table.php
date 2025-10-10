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
        Schema::create('files', function (Blueprint $table) {
            // Composite primary key: id_publication + ord_num
            $table->unsignedBigInteger('id_publication');
            $table->integer('ord_num');

            // File details
            $table->string('file_name')->unique();
            $table->string('file_name_low');
            $table->string('file_description')->nullable();
            $table->char('file_issue_year', 4)->nullable();
            $table->char('file_volume', 5)->nullable();
            $table->char('file_number', 7)->nullable();
            $table->char('file_page', 9)->nullable();
            $table->char('file_size', 11)->default('');
            $table->string('file_source')->nullable();

            $table->timestamps();

            // Composite primary key
            $table->primary(['id_publication', 'file_name']);

            // Foreign key to publications
            $table->foreign('id_publication')
                  ->references('id_publication')
                  ->on('publications')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
