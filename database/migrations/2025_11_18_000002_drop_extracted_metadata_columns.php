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
            // Drop extracted metadata columns that are now stored in normalized tables
            $table->dropColumn([
                'extracted_author_names',
                'extracted_publication_year',
                'extracted_publisher',
                'extracted_isbn',
                'extracted_doi',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->json('extracted_author_names')->nullable()->after('title_low');
            $table->unsignedInteger('extracted_publication_year')->nullable()->after('extracted_author_names');
            $table->string('extracted_publisher')->nullable()->after('extracted_publication_year');
            $table->string('extracted_isbn', 20)->nullable()->after('extracted_publisher');
            $table->string('extracted_doi')->nullable()->after('extracted_isbn');
        });
    }
};
