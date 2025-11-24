<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add failure_reason column first
        Schema::table('survey_invitations', function (Blueprint $table) {
            $table->text('failure_reason')->nullable()->after('user_agent');
        });

        // Step 2: Add a temporary column to store existing status values
        Schema::table('survey_invitations', function (Blueprint $table) {
            $table->string('temp_status')->nullable()->after('status');
        });

        // Step 3: Copy existing status values to temporary column
        DB::statement('UPDATE survey_invitations SET temp_status = status');

        // Step 4: Drop the existing status column
        Schema::table('survey_invitations', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Step 5: Add the status column back with new enum values including 'failed'
        Schema::table('survey_invitations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'queued', 'sent', 'viewed', 'completed', 'expired', 'cancelled', 'failed'])
                ->default('pending')
                ->after('invitation_token');
        });

        // Step 6: Restore the original status values from temporary column
        DB::statement('UPDATE survey_invitations SET status = temp_status WHERE temp_status IS NOT NULL');

        // Step 7: Drop the temporary column
        Schema::table('survey_invitations', function (Blueprint $table) {
            $table->dropColumn('temp_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Add temporary column to preserve existing status values
        Schema::table('survey_invitations', function (Blueprint $table) {
            $table->string('temp_status')->nullable()->after('status');
        });

        // Step 2: Copy existing status values to temporary column (except 'failed')
        DB::statement("UPDATE survey_invitations SET temp_status = CASE WHEN status = 'failed' THEN 'cancelled' ELSE status END");

        // Step 3: Drop the status column
        Schema::table('survey_invitations', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Step 4: Add back the original status column without 'failed'
        Schema::table('survey_invitations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'queued', 'sent', 'viewed', 'completed', 'expired', 'cancelled'])
                ->default('pending')
                ->after('invitation_token');
        });

        // Step 5: Restore the status values from temporary column
        DB::statement('UPDATE survey_invitations SET status = temp_status WHERE temp_status IS NOT NULL');

        // Step 6: Drop the temporary column
        Schema::table('survey_invitations', function (Blueprint $table) {
            $table->dropColumn('temp_status');
        });

        // Step 7: Remove failure_reason column
        Schema::table('survey_invitations', function (Blueprint $table) {
            $table->dropColumn('failure_reason');
        });
    }
};
