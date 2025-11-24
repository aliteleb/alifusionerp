# Ali Fusion ERP - Project Structure Documentation

## 📋 Table of Contents

1. [Overview](#overview)
2. [Root Directory Structure](#root-directory-structure)
3. [Module System](#module-system)
4. [Module Details](#module-details)
5. [Naming Conventions](#naming-conventions)
6. [File Organization](#file-organization)
7. [Autoloading](#autoloading)
8. [Database Structure](#database-structure)
9. [Panel Providers](#panel-providers)

---

## Overview

Ali Fusion ERP is built using **Laravel Modules** (`nwidart/laravel-modules`) with **Filament PHP** for the admin interface. The project follows a modular architecture where each module is self-contained with its own entities, controllers, resources, and routes.

### Key Technologies

- **Laravel 12**: PHP Framework
- **Filament v4**: Admin Panel Framework
- **Livewire v3**: Reactive Components
- **PostgreSQL**: Primary Database
- **Laravel Modules**: Modular Architecture
- **Filament Modules**: Module Integration for Filament

---

## Root Directory Structure

```
alifusionerp/
├── app/                          # Core application files (minimal)
│   ├── Console/                  # Artisan commands
│   ├── Providers/               # Service providers
│   └── ...
├── bootstrap/                    # Application bootstrap
│   ├── app.php                  # Application configuration
│   └── providers.php            # Service provider registration
├── config/                       # Configuration files
│   ├── modules.php              # Laravel Modules configuration
│   ├── filament-modules.php     # Filament Modules configuration
│   └── ...
├── database/
│   ├── migrations/
│   │   ├── master/              # Master database migrations
│   │   └── tenant/              # Tenant database migrations
│   └── seeders/                 # Database seeders
├── modules/                      # All application modules (lowercase)
│   ├── Core/                    # Shared organization data & tenant panel (users, roles, tenant resources)
│   ├── System/                  # System-wide tenant orchestration & infrastructure
│   ├── Master/                  # Master panel module
│   └── Survey/                  # Survey management module + dedicated survey panel
├── public/                       # Public assets
├── resources/
│   ├── css/                     # Stylesheets
│   ├── js/                      # JavaScript files
│   └── views/                   # Blade templates
├── routes/                       # Application routes
│   ├── web.php                  # Web routes
│   └── api.php                  # API routes
└── vendor/                       # Composer dependencies
```

---

## Module System

The project uses **Laravel Modules** to organize code into logical, self-contained modules. Each module follows a consistent structure.

### Module Directory Structure

Each module follows this standard structure:

```
modules/{ModuleName}/
├── app/                          # Application code
│   ├── Actions/                 # Action classes
│   ├── Console/                 # Artisan commands
│   ├── Enums/                   # Enum classes
│   ├── Filament/                # Filament resources, pages, widgets
│   │   ├── Resources/           # Filament resources
│   │   ├── Pages/               # Filament pages
│   │   ├── Widgets/             # Filament widgets
│   │   └── Clusters/            # Filament clusters
│   ├── Helpers/                 # Helper functions
│   ├── Observers/                # Eloquent observers
│   ├── Providers/               # Service providers
│   │   ├── Filament/            # Filament panel providers
│   │   └── RouteServiceProvider.php
│   ├── Services/                # Service classes
│   └── Traits/                  # Reusable traits
├── Entities/                     # Eloquent models (renamed from Models)
├── Http/                         # HTTP layer
│   ├── Controllers/             # Controllers
│   └── Middleware/              # Middleware classes
├── Database/
│   ├── factories/               # Model factories
│   ├── migrations/              # Database migrations
│   └── seeders/                 # Database seeders
├── Routes/                       # Module routes (renamed from routes)
│   ├── web.php                  # Web routes
│   └── api.php                  # API routes
├── Resources/                    # Module resources (renamed from resources)
│   ├── assets/                  # Frontend assets
│   └── views/                   # Blade templates
├── Config/                       # Module configuration (renamed from config)
│   └── config.php               # Module config
├── Tests/                        # Test files
│   ├── Feature/                 # Feature tests
│   └── Unit/                    # Unit tests
├── composer.json                 # Module autoloading
├── module.json                   # Module metadata
├── package.json                  # NPM dependencies
└── vite.config.js                # Vite configuration
```

### Key Naming Conventions

- **Models** → **Entities**: All Eloquent models are stored in `Entities/` directory
- **routes/** → **Routes/**: Route files are in `Routes/` directory
- **resources/** → **Resources/**: Resource files are in `Resources/` directory
- **config/** → **Config/**: Config files are in `Config/` directory
- **database/** → **Database/**: Database files are in `Database/` directory

---

## Module Details

### 1. Core Module

**Location**: `modules/Core/`  
**Namespace**: `Modules\Core`  
**Purpose**: Shared organizational data models (users, roles, branches, departments, etc.), reusable helpers/traits, **and** the entire tenant-facing Filament panel.

**Key Components**:
- **Entities**: `User`, `Setting`, `Branch`, `Department`, `Designation`, `Warehouse`, `Country`, `Currency`, `Gender`, `MaritalStatus`, `Nationality`, `ActivityLog`, `Backup`, `PushSubscription`, `Reply`, `Role`, `Message`, `Job`, `FailedJob`
- **Services**: `TenantDatabaseService`, `MigrationStatusService`, `DatabaseNotificationService`, `QueueEventListenersService`, `PgAdminService`
- **Middleware**: `AdminPanelAuthenticate`, `SetSubdomainRouteParameter`, `TenantDatabaseMiddleware`, `TrackUserActivity`
- **Traits**: `TenantAware`, `UsesTenantConnection`, `HasBranchAccess`, `ActivityLoggable`, `LogsActivity`, `HasReplies`, `SafeNotifications`, `HasActiveStatusTabs`
- **Commands**: `MasterMigrateCommand`, `TenantMigrateCommand`, `TenantSeedCommand`, `TenantDatabaseCommand`, `SyncTranslations`
- **Helpers**: `General.php`, `TenantHelpers.php`, `TranslationHelper.php`, `TranslationGlobalHelpers.php`, `SEO.php`
- **Tenant Panel (Filament)**: `Providers/Filament/AdminPanelProvider.php` (panel ID `admin`, path `/admin`, domain `{subdomain}.alifusionerp.test`, guard `web`), Filament pages (`Dashboard`, `Settings`, `Reports`, `BranchReports`, `Login`), widgets (`CustomAccountWidget`, `ActivityLogWidget`), and tenant panel controllers.

**Autoloading** (`composer.json`):
```json
{
  "autoload": {
    "psr-4": {
      "Modules\\Core\\": "app/",
      "Modules\\Core\\Entities\\": "Entities/",
      "Modules\\Core\\Http\\": "Http/",
      "Modules\\Core\\Database\\Factories\\": "Database/factories/",
      "Modules\\Core\\Database\\Seeders\\": "Database/Seeders/"
    }
  }
}
```

---

### 2. System Module

**Location**: `modules/System/`  
**Namespace**: `Modules\System`  
**Purpose**: Cross-tenant infrastructure such as database orchestration, automated seeding/bootstrapping, and master database backups.

**Key Components**:
- **Actions**: `TenantDatabaseActions`, `SeedFacilityDataAction` (and its seeding helpers), `BootTenantAction`, `CreateBackupAction`, `RestoreBackupAction`
- **Responsibilities**: creating/restoring tenant databases, running tenant migrations & seeders, provisioning new facilities, and exposing reusable backup actions for the Master panel.

---

### 3. Master Module

**Location**: `modules/Master/`  
**Namespace**: `Modules\Master`  
**Purpose**: Master panel for system administration

**Key Components**:
- **Entities**: `Facility`
- **Panel Provider**: `Providers/Filament/MasterPanelProvider.php`
- **Middleware**: `Http/Middleware/MasterPanelAuthenticate.php`
- **Filament Pages**: `Settings`, `DatabaseManager`
- **Filament Resources**: Master-specific resources for facilities, users, etc.
- **Controllers**: Master panel controllers

**Panel Configuration**:
- **ID**: `master`
- **Path**: `/master`
- **Domain**: Main domain (no subdomain)
- **Auth Guard**: `web`

---

### 4. Survey Module

**Location**: `modules/Survey/`  
**Namespace**: `Modules\Survey`  
**Purpose**: Encapsulates the standalone survey project (`dws-survey`) inside Ali Fusion ERP as a first-class module. Hosts survey definitions, invitations, responses, WhatsApp automation, and provides a dedicated Filament panel that still reuses Core resources.

**Key Components**:
- **Entities**: `Survey`, `SurveyCategory`, `SurveyInvitation`, `SurveyResponse`, `RatingReport`, `Notice`, `Reward`, and supporting models migrated from the survey app.
- **Actions & Services**: Invitation scheduling, WhatsApp/UltraMSG dispatchers, public survey access helpers, context-aware queue/database utilities.
- **Filament Resources**: All survey-specific resources plus discovery of shared `Core` resources so operators can manage users, roles, branches, etc., without duplication.
- **Imports/Exports**: Survey CSV/XLSX flows mapped to the module.
- **Seeders & Migrations**: Module-scoped under `modules/Survey/database/*`, allowing tenant migrations to include/exclude survey structures independently.
- **Panel Provider**: `Providers/Filament/SurveyPanelProvider.php` (see [Panel Providers](#panel-providers)).

---

## Naming Conventions

### Namespaces

- **Module Namespace**: `Modules\{ModuleName}`
- **Entities**: `Modules\{ModuleName}\Entities`
- **Controllers**: `Modules\{ModuleName}\Http\Controllers`
- **Services**: `Modules\{ModuleName}\Services`
- **Filament Resources**: `Modules\{ModuleName}\Filament\Resources`
- **Filament Pages**: `Modules\{ModuleName}\Filament\Pages`
- **Filament Widgets**: `Modules\{ModuleName}\Filament\Widgets`

### File Naming

- **Models/Entities**: PascalCase (e.g., `User.php`, `Facility.php`)
- **Controllers**: PascalCase with `Controller` suffix (e.g., `UserController.php`)
- **Services**: PascalCase with `Service` suffix (e.g., `TenantDatabaseService.php`)
- **Actions**: PascalCase with `Action` suffix (e.g., `SeedFacilityDataAction.php`)
- **Traits**: PascalCase (e.g., `TenantAware.php`)
- **Enums**: PascalCase (e.g., `ActivityAction.php`)
- **Helpers**: PascalCase (e.g., `General.php`)

### Directory Naming

- **Directories**: PascalCase (e.g., `Http/`, `Entities/`, `Services/`)
- **Module Root**: PascalCase (e.g., `Core/`, `Master/`, `ReferenceData/`)

---

## File Organization

### Entity Files

All Eloquent models are stored in the `Entities/` directory within each module:

```
modules/Core/Entities/
├── User.php
├── Setting.php
├── Branch.php
├── Department.php
└── ...

modules/Master/Entities/
└── Facility.php
```

### Filament Resources

Filament resources are organized by module:

```
modules/{ModuleName}/app/Filament/Resources/
├── {ResourceName}/
│   ├── {ResourceName}Resource.php
│   ├── Pages/
│   │   ├── List{ResourceName}.php
│   │   ├── Create{ResourceName}.php
│   │   └── Edit{ResourceName}.php
│   └── Schemas/
│       ├── {ResourceName}Form.php
│       └── {ResourceName}Table.php
```

### Service Classes

Services are organized by functionality:

```
modules/Core/app/Services/
├── TenantDatabaseService.php
├── MigrationStatusService.php
├── DatabaseNotificationService.php
└── PgAdmin/
    └── PgAdminService.php
```

### Middleware

Middleware is organized by purpose:

```
modules/Core/Http/Middleware/
├── Panels/
│   ├── AdminPanelAuthenticate.php
│   └── MasterPanelAuthenticate.php
├── SetSubdomainRouteParameter.php
├── TenantDatabaseMiddleware.php
└── TrackUserActivity.php
```

---

## Autoloading

### Main Project Autoloading

The main `composer.json` includes module autoloading:

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "Modules\\Core\\": "modules/Core/app/",
      "Modules\\Core\\Entities\\": "modules/Core/Entities/"
    },
    "files": [
      "modules/Core/app/Helpers/General.php"
    ]
  }
}
```

### Module Autoloading

Each module has its own `composer.json` with autoloading configuration:

```json
{
  "autoload": {
    "psr-4": {
      "Modules\\{ModuleName}\\": "app/",
      "Modules\\{ModuleName}\\Entities\\": "Entities/",
      "Modules\\{ModuleName}\\Http\\": "Http/",
      "Modules\\{ModuleName}\\Database\\Factories\\": "Database/factories/",
      "Modules\\{ModuleName}\\Database\\Seeders\\": "Database/Seeders/"
    }
  }
}
```

### Autoloading Helper

The `wikimedia/composer-merge-plugin` is used to merge module `composer.json` files:

```json
{
  "extra": {
    "merge-plugin": {
      "include": [
        "modules/*/composer.json"
      ]
    }
  }
}
```

---

## Database Structure

### Master Database

**Location**: `modules/Master/database/migrations/`  
**Purpose**: System-wide data and configuration

**Key Tables**:
- `facilities` - Tenant configuration
- `users` - System users
- `settings` - Global settings
- `countries` - Reference data
- `currencies` - Reference data
- `genders` - Reference data
- `marital_statuses` - Reference data
- `nationalities` - Reference data

### Tenant Databases

**Location**: Module-scoped migrations (e.g. `modules/Core/database/migrations/`, `modules/Survey/database/migrations/`, etc.)  
**Purpose**: Facility-specific operational data. The `tenant:migrate` command now discovers every module (except `Master`) and can optionally target specific modules with `--module=ModuleName`.

**Core Key Tables** (`modules/Core/database/migrations`):
- `branches`, `departments`, `users`, `roles`, `activity_logs`, `messages`, etc.

**Survey Key Tables** (`modules/Survey/database/migrations`):
- `surveys`, `survey_categories`, `survey_invitations`, `survey_responses`, `rating_reports`, `notices`, `rewards`, plus pivot/audit tables required by survey features.

### Migration Commands

```bash
# Master database
php artisan master:migrate --fresh --seed --force

# Tenant databases
php artisan tenant:migrate --force                       # All modules except Master
php artisan tenant:migrate --module=Survey --force        # Survey-only migrations
php artisan tenant:migrate --facility=subdomain --force   # Single facility, all modules
```

---

## Panel Providers

### Master Panel Provider

**Location**: `modules/Master/app/Providers/Filament/MasterPanelProvider.php`  
**Namespace**: `Modules\Master\Providers\Filament`

**Configuration**:
- **Panel ID**: `master`
- **Path**: `/master`
- **Domain**: Main domain
- **Resources**: Master-specific resources
- **Pages**: `Dashboard`, `DatabaseManager`, `Settings`
- **Widgets**: `AccountWidget`, `FilamentInfoWidget`

### Tenant Admin Panel Provider (Core Module)

**Location**: `modules/Core/app/Providers/Filament/AdminPanelProvider.php`  
**Namespace**: `Modules\Core\Providers\Filament`

**Configuration**:
- **Panel ID**: `admin`
- **Path**: `/admin`
- **Domain**: `{subdomain}.alifusionerp.test`
- **Resources**: Tenant-specific resources
- **Pages**: `Dashboard`, `Settings`, `Reports`, `BranchReports`
- **Widgets**: `CustomAccountWidget`, `ActivityLogWidget`

### Survey Panel Provider

**Location**: `modules/Survey/app/Providers/Filament/SurveyPanelProvider.php`  
**Namespace**: `Modules\Survey\Providers\Filament`

**Configuration**:
- **Panel ID**: `survey`
- **Path**: `/survey`
- **Domain**: `{subdomain}.alifusionerp.test`
- **Resource Discovery**: Always discovers Survey module resources *and* Core module resources so operators can manage shared entities (Users, Branches, Roles, etc.) without leaving the survey workspace.
- **Pages**: Shares the global dashboard + survey-specific operational pages.
- **Widgets**: Reuses shared account/activity widgets and registers survey widgets defined inside the module.

### Service Provider Registration

Service providers are registered in `bootstrap/providers.php`:

```php
return [
    // Core Module - Must be loaded first
    Modules\Core\Providers\CoreServiceProvider::class,
    
    // Application Providers
    App\Providers\AppServiceProvider::class,
    
    // Module Service Providers
    Modules\Master\Providers\MasterServiceProvider::class,
    Modules\Master\Providers\Filament\MasterPanelProvider::class,
    Modules\Core\Providers\Filament\AdminPanelProvider::class,
    Modules\Survey\Providers\SurveyServiceProvider::class,
    Modules\Survey\Providers\Filament\SurveyPanelProvider::class,
];
```

---

## Helper Functions

Helper functions are loaded from the Core module:

**Location**: `modules/Core/app/Helpers/General.php`

**Registration**: In main `composer.json`:
```json
{
  "autoload": {
    "files": [
      "modules/Core/app/Helpers/General.php"
    ]
  }
}
```

---

## Module Configuration

### Module Metadata

Each module has a `module.json` file:

```json
{
  "name": "ModuleName",
  "alias": "modulename",
  "description": "Module description",
  "keywords": [],
  "priority": 0,
  "providers": [
    "Modules\\ModuleName\\Providers\\ModuleNameServiceProvider"
  ],
  "aliases": {},
  "files": [],
  "requires": []
}
```

### Module Config

Module configuration files are in `Config/config.php`:

```php
return [
    'key' => 'value',
    // Module-specific configuration
];
```

---

## Best Practices

### Creating New Modules

1. Use Artisan command: `php artisan module:make {ModuleName}`
2. Move generated files to match project structure (Entities, Routes, etc.)
3. Update `composer.json` for proper autoloading
4. Register service provider in `bootstrap/providers.php`
5. Update `module.json` with correct namespaces

### Adding New Entities

1. Create entity in `Entities/` directory
2. Use namespace: `Modules\{ModuleName}\Entities`
3. Update module `composer.json` autoloading
4. Run `composer dump-autoload`

### Adding Filament Resources

1. Use Artisan command: `php artisan module:make:filament-resource {ResourceName} {ModuleName}`
2. Resources are auto-discovered by Filament
3. Ensure proper namespace: `Modules\{ModuleName}\Filament\Resources`

### Module Dependencies

- **Core Module**: Must be loaded first (contains shared entities, services, and the tenant panel)
- **Master Module**: Depends on Core
- Other modules can depend on Core or each other as needed

---

## Troubleshooting

### Class Not Found Errors

1. Check namespace matches file location
2. Verify `composer.json` autoloading configuration
3. Run `composer dump-autoload`
4. Clear Laravel caches: `php artisan optimize:clear`

### Module Not Loading

1. Verify service provider is registered in `bootstrap/providers.php`
2. Check `module.json` configuration
3. Ensure module directory is in `modules/` (lowercase)
4. Verify module namespace matches directory name

### Autoloading Issues

1. Check module `composer.json` for correct PSR-4 mappings
2. Verify `wikimedia/composer-merge-plugin` is configured
3. Run `composer dump-autoload`
4. Check for namespace conflicts

---

## Additional Resources

- [Laravel Modules Documentation](https://nwidart.com/laravel-modules/)
- [Filament Documentation](https://filamentphp.com/docs)
- [Filament Modules Documentation](https://github.com/savannabits/filament-modules)

---

**Last Updated**: 2024  
**Version**: 1.0.0

