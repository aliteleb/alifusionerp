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
        Schema::create('survey_question_groups', function (Blueprint $table) {
            $table->id();

            // Group basic information
            $table->json('name'); // Multi-language group name {"en": "...", "ar": "...", "ku": "..."}
            $table->json('description')->nullable(); // Multi-language group description
            $table->string('slug')->index(); // URL-friendly identifier

            // Group configuration
            $table->integer('sort_order')->default(0)->index(); // Group order within survey
            $table->boolean('is_active')->default(true)->index(); // Whether group is enabled
            $table->boolean('is_collapsible')->default(false); // Whether group can be collapsed
            $table->boolean('is_collapsed_by_default')->default(false); // Default collapse state

            // Group display settings
            $table->string('icon', 100)->nullable(); // Optional group icon
            $table->string('color', 20)->default('primary'); // Group color theme
            $table->json('settings')->nullable(); // Additional group-specific settings

            // Relationships
            $table->foreignId('survey_id')->constrained('surveys')->onDelete('cascade'); // Parent survey
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Group creator
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null'); // Last editor

            // Soft deletes and timestamps
            $table->softDeletes();
            $table->timestamps();

            // Performance indexes
            $table->index(['survey_id', 'sort_order']); // Group ordering within survey
            $table->index(['survey_id', 'is_active']); // Active groups in survey
            $table->index(['created_by', 'survey_id']); // User's groups per survey
            $table->index(['slug', 'survey_id']); // Unique group identification

            // Unique constraint on slug within survey
            $table->unique(['survey_id', 'slug'], 'unique_group_slug_per_survey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_question_groups');
    }
};
