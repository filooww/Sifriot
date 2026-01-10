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
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_type_id')->constrained('content_types')->onDelete('cascade');
            $table->string('field_name');
            $table->string('label_en');
            $table->string('label_ru')->nullable();
            $table->string('label_he')->nullable();
            $table->enum('field_type', ['text', 'number', 'date', 'dropdown', 'multiselect', 'boolean', 'long_text']);
            $table->json('field_config')->nullable();
            $table->boolean('is_required')->default(false);
            $table->enum('visibility', ['public', 'admin_only', 'hidden'])->default('public');
            $table->boolean('is_searchable')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['content_type_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
