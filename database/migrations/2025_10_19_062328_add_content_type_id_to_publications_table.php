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
            $table->unsignedBigInteger('content_type_id')->nullable()->after('status');
            $table->foreign('content_type_id')->references('id')->on('content_types')->onDelete('set null');
            $table->index('content_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropForeign(['content_type_id']);
            $table->dropIndex(['content_type_id']);
            $table->dropColumn('content_type_id');
        });
    }
};
