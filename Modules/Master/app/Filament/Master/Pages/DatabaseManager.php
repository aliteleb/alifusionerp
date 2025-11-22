<?php

namespace Modules\Master\Filament\Master\Pages;

use Modules\System\Actions\Database\TenantDatabaseActions;
use Modules\Master\Entities\Facility;
use Modules\Core\Services\TenantDatabaseService;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseManager extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';

    protected string $view = 'filament.master.pages.database-manager';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('Tenant Databases');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Database Management');
    }

    public function getTitle(): string
    {
        return __('Tenant Database Manager');
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('migrateAllTenants')
                ->label(__('Migrate All'))
                ->icon('heroicon-o-arrow-up')
                ->color('success')
                ->action('migrateAllTenants')
                ->requiresConfirmation()
                ->modalHeading(__('Migrate All Tenants'))
                ->modalDescription(__('This will run migrations for all tenant databases. Are you sure you want to proceed?'))
                ->modalSubmitActionLabel(__('Yes, Migrate All'))
                ->modalCancelActionLabel(__('Cancel')),
            Action::make('rollbackAllTenants')
                ->label(__('Rollback All'))
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->action('rollbackAllTenants')
                ->requiresConfirmation()
                ->modalHeading(__('Rollback All Tenants'))
                ->modalDescription(__('This will rollback migrations for all tenant databases. Are you sure you want to proceed?'))
                ->modalSubmitActionLabel(__('Yes, Rollback All'))
                ->modalCancelActionLabel(__('Cancel')),
            Action::make('refreshStatus')
                ->label(__('Refresh Status'))
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action('refreshStatus'),
            // Action::make('createAllTenantDatabases')
            //     ->label(__('Create All Tenant Databases'))
            //     ->icon('heroicon-o-plus-circle')
            //     ->color('success')
            //     ->action('createAllTenantDatabases')
            //     ->requiresConfirmation()
            //     ->modalHeading(__('Create All Tenant Databases'))
            //     ->modalDescription(__("This will create databases for all facilities that don't have one yet. Are you sure you want to proceed?"))
            //     ->modalSubmitActionLabel(__('Yes, Create All'))
            //     ->modalCancelActionLabel(__('Cancel')),
        ];
    }

    public function migrateAllTenants(): void
    {
        try {
            $facilities = Facility::all();
            $results = [];
            $successCount = 0;
            $failCount = 0;

            foreach ($facilities as $facility) {
                if (TenantDatabaseService::tenantDatabaseExists($facility)) {
                    try {
                        $tenantDatabaseActions = app(TenantDatabaseActions::class);
                        $result = $tenantDatabaseActions->runTenantMigration($facility->id);
                        $results[] = $result;
                        $successCount++;
                    } catch (Exception $e) {
                        $failCount++;
                        Log::error('Migration failed for facility', [
                            'facility_id' => $facility->id,
                            'facility_name' => $facility->name,
                            'error' => $e->getMessage(),
                        ]);
                        Notification::make()
                            ->title(__('Migration failed for :facility', ['facility' => $facility->name]))
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }
            }

            if ($successCount > 0) {
                Notification::make()
                    ->title(__('Migrations completed'))
                    ->body(__(':success successful, :failed failed', ['success' => $successCount, 'failed' => $failCount]))
                    ->success()
                    ->send();
            }

            if ($failCount > 0) {
                Notification::make()
                    ->title(__('Some migrations failed'))
                    ->body(__(':count migrations failed. Check logs for details.', ['count' => $failCount]))
                    ->warning()
                    ->send();
            }
        } catch (Exception $e) {
            Notification::make()
                ->title(__('Operation failed'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function rollbackAllTenants(): void
    {
        try {
            $facilities = Facility::all();
            $results = [];
            $successCount = 0;
            $failCount = 0;

            foreach ($facilities as $facility) {
                if (TenantDatabaseService::tenantDatabaseExists($facility)) {
                    try {
                        $tenantDatabaseActions = app(TenantDatabaseActions::class);
                        $result = $tenantDatabaseActions->rollbackTenantMigration($facility->id);
                        $results[] = $result;
                        $successCount++;
                    } catch (Exception $e) {
                        $failCount++;
                        Log::error('Rollback failed for facility', [
                            'facility_id' => $facility->id,
                            'facility_name' => $facility->name,
                            'error' => $e->getMessage(),
                        ]);
                        Notification::make()
                            ->title(__('Rollback failed for :facility', ['facility' => $facility->name]))
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }
            }

            if ($successCount > 0) {
                Notification::make()
                    ->title(__('Rollbacks completed'))
                    ->body(__(':success successful, :failed failed', ['success' => $successCount, 'failed' => $failCount]))
                    ->success()
                    ->send();
            }

            if ($failCount > 0) {
                Notification::make()
                    ->title(__('Some rollbacks failed'))
                    ->body(__(':count rollbacks failed. Check logs for details.', ['count' => $failCount]))
                    ->warning()
                    ->send();
            }
        } catch (Exception $e) {
            Notification::make()
                ->title(__('Operation failed'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function refreshStatus(): void
    {
        Notification::make()->title(__('Status refreshed'))->success()->send();
    }

    public function createAllTenantDatabases(): void
    {
        try {
            $facilities = Facility::all();
            $created = 0;

            foreach ($facilities as $facility) {
                if (! TenantDatabaseService::tenantDatabaseExists($facility)) {
                    TenantDatabaseService::createTenantDatabase($facility);
                    $created++;
                }
            }

            Notification::make()
                ->title(__('Tenant databases created'))
                ->body(__(':count databases created successfully.', ['count' => $created]))
                ->success()->send();
        } catch (Exception $e) {
            Notification::make()
                ->title(__('Operation failed'))
                ->body($e->getMessage())
                ->danger()->send();
        }
    }

    // Delegate database operations to action class using method injection
    public function checkMigrationStatus($facilityId): void
    {
        try {
            $tenantDatabaseActions = app(TenantDatabaseActions::class);
            $result = $tenantDatabaseActions->checkMigrationStatus($facilityId);
            $this->dispatch('open-migration-status-modal', $result);
        } catch (Exception $e) {
            Log::error('Migration status check failed', [
                'facility_id' => $facilityId,
                'error' => $e->getMessage(),
            ]);
            Notification::make()
                ->title(__('Migration status check failed'))
                ->body($e->getMessage())
                ->danger()->send();
        }
    }

    public function testTenantConnection($facilityId): void
    {
        try {
            $tenantDatabaseActions = app(TenantDatabaseActions::class);
            $tenantDatabaseActions->testTenantConnection($facilityId);
        } catch (Exception $e) {
            Notification::make()
                ->title(__('Connection test failed'))
                ->body($e->getMessage())
                ->danger()->send();
        }
    }

    public function createTenantDatabase($facilityId): void
    {
        try {
            $tenantDatabaseActions = app(TenantDatabaseActions::class);
            $tenantDatabaseActions->createTenantDatabase($facilityId);
        } catch (Exception $e) {
            Notification::make()
                ->title(__('Database creation failed'))
                ->body($e->getMessage())
                ->danger()->send();
        }
    }

    public function runTenantMigration($facilityId): void
    {
        try {
            $tenantDatabaseActions = app(TenantDatabaseActions::class);
            $result = $tenantDatabaseActions->runTenantMigration($facilityId);

            // Dispatch event to open migration result modal
            $this->dispatch('open-migration-result-modal', $result);
        } catch (Exception $e) {
            Notification::make()
                ->title(__('Migration failed'))
                ->body($e->getMessage())
                ->danger()->send();
        }
    }

    public function rollbackTenantMigration($facilityId): void
    {
        try {
            $tenantDatabaseActions = app(TenantDatabaseActions::class);
            $result = $tenantDatabaseActions->rollbackTenantMigration($facilityId);

            // Dispatch event to open migration result modal
            $this->dispatch('open-migration-result-modal', $result);
        } catch (Exception $e) {
            Notification::make()
                ->title(__('Rollback failed'))
                ->body($e->getMessage())
                ->danger()->send();
        }
    }

    public function seedTenantDatabase($facilityId): void
    {
        try {
            $tenantDatabaseActions = app(TenantDatabaseActions::class);
            $tenantDatabaseActions->seedTenantDatabase($facilityId);
        } catch (Exception $e) {
            Notification::make()
                ->title(__('Seeding failed'))
                ->body($e->getMessage())
                ->danger()->send();
        }
    }

    public function dropTenantDatabase($facilityId): void
    {
        try {
            $tenantDatabaseActions = app(TenantDatabaseActions::class);
            $tenantDatabaseActions->dropTenantDatabase($facilityId);
        } catch (Exception $e) {
            Notification::make()
                ->title(__('Database drop failed'))
                ->body($e->getMessage())
                ->danger()->send();
        }
    }

    public function backupTenantDatabase($facilityId): void
    {
        try {
            $tenantDatabaseActions = app(TenantDatabaseActions::class);
            $backupFileName = $tenantDatabaseActions->backupTenantDatabase($facilityId);

            // Dispatch event to refresh backup list if backup modal is open
            $this->dispatch('backup-created', [
                'facilityId' => $facilityId,
                'filename' => $backupFileName,
            ]);
        } catch (Exception $e) {
            Notification::make()
                ->title(__('Backup failed'))
                ->body($e->getMessage())
                ->danger()->send();
        }
    }

    public function restoreTenantDatabase($facilityId, $backupFileName): void
    {
        try {
            Log::info('Starting database restore from UI', [
                'facility_id' => $facilityId,
                'backup_file' => $backupFileName,
                'user_id' => Auth::id(),
            ]);

            $tenantDatabaseActions = app(TenantDatabaseActions::class);
            $tenantDatabaseActions->restoreTenantDatabase($facilityId, $backupFileName);

            Log::info('Database restore completed successfully from UI', [
                'facility_id' => $facilityId,
                'backup_file' => $backupFileName,
            ]);
        } catch (Exception $e) {
            Log::error('Database restore failed from UI', [
                'facility_id' => $facilityId,
                'backup_file' => $backupFileName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->title(__('Restore failed'))
                ->body($e->getMessage())
                ->danger()->send();
        }
    }

    public function listTenantBackups($facilityId): void
    {
        try {
            $tenantDatabaseActions = app(TenantDatabaseActions::class);
            $backups = $tenantDatabaseActions->listTenantBackups($facilityId);

            $this->dispatch('open-backup-manager-modal', [
                'facilityId' => $facilityId,
                'backups' => $backups,
            ]);
        } catch (Exception $e) {
            Notification::make()
                ->title(__('Failed to load backups'))
                ->body($e->getMessage())
                ->danger()->send();
        }
    }

    public function deleteTenantBackup($facilityId, $backupFileName): void
    {
        try {
            $tenantDatabaseActions = app(TenantDatabaseActions::class);
            $tenantDatabaseActions->deleteTenantBackup($facilityId, $backupFileName);

            // Refresh backup list
            $this->listTenantBackups($facilityId);
        } catch (Exception $e) {
            Notification::make()
                ->title(__('Delete backup failed'))
                ->body($e->getMessage())
                ->danger()->send();
        }
    }

    public function testBackupEnvironment($facilityId): void
    {
        try {
            $tenantDatabaseActions = app(TenantDatabaseActions::class);
            $results = $tenantDatabaseActions->testBackupEnvironment($facilityId);

            $this->dispatch('open-backup-test-modal', [
                'facilityId' => $facilityId,
                'results' => $results,
            ]);
        } catch (Exception $e) {
            Notification::make()
                ->title(__('Backup environment test failed'))
                ->body($e->getMessage())
                ->danger()->send();
        }
    }

    public function getTenantDatabases(): array
    {
        try {
            $facilities = Facility::all();
            $tenantDatabases = [];

            foreach ($facilities as $facility) {
                $tenantDatabases[] = $this->getFacilityDatabaseInfo($facility);
            }

            return $tenantDatabases;
        } catch (Exception $e) {
            Log::error('Error getting tenant databases', ['error' => $e->getMessage()]);

            return [];
        }
    }

    private function getFacilityDatabaseInfo(Facility $facility): array
    {
        $databaseName = TenantDatabaseService::getTenantDatabaseName($facility);
        $connectionName = TenantDatabaseService::TENANT_CONNECTION;
        $exists = TenantDatabaseService::tenantDatabaseExists($facility);
        $canConnect = false;
        $tableCount = 0;
        $migrationStatus = null;
        $error = null;

        if ($exists) {
            try {
                $canConnect = TenantDatabaseService::testTenantConnection($facility);
                if ($canConnect) {
                    // Get table count using raw database query
                    $tableCount = $this->getTenantTableCount($facility);

                    // Get migration status summary using our action class
                    $migrationStatus = $this->getMigrationStatusSummary($facility);
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                Log::warning('Error getting facility database info', [
                    'facility_id' => $facility->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'facility' => $facility,
            'database_name' => $databaseName,
            'connection_name' => $connectionName,
            'exists' => $exists,
            'can_connect' => $canConnect,
            'table_count' => $tableCount,
            'migration_status' => $migrationStatus,
            'error' => $error,
        ];
    }

    /**
     * Get table count for a tenant database
     */
    private function getTenantTableCount(Facility $facility): int
    {
        try {
            $connectionName = TenantDatabaseService::TENANT_CONNECTION;
            $databaseName = TenantDatabaseService::getTenantDatabaseName($facility);

            // Configure tenant connection
            $templateConnection = config('tenant.database.connection_template', 'pgsql');
            $defaultConfig = config("database.connections.{$templateConnection}");

            config(["database.connections.{$connectionName}" => array_merge($defaultConfig, [
                'database' => $databaseName,
            ])]);

            // Purge and test the connection
            DB::purge($connectionName);

            $driver = config("database.connections.{$connectionName}.driver");
            if ($driver === 'pgsql') {
                // PostgreSQL query to count user tables
                $result = DB::connection($connectionName)->selectOne(
                    "SELECT count(*) as count FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE'"
                );
                $count = $result->count ?? 0;
            } else {
                // MySQL query to count tables
                $result = DB::connection($connectionName)->selectOne(
                    'SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?',
                    [$databaseName]
                );
                $count = $result->count ?? 0;
            }

            return (int) $count;
        } catch (Exception $e) {
            Log::warning('Error getting table count for facility', [
                'facility_id' => $facility->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 0;
        }
    }

    /**
     * Get migration status summary for a tenant database
     */
    private function getMigrationStatusSummary(Facility $facility): ?array
    {
        try {
            $tenantDatabaseActions = app(TenantDatabaseActions::class);
            $result = $tenantDatabaseActions->checkMigrationStatus($facility->id);

            return [
                'pending' => $result['pending'] ?? 0,
                'ran' => $result['ran'] ?? 0,
                'total' => $result['total'] ?? 0,
                'summary' => $result['summary'] ?? __('No summary available'),
                'migrations' => $result['migrations'] ?? [],
                'lastRun' => $result['lastRun'] ?? null,
            ];
        } catch (Exception $e) {
            Log::warning('Error getting migration status summary for facility', [
                'facility_id' => $facility->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'pending' => 0,
                'ran' => 0,
                'total' => 0,
                'summary' => __('Migration status unavailable'),
                'migrations' => [],
                'lastRun' => null,
            ];
        }
    }
}
