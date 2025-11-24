<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that have created_by and updated_by columns to be removed.
     */
    protected array $tables = [
        'announcements',
        'client_groups',
        'clients',
        'complaints',
        'contracts',
        'deals',
        'marketing_campaigns',
        'opportunities',
        'project_categories',
        'projects',
        'task_categories',
        'tasks',
        'tickets',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                // Drop foreign key constraints using raw SQL
                $this->dropForeignKeyConstraints($tableName);

                // Drop the columns
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'created_by')) {
                        $table->dropColumn('created_by');
                    }
                    if (Schema::hasColumn($tableName, 'updated_by')) {
                        $table->dropColumn('updated_by');
                    }
                });
            }
        }
    }

    /**
     * Drop foreign key constraints for created_by and updated_by columns.
     */
    private function dropForeignKeyConstraints(string $tableName): void
    {
        $constraints = [
            "{$tableName}_created_by_foreign",
            "{$tableName}_updated_by_foreign",
        ];

        foreach ($constraints as $constraintName) {
            try {
                \DB::statement("ALTER TABLE {$tableName} DROP CONSTRAINT IF EXISTS {$constraintName}");
            } catch (\Exception $e) {
                // Continue if constraint doesn't exist
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    // Add the columns back
                    $table->unsignedBigInteger('created_by')->nullable();
                    $table->unsignedBigInteger('updated_by')->nullable();

                    // Add foreign key constraints
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                    $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                });
            }
        }
    }
};
