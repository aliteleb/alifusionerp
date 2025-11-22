# Reusable Active Status Tabs Guide

This guide explains how to use the reusable tab traits to create consistent tab functionality across Filament resources automatically.

## Overview

We now have two specialized traits that automatically handle tabs and default tab selection:

1. **`HasActiveStatusTabs`** - For resources with All, Active, Inactive, Trashed tabs (or simplified All, Active, Trashed)
2. **`HasCustomStatusTabs`** - For resources with custom status-based tabs

## Usage

### 1. Automatic Usage (All, Active, Inactive, Trashed)

```php
<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use App\Traits\HasActiveStatusTabs;
use Filament\Resources\Pages\ListRecords;

class ListBranches extends ListRecords
{
    use HasActiveStatusTabs;

    protected static string $resource = BranchResource::class;

    // That's it! Tabs and default tab are automatically handled by the trait
}
```

### 2. Automatic Simplified Usage (All, Active, Trashed)

For resources that don't need the "Inactive" tab:

```php
<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Traits\HasActiveStatusTabs;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    use HasActiveStatusTabs;

    protected static string $resource = ClientResource::class;

    // Override to use simplified tabs (All, Active, Trashed only)
    protected function useSimplifiedTabs(): bool
    {
        return true;
    }
}
```

### 3. Automatic Custom Usage

For resources with custom status logic:

```php
<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use App\Traits\HasCustomStatusTabs;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTasks extends ListRecords
{
    use HasCustomStatusTabs;

    protected static string $resource = TaskResource::class;

    // Define your custom status configuration
    protected function getStatusConfig(): array
    {
        return [
            'all' => [
                'icon' => 'heroicon-o-squares-2x2',
                'badge' => static::getModel()::count(),
                'color' => 'primary',
            ],
            'not_started' => [
                'icon' => 'heroicon-o-clock',
                'badge' => static::getModel()::where('status', 'not_started')->count(),
                'color' => 'gray',
                'query' => fn (Builder $query) => $query->where('status', 'not_started'),
            ],
            'in_progress' => [
                'icon' => 'heroicon-o-play',
                'badge' => static::getModel()::where('status', 'in_progress')->count(),
                'color' => 'warning',
                'query' => fn (Builder $query) => $query->where('status', 'in_progress'),
            ],
            'completed' => [
                'icon' => 'heroicon-o-check-circle',
                'badge' => static::getModel()::where('status', 'completed')->count(),
                'color' => 'success',
                'query' => fn (Builder $query) => $query->where('status', 'completed'),
            ],
            'trashed' => [
                'icon' => 'heroicon-o-trash',
                'badge' => static::getModel()::onlyTrashed()->count(),
                'color' => 'danger',
                'query' => fn (Builder $query) => $query->onlyTrashed(),
            ],
        ];
    }
}
```

## Available Methods

### `getActiveStatusTabs()`
Returns tabs for: All, Active, Inactive, Trashed
- **All**: Shows all records
- **Active**: Shows records where `is_active = true`
- **Inactive**: Shows records where `is_active = false`
- **Trashed**: Shows soft-deleted records

### `getSimplifiedActiveTabs()`
Returns tabs for: All, Active, Trashed
- **All**: Shows all records
- **Active**: Shows records where `is_active = true`
- **Trashed**: Shows soft-deleted records

### `getCustomStatusTabs(array $statusConfig)`
Returns custom tabs based on configuration array.

### Default Tab Methods

### `getDefaultActiveTabFromTrait()`
Returns `'active'` as the default tab for active status resources.

### `getDefaultSimplifiedTabFromTrait()`
Returns `'active'` as the default tab for simplified resources.

### `getDefaultCustomTabFromTrait()`
Returns `'all'` as the default tab for custom resources.

**Configuration Array Structure:**
```php
[
    'tab_key' => [
        'icon' => 'heroicon-o-icon-name',    // Optional
        'badge' => 123,                      // Required
        'color' => 'primary',                 // Optional
        'query' => fn (Builder $query) => $query->where(...), // Optional
    ],
]
```

## Benefits

1. **Consistency**: Standardized tab appearance across resources
2. **DRY Principle**: No code duplication
3. **Maintainability**: Changes in one place affect all resources
4. **Flexibility**: Multiple methods for different use cases
5. **Icons**: Pre-configured meaningful icons
6. **Colors**: Consistent color scheme
7. **Translation**: All labels use `__()` function

## Requirements

- Model must have `is_active` field (for active/inactive tabs)
- Model must use `SoftDeletes` trait (for trashed tab)
- Resource page must extend `ListRecords`
- Resource page must implement `getTabs()` method

## Examples in Codebase

- **Branches**: Uses `getActiveStatusTabs()` (All, Active, Inactive, Trashed)
- **Clients**: Can be updated to use `getSimplifiedActiveTabs()` (All, Active, Trashed)
- **Tasks**: Can use `getCustomStatusTabs()` for status-based filtering
- **Tickets**: Can use `getCustomStatusTabs()` for status-based filtering
