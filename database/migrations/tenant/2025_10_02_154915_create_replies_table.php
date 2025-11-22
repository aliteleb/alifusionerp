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
        Schema::create('replies', function (Blueprint $table) {
            $table->id();

            // Polymorphic relationship
            $table->morphs('repliable');

            // User and message
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id');
            $table->longText('message');

            // Reply metadata
            $table->enum('type', ['reply', 'internal_note', 'status_change', 'system'])->default('reply');
            $table->boolean('is_internal')->default(false);
            $table->boolean('is_from_client')->default(false);
            $table->boolean('is_system_generated')->default(false);

            // Status and workflow
            $table->json('status_changes')->nullable();
            $table->decimal('time_spent', 8, 2)->nullable();

            // Email integration
            $table->string('email_message_id')->nullable();
            $table->boolean('sent_via_email')->default(false);
            $table->timestamp('email_sent_at')->nullable();

            // Client tracking
            $table->string('client_ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('source')->nullable();

            // File attachments and metadata
            $table->json('attachments')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance (morphs() already creates repliable_type, repliable_id index)
            $table->index(['branch_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['is_internal', 'created_at']);
            $table->index(['is_from_client', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index('created_at');

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('replies', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['branch_id']);
        });

        Schema::dropIfExists('replies');
    }
};
