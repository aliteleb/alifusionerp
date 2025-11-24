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
        Schema::create('survey_question_responses', function (Blueprint $table) {
            $table->id();

            // Response relationships
            $table->foreignId('survey_response_id')->constrained('survey_responses')->onDelete('cascade'); // Parent response
            $table->foreignId('survey_question_id')->constrained('survey_questions')->onDelete('cascade'); // Question answered

            // Answer data based on question type
            $table->text('text_value')->nullable(); // For text questions
            $table->integer('rating_value')->nullable(); // For rating questions (1-5)
            $table->text('reason_for_rating')->nullable()->comment('Reason provided for low ratings (2 stars or less)'); // Reason for low ratings
            $table->json('raw_data')->nullable(); // Store original answer data

            // Answer metadata
            $table->boolean('is_skipped')->default(false)->index(); // Whether question was skipped
            $table->boolean('is_required_answered')->default(false); // Whether required question was answered
            $table->integer('time_spent_seconds')->nullable(); // Time spent on this question

            // Answer quality and validation
            $table->boolean('is_valid')->default(true)->index(); // Whether answer passes validation
            $table->json('validation_errors')->nullable(); // Validation error details
            $table->boolean('is_flagged')->default(false); // Flagged for review
            $table->text('flag_reason')->nullable(); // Reason for flagging

            // Admin fields
            $table->text('notes')->nullable(); // Internal admin notes
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            // Timestamps
            $table->timestamp('answered_at')->nullable()->index(); // When this question was answered
            $table->timestamps();

            // Performance indexes for billions of rows
            $table->index(['survey_response_id', 'survey_question_id']); // Response-question lookup
            $table->index(['survey_question_id', 'is_skipped']); // Question skip analysis
            $table->index(['survey_question_id', 'rating_value']); // Rating analysis per question
            $table->index(['is_valid', 'is_flagged']); // Data quality filtering
            $table->index(['answered_at', 'survey_question_id']); // Response timeline per question
            $table->index(['time_spent_seconds', 'survey_question_id']); // Question difficulty analysis

            // Unique constraint to prevent duplicate answers
            $table->unique(['survey_response_id', 'survey_question_id'], 'unique_response_question');

            // Compound indexes for analytics
            $table->index(['survey_question_id', 'rating_value', 'answered_at'], 'question_rating_time_idx');
            $table->index(['survey_response_id', 'is_skipped', 'is_valid'], 'response_quality_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_question_responses');
    }
};
