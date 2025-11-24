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
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->foreignId('survey_invitation_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('survey_invitations')
                ->nullOnDelete();

            $table->index(['survey_invitation_id', 'is_complete'], 'survey_responses_invitation_complete_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropForeign(['survey_invitation_id']);
            $table->dropIndex('survey_responses_invitation_complete_idx');
            $table->dropColumn('survey_invitation_id');
        });
    }
};
