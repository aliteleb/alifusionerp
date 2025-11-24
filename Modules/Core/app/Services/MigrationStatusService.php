<?php

namespace Modules\Core\Services;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrationStatusService
{
    public function getMigrationStatus(string $connectionName): array
    {
        // Get migration status using Artisan
        $exitCode = Artisan::call('migrate:status', [
            '--database' => $connectionName,
            '--path' => 'Modules/Core/database/migrations',
        ]);

        $output = Artisan::output();

        // Try alternative command if the first one fails or returns no useful output
        if (empty(trim($output)) || $exitCode !== 0) {
            Log::info('First migrate:status failed, trying alternative approach');

            // Try without --path parameter
            $exitCode = Artisan::call('migrate:status', [
                '--database' => $connectionName,
            ]);
            $output = Artisan::output();
        }

        Log::info('Migration status output', [
            'exit_code' => $exitCode,
            'raw_output' => $output,
            'output_length' => strlen($output),
        ]);

        // If no output or empty, provide fallback
        if (empty(trim($output))) {
            $output = "No migrations found or command failed.\nExit code: $exitCode";
            $statusInfo = [
                'ran' => 0,
                'pending' => 0,
                'total' => 0,
                'summary' => __('No migration information available'),
                'migrations' => [],
                'lastRun' => null,
            ];
        } else {
            // Parse the output to get detailed migration information
            $statusInfo = $this->parseMigrationStatus($output);
        }

        $formattedOutput = $this->formatMigrationOutput($output);

        return [
            'statusInfo' => $statusInfo,
            'formattedOutput' => $formattedOutput,
            'rawOutput' => $output,
        ];
    }

    private function parseMigrationStatus(string $output): array
    {
        $lines = explode("\n", $output);
        $ran = 0;
        $pending = 0;
        $migrations = [];
        $lastRun = null;

        Log::info('Parsing migration status', [
            'line_count' => count($lines),
            'first_few_lines' => array_slice($lines, 0, 5),
        ]);

        // Look for different output patterns
        $foundMigrationData = false;
        $inTableSection = false;

        foreach ($lines as $lineIndex => $line) {
            $originalLine = $line;
            $line = trim($line);

            // Skip completely empty lines
            if (empty($line)) {
                continue;
            }

            // Detect table headers - various formats
            if (preg_match('/ran\?|migration\s*name|status/i', $line)) {
                $inTableSection = true;
                Log::info('Found table header', ['line' => $line]);

                continue;
            }

            // Skip separator lines
            if (preg_match('/^[+\-=|\s]+$/', $line)) {
                continue;
            }

            // Parse migration status line - Fixed logic
            if (preg_match('/^\|?\s*\[([YN])\]\s*\|?\s*(.+?)\s*(?:\|\s*(.+?)\s*\|?)?$/i', $line, $matches)) {
                $foundMigrationData = true;
                $status = strtoupper($matches[1]);
                $migrationName = trim($matches[2]);
                $batchInfo = isset($matches[3]) ? trim($matches[3]) : '';

                // Clean up migration name (remove extra pipes and whitespace)
                $migrationName = trim($migrationName, '| ');

                $isRan = $status === 'Y';

                $migrations[] = [
                    'name' => $migrationName,
                    'status' => $status,
                    'batch' => $batchInfo,
                    'ran' => $isRan,
                ];

                // Fixed counting logic
                if ($isRan) {
                    $ran++;
                    $lastRun = $migrationName;
                } else {
                    $pending++;
                }

                Log::info('Parsed migration', [
                    'name' => $migrationName,
                    'status' => $status,
                    'ran' => $isRan,
                ]);
            }
        }

        // If no migrations found, try to get info from the database directly
        if (! $foundMigrationData) {
            Log::info('No migration data found in output, trying database query');
            $migrations = $this->getMigrationsFromDatabase();
            $ran = collect($migrations)->where('ran', true)->count();
            $pending = collect($migrations)->where('ran', false)->count();
            $lastRun = collect($migrations)->where('ran', true)->last()['name'] ?? null;
        }

        Log::info('Migration parsing results', [
            'found_migration_data' => $foundMigrationData,
            'ran' => $ran,
            'pending' => $pending,
            'migrations_count' => count($migrations),
        ]);

        // Generate summary message
        if ($pending > 0) {
            $summary = __(':pending migrations pending, :ran migrations completed', [
                'pending' => $pending,
                'ran' => $ran,
            ]);
        } elseif ($ran > 0) {
            $summary = __('All :total migrations completed successfully', ['total' => $ran]);
        } else {
            $summary = __('No migrations found or migration table not initialized');
        }

        return [
            'ran' => $ran,
            'pending' => $pending,
            'total' => $ran + $pending,
            'summary' => $summary,
            'migrations' => $migrations,
            'lastRun' => $lastRun,
        ];
    }

    private function getMigrationsFromDatabase(): array
    {
        try {
            // Get all migration files from the Core module migrations directory
            $migrationPath = module_path('Core', 'database/migrations');
            $migrations = [];

            if (! is_dir($migrationPath)) {
                Log::warning('Tenant migrations directory not found', ['path' => $migrationPath]);

                return [];
            }

            $files = glob($migrationPath.'/*.php');

            // Get ran migrations from database
            $ranMigrations = [];
            try {
                $ranMigrations = DB::table('migrations')
                    ->pluck('migration')
                    ->toArray();
            } catch (Exception $e) {
                Log::warning('Could not query migrations table', ['error' => $e->getMessage()]);
            }

            foreach ($files as $file) {
                $filename = basename($file, '.php');
                $isRan = in_array($filename, $ranMigrations);

                $migrations[] = [
                    'name' => $filename,
                    'status' => $isRan ? 'Y' : 'N',
                    'batch' => '', // Would need additional query to get batch info
                    'ran' => $isRan,
                ];
            }

            Log::info('Retrieved migrations from database', [
                'total_files' => count($files),
                'ran_migrations' => count($ranMigrations),
                'migrations' => $migrations,
            ]);

            return $migrations;
        } catch (Exception $e) {
            Log::error('Error getting migrations from database', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    private function formatMigrationOutput(string $output): string
    {
        $lines = explode("\n", $output);
        $formattedLines = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Color code the status indicators
            if (str_contains($line, '[Y]')) {
                $line = str_replace('[Y]', '\033[32m[✓]\033[0m', $line); // Green checkmark
            } elseif (str_contains($line, '[N]')) {
                $line = str_replace('[N]', '\033[33m[⏳]\033[0m', $line); // Yellow pending
            }

            // Format headers
            if (str_contains($line, 'Migration name') || str_contains($line, '----')) {
                $line = "\033[36m$line\033[0m"; // Cyan headers
            }

            $formattedLines[] = $line;
        }

        return implode("\n", $formattedLines);
    }
}
