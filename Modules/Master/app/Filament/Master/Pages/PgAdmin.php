<?php

namespace Modules\Master\Filament\Master\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class PgAdmin extends Page
{
    use WithPagination;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';

    protected string $view = 'filament.master.pages.pg-admin';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'PgAdmin';

    protected static ?string $navigationLabel = 'PgAdmin';

    public ?string $selectedDatabase = null;

    public ?string $selectedTable = null;

    public string $sqlQuery = '';

    public array $queryResults = [];

    public bool $showQueryResults = false;

    public array $tableData = [];

    public array $tableStructure = [];

    public int $perPage = 25;

    public array $expandedDatabases = [];

    public array $databaseTables = [];

    public bool $isLoadingTableData = false;

    public bool $dataLoaded = false;

    public ?int $pageInput = null;

    private array $cachedRowCounts = [];

    private array $cachedPrimaryKeys = [];

    public static function getNavigationLabel(): string
    {
        return __('PgAdmin');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Database Management');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public function getTitle(): string
    {
        return __('PgAdmin');
    }

    public function getHeading(): string
    {
        return __('');
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'pgadmin';
    }

    protected ?string $heading = '';

    public function mount(): void
    {
        $startTime = microtime(true);
        Log::info('[PgAdmin] Mount started');

        $this->loadDatabases();

        $this->restoreFromUrlParams();

        $duration = round((microtime(true) - $startTime) * 1000, 2);
        Log::info("[PgAdmin] Mount completed in {$duration}ms");
    }

    protected function restoreFromUrlParams(): void
    {
        $request = request();
        $database = $request->get('database');
        $table = $request->get('table');
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 25);

        if ($database && $table) {
            // Validate database exists
            if (! $this->validateDatabase($database)) {
                return;
            }

            // Set pagination first
            $this->perPage = (int) $perPage;
            $this->setPage((int) $page);

            // Restore expanded databases state
            if (! in_array($database, $this->expandedDatabases)) {
                $this->expandedDatabases[] = $database;
                $this->loadTablesForDatabase($database);
            }

            // Validate table exists in the database
            if (! $this->validateTable($database, $table)) {
                return;
            }

            // Select the table
            $this->selectedDatabase = $database;
            $this->selectedTable = $table;
            $this->isLoadingTableData = true;
            $this->dataLoaded = false;

            // Clear previous data
            $this->tableData = [];
            $this->tableStructure = [];
            $this->showQueryResults = false;
            $this->queryResults = [];

            // Use the async loading method to ensure proper loading state management
            $this->loadTableDataAsync();
        }
    }

    protected function validateDatabase(string $database): bool
    {
        try {
            $databases = $this->databases();

            return $databases->contains($database);
        } catch (\Exception $e) {
            Log::error("[PgAdmin] Database validation error: {$e->getMessage()}");

            return false;
        }
    }

    protected function validateTable(string $database, string $table): bool
    {
        try {
            if (! isset($this->databaseTables[$database])) {
                $this->loadTablesForDatabase($database);
            }

            return in_array($table, $this->databaseTables[$database] ?? []);
        } catch (\Exception $e) {
            Log::error("[PgAdmin] Table validation error: {$e->getMessage()}");

            return false;
        }
    }

    protected function updateUrl(): void
    {
        $params = [];

        if ($this->selectedDatabase) {
            $params['database'] = $this->selectedDatabase;
        }

        if ($this->selectedTable) {
            $params['table'] = $this->selectedTable;
        }

        if ($this->getPage() > 1) {
            $params['page'] = $this->getPage();
        }

        if ($this->perPage !== 25) {
            $params['per_page'] = $this->perPage;
        }

        // Use the correct page URL instead of current URL
        $url = static::getUrl();
        if (! empty($params)) {
            $url .= '?'.http_build_query($params);
        }

        $this->dispatch('url-updated', ['url' => $url]);
    }

