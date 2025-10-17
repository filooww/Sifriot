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
        Schema::create('author_publication', function (Blueprint $table) {
            $table->unsignedBigInteger('id_author');
            $table->unsignedBigInteger('id_publication');
            $table->integer('order')->default(0);
            $table->timestamps();

            // Foreign keys with explicit column references
            $table->foreign('id_author')
                ->references('id_author')
                ->on('authors')
                ->onDelete('cascade');

            $table->foreign('id_publication')
                ->references('id_publication')
                ->on('publications')
                ->onDelete('cascade');

            // Composite unique constraint
            $table->unique(['id_author', 'id_publication']);

            // Individual indexes for performance
            $table->index('id_author');
            $table->index('id_publication');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('author_publication');
    }
};
