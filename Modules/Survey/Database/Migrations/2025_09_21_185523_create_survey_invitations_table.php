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
        Schema::create('survey_invitations', function (Blueprint $table) {
            $table->id();

            // Core relationships
            $table->foreignId('survey_id')->constrained('surveys')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            // Invitation details
            $table->string('invitation_token', 64)->unique()->index(); // Unique access token
            // Removed invitation_type column as it's no longer needed
            $table->enum('status', ['pending', 'queued', 'sent', 'viewed', 'completed', 'expired', 'cancelled'])->default('pending')->index();

            // Timing and expiration
            $table->datetime('expires_at')->index(); // When invitation expires
            $table->timestamp('send_after')->nullable(); // When invitation can be sent
            $table->datetime('sent_at')->nullable(); // When invitation was sent
            $table->datetime('viewed_at')->nullable(); // When customer first accessed
            $table->datetime('completed_at')->nullable(); // When survey was completed

            // Tracking and metadata
            $table->enum('sent_via', ['whatsapp', 'email', 'sms', 'command', 'manual'])->nullable(); // whatsapp, email, sms, command, manual
            $table->string('customer_phone', 20)->nullable(); // Phone used for sending
            $table->string('customer_email')->nullable(); // Email used for sending
            $table->json('send_attempts')->nullable(); // Track sending attempts
            $table->integer('view_count')->default(0); // How many times accessed
            $table->string('ip_address', 45)->nullable(); // Last access IP
            $table->text('user_agent')->nullable(); // Last access user agent

            // Soft deletes and timestamps
            $table->softDeletes();
            $table->timestamps();

            // Performance indexes
            $table->index(['survey_id', 'status']); // Find invitations by survey and status
            $table->index(['customer_id', 'status']); // Find customer invitations
            $table->index(['expires_at', 'status']); // Find expired invitations
            $table->index(['sent_at', 'sent_via']); // Track sending performance
            $table->index(['created_at', 'branch_id']); // Recent invitations by branch
            $table->index('send_after'); // Index for send_after filtering

            // Unique constraint: one active invitation per survey-customer pair
            $table->unique(['survey_id', 'customer_id', 'deleted_at'], 'unique_survey_customer_invitation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_invitations');
    }
};
