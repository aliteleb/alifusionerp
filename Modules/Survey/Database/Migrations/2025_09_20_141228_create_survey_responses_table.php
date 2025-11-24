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
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();

            // Response identification
            $table->string('response_uuid', 36)->unique()->index(); // Unique response identifier
            $table->enum('status', ['draft', 'partial', 'completed', 'submitted'])->default('draft')->index();

            // Survey and customer information
            $table->foreignId('survey_id')->constrained('surveys')->onDelete('cascade'); // Parent survey
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null'); // Customer who responded (nullable for anonymous)
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null'); // Branch where response was collected

            // Response metadata
            $table->string('language', 2)->default('en')->index(); // Language used for response (en, ar, ku)
            $table->string('device_type', 20)->nullable(); // mobile, tablet, desktop
            $table->string('user_agent')->nullable(); // Browser/device info
            $table->ipAddress('ip_address')->nullable(); // IP address

            // Response timing
            $table->timestamp('started_at')->nullable()->index(); // When response was started
            $table->timestamp('completed_at')->nullable()->index(); // When response was completed
            $table->integer('duration_seconds')->nullable(); // Time taken to complete

            // Response quality metrics
            $table->decimal('completion_percentage', 5, 2)->default(0.00); // Percentage of questions answered
            $table->integer('questions_answered')->default(0); // Number of questions answered
            $table->integer('questions_skipped')->default(0); // Number of questions skipped
            $table->boolean('is_complete')->default(false)->index(); // Whether all required questions answered

            // Location and context (optional)
            $table->decimal('latitude', 10, 8)->nullable(); // GPS latitude
            $table->decimal('longitude', 11, 8)->nullable(); // GPS longitude
            $table->json('context_data')->nullable(); // Additional context (referrer, campaign, etc.)

            // Data quality flags
            $table->boolean('is_suspicious')->default(false)->index(); // Flagged as potential spam/fraud
            $table->boolean('is_verified')->default(false); // Manually verified as legitimate
            $table->boolean('is_anonymous')->default(false); // Whether response was submitted anonymously
            $table->text('admin_notes')->nullable(); // Admin notes about this response
            $table->text('feedback')->nullable(); // Additional feedback from respondent
            $table->text('notes')->nullable(); // Alias for admin_notes (form compatibility)

            // Performance analytics (denormalized)
            $table->decimal('average_rating', 3, 2)->nullable(); // Average rating across all rating questions
            $table->integer('total_rating_questions')->default(0); // Number of rating questions in survey
            $table->integer('answered_rating_questions')->default(0); // Number of rating questions answered

            // Form field aliases for compatibility
            $table->integer('total_questions')->default(0); // Alias for total questions in survey
            $table->integer('answered_questions')->default(0); // Alias for questions_answered
            $table->integer('skipped_questions')->default(0); // Alias for questions_skipped

            // Relationships and tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // User who created (for admin entries)
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null'); // Last editor

            // Soft deletes and timestamps
            $table->softDeletes();
            $table->timestamps();

            // Performance indexes for billions of rows
            $table->index(['survey_id', 'status']); // Survey responses by status
            $table->index(['survey_id', 'completed_at']); // Recent responses per survey
            $table->index(['customer_id', 'survey_id']); // Customer's responses to specific survey
            $table->index(['branch_id', 'completed_at']); // Branch responses over time
            $table->index(['language', 'survey_id']); // Responses by language per survey
            $table->index(['is_complete', 'completed_at']); // Completed responses by date
            $table->index(['created_at', 'branch_id']); // Response timeline by branch
            $table->index(['average_rating', 'survey_id']); // Rating analysis
            $table->index(['completion_percentage', 'survey_id']); // Completion analysis
            $table->index(['is_suspicious', 'is_verified']); // Data quality filtering

            // Compound indexes for complex queries
            $table->index(['survey_id', 'customer_id', 'completed_at'], 'survey_customer_responses_idx');
            $table->index(['branch_id', 'survey_id', 'status', 'completed_at'], 'branch_survey_status_idx');
            $table->index(['language', 'is_complete', 'average_rating'], 'language_completion_rating_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
