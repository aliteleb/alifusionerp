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
        Schema::create('survey_categories', function (Blueprint $table) {
            $table->id();

            // Multi-language name support
            $table->json('name'); // {"en": "Satisfaction Survey", "ar": "مسح الرضا", "ku": "پشکنینی ڕەزامەندی"}
            $table->json('description')->nullable(); // Multi-language description
            $table->string('slug')->unique()->index(); // URL-friendly identifier
            $table->string('icon', 50)->default('heroicon-o-clipboard-document-list'); // Icon for UI
            $table->string('color', 20)->default('primary'); // Color theme for UI
            $table->boolean('is_active')->default(true)->index(); // Can be used for new surveys
            $table->integer('order')->default(0)->index(); // Display order

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Performance indexes
            $table->index(['is_active', 'order']); // Active categories ordered
            $table->index(['slug']); // Quick slug lookup
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_categories');
    }
};
