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
        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_id')->constrained('custom_fields')->onDelete('cascade');
            $table->string('fieldable_type');
            $table->unsignedBigInteger('fieldable_id');
            $table->json('value')->nullable();
            $table->timestamps();

            $table->index(['fieldable_type', 'fieldable_id']);
            $table->unique(['custom_field_id', 'fieldable_type', 'fieldable_id'], 'unique_field_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
    }
};
