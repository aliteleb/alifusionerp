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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // Core customer information
            $table->string('name', 100)->index(); // Indexed for search performance
            $table->string('phone', 20)->nullable()->index(); // Indexed for lookup
            $table->string('email', 150)->nullable()->index(); // Indexed for search
            $table->date('birthday')->nullable()->index(); // Indexed for age-based queries
            $table->foreignId('gender_id')->nullable()->constrained('genders')->onDelete('set null');

            // Address information
            $table->text('address')->nullable();

            // Visit information
            $table->timestamp('visit_time')->index(); // Indexed for chronological queries

            // Branch relationship - critical for multi-branch performance
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');

            // Admin fields
            $table->text('notes')->nullable(); // Customer notes
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // User who created
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null'); // Last editor

            // Soft deletes for data integrity
            $table->softDeletes();

            // Timestamps
            $table->timestamps();

            // Composite indexes for performance optimization
            $table->index(['branch_id', 'visit_time']); // Branch-specific visit queries
            $table->index(['branch_id', 'name']); // Branch-specific name searches
            $table->index(['phone', 'branch_id']); // Phone lookup within branch
            $table->index(['email', 'branch_id']); // Email lookup within branch
            $table->index(['created_at', 'branch_id']); // Recent customers by branch

            // Full-text search index for name (MySQL specific)
            if (config('database.default') === 'mysql') {
                $table->fullText(['name', 'address']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
