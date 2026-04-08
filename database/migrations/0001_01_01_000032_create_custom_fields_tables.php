<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_type_id');
            $table->string('field_name');
            $table->string('label_en');
            $table->string('label_ru')->nullable();
            $table->string('label_he')->nullable();
            $table->string('field_type', 30)->default('text');
            $table->json('field_config')->nullable();
            $table->boolean('is_required')->default(false);
            $table->string('visibility', 20)->default('public');
            $table->boolean('is_searchable')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('content_type_id')->references('id_content_type')->on('content_types')->cascadeOnDelete();
            $table->index(['content_type_id', 'sort_order'], 'cf_type_sort');
        });

        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_id')->constrained()->cascadeOnDelete();
            $table->string('fieldable_type');
            $table->unsignedBigInteger('fieldable_id');
            $table->json('value')->nullable();
            $table->timestamps();

            $table->index(['fieldable_type', 'fieldable_id']);
            $table->unique(['custom_field_id', 'fieldable_type', 'fieldable_id'], 'cfv_field_fieldable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_fields');
    }
};
