<?php

namespace App\Providers;

use Filament\Forms\Components\Select;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Cache for column types to avoid repeated database queries
     */
    private static array $columnTypeCache = [];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Select (Form) macro for searching by specific columns (including JSON)
        Select::macro('searchableBy', function (array|string $columns, ?string $modelClass = null, int $limit = 50, ?\Closure $labelFormatter = null) {
            /** @var Select $this */
            $columns = \is_array($columns) ? $columns : [$columns];

            if (! $modelClass && ($relationshipName = $this->getRelationshipName())) {
                $modelClass = '\\App\\Models\\'.ucfirst($relationshipName);
            }

            $select = $this
                ->searchable()
                ->getSearchResultsUsing(function (string $search) use ($columns, $limit, $modelClass, $labelFormatter) {
                    if (! $modelClass || ! class_exists($modelClass)) {
                        return collect();
                    }

                    $query = $modelClass::query()
                        ->when(\method_exists($modelClass, 'scopeActive'), fn ($q) => $q->active());

                    $query->where(function ($q) use ($columns, $search) {
                        foreach ($columns as $column) {
                            $q->orWhere(function ($subQuery) use ($column, $search) {
                                $subQuery->where($column, 'like', "%{$search}%");

                                if (Str::contains($column, 'phone')) {
                                    $flatSearch = str_replace([' ', '-', '(', ')', '+'], '', $search);

                                    $subQuery->orWhereRaw(
                                        "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER({$column}), ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') LIKE ?",
                                        ['%'.strtolower($flatSearch).'%']
                                    );
                                }
                            });
                        }
                    });

                    $results = $query->limit($limit)->get();

                    if ($labelFormatter) {
                        return $results->mapWithKeys(fn ($record) => [$record->id => $labelFormatter($record)]);
                    }

                    return $results->pluck($columns[0], 'id');
                });

            if ($labelFormatter) {
                $select->getOptionLabelFromRecordUsing($labelFormatter);
            }

            return $select;
        });

        // Register SelectFilter macro for searching by specific columns (including JSON)
        SelectFilter::macro('searchableBy', function (array|string $columns, ?string $modelClass = null, int $limit = 50, ?\Closure $labelFormatter = null) {
            /** @var SelectFilter $this */
            $columns = \is_array($columns) ? $columns : [$columns];

            if (! $modelClass && ($relationshipName = $this->getRelationshipName())) {
                $modelClass = '\\App\\Models\\'.ucfirst($relationshipName);
            }

            $filter = $this
                ->searchable()
                ->getSearchResultsUsing(function (string $search) use ($columns, $limit, $modelClass, $labelFormatter) {
                    if (! $modelClass || ! class_exists($modelClass)) {
                        return collect();
                    }

                    $query = $modelClass::query()
                        ->when(\method_exists($modelClass, 'scopeActive'), fn ($q) => $q->active());

                    $query->where(function ($q) use ($columns, $search) {
                        foreach ($columns as $column) {
                            $q->orWhere(function ($subQuery) use ($column, $search) {
                                $subQuery->where($column, 'like', "%{$search}%");

                                if (Str::contains($column, 'phone')) {
                                    $flatSearch = str_replace([' ', '-', '(', ')', '+'], '', $search);

                                    $subQuery->orWhereRaw(
                                        "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER({$column}), ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') LIKE ?",
                                        ['%'.strtolower($flatSearch).'%']
                                    );
                                }
                            });
                        }
                    });

                    $results = $query->limit($limit)->get();

                    if ($labelFormatter) {
                        return $results->mapWithKeys(fn ($record) => [$record->id => $labelFormatter($record)]);
                    }

                    return $results->pluck($columns[0], 'id');
                });

            if ($labelFormatter) {
                $filter->getOptionLabelFromRecordUsing($labelFormatter);
            }

            return $filter;
        });

        // Custom macro for sorting by JSON relationship columns - detects export context
        Builder::macro('orderByJsonRelation', function (string $relationPath, string $direction = 'asc') {
            $locale = app()->getLocale();

            // Parse the relation path (e.g., 'branch.name' or 'department.name')
            $relationParts = explode('.', $relationPath);
            $relationName = $relationParts[0]; // e.g., 'branch'
            $columnName = $relationParts[1]; // e.g., 'name'

            // Get the relationship from the builder
            $relation = $this->getRelation($relationName);
            $relationTable = $relation->getRelated()->getTable();
            $relationKey = $relation->getForeignKeyName();
            $localKey = $relation->getOwnerKeyName();
            $model = $this->getModel();
            $relatedModel = $relation->getRelated();

            // Check if we're in an export context by looking at the query
            $isExportContext = $this->getQuery()->from === $model->getTable() &&
                              ! $this->getQuery()->joins;

            if ($isExportContext) {
                // For export contexts, sort by foreign key to avoid join issues
                return $this->orderBy("{$model->getTable()}.{$relationKey}", $direction);
            }

            // For normal table display, use the full join-based sorting
            // Check if the column is JSON using cached information
            $isJsonColumn = MacroServiceProvider::isJsonColumn($relatedModel, $relationTable, $columnName);

            if (! $isJsonColumn) {
                // Not a JSON column, use regular ordering
                $orderByColumn = "{$relationTable}.{$columnName}";
            } else {
                // Build the order by clause based on database type
                $connection = $relatedModel->getConnection();
                $driver = $connection->getDriverName();

                switch ($driver) {
                    case 'pgsql':
                        $orderByColumn = "{$relationTable}.{$columnName}->>'{$locale}'";
                        break;
                    case 'mysql':
                        $orderByColumn = "JSON_UNQUOTE(JSON_EXTRACT({$relationTable}.{$columnName}, '$.{$locale}'))";
                        break;
                    case 'sqlite':
                        $orderByColumn = "json_extract({$relationTable}.{$columnName}, '$.{$locale}')";
                        break;
                    default:
                        // Fallback to regular column ordering for unsupported databases
                        $orderByColumn = "{$relationTable}.{$columnName}";
                }
            }

            return $this
                ->leftJoin($relationTable, "{$model->getTable()}.{$relationKey}", '=', "{$relationTable}.{$localKey}")
                ->whereNull($relationTable.'.deleted_at')
                ->orderByRaw("COALESCE({$orderByColumn}, '') {$direction}")
                ->select("{$model->getTable()}.*");
        });

        // Custom macro for sorting by JSON columns on the current model
        Builder::macro('orderByJsonColumn', function (string $columnName, string $direction = 'asc') {
            $locale = app()->getLocale();
            $model = $this->getModel();
            $table = $model->getTable();
            $connection = $model->getConnection();

            // Check if the column is JSON using cached information
            $isJsonColumn = MacroServiceProvider::isJsonColumn($model, $table, $columnName);

            if (! $isJsonColumn) {
                // Not a JSON column, use regular ordering
                return $this->orderBy("{$table}.{$columnName}", $direction);
            }

            // Build the order by clause based on database type
            $driver = $connection->getDriverName();

            switch ($driver) {
                case 'pgsql':
                    $orderByColumn = "{$table}.{$columnName}->>'{$locale}'";
                    break;
                case 'mysql':
                    $orderByColumn = "JSON_UNQUOTE(JSON_EXTRACT({$table}.{$columnName}, '$.{$locale}'))";
                    break;
                case 'sqlite':
                    $orderByColumn = "json_extract({$table}.{$columnName}, '$.{$locale}')";
                    break;
                default:
                    // Fallback to regular column ordering for unsupported databases
                    return $this->orderBy("{$table}.{$columnName}", $direction);
            }

            return $this->orderByRaw("COALESCE({$orderByColumn}, '') {$direction}");
        });
    }

    /**
     * Check if a column is JSON type using caching
     */
    public static function isJsonColumn($model, string $table, string $column): bool
    {
        $cacheKey = "column_type_{$table}_{$column}";

        // Check static cache first (fastest)
        if (isset(self::$columnTypeCache[$cacheKey])) {
            return self::$columnTypeCache[$cacheKey];
        }

        // Check Laravel cache (persistent across requests)
        $cachedResult = Cache::remember($cacheKey, 3600, function () use ($model, $table, $column) {
            try {
                $connection = $model->getConnection();
                $columnType = $connection->getSchemaBuilder()->getColumnType($table, $column);

                return in_array(strtolower($columnType), ['json', 'jsonb']);
            } catch (\Exception $e) {
                // If we can't determine the column type, assume it's not JSON
                return false;
            }
        });

        // Store in static cache for this request
        self::$columnTypeCache[$cacheKey] = $cachedResult;

        return $cachedResult;
    }
}
