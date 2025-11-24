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
        if (Schema::hasTable('jobs') && ! Schema::hasColumn('jobs', 'facility_id')) {
            Schema::table('jobs', function (Blueprint $table) {
                $table->unsignedBigInteger('facility_id')->nullable()->after('queue');
                $table->index('facility_id');
                $table->foreign('facility_id')
                    ->references('id')
                    ->on('facilities')
                    ->onDelete('cascade');
            });
        }

        if (Schema::hasTable('failed_jobs') && ! Schema::hasColumn('failed_jobs', 'facility_id')) {
            Schema::table('failed_jobs', function (Blueprint $table) {
                $table->unsignedBigInteger('facility_id')->nullable()->after('queue');
                $table->index('facility_id');
                $table->foreign('facility_id')
                    ->references('id')
                    ->on('facilities')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('jobs') && Schema::hasColumn('jobs', 'facility_id')) {
            Schema::table('jobs', function (Blueprint $table) {
                $table->dropForeign(['facility_id']);
                $table->dropIndex(['facility_id']);
                $table->dropColumn('facility_id');
            });
        }

        if (Schema::hasTable('failed_jobs') && Schema::hasColumn('failed_jobs', 'facility_id')) {
            Schema::table('failed_jobs', function (Blueprint $table) {
                $table->dropForeign(['facility_id']);
                $table->dropIndex(['facility_id']);
                $table->dropColumn('facility_id');
            });
        }
    }
};
