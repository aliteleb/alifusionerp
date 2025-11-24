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
        Schema::create('branch_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->boolean('is_primary')->default(false)->comment('Indicates if this is the user\'s primary branch');
            $table->timestamps();

            // Composite unique index to prevent duplicate user-branch assignments
            $table->unique(['user_id', 'branch_id'], 'user_branch_unique');

            // Indexes for faster queries
            $table->index('user_id');
            $table->index('branch_id');
            $table->index('is_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_user');
    }
};
