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
        Schema::table('branches', function (Blueprint $table) {
            // Default survey for WhatsApp invitations
            $table->foreignId('default_survey_id')
                ->nullable()
                ->constrained('surveys')
                ->nullOnDelete()
                ->comment('Default survey to be sent for new customers via WhatsApp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('default_survey_id');
        });
    }
};
