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
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();

            // Question basic information
            $table->json('question_text'); // Multi-language question text {"en": "...", "ar": "...", "ku": "..."}
            $table->json('description')->nullable(); // Multi-language question description/help text
            $table->json('placeholder')->nullable(); // Multi-language placeholder text

            // Question configuration
            $table->enum('question_type', ['text', 'rating'])->index(); // Question type for filtering

            $table->integer('order')->default(0)->index(); // Question order within survey
            $table->boolean('is_required')->default(false); // Whether question is mandatory
            $table->boolean('is_active')->default(true)->index(); // Whether question is enabled

            // Question settings for rating questions
            $table->json('validation_rules')->nullable(); // Validation rules (min, max, regex, etc.)
            $table->integer('min_value')->default(1)->nullable(); // Minimum value for rating questions
            $table->integer('max_value')->default(5)->nullable(); // Maximum value for rating questions

            // Question analytics (denormalized for performance)
            $table->integer('response_count')->default(0)->index(); // Number of responses
            $table->decimal('average_score', 5, 2)->nullable(); // Average score for rating questions
            $table->integer('skip_count')->default(0); // Number of times question was skipped

            // Relationships
            $table->foreignId('survey_id')->constrained('surveys')->onDelete('cascade'); // Parent survey
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Question creator
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null'); // Last editor

            // Soft deletes and timestamps
            $table->softDeletes();
            $table->timestamps();

            // Performance indexes
            $table->index(['survey_id', 'order']); // Question ordering within survey
            $table->index(['survey_id', 'is_active']); // Active questions in survey
            $table->index(['question_type', 'is_active']); // Filter by question type
            $table->index(['is_required', 'is_active']); // Required questions
            $table->index(['created_by', 'survey_id']); // User's questions per survey
            $table->index(['response_count', 'survey_id']); // Popular questions analytics
            $table->index(['created_at', 'survey_id']); // Recent questions by survey
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};
