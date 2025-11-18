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
        Schema::create('genre_publication', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('publication_id');
            $table->unsignedBigInteger('genre_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('publication_id')
                ->references('id_publication')
                ->on('publications')
                ->onDelete('cascade');

            $table->foreign('genre_id')
                ->references('id')
                ->on('genres')
                ->onDelete('cascade');

            // Ensure unique combination of publication and genre
            $table->unique(['publication_id', 'genre_id']);

            // Indexes for relationship queries
            $table->index('publication_id');
            $table->index('genre_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('genre_publication');
    }
};