    #[Computed]
    public function databases(): Collection
    {
        $startTime = microtime(true);

        try {
            $databases = Cache::remember('pgadmin_databases_list', 60, function () {
                Log::info('[PgAdmin] Fetching databases list from DB');

                return collect(DB::select("
                    SELECT datname as name 
                    FROM pg_database 
                    WHERE datistemplate = false 
                    AND datname != 'postgres'
                    ORDER BY datname
                "))->pluck('name');
            });

            $duration = round((microtime(true) - $startTime) * 1000, 2);
            Log::info("[PgAdmin] Databases list loaded in {$duration}ms (count: {$databases->count()})");

            return $databases;
        } catch (\Exception $e) {
            Log::error("[PgAdmin] Error loading databases: {$e->getMessage()}");
            Notification::make()
                ->title(__('Error loading databases'))
                ->body($e->getMessage())
                ->danger()
                ->send();

            return collect();
        }
    }

    public function toggleDatabase(string $database): void
    {
        if (in_array($database, $this->expandedDatabases)) {
            $this->expandedDatabases = array_filter($this->expandedDatabases, fn ($db) => $db !== $database);
            unset($this->databaseTables[$database]);

            // If the collapsed database contains the currently selected table, clear selection
            if ($this->selectedDatabase === $database) {
                $this->selectedDatabase = null;
                $this->selectedTable = null;
                $this->tableData = [];
                $this->tableStructure = [];
                $this->updateUrl();
            }
        } else {
            $this->expandedDatabases[] = $database;
            $this->loadTablesForDatabase($database);
        }
    }

    public function loadTablesForDatabase(string $database): void
    {
        try {
            $this->databaseTables[$database] = Cache::remember("pgadmin_tables_{$database}", 60, function () use ($database) {
                $connectionName = config('database.default');
                $originalDatabase = config("database.connections.{$connectionName}.database");

                config(["database.connections.{$connectionName}.database" => $database]);
                DB::purge($connectionName);

                $tables = DB::select("
                    SELECT tablename as name
                    FROM pg_tables 
                    WHERE schemaname = 'public'
                    ORDER BY tablename
                ");

                config(["database.connections.{$connectionName}.database" => $originalDatabase]);
                DB::purge($connectionName);

                return collect($tables)->pluck('name')->toArray();
            });

        } catch (\Exception $e) {
            Log::error("[PgAdmin] Error loading tables for {$database}: {$e->getMessage()}");
            Notification::make()
                ->title(__('Error loading tables for database: :database', ['database' => $database]))
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->databaseTables[$database] = [];
        }
    }

    public function selectTable(string $database, string $table): void
    {
        $this->resetPage();

        $this->isLoadingTableData = true;
        $this->dataLoaded = false;

        $this->selectedDatabase = $database;
        $this->selectedTable = $table;
        $this->tableData = [];
        $this->tableStructure = [];
        $this->showQueryResults = false;
        $this->queryResults = [];

        $cacheKey = "{$database}.{$table}";
        unset($this->cachedRowCounts[$cacheKey]);
        unset($this->cachedPrimaryKeys[$cacheKey]);

        // Update URL with new selection
        $this->updateUrl();

        // Dispatch browser event to refresh UI immediately
        $this->dispatch('table-selected', [
            'database' => $database,
            'table' => $table,
            'loading' => true,
        ]);

        $this->dispatch('$refresh');
        $this->loadTableDataAsync();
    }

    public function clearSelection(): void
    {
        $this->selectedDatabase = null;
        $this->selectedTable = null;
        $this->tableData = [];
        $this->tableStructure = [];
        $this->showQueryResults = false;
        $this->queryResults = [];
        $this->isLoadingTableData = false;
        $this->dataLoaded = false;
        $this->resetPage();
        $this->updateUrl();
    }

    public function loadTableDataAsync(): void
    {
        $this->loadTableData();
        $this->loadTableStructure();
        $this->isLoadingTableData = false;
        $this->dataLoaded = true;

        // Dispatch completion event
        $this->dispatch('table-data-loaded', [
            'database' => $this->selectedDatabase,
            'table' => $this->selectedTable,
            'rowCount' => count($this->tableData),
        ]);
    }

    public function loadDatabases(): void
    {
        $this->databases;
    }

    public function loadTableData(): void
    {
        if (! $this->selectedTable || ! $this->selectedDatabase) {
            $this->tableData = [];

            return;
        }

        try {
            // Use simpler connection approach
            $connectionName = config('database.default');
            $originalDatabase = config("database.connections.{$connectionName}.database");

            // Temporarily change the database
            config(["database.connections.{$connectionName}.database" => $this->selectedDatabase]);

            // Clear any existing connections to force new connection
            DB::purge($connectionName);

            $offset = ($this->getPage() - 1) * $this->perPage;
            $primaryKey = $this->getPrimaryKey();

            $data = DB::table($this->selectedTable)
                ->orderBy($primaryKey)
                ->offset($offset)
                ->limit($this->perPage)
                ->get()
                ->toArray();

            $this->tableData = json_decode(json_encode($data), true);

            // Restore original database config
            config(["database.connections.{$connectionName}.database" => $originalDatabase]);
            DB::purge($connectionName);

            Log::info('PgAdmin: Loaded '.count($this->tableData)." rows from {$this->selectedDatabase}.{$this->selectedTable}");

        } catch (\Exception $e) {
            Log::error('PgAdmin Error loading table data: '.$e->getMessage());

            // Make sure to restore original config even on error
            $connectionName = config('database.default');
            $originalDatabase = config("database.connections.{$connectionName}.database");
            config(["database.connections.{$connectionName}.database" => $originalDatabase]);
            DB::purge($connectionName);

            Notification::make()
                ->title(__('Error loading table data'))
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->tableData = [];
        }
    }

    public function loadTableStructure(): void
    {
        if (! $this->selectedTable || ! $this->selectedDatabase) {
            return;
        }

        try {
            $cacheKey = "pgadmin_structure_{$this->selectedDatabase}_{$this->selectedTable}";
            $this->tableStructure = Cache::remember($cacheKey, 300, function () {
                $connectionName = config('database.default');
                $originalDatabase = config("database.connections.{$connectionName}.database");

                config(["database.connections.{$connectionName}.database" => $this->selectedDatabase]);
                DB::purge($connectionName);

                $structure = DB::select("
                    SELECT 
                        column_name,
                        data_type,
                        is_nullable,
                        column_default,
                        character_maximum_length
                    FROM information_schema.columns 
                    WHERE table_name = ? 
                    AND table_schema = 'public'
                    ORDER BY ordinal_position
                ", [$this->selectedTable]);

                config(["database.connections.{$connectionName}.database" => $originalDatabase]);
                DB::purge($connectionName);

                return json_decode(json_encode($structure), true);
            });
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Error loading table structure'))
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->tableStructure = [];
        }
    }

    public function executeQuery(): void
    {
        if (empty(trim($this->sqlQuery))) {
            Notification::make()
                ->title(__('Error'))
                ->body(__('Please enter a SQL query'))
                ->warning()
                ->send();

            return;
        }

        try {
            $query = trim($this->sqlQuery);

            // Check if it's a SELECT query
            if (stripos($query, 'select') === 0) {
                $results = DB::select($query);
                $this->queryResults = json_decode(json_encode($results), true);
                $this->showQueryResults = true;

                Notification::make()
                    ->title(__('Query executed successfully'))
                    ->body(__('Found :count rows', ['count' => count($results)]))
                    ->success()
                    ->send();
            } else {
                // For non-SELECT queries
                $affected = DB::statement($query);
                $this->queryResults = [];
                $this->showQueryResults = false;

                Notification::make()
                    ->title(__('Query executed successfully'))
                    ->body(__('Query completed'))
                    ->success()
                    ->send();

                // Refresh table data if a table is selected
                if ($this->selectedTable) {
                    $this->loadTableData();
                }
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Query execution failed'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function saveRow(string $rowIndex, array $editingData): void
    {
        if (! $this->selectedTable) {
            return;
        }

        try {
            $index = (int) $rowIndex;
            $originalRow = $this->tableData[$index] ?? null;

            if (! $originalRow) {
                Notification::make()
                    ->title(__('Error'))
                    ->body(__('Row not found'))
                    ->danger()
                    ->send();

                return;
            }

            $primaryKey = $this->getPrimaryKey();

            if (! $primaryKey) {
                Notification::make()
                    ->title(__('Error'))
                    ->body(__('Cannot update row: No primary key found'))
                    ->danger()
                    ->send();

                return;
            }

            $connectionName = config('database.default');
            $originalDatabase = config("database.connections.{$connectionName}.database");

            config(["database.connections.{$connectionName}.database" => $this->selectedDatabase]);
            DB::purge($connectionName);

            $updates = [];
            $bindings = [];

            foreach ($editingData as $column => $value) {
                if ($column !== $primaryKey) {
                    $updates[] = "\"{$column}\" = ?";
                    $bindings[] = $value;
                }
            }

            $bindings[] = $originalRow[$primaryKey];

            $sql = "UPDATE {$this->selectedTable} SET ".implode(', ', $updates)." WHERE \"{$primaryKey}\" = ?";

            DB::statement($sql, $bindings);

            config(["database.connections.{$connectionName}.database" => $originalDatabase]);
            DB::purge($connectionName);

            $this->loadTableData();

            Notification::make()
                ->title(__('Success'))
                ->body(__('Row updated successfully'))
                ->success()
                ->send();
        } catch (\Exception $e) {
            $connectionName = config('database.default');
            $originalDatabase = config("database.connections.{$connectionName}.database");
            config(["database.connections.{$connectionName}.database" => $originalDatabase]);
            DB::purge($connectionName);

            Notification::make()
                ->title(__('Error updating row'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteRow($rowIndex): void
    {
        if (! $this->selectedTable) {
            return;
        }

        try {
            $row = $this->tableData[$rowIndex];
            $primaryKey = $this->getPrimaryKey();

            if (! $primaryKey) {
                Notification::make()
                    ->title(__('Error'))
                    ->body(__('Cannot delete row: No primary key found'))
                    ->danger()
                    ->send();

                return;
            }

            $connectionName = config('database.default');
            $originalDatabase = config("database.connections.{$connectionName}.database");

            config(["database.connections.{$connectionName}.database" => $this->selectedDatabase]);
            DB::purge($connectionName);

            DB::statement("DELETE FROM {$this->selectedTable} WHERE \"{$primaryKey}\" = ?", [$row[$primaryKey]]);

            config(["database.connections.{$connectionName}.database" => $originalDatabase]);
            DB::purge($connectionName);

            $this->loadTableData();

            Notification::make()
                ->title(__('Success'))
                ->body(__('Row deleted successfully'))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Error deleting row'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function bulkDeleteRows(array $rowIndexes): void
    {
        if (! $this->selectedTable || empty($rowIndexes)) {
            return;
        }

        try {
            $primaryKey = $this->getPrimaryKey();

            if (! $primaryKey) {
                Notification::make()
                    ->title(__('Error'))
                    ->body(__('Cannot delete rows: No primary key found'))
                    ->danger()
                    ->send();

                return;
            }

            $connectionName = config('database.default');
            $originalDatabase = config("database.connections.{$connectionName}.database");

            config(["database.connections.{$connectionName}.database" => $this->selectedDatabase]);
            DB::purge($connectionName);

            $primaryKeyValues = [];
            foreach ($rowIndexes as $index) {
                $index = (int) $index;
                if (isset($this->tableData[$index][$primaryKey])) {
                    $primaryKeyValues[] = $this->tableData[$index][$primaryKey];
                }
            }

            if (! empty($primaryKeyValues)) {
                $placeholders = implode(',', array_fill(0, count($primaryKeyValues), '?'));
                DB::statement("DELETE FROM {$this->selectedTable} WHERE \"{$primaryKey}\" IN ({$placeholders})", $primaryKeyValues);
            }

            config(["database.connections.{$connectionName}.database" => $originalDatabase]);
            DB::purge($connectionName);

            $cacheKey = "{$this->selectedDatabase}.{$this->selectedTable}";
            unset($this->cachedRowCounts[$cacheKey]);
            Cache::forget("pgadmin_count_{$this->selectedDatabase}_{$this->selectedTable}");

            $this->loadTableData();

            Notification::make()
                ->title(__('Success'))
                ->body(__('Deleted :count rows successfully', ['count' => count($primaryKeyValues)]))
                ->success()
                ->send();
        } catch (\Exception $e) {
            $connectionName = config('database.default');
            $originalDatabase = config("database.connections.{$connectionName}.database");
            config(["database.connections.{$connectionName}.database" => $originalDatabase]);
            DB::purge($connectionName);

            Notification::make()
                ->title(__('Error deleting rows'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function getPrimaryKey(): ?string
    {
        if (! $this->selectedTable || ! $this->selectedDatabase) {
            return null;
        }

        $cacheKey = "{$this->selectedDatabase}.{$this->selectedTable}";

        if (isset($this->cachedPrimaryKeys[$cacheKey])) {
            return $this->cachedPrimaryKeys[$cacheKey];
        }

        try {
            $pkCacheKey = "pgadmin_pk_{$this->selectedDatabase}_{$this->selectedTable}";
            $primaryKey = Cache::remember($pkCacheKey, 300, function () {
                $connectionName = config('database.default');
                $originalDatabase = config("database.connections.{$connectionName}.database");

                config(["database.connections.{$connectionName}.database" => $this->selectedDatabase]);
                DB::purge($connectionName);

                $result = DB::select("
                    SELECT column_name
                    FROM information_schema.table_constraints tc
                    JOIN information_schema.key_column_usage kcu 
                        ON tc.constraint_name = kcu.constraint_name
                    WHERE tc.table_name = ? 
                    AND tc.constraint_type = 'PRIMARY KEY'
                    AND tc.table_schema = 'public'
                ", [$this->selectedTable]);

                config(["database.connections.{$connectionName}.database" => $originalDatabase]);
                DB::purge($connectionName);

                return $result[0]->column_name ?? 'id';
            });

            $this->cachedPrimaryKeys[$cacheKey] = $primaryKey;

            return $primaryKey;
        } catch (\Exception $e) {
            return 'id';
        }
    }

    public function getTableRowCount(): int
    {
        if (! $this->selectedTable || ! $this->selectedDatabase) {
            return 0;
        }

        try {
            $cacheKey = "{$this->selectedDatabase}.{$this->selectedTable}";

            if (isset($this->cachedRowCounts[$cacheKey])) {
                return $this->cachedRowCounts[$cacheKey];
            }

            $countCacheKey = "pgadmin_count_{$this->selectedDatabase}_{$this->selectedTable}";
            $count = Cache::remember($countCacheKey, 30, function () {
                $connectionName = config('database.default');
                $originalDatabase = config("database.connections.{$connectionName}.database");

                config(["database.connections.{$connectionName}.database" => $this->selectedDatabase]);
                DB::purge($connectionName);

                $count = DB::table($this->selectedTable)->count();

                config(["database.connections.{$connectionName}.database" => $originalDatabase]);
                DB::purge($connectionName);

                return $count;
            });

            $this->cachedRowCounts[$cacheKey] = $count;

            return $count;
        } catch (\Exception $e) {
            // Make sure to restore original config even on error
            $connectionName = config('database.default');
            $originalDatabase = config("database.connections.{$connectionName}.database");
            config(["database.connections.{$connectionName}.database" => $originalDatabase]);
            DB::purge($connectionName);

            Log::error('PgAdmin Error getting row count: '.$e->getMessage());

            return 0;
        }
    }

    public function checkTableExists(): bool
    {
        if (! $this->selectedTable || ! $this->selectedDatabase) {
            return false;
        }

        try {
            $connectionName = config('database.default');
            $originalDatabase = config("database.connections.{$connectionName}.database");

            config(["database.connections.{$connectionName}.database" => $this->selectedDatabase]);
            DB::purge($connectionName);

            $result = DB::select(
                "SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_schema = 'public' 
                    AND table_name = ?
                ) as exists",
                [$this->selectedTable]
            );

            config(["database.connections.{$connectionName}.database" => $originalDatabase]);
            DB::purge($connectionName);

            return $result[0]->exists ?? false;
        } catch (\Exception $e) {
            Log::error('PgAdmin: Error checking table existence: '.$e->getMessage());

            return false;
        }
    }

    public function refreshTableData(): void
    {
        if ($this->selectedTable && $this->selectedDatabase) {
            $this->isLoadingTableData = true;
            $this->loadTableData();
            $this->loadTableStructure();
            $this->isLoadingTableData = false;

            Notification::make()
                ->title(__('Table data refreshed'))
                ->success()
                ->send();
        }
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->isLoadingTableData = true;
        $this->loadTableData();
        $this->isLoadingTableData = false;
        $this->updateUrl();
    }

    public function paginationView(): string
    {
        return 'filament.master.pages.pg-admin-pagination';
    }

    public function nextPage(): void
    {
        $this->setPage($this->getPage() + 1);
        $this->isLoadingTableData = true;
        $this->loadTableData();
        $this->isLoadingTableData = false;
        $this->updateUrl();
    }

    public function previousPage(): void
    {
        $this->setPage(max(1, $this->getPage() - 1));
        $this->isLoadingTableData = true;
        $this->loadTableData();
        $this->isLoadingTableData = false;
        $this->updateUrl();
    }

    public function gotoPage($page): void
    {
        $this->setPage($page);
        $this->isLoadingTableData = true;
        $this->loadTableData();
        $this->isLoadingTableData = false;
        $this->updateUrl();
    }

    public function getTotalPages(): int
    {
        $totalRows = $this->getTableRowCount();

        return (int) ceil($totalRows / $this->perPage);
    }
}
