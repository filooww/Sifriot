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
            $table->integer('word_count')->nullable()->after('add_char');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->string('mime_type', 100)->nullable()->after('file_source');
            $table->bigInteger('file_size_bytes')->nullable()->after('mime_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropColumn('word_count');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn(['mime_type', 'file_size_bytes']);
        });
    }
};
