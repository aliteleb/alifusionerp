<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the unique constraint first
        Schema::table('survey_invitations', function (Blueprint $table) {
            $table->dropUnique('survey_invitations_invitation_token_unique');
        });

        // Update existing tokens to 6 characters
        DB::transaction(function () {
            DB::table('survey_invitations')->orderBy('id')->chunk(100, function ($invitations) {
                foreach ($invitations as $invitation) {
                    $newToken = $this->generateUniqueCode('survey_invitations', 'invitation_token', 6);
                    DB::table('survey_invitations')
                        ->where('id', $invitation->id)
                        ->update(['invitation_token' => $newToken]);
                }
            });
        });

        // Then modify the column structure
        Schema::table('survey_invitations', function (Blueprint $table) {
            $table->string('invitation_token', 6)->unique()->index()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the unique constraint first
        Schema::table('survey_invitations', function (Blueprint $table) {
            $table->dropUnique('survey_invitations_invitation_token_unique');
        });

        // Revert column to original length
        Schema::table('survey_invitations', function (Blueprint $table) {
            $table->string('invitation_token', 64)->change();
        });

        // Regenerate tokens with original length
        DB::transaction(function () {
            DB::table('survey_invitations')->orderBy('id')->chunk(100, function ($invitations) {
                foreach ($invitations as $invitation) {
                    $newToken = $this->generateUniqueCode('survey_invitations', 'invitation_token', 64);
                    DB::table('survey_invitations')
                        ->where('id', $invitation->id)
                        ->update(['invitation_token' => $newToken]);
                }
            });
        });

        // Add the unique constraint back
        Schema::table('survey_invitations', function (Blueprint $table) {
            $table->unique('invitation_token', 'survey_invitations_invitation_token_unique');
        });
    }

    /**
     * Generate a unique code for the given table/column.
     */
    private function generateUniqueCode(string $table, string $column, int $length = 6): string
    {
        do {
            $code = Str::random($length);
        } while (DB::table($table)->where($column, $code)->exists());

        return $code;
    }
};
