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
        $tableName = config('webpush.table_name', 'push_subscriptions');
        $connection = config('webpush.database_connection');

        Schema::connection($connection)->table($tableName, function (Blueprint $table) use ($connection, $tableName) {
            if (! Schema::connection($connection)->hasColumn($tableName, 'facility_id')) {
                $table->foreignId('facility_id')
                    ->nullable()
                    ->after('subscribable_type')
                    ->constrained('facilities')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();

                $table->index(['facility_id', 'endpoint'], 'push_subscriptions_facility_endpoint_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('webpush.table_name', 'push_subscriptions');
        $connection = config('webpush.database_connection');

        Schema::connection($connection)->table($tableName, function (Blueprint $table) use ($connection, $tableName) {
            if (Schema::connection($connection)->hasColumn($tableName, 'facility_id')) {
                $table->dropConstrainedForeignId('facility_id');
                $table->dropIndex('push_subscriptions_facility_endpoint_index');
            }
        });
    }
};
