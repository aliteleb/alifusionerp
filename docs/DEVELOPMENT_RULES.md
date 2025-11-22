# Development Rules & Guidelines

This document contains development rules and guidelines for the Ali Fusion ERP Laravel application.

## Command Execution Rules

### Artisan Commands
- **ALWAYS** use `--no-interaction` flag when executing Artisan commands in automation scripts or tools
- This ensures commands work without user input and prevents hanging processes

**Examples:**
```bash
# ✅ CORRECT
php artisan make:model User --no-interaction
php artisan make:migration create_users_table --no-interaction
php artisan make:filament-resource Announcement --no-interaction

# ❌ INCORRECT
php artisan make:model User
php artisan make:migration create_users_table
```

## Authentication Rules

### Auth Helper vs Auth Facade
- **ALWAYS** use `Auth` facade instead of `auth()` helper function
- **NEVER** use `auth()->check()` or `auth()->id()` in models, controllers, or other classes
- Use `Auth::check()` and `Auth::id()` for better type safety and consistency

**Examples:**
```php
// ✅ CORRECT
use Illuminate\Support\Facades\Auth;

if (Auth::check()) {
    $model->created_by = Auth::id();
}

// ❌ INCORRECT
if (auth()->check()) {
    $model->created_by = auth()->id();
}
```

## Filament Resource Design Patterns

### Simple vs Complex Resources
- **Use simple Filament resources** when the form has few inputs (≤ 5-7 fields)
- **Use full resources with separate pages** for complex forms with many fields (> 7 fields)
- Simple resources reduce complexity and improve performance for basic CRUD operations

**Examples:**
```bash
# ✅ Simple resource for basic models
php artisan make:filament-resource Gender --simple --no-interaction

# ✅ Full resource for complex models  
php artisan make:filament-resource Customer --no-interaction
```

## Enum Implementation Standards

### Enum Class Structure
- **ALWAYS** create enum classes when models use enum fields
- **ALWAYS** implement Filament contracts: `HasLabel`, `HasColor`, `HasIcon`, `HasDescription`
- Follow the established project structure for consistency

**Required Enum Structure:**
```php
<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum ExampleStatus: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => __('Draft'),
            self::PUBLISHED => __('Published'),
            self::ARCHIVED => __('Archived'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PUBLISHED => 'success',
            self::ARCHIVED => 'secondary',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::DRAFT => Heroicon::DocumentText,
            self::PUBLISHED => Heroicon::CheckCircle,
            self::ARCHIVED => Heroicon::ArchiveBox,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::DRAFT => __('Item is in draft mode'),
            self::PUBLISHED => __('Item is published and visible'),
            self::ARCHIVED => __('Item has been archived'),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->getLabel(),
        ])->toArray();
    }
}
```

### Enum Guidelines
1. **Use descriptive case names** in UPPER_CASE format
2. **Provide translations** for all labels and descriptions using `__()`
3. **Use appropriate colors** that match Filament's color system
4. **Choose meaningful icons** from the Heroicon set
5. **Include helper methods** like `options()` for form selects
6. **Maintain consistency** with existing enums in the project

## Filament Form Structure Pattern

### Two-Column Group Layout (Standard Pattern)
- **ALWAYS** use Groups instead of Grids for main form layout
- **TOTAL COLUMNS**: 3 (for 2:1 ratio)
- **LEFT GROUP**: Colspan 2 (Main Information)
- **RIGHT GROUP**: Colspan 1 (Options/Configurations)
- **SECTIONS**: Always wrap inputs in sections, never use direct inputs in groups

**Standard Form Schema Structure:**
```php
<?php

namespace App\Filament\Resources\{Model}\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class {Model}Form
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)  // Total columns for layout
            ->components([
                // LEFT GROUP - Main Information (Colspan 2)
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('Primary Section'))
                            ->description(__('Main data fields'))
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('Name'))
                                    ->required()
                                    ->columnSpanFull(),
                                
                                TextInput::make('field1')
                                    ->label(__('Field 1')),
                                
                                TextInput::make('field2')
                                    ->label(__('Field 2')),
                            ])
                            ->columns(2),  // Internal columns for section
                        
                        // Additional sections as needed
                    ]),

                // RIGHT GROUP - Options/Configurations (Colspan 1)
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make(__('Related Data'))
                            ->description(__('Associated records'))
                            ->schema([
                                CheckboxList::make('related_items')
                                    ->label(__('Items'))
                                    ->relationship('items')
                                    ->options(fn () => \App\Models\Item::active()->get()->pluck('name', 'id'))
                                    ->columns(1),
                                
                                Select::make('category_id')
                                    ->label(__('Category'))
                                    ->relationship('category', 'name'),
                            ]),
                        
                        Section::make(__('Options'))
                            ->description(__('Settings and configurations'))
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}
```

### Form Structure Rules
1. **NO GRIDS** - Use Groups and Sections for layout
2. **3-Column Schema** - Always set `->columns(3)` on the main schema
3. **2:1 Ratio** - Left group (colspan 2), Right group (colspan 1)
4. **Sections First** - Always wrap inputs in sections, not directly in groups
5. **Column Spans** - Use `->columnSpanFull()` for full-width fields within sections
6. **Internal Columns** - Set `->columns(2)` or appropriate value on sections for internal layout
7. **Checkbox Lists** - Use `->columns(1)` in narrow right column for proper display

### Relation Manager Rules
1. **Disable Lazy Loading** - Always add `protected static bool $isLazy = false;` to all relation managers
2. **Performance** - This ensures relation managers load immediately without lazy loading delays
3. **User Experience** - Provides faster navigation and better responsiveness

### PostgreSQL JSON Column Handling
When working with translatable fields (JSON columns) in forms:

**For Select Components:**
```php
Select::make('field_id')
    ->relationship('relation', 'name')
    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
```

**For CheckboxList Components:**
```php
CheckboxList::make('items')
    ->relationship('items')
    ->options(fn () => \App\Models\Item::active()->get()->pluck('name', 'id'))
```

**Why:** PostgreSQL cannot use `SELECT DISTINCT` or ordering on JSON columns. Using `options()` with manual option generation bypasses this limitation while maintaining proper translation support through the `HasTranslations` trait.

## Translation Standards
- **ALWAYS** wrap user-facing text with `__()` function
- **NEVER** hardcode strings in Filament resources, forms, or tables
- **ALWAYS** run `php artisan sync:translations` after adding translatable text

## Code Quality
- **ALWAYS** run `./vendor/bin/pint --dirty` after code changes
- Follow Laravel's PSR-12 standards
- Use meaningful variable and method names
- Add proper docblocks for public methods and classes

## Multi-Tenant Architecture
- **ALWAYS** use `TenantDatabaseService::connectToFacility($facility)` before tenant queries
- **VERIFY** database connection before executing tenant-specific operations
- **SEPARATE** master and tenant migrations appropriately

## Testing Standards
- Write feature tests for major functionality
- Include unit tests for complex business logic
- Test both happy path and error scenarios
- Use Laravel's testing helpers and factories

---

**Note**: These rules should be followed consistently to maintain code quality, security, and performance. Update this document as the project evolves.
