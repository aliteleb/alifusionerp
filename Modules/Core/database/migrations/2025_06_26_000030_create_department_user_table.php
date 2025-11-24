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
        Schema::create('department_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->boolean('is_primary')->default(false)->comment('Indicates if this is the user\'s primary department');
            $table->timestamps();

            // Composite unique index to prevent duplicate user-department assignments
            $table->unique(['user_id', 'department_id'], 'user_department_unique');

            // Indexes for faster queries
            $table->index('user_id');
            $table->index('department_id');
            $table->index('is_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_user');
    }
};
