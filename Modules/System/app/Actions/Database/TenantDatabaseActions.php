<?php

namespace Modules\System\Actions\Database;

use Modules\System\Actions\Facility\SeedFacilityDataAction;
use Modules\Master\Entities\Facility;
use Modules\Core\Services\MigrationStatusService;
use Modules\Core\Services\TenantDatabaseService;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class TenantDatabaseActions
{
    public function __construct(
        private MigrationStatusService $migrationStatusService
    ) {}

    public function checkMigrationStatus($facilityId): array
    {
        $facility = Facility::find($facilityId);
        if (! $facility) {
            throw new Exception(__('Facility not found'));
        }

        if (! TenantDatabaseService::tenantDatabaseExists($facility)) {
            throw new Exception(__('Database for :name does not exist', ['name' => $facility->name]));
        }

        $connectionName = TenantDatabaseService::TENANT_CONNECTION;

        TenantDatabaseService::connectToFacility($facility);

        try {
            $result = $this->migrationStatusService->getMigrationStatus($connectionName);
            TenantDatabaseService::switchToMaster();

            return [
                'facilityName' => $facility->name,
                'connectionName' => $connectionName,
                'output' => $result['formattedOutput'],
                'rawOutput' => $result['rawOutput'],
                'summary' => $result['statusInfo']['summary'],
                'pending' => (int) $result['statusInfo']['pending'],
                'ran' => (int) $result['statusInfo']['ran'],
                'total' => (int) $result['statusInfo']['total'],
                'migrations' => $result['statusInfo']['migrations'] ?? [],
                'lastRun' => $result['statusInfo']['lastRun'],
            ];
        } catch (Exception $e) {
            TenantDatabaseService::switchToMaster();
            throw $e;
        }
    }

    public function testTenantConnection($facilityId): void
    {
        $facility = Facility::find($facilityId);
        if (! $facility) {
            throw new Exception(__('Facility not found'));
        }

        $canConnect = TenantDatabaseService::testTenantConnection($facility);

        if ($canConnect) {
            Notification::make()
                ->title(__('Connection successful'))
                ->body(__('Successfully connected to :name database', ['name' => $facility->name]))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('Connection failed'))
                ->body(__('Could not connect to :name database', ['name' => $facility->name]))
                ->warning()
                ->send();
        }
    }

    public function createTenantDatabase($facilityId): void
    {
        $facility = Facility::find($facilityId);
        if (! $facility) {
            throw new Exception(__('Facility not found'));
        }

        if (TenantDatabaseService::tenantDatabaseExists($facility)) {
            Notification::make()
                ->title(__('Database already exists'))
                ->body(__('Database for :name already exists', ['name' => $facility->name]))
                ->warning()
                ->send();

            return;
        }

        TenantDatabaseService::createTenantDatabase($facility);

        Notification::make()
            ->title(__('Database created'))
            ->body(__('Database created successfully for :name', ['name' => $facility->name]))
            ->success()
            ->send();
    }

    public function runTenantMigration($facilityId): array
    {
        $facility = Facility::find($facilityId);
        if (! $facility) {
            throw new Exception(__('Facility not found'));
        }

        $connectionName = TenantDatabaseService::TENANT_CONNECTION;
        TenantDatabaseService::connectToFacility($facility);

        try {
            // Capture the output of the migrate command directly
            Artisan::call('migrate', [
                '--database' => $connectionName,
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            $migrationOutput = Artisan::output();

            // Add debug information if output is empty
            if (empty($migrationOutput)) {
                $migrationOutput = "Migration completed successfully but no output was generated.\n";
                $migrationOutput .= "Connection: {$connectionName}\n";
                $migrationOutput .= "Path: database/migrations/tenant\n";
            }

            TenantDatabaseService::switchToMaster();

            // Return the migration result for modal display
            return [
                'facilityName' => $facility->name,
                'connectionName' => $connectionName,
                'output' => $migrationOutput,
                'status' => 'success',
                'message' => __('Migration completed for :name', ['name' => $facility->name]),
            ];
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $errorTrace = $e->getTraceAsString();

            TenantDatabaseService::switchToMaster();

            // Return the error for modal display
            return [
                'facilityName' => $facility->name,
                'connectionName' => $connectionName,
                'output' => $errorMessage."\n\n".$errorTrace,
                'status' => 'error',
                'message' => __('Migration failed for :name: :error', ['name' => $facility->name, 'error' => $errorMessage]),
            ];
        }
    }

    public function rollbackTenantMigration($facilityId): array
    {
        $facility = Facility::find($facilityId);
        if (! $facility) {
            throw new Exception(__('Facility not found'));
        }

        $connectionName = TenantDatabaseService::TENANT_CONNECTION;
        TenantDatabaseService::connectToFacility($facility);

        try {
            // Capture the output of the migrate:rollback command
            Artisan::call('migrate:rollback', [
                '--database' => $connectionName,
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            $rollbackOutput = Artisan::output();

            // Add debug information if output is empty
            if (empty($rollbackOutput)) {
                $rollbackOutput = "Rollback completed successfully but no output was generated.\n";
                $rollbackOutput .= "Connection: {$connectionName}\n";
                $rollbackOutput .= "Path: database/migrations/tenant\n";
            }

            TenantDatabaseService::switchToMaster();

            // Return the rollback result for modal display
            return [
                'facilityName' => $facility->name,
                'connectionName' => $connectionName,
                'output' => $rollbackOutput,
                'status' => 'success',
                'message' => __('Rollback completed for :name', ['name' => $facility->name]),
            ];
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $errorTrace = $e->getTraceAsString();

            TenantDatabaseService::switchToMaster();

            // Return the error for modal display
            return [
                'facilityName' => $facility->name,
                'connectionName' => $connectionName,
                'output' => $errorMessage."\n\n".$errorTrace,
                'status' => 'error',
                'message' => __('Rollback failed for :name: :error', ['name' => $facility->name, 'error' => $errorMessage]),
            ];
        }
    }

    public function seedTenantDatabase($facilityId): void
    {
        $facility = Facility::find($facilityId);
        if (! $facility) {
            throw new Exception(__('Facility not found'));
        }

        // Switch to tenant database connection before seeding
        TenantDatabaseService::connectToFacility($facility);

        try {
            Log::info('Starting tenant database seeding', ['facility_id' => $facilityId]);
            $seedAction = new SeedFacilityDataAction;
            $seedAction->execute($facility);
            Log::info('Completed tenant database seeding', ['facility_id' => $facilityId]);

            Notification::make()
                ->title(__('Tenant database seeded'))
                ->body(__('Database seeded successfully for :name', ['name' => $facility->name]))
                ->success()
                ->send();
        } catch (Exception $e) {
            Log::error('Error during tenant database seeding', [
                'facility_id' => $facilityId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Switch back to default connection before re-throwing
            TenantDatabaseService::switchToMaster();
            throw $e;
        } finally {
            // Switch back to default connection
            TenantDatabaseService::switchToMaster();
        }
    }

    public function dropTenantDatabase($facilityId): void
    {
        $facility = Facility::find($facilityId);
        if (! $facility) {
            throw new Exception(__('Facility not found'));
        }

        TenantDatabaseService::dropTenantDatabase($facility);

        Notification::make()
            ->title(__('Database dropped'))
            ->body(__('Database dropped successfully for :name', ['name' => $facility->name]))
            ->success()
            ->send();
    }

    public function backupTenantDatabase($facilityId): string
    {
        $facility = Facility::find($facilityId);
        if (! $facility) {
            throw new Exception(__('Facility not found'));
        }

        if (! TenantDatabaseService::tenantDatabaseExists($facility)) {
            throw new Exception(__('Database for :name does not exist', ['name' => $facility->name]));
        }

        $connectionName = TenantDatabaseService::TENANT_CONNECTION;
        $databaseName = TenantDatabaseService::getTenantDatabaseName($facility);

        // Configure the tenant connection first
        $templateConnection = config('tenant.database.connection_template', 'pgsql');
        $defaultConfig = config("database.connections.{$templateConnection}");

        config(["database.connections.{$connectionName}" => array_merge($defaultConfig, [
            'database' => $databaseName,
        ])]);

        // Create backup directory if it doesn't exist
        $backupDir = storage_path('app/backups/tenant-databases');
        if (! File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        // Generate backup filename with timestamp
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupFileName = "{$facility->subdomain}_{$timestamp}.sql";
        $backupPath = "{$backupDir}/{$backupFileName}";

        try {
            $driver = config("database.connections.{$connectionName}.driver");
            $host = config("database.connections.{$connectionName}.host");
            $port = config("database.connections.{$connectionName}.port");
            $username = config("database.connections.{$connectionName}.username");
            $password = config("database.connections.{$connectionName}.password");

            if ($driver === 'pgsql') {
                // Check if pg_dump is available, otherwise use Laravel fallback
                $pgDumpAvailable = $this->isPgDumpAvailable();

                if ($pgDumpAvailable) {
                    // PostgreSQL backup using pg_dump with improved options
                    $command = sprintf(
                        'pg_dump -h %s -p %s -U %s -d %s --no-password --verbose --clean --if-exists --create --format=plain --file=%s',
                        escapeshellarg($host),
                        escapeshellarg($port),
                        escapeshellarg($username),
                        escapeshellarg($databaseName),
                        escapeshellarg($backupPath)
                    );

                    // Set PGPASSWORD environment variable for authentication
                    $env = ['PGPASSWORD' => $password];

                    // Test connection first
                    $testCommand = sprintf(
                        'pg_isready -h %s -p %s',
                        escapeshellarg($host),
                        escapeshellarg($port)
                    );

                    $testResult = Process::env($env)->timeout(10)->run($testCommand);
                    if (! $testResult->successful()) {
                        Log::error('PostgreSQL connection test failed', [
                            'facility_id' => $facilityId,
                            'host' => $host,
                            'port' => $port,
                            'error' => $testResult->errorOutput(),
                        ]);
                        throw new Exception(__('Cannot connect to PostgreSQL server: :error', ['error' => $testResult->errorOutput()]));
                    }
                } else {
                    // Fallback: Use Laravel-based backup for PostgreSQL
                    Log::info('pg_dump not available, using Laravel fallback backup', [
                        'facility_id' => $facilityId,
                        'database_name' => $databaseName,
                    ]);

                    // Test Laravel database connection instead
                    try {
                        DB::connection($connectionName)->getPdo();
                        Log::info('Laravel database connection test successful', [
                            'facility_id' => $facilityId,
                            'connection_name' => $connectionName,
                        ]);
                    } catch (Exception $e) {
                        Log::error('Laravel database connection test failed', [
                            'facility_id' => $facilityId,
                            'connection_name' => $connectionName,
                            'error' => $e->getMessage(),
                        ]);
                        throw new Exception(__('Cannot connect to database: :error', ['error' => $e->getMessage()]));
                    }

                    // Perform Laravel-based backup
                    $this->createLaravelBasedBackup($connectionName, $backupPath);

                    // Set flags to skip external command execution
                    $command = null;
                    $env = [];
                }

            } else {
                // MySQL backup using mysqldump
                $command = sprintf(
                    'mysqldump -h %s -P %s -u %s -p%s --single-transaction --routines --triggers %s > %s',
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($databaseName),
                    escapeshellarg($backupPath)
                );

                $env = [];
            }

            Log::info('Creating database backup', [
                'facility_id' => $facilityId,
                'facility_name' => $facility->name,
                'database_name' => $databaseName,
                'backup_path' => $backupPath,
                'driver' => $driver,
                'method' => $command ? 'external_tool' : 'laravel_fallback',
                'command' => $command ? str_replace($password, '***', $command) : 'N/A',
            ]);

            // Only run external command if available
            if ($command) {
                // Set timeout for backup process (5 minutes)
                $result = Process::env($env)->timeout(300)->run($command);

                if (! $result->successful()) {
                    Log::error('Database backup failed', [
                        'facility_id' => $facilityId,
                        'command' => str_replace($password, '***', $command),
                        'exit_code' => $result->exitCode(),
                        'output' => $result->output(),
                        'error' => $result->errorOutput(),
                        'timeout' => false, // Will be updated based on actual timeout status
                    ]);

                    $errorMessage = $result->errorOutput();
                    if (empty($errorMessage)) {
                        $errorMessage = 'Unknown error occurred during backup';
                    }

                    throw new Exception(__('Backup failed: :error', ['error' => $errorMessage]));
                }
            }

            // Verify backup file was created and is not empty
            if (! File::exists($backupPath) || File::size($backupPath) === 0) {
                throw new Exception(__('Backup file was not created or is empty'));
            }

            Log::info('Database backup completed successfully', [
                'facility_id' => $facilityId,
                'backup_path' => $backupPath,
                'file_size' => File::size($backupPath),
            ]);

            Notification::make()
                ->title(__('Backup created'))
                ->body(__('Database backup created successfully for :name', ['name' => $facility->name]))
                ->success()
                ->send();

            return $backupFileName;
        } catch (Exception $e) {
            // Clean up failed backup file if it exists
            if (File::exists($backupPath)) {
                File::delete($backupPath);
            }

            throw $e;
        }
    }

    public function restoreTenantDatabase($facilityId, string $backupFileName): void
    {
        $facility = Facility::find($facilityId);
        if (! $facility) {
            throw new Exception(__('Facility not found'));
        }

        $backupPath = storage_path("app/backups/tenant-databases/{$backupFileName}");
        if (! File::exists($backupPath)) {
            throw new Exception(__('Backup file not found: :filename', ['filename' => $backupFileName]));
        }

        $connectionName = TenantDatabaseService::TENANT_CONNECTION;
        $databaseName = TenantDatabaseService::getTenantDatabaseName($facility);

        // Configure the tenant connection first
        $templateConnection = config('tenant.database.connection_template', 'pgsql');
        $defaultConfig = config("database.connections.{$templateConnection}");

        config(["database.connections.{$connectionName}" => array_merge($defaultConfig, [
            'database' => $databaseName,
        ])]);

        // Clear any cached connections to ensure fresh config is used
        DB::purge($connectionName);

        try {
            $driver = config("database.connections.{$connectionName}.driver");
            $host = config("database.connections.{$connectionName}.host");
            $port = config("database.connections.{$connectionName}.port");
            $username = config("database.connections.{$connectionName}.username");
            $password = config("database.connections.{$connectionName}.password");

            Log::info('Starting database restore from backup', [
                'facility_id' => $facilityId,
                'facility_name' => $facility->name,
                'database_name' => $databaseName,
                'backup_file' => $backupFileName,
                'driver' => $driver,
                'host' => $host,
                'port' => $port,
                'username' => $username,
                'connection_name' => $connectionName,
                'template_connection' => $templateConnection,
            ]);

            // Ensure the database exists
            if (! TenantDatabaseService::tenantDatabaseExists($facility)) {
                throw new Exception(__('Database for :name does not exist', ['name' => $facility->name]));
            }

            // Test database connection before proceeding
            try {
                DB::connection($connectionName)->getPdo();
                Log::info('Database connection test successful', [
                    'facility_id' => $facilityId,
                    'connection_name' => $connectionName,
                ]);
            } catch (Exception $e) {
                Log::error('Database connection test failed', [
                    'facility_id' => $facilityId,
                    'connection_name' => $connectionName,
                    'error' => $e->getMessage(),
                ]);
                throw new Exception(__('Cannot connect to database: :error', ['error' => $e->getMessage()]));
            }

            // Check backup file format to determine restore method
            $backupContent = File::get($backupPath);
            $isLaravelBackup = str_contains($backupContent, '-- Laravel-based PostgreSQL Backup');

            Log::info('Backup file analysis', [
                'facility_id' => $facilityId,
                'backup_file' => $backupFileName,
                'is_laravel_backup' => $isLaravelBackup,
                'file_size' => File::size($backupPath),
                'first_100_chars' => substr($backupContent, 0, 100),
            ]);

            if ($driver === 'pgsql') {
                // For PostgreSQL, use different approaches based on backup format
                if ($isLaravelBackup) {
                    Log::info('Detected Laravel-based backup, using Laravel restore method', [
                        'facility_id' => $facilityId,
                    ]);

                    // Step 1: Truncate all tables to clear existing data
                    $this->truncateAllTables($connectionName, $driver);

                    // Step 2: Use Laravel-based restore for Laravel backups
                    $this->executeLaravelBasedRestore($connectionName, $backupPath);
                } else {
                    // For pg_dump created backups
                    $psqlAvailable = $this->isPsqlAvailable();

                    if ($psqlAvailable) {
                        Log::info('Detected pg_dump backup, using psql restore', [
                            'facility_id' => $facilityId,
                        ]);

                        // For pg_dump backups, we don't need to truncate since the backup contains DROP statements
                        $command = sprintf(
                            'psql -h %s -p %s -U %s -d %s --no-password -f %s',
                            escapeshellarg($host),
                            escapeshellarg($port),
                            escapeshellarg($username),
                            escapeshellarg($databaseName),
                            escapeshellarg($backupPath)
                        );

                        // Set PGPASSWORD environment variable for authentication
                        $env = ['PGPASSWORD' => $password];

                        $result = Process::env($env)->timeout(600)->run($command); // Increased timeout

                        if (! $result->successful()) {
                            Log::error('PostgreSQL restore failed', [
                                'facility_id' => $facilityId,
                                'command' => str_replace($password, '***', $command),
                                'exit_code' => $result->exitCode(),
                                'output' => $result->output(),
                                'error' => $result->errorOutput(),
                            ]);

                            $errorMessage = $result->errorOutput() ?: $result->output() ?: 'Unknown error occurred during restore';
                            throw new Exception(__('Restore failed: :error', ['error' => $errorMessage]));
                        }

                        Log::info('psql restore output', [
                            'facility_id' => $facilityId,
                            'output' => $result->output(),
                        ]);
                    } else {
                        Log::info('psql not available, attempting Laravel fallback for pg_dump backup', [
                            'facility_id' => $facilityId,
                        ]);

                        // Step 1: Truncate all tables to clear existing data
                        $this->truncateAllTables($connectionName, $driver);

                        // Step 2: Try Laravel-based restore (less reliable for pg_dump backups)
                        $this->executeLaravelBasedRestore($connectionName, $backupPath);
                    }
                }
            } else {
                // MySQL restore using mysql
                $command = sprintf(
                    'mysql -h %s -P %s -u %s -p%s %s < %s',
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($databaseName),
                    escapeshellarg($backupPath)
                );

                $result = Process::timeout(600)->run($command); // Increased timeout

                if (! $result->successful()) {
                    Log::error('MySQL restore failed', [
                        'facility_id' => $facilityId,
                        'command' => str_replace($password, '***', $command),
                        'output' => $result->output(),
                        'error' => $result->errorOutput(),
                    ]);
                    throw new Exception(__('Restore failed: :error', ['error' => $result->errorOutput()]));
                }
            }

            Log::info('Database restore completed successfully', [
                'facility_id' => $facilityId,
                'backup_file' => $backupFileName,
            ]);

            Notification::make()
                ->title(__('Database restored'))
                ->body(__('Database restored successfully for :name from backup :file', [
                    'name' => $facility->name,
                    'file' => $backupFileName,
                ]))
                ->success()
                ->send();
        } catch (Exception $e) {
            Log::error('Database restore failed with exception', [
                'facility_id' => $facilityId,
                'backup_file' => $backupFileName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function listTenantBackups($facilityId): array
    {
        $facility = Facility::find($facilityId);
        if (! $facility) {
            throw new Exception(__('Facility not found'));
        }

        $backupDir = storage_path('app/backups/tenant-databases');
        if (! File::exists($backupDir)) {
            return [];
        }

        $facilitySubdomain = $facility->subdomain;
        $backups = [];

        $files = File::files($backupDir);

        foreach ($files as $file) {
            $filename = $file->getFilename();

            // Check if backup belongs to this facility
            if (str_starts_with($filename, $facilitySubdomain.'_')) {
                $backups[] = [
                    'filename' => $filename,
                    'size' => File::size($file->getPathname()),
                    'created_at' => File::lastModified($file->getPathname()),
                    'human_size' => $this->formatBytes(File::size($file->getPathname())),
                    'human_date' => date('Y-m-d H:i:s', File::lastModified($file->getPathname())),
                ];
            }
        }

        // Sort by creation date descending (newest first)
        usort($backups, function ($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });

        return $backups;
    }

    public function deleteTenantBackup($facilityId, string $backupFileName): void
    {
        $facility = Facility::find($facilityId);
        if (! $facility) {
            throw new Exception(__('Facility not found'));
        }

        $backupPath = storage_path("app/backups/tenant-databases/{$backupFileName}");
        if (! File::exists($backupPath)) {
            throw new Exception(__('Backup file not found: :filename', ['filename' => $backupFileName]));
        }

        // Verify backup belongs to this facility
        if (! str_starts_with($backupFileName, $facility->subdomain.'_')) {
            throw new Exception(__('Backup does not belong to this facility'));
        }

        File::delete($backupPath);

        Log::info('Backup file deleted', [
            'facility_id' => $facilityId,
            'backup_file' => $backupFileName,
        ]);

        Notification::make()
            ->title(__('Backup deleted'))
            ->body(__('Backup file :file deleted successfully', ['file' => $backupFileName]))
            ->success()
            ->send();
    }

    public function testBackupEnvironment($facilityId): array
    {
        $facility = Facility::find($facilityId);
        if (! $facility) {
            throw new Exception(__('Facility not found'));
        }

        $connectionName = TenantDatabaseService::TENANT_CONNECTION;
        $databaseName = TenantDatabaseService::getTenantDatabaseName($facility);

        // Configure the tenant connection first
        $templateConnection = config('tenant.database.connection_template', 'pgsql');
        $defaultConfig = config("database.connections.{$templateConnection}");

        config(["database.connections.{$connectionName}" => array_merge($defaultConfig, [
            'database' => $databaseName,
        ])]);

        // Now get the actual connection details
        $driver = config("database.connections.{$connectionName}.driver");
        $host = config("database.connections.{$connectionName}.host");
        $port = config("database.connections.{$connectionName}.port");
        $username = config("database.connections.{$connectionName}.username");
        $password = config("database.connections.{$connectionName}.password");

        $results = [
            'driver' => $driver,
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'database' => $databaseName,
            'connection_name' => $connectionName,
            'template_connection' => $templateConnection,
            'tests' => [],
        ];

        try {
            if ($driver === 'pgsql') {
                // Test pg_dump availability
                $pgDumpTest = Process::run('pg_dump --version');
                $results['tests']['pg_dump_available'] = [
                    'success' => $pgDumpTest->successful(),
                    'output' => $pgDumpTest->output(),
                    'error' => $pgDumpTest->errorOutput(),
                    'fallback_available' => true,
                    'note' => $pgDumpTest->successful() ? 'Using pg_dump' : 'Will use Laravel fallback backup',
                ];

                // Only test pg_isready and psql if pg_dump is available
                if ($pgDumpTest->successful()) {
                    // Test pg_isready
                    $pgReadyTest = Process::timeout(10)->run(sprintf(
                        'pg_isready -h %s -p %s',
                        escapeshellarg($host),
                        escapeshellarg($port)
                    ));
                    $results['tests']['server_ready'] = [
                        'success' => $pgReadyTest->successful(),
                        'output' => $pgReadyTest->output(),
                        'error' => $pgReadyTest->errorOutput(),
                    ];

                    // Test database connection with credentials
                    $connectTest = Process::env(['PGPASSWORD' => $password])->timeout(10)->run(sprintf(
                        'psql -h %s -p %s -U %s -d %s -c "SELECT 1;" --no-password',
                        escapeshellarg($host),
                        escapeshellarg($port),
                        escapeshellarg($username),
                        escapeshellarg($databaseName)
                    ));
                    $results['tests']['database_connection'] = [
                        'success' => $connectTest->successful(),
                        'output' => $connectTest->output(),
                        'error' => $connectTest->errorOutput(),
                    ];

                    // Test psql availability for restore
                    $psqlTest = Process::run('psql --version');
                    $results['tests']['psql_available'] = [
                        'success' => $psqlTest->successful(),
                        'output' => $psqlTest->output(),
                        'error' => $psqlTest->errorOutput(),
                        'fallback_available' => true,
                        'note' => $psqlTest->successful() ? 'Using psql for restore' : 'Will use Laravel fallback restore',
                    ];
                } else {
                    // Test Laravel database connection as fallback
                    try {
                        DB::connection($connectionName)->getPdo();
                        $results['tests']['laravel_database_connection'] = [
                            'success' => true,
                            'output' => 'Laravel database connection successful',
                            'error' => null,
                            'note' => 'Using Laravel database connection for backup',
                        ];
                    } catch (Exception $e) {
                        $results['tests']['laravel_database_connection'] = [
                            'success' => false,
                            'output' => '',
                            'error' => $e->getMessage(),
                        ];
                    }
                }

                // Test backup directory permissions
                $backupDir = storage_path('app/backups/tenant-databases');
                if (! File::exists($backupDir)) {
                    File::makeDirectory($backupDir, 0755, true);
                }

                $testFile = $backupDir.'/test_write.tmp';
                $writeTest = File::put($testFile, 'test');
                if ($writeTest) {
                    File::delete($testFile);
                }

                $results['tests']['backup_directory_writable'] = [
                    'success' => (bool) $writeTest,
                    'path' => $backupDir,
                    'error' => $writeTest ? null : 'Cannot write to backup directory',
                ];

                // Test configuration loading
                $results['tests']['configuration_loaded'] = [
                    'success' => ! empty($host) && ! empty($username),
                    'template_connection' => $templateConnection,
                    'configured_connection' => $connectionName,
                    'config_keys_found' => [
                        'driver' => ! empty($driver),
                        'host' => ! empty($host),
                        'port' => ! empty($port),
                        'username' => ! empty($username),
                        'password' => ! empty($password),
                    ],
                    'error' => empty($host) || empty($username) ? 'Configuration not properly loaded' : null,
                ];
            }
        } catch (Exception $e) {
            $results['tests']['general_error'] = [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }

        return $results;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }

    /**
     * Check if pg_dump is available on the system
     */
    private function isPgDumpAvailable(): bool
    {
        try {
            $result = Process::timeout(5)->run('pg_dump --version');

            return $result->successful();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create a Laravel-based backup for PostgreSQL when pg_dump is not available
     */
    private function createLaravelBasedBackup(string $connectionName, string $backupPath): void
    {
        try {
            $connection = DB::connection($connectionName);
            $sql = "-- Laravel-based PostgreSQL Backup\n";
            $sql .= '-- Created at: '.now()->toDateTimeString()."\n\n";

            // Get all table names
            $tables = $connection->select(
                "SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename"
            );

            foreach ($tables as $table) {
                $tableName = $table->tablename;

                // Skip system tables
                if (in_array($tableName, ['migrations'])) {
                    continue;
                }

                $sql .= "\n-- Table: {$tableName}\n";

                // Get table structure (simplified)
                $sql .= "DROP TABLE IF EXISTS \"{$tableName}\" CASCADE;\n";

                // Get CREATE TABLE statement (simplified approach)
                $columns = $connection->select(
                    "SELECT column_name, data_type, is_nullable, column_default 
                     FROM information_schema.columns 
                     WHERE table_name = ? AND table_schema = 'public' 
                     ORDER BY ordinal_position",
                    [$tableName]
                );

                $sql .= "CREATE TABLE \"{$tableName}\" (\n";
                $columnDefinitions = [];

                foreach ($columns as $column) {
                    $def = "  \"{$column->column_name}\" {$column->data_type}";
                    if ($column->is_nullable === 'NO') {
                        $def .= ' NOT NULL';
                    }
                    if ($column->column_default) {
                        $def .= " DEFAULT {$column->column_default}";
                    }
                    $columnDefinitions[] = $def;
                }

                $sql .= implode(",\n", $columnDefinitions);
                $sql .= "\n);\n\n";

                // Get table data
                $rows = $connection->table($tableName)->get();

                if ($rows->count() > 0) {
                    $sql .= "-- Data for table {$tableName}\n";

                    foreach ($rows as $row) {
                        $values = [];
                        foreach ((array) $row as $value) {
                            if (is_null($value)) {
                                $values[] = 'NULL';
                            } elseif (is_string($value)) {
                                $values[] = "'".str_replace("'", "''", $value)."'";
                            } elseif (is_bool($value)) {
                                $values[] = $value ? 'TRUE' : 'FALSE';
                            } else {
                                $values[] = $value;
                            }
                        }

                        $columnNames = array_keys((array) $row);
                        $quotedColumns = array_map(fn ($col) => "\"{$col}\"", $columnNames);

                        $sql .= "INSERT INTO \"{$tableName}\" (".implode(', ', $quotedColumns).') VALUES ('.implode(', ', $values).");\n";
                    }

                    $sql .= "\n";
                }
            }

            // Write to file
            File::put($backupPath, $sql);

        } catch (Exception $e) {
            Log::error('Laravel-based backup failed', [
                'connection' => $connectionName,
                'error' => $e->getMessage(),
            ]);
            throw new Exception(__('Laravel-based backup failed: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Check if psql is available on the system
     */
    private function isPsqlAvailable(): bool
    {
        try {
            $result = Process::timeout(5)->run('psql --version');

            return $result->successful();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Truncate all tables in the tenant database
     */
    private function truncateAllTables(string $connectionName, string $driver): void
    {
        try {
            $connection = DB::connection($connectionName);

            Log::info('Truncating all tables', [
                'connection' => $connectionName,
                'driver' => $driver,
            ]);

            if ($driver === 'pgsql') {
                // PostgreSQL: Get all table names from public schema
                $tables = $connection->select(
                    "SELECT tablename FROM pg_tables WHERE schemaname = 'public' AND tablename NOT IN ('migrations') ORDER BY tablename"
                );

                Log::info('Found tables to truncate', [
                    'connection' => $connectionName,
                    'table_count' => count($tables),
                    'tables' => array_column($tables, 'tablename'),
                ]);

                if (count($tables) > 0) {
                    // Build a single TRUNCATE statement with CASCADE to handle foreign keys
                    $tableNames = array_map(function ($table) {
                        return '"'.$table->tablename.'"';
                    }, $tables);

                    $truncateStatement = 'TRUNCATE TABLE '.implode(', ', $tableNames).' RESTART IDENTITY CASCADE';

                    Log::info('Executing truncate statement', [
                        'connection' => $connectionName,
                        'statement' => $truncateStatement,
                    ]);

                    $connection->statement($truncateStatement);
                }
            } else {
                // MySQL: Get all table names
                $tables = $connection->select('SHOW TABLES');

                Log::info('Found tables to truncate', [
                    'connection' => $connectionName,
                    'table_count' => count($tables),
                ]);

                if (count($tables) > 0) {
                    $connection->statement('SET FOREIGN_KEY_CHECKS = 0');

                    foreach ($tables as $table) {
                        $tableName = array_values((array) $table)[0];
                        // Skip system tables
                        if (! in_array($tableName, ['migrations'])) {
                            $connection->statement("TRUNCATE TABLE `{$tableName}`");
                        }
                    }

                    $connection->statement('SET FOREIGN_KEY_CHECKS = 1');
                }
            }

            Log::info('All tables truncated successfully', [
                'connection' => $connectionName,
                'table_count' => count($tables),
            ]);

        } catch (Exception $e) {
            Log::error('Failed to truncate tables', [
                'connection' => $connectionName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception(__('Failed to truncate tables: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Execute Laravel-based restore when psql is not available
     */
    private function executeLaravelBasedRestore(string $connectionName, string $backupPath): void
    {
        try {
            $connection = DB::connection($connectionName);

            Log::info('Starting Laravel-based restore', [
                'connection' => $connectionName,
                'backup_path' => $backupPath,
            ]);

            // Read the backup file
            $sql = File::get($backupPath);

            // Detect backup format
            $isLaravelBackup = str_contains($sql, '-- Laravel-based PostgreSQL Backup');

            Log::info('Backup format detected', [
                'connection' => $connectionName,
                'is_laravel_backup' => $isLaravelBackup,
                'file_size' => strlen($sql),
            ]);

            if ($isLaravelBackup) {
                // Handle Laravel-generated backup
                $this->restoreLaravelBackupFormat($connection, $sql, $connectionName);
            } else {
                // Handle pg_dump backup with Laravel
                $this->restorePgDumpBackupFormat($connection, $sql, $connectionName);
            }

            Log::info('Laravel-based restore completed successfully', [
                'connection' => $connectionName,
            ]);

        } catch (Exception $e) {
            Log::error('Laravel-based restore failed', [
                'connection' => $connectionName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception(__('Laravel-based restore failed: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Restore from Laravel-generated backup format
     */
    private function restoreLaravelBackupFormat($connection, string $sql, string $connectionName): void
    {
        // Split into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        $connection->beginTransaction();

        $executedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        try {
            foreach ($statements as $statement) {
                if (empty($statement) || str_starts_with($statement, '--')) {
                    $skippedCount++;

                    continue; // Skip empty statements and comments
                }

                // Execute only data manipulation statements (INSERT, UPDATE, etc.)
                $upperStatement = strtoupper(trim($statement));

                if (str_starts_with($upperStatement, 'INSERT') ||
                    str_starts_with($upperStatement, 'UPDATE') ||
                    str_starts_with($upperStatement, 'ALTER SEQUENCE') ||
                    str_starts_with($upperStatement, 'SELECT SETVAL')) {
                    try {
                        $connection->statement($statement);
                        $executedCount++;
                    } catch (Exception $e) {
                        $errorCount++;
                        Log::warning('Failed to execute statement during Laravel backup restore', [
                            'connection' => $connectionName,
                            'statement' => substr($statement, 0, 200).'...', // Log first 200 chars
                            'error' => $e->getMessage(),
                        ]);
                        // Continue with other statements instead of failing completely
                    }
                } else {
                    $skippedCount++;
                    Log::debug('Skipping statement during Laravel backup restore', [
                        'statement_type' => explode(' ', $upperStatement)[0],
                        'connection' => $connectionName,
                    ]);
                }
            }

            $connection->commit();

            Log::info('Laravel backup restore statistics', [
                'connection' => $connectionName,
                'total_statements' => count($statements),
                'executed' => $executedCount,
                'skipped' => $skippedCount,
                'errors' => $errorCount,
            ]);

        } catch (Exception $e) {
            try {
                $connection->rollBack();
            } catch (Exception $rollbackError) {
                Log::warning('Failed to rollback transaction during restore error', [
                    'connection' => $connectionName,
                    'rollback_error' => $rollbackError->getMessage(),
                ]);
            }
            throw $e;
        }
    }

    /**
     * Restore from pg_dump backup format using Laravel
     */
    private function restorePgDumpBackupFormat($connection, string $sql, string $connectionName): void
    {
        // For pg_dump format, we need to be more careful about statement execution order
        // Split by semicolons but be smarter about it
        $statements = $this->parsePgDumpStatements($sql);

        $connection->beginTransaction();

        $executedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        try {
            foreach ($statements as $statement) {
                $trimmedStatement = trim($statement);

                if (empty($trimmedStatement) || str_starts_with($trimmedStatement, '--')) {
                    $skippedCount++;

                    continue;
                }

                $upperStatement = strtoupper($trimmedStatement);

                // Skip certain statements that might cause issues
                if (str_starts_with($upperStatement, 'DROP TABLE') ||
                    str_starts_with($upperStatement, 'DROP SEQUENCE') ||
                    str_starts_with($upperStatement, 'CREATE TABLE') ||
                    str_starts_with($upperStatement, 'CREATE SEQUENCE') ||
                    str_starts_with($upperStatement, 'CREATE INDEX') ||
                    str_starts_with($upperStatement, 'ALTER TABLE') ||
                    str_starts_with($upperStatement, 'SET ') ||
                    str_starts_with($upperStatement, 'SELECT PG_CATALOG')) {
                    $skippedCount++;
                    Log::debug('Skipping DDL/system statement during pg_dump restore', [
                        'statement_type' => explode(' ', $upperStatement)[0],
                        'connection' => $connectionName,
                    ]);

                    continue;
                }

                // Execute data manipulation statements
                if (str_starts_with($upperStatement, 'INSERT') ||
                    str_starts_with($upperStatement, 'UPDATE') ||
                    str_starts_with($upperStatement, 'COPY') ||
                    str_starts_with($upperStatement, 'SELECT SETVAL')) {
                    try {
                        $connection->statement($trimmedStatement);
                        $executedCount++;
                    } catch (Exception $e) {
                        $errorCount++;
                        Log::warning('Failed to execute statement during pg_dump restore', [
                            'connection' => $connectionName,
                            'statement' => substr($trimmedStatement, 0, 200).'...', // Log first 200 chars
                            'error' => $e->getMessage(),
                        ]);
                        // Continue with other statements instead of failing completely
                    }
                } else {
                    $skippedCount++;
                }
            }

            $connection->commit();

            Log::info('pg_dump backup restore statistics', [
                'connection' => $connectionName,
                'total_statements' => count($statements),
                'executed' => $executedCount,
                'skipped' => $skippedCount,
                'errors' => $errorCount,
            ]);

        } catch (Exception $e) {
            try {
                $connection->rollBack();
            } catch (Exception $rollbackError) {
                Log::warning('Failed to rollback transaction during restore error', [
                    'connection' => $connectionName,
                    'rollback_error' => $rollbackError->getMessage(),
                ]);
            }
            throw $e;
        }
    }

    /**
     * Parse pg_dump statements more intelligently
     */
    private function parsePgDumpStatements(string $sql): array
    {
        // Split by semicolon but handle multi-line statements properly
        $lines = explode("\n", $sql);
        $statements = [];
        $currentStatement = '';
        $inCopyBlock = false;

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            // Handle COPY statements specially
            if (str_starts_with(strtoupper($trimmedLine), 'COPY ')) {
                $inCopyBlock = true;
                $currentStatement .= $line."\n";

                continue;
            }

            if ($inCopyBlock) {
                $currentStatement .= $line."\n";
                if ($trimmedLine === '\\.' || $trimmedLine === '') {
                    $inCopyBlock = false;
                    if (! empty(trim($currentStatement))) {
                        $statements[] = trim($currentStatement);
                    }
                    $currentStatement = '';
                }

                continue;
            }

            // Regular statement handling
            $currentStatement .= $line."\n";

            // If line ends with semicolon and we're not in a function/procedure definition
            if (str_ends_with($trimmedLine, ';') && ! $this->isInFunctionDefinition($currentStatement)) {
                if (! empty(trim($currentStatement))) {
                    $statements[] = trim($currentStatement);
                }
                $currentStatement = '';
            }
        }

        // Add any remaining statement
        if (! empty(trim($currentStatement))) {
            $statements[] = trim($currentStatement);
        }

        return array_filter($statements, fn ($stmt) => ! empty(trim($stmt)));
    }

    /**
     * Check if we're inside a function definition
     */
    private function isInFunctionDefinition(string $statement): bool
    {
        $upper = strtoupper($statement);
        $createFunctionCount = substr_count($upper, 'CREATE FUNCTION') + substr_count($upper, 'CREATE OR REPLACE FUNCTION');
        $dollarQuoteCount = substr_count($statement, '$$');

        // If we have an odd number of $$ quotes, we're likely inside a function
        return $createFunctionCount > 0 && ($dollarQuoteCount % 2 !== 0);
    }

    /**
     * Terminate active connections to a PostgreSQL database before dropping it
     */
    private function terminateActiveConnections(string $databaseName, string $templateConnection): void
    {
        try {
            // Use the main PostgreSQL connection (not the tenant connection)
            $mainConnection = DB::connection($templateConnection);

            Log::info('Terminating active connections to database', [
                'database' => $databaseName,
            ]);

            // First, try to terminate connections gracefully
            $terminateQuery = '
                SELECT pg_terminate_backend(pid) 
                FROM pg_stat_activity 
                WHERE datname = ? 
                  AND pid <> pg_backend_pid()
            ';

            $result = $mainConnection->select($terminateQuery, [$databaseName]);

            Log::info('Terminated active connections', [
                'database' => $databaseName,
                'terminated_count' => count($result),
            ]);

            // Wait a moment for connections to close
            sleep(1);

        } catch (Exception $e) {
            Log::warning('Failed to terminate active connections', [
                'database' => $databaseName,
                'error' => $e->getMessage(),
            ]);

            // Don't throw here - let the drop operation handle the error
            // The user will get the original error message which is more informative
        }
    }
}
