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
        Schema::table('publications', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('authors', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('files', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('authors', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('files', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
