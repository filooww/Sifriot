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
        Schema::create('publications', function (Blueprint $table) {
            $table->id('id_publication');

            // Foreign keys (nullable as in original)
            $table->unsignedBigInteger('id_publishing')->nullable()->default(0);
            $table->unsignedBigInteger('id_part')->nullable()->default(0);
            $table->unsignedBigInteger('id_issue_type')->nullable()->default(0);
            $table->unsignedBigInteger('id_magazine')->nullable()->default(0);
            $table->unsignedBigInteger('id_theme_set')->nullable()->default(0);
            $table->unsignedBigInteger('id_author_set')->nullable()->default(0);

            // Publication details
            $table->string('title')->nullable();
            $table->string('title_low')->nullable();
            $table->char('issue_year', 4)->nullable();
            $table->date('upload_date');
            $table->tinyInteger('actuality')->nullable()->default(0);

            // System fields
            $table->tinyInteger('_del_mark')->default(0);
            $table->integer('add_int')->default(0);
            $table->string('add_char')->default('');

            $table->timestamps();

            // Add indexes for foreign keys
            $table->index('id_publishing');
            $table->index('id_part');
            $table->index('id_issue_type');
            $table->index('id_magazine');
            $table->index('id_theme_set');
            $table->index('id_author_set');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
