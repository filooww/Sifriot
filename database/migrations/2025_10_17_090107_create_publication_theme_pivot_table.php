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
        Schema::create('publication_theme', function (Blueprint $table) {
            $table->unsignedBigInteger('id_publication');
            $table->unsignedBigInteger('id_theme');
            $table->timestamps();

            // Foreign keys with explicit column references
            $table->foreign('id_publication')
                ->references('id_publication')
                ->on('publications')
                ->onDelete('cascade');

            $table->foreign('id_theme')
                ->references('id_theme')
                ->on('themes')
                ->onDelete('cascade');

            // Composite unique constraint
            $table->unique(['id_publication', 'id_theme']);

            // Individual indexes for performance
            $table->index('id_publication');
            $table->index('id_theme');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publication_theme');
    }
};
