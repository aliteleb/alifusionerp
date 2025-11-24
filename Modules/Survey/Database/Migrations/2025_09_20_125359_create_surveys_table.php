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
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();

            // Survey basic information
            $table->json('title'); // Multi-language title {"en": "...", "ar": "...", "ku": "..."}
            $table->json('description')->nullable(); // Multi-language description
            $table->string('slug')->unique()->index(); // URL-friendly identifier

            // Survey configuration
            $table->enum('status', ['draft', 'active', 'paused', 'completed', 'archived'])
                ->default('draft')
                ->index(); // For filtering by status

            $table->foreignId('survey_category_id')->constrained('survey_categories')->onDelete('restrict'); // Survey category relationship

            // Survey timing and availability
            $table->datetime('starts_at')->nullable()->index(); // When survey becomes available
            $table->datetime('ends_at')->nullable()->index(); // When survey expires
            $table->boolean('is_anonymous')->default(false); // Whether to collect customer info
            $table->boolean('allow_multiple_responses')->default(false); // Allow same customer multiple responses

            // Survey settings
            $table->integer('max_responses')->nullable(); // Maximum number of responses
            $table->integer('estimated_duration')->nullable(); // Estimated completion time in minutes
            $table->boolean('is_required_login')->default(false); // Require authentication
            $table->boolean('show_progress_bar')->default(true); // Show completion progress
            $table->boolean('randomize_questions')->default(false); // Randomize question order

            $table->string('theme_color', 7)->default('#3B82F6'); // Primary color for survey
            $table->json('welcome_message')->nullable(); // Custom welcome message (multi-language)
            $table->json('thank_you_message')->nullable(); // Custom completion message (multi-language)

            // WhatsApp Configuration (Simple)
            $table->boolean('whatsapp_enabled')->default(false); // Enable WhatsApp messaging
            $table->json('whatsapp_message')->nullable(); // WhatsApp message template (multi-language)
            $table->json('bad_rating_alert_phones')->nullable(); // Phone numbers to alert for bad ratings (≤2 stars)

            // Relationships
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade'); // Branch-specific surveys
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Survey creator
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null'); // Last editor

            // Response statistics (denormalized for performance)
            $table->integer('total_responses')->default(0)->index(); // Cache response count
            $table->decimal('average_rating', 3, 2)->nullable(); // Cache average rating
            $table->integer('completion_rate')->default(0); // Completion percentage

            // Soft deletes and timestamps
            $table->softDeletes();
            $table->timestamps();

            // Performance indexes
            $table->index(['status', 'branch_id']); // Filter active surveys by branch
            $table->index(['survey_category_id', 'status']); // Filter by category and status
            $table->index(['starts_at', 'ends_at']); // Date range queries
            $table->index(['created_by', 'status']); // User's surveys
            $table->index(['created_at', 'branch_id']); // Recent surveys by branch
            $table->index(['total_responses', 'status']); // Popular surveys

            // Full-text search for multi-language content (MySQL specific)
            if (config('database.default') === 'mysql') {
                // Note: JSON full-text search requires MySQL 8.0+
                $table->index(['slug']); // Fallback for older MySQL versions
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
