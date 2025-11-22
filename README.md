# Ali Fusion ERP

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Ali Fusion ERP

A comprehensive ERP foundation built with Laravel and Filament PHP. This multi-tenant application provides core facility management capabilities, shared reference data, and centralized configuration that you can extend with custom business domains.

### Key Features

- 🏢 **Multi-Tenant Core**: Isolated data stores per facility with a shared master panel
- 🧩 **Reference Data**: Central management of branches, departments, designations, and warehouses
- 🌍 **Multi-Language**: Support for English, Arabic, and Kurdish
- 🔐 **Role-Based Access**: Comprehensive permission system powered by Spatie Permissions
- 🛠️ **Extensible Architecture**: Add your own Filament resources and modules on top of the base system
- 📱 **Responsive Design**: Mobile-friendly interface for internal teams

## Quick Start

### Installation

```bash
# Clone the repository
git clone <repository-url> customer-system
cd customer-system

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup databases
php artisan master:migrate --fresh --seed --force
php artisan tenant:migrate --force

# Build assets
npm run build

# Start the application
php artisan serve
```

### Migration Commands

The system uses a dual-database architecture:

```bash
# Master database (system-wide data)
php artisan master:migrate --force

# Tenant databases (facility-specific data)
php artisan tenant:migrate --force

# Migrate specific facility by subdomain
php artisan tenant:migrate --facility=mydomain --force
```

## Architecture

### Multi-Tenant Database Design

Ali Fusion ERP uses a sophisticated multi-tenant architecture:

- **Master Database**: Stores system-wide configuration, facilities, and global reference data
- **Tenant Databases**: Each facility has its own isolated database for operational data
- **Automatic Management**: Database creation, switching, and cleanup handled automatically

### Database Structure

```
Master Database (Global)
├── facilities          # Tenant configuration
├── users              # System users  
├── countries          # Global reference data
├── currencies         # Currency definitions
└── ... (system tables)

Tenant Databases (Per Facility)
├── customers          # Customer records
├── branches           # Branch information
└── ... (operational tables)
```

### Enhanced Migration System

#### Master Migration (`master:migrate`)
- 🔄 Manages system-wide database
- ⚠️ Interactive confirmation for destructive operations
- 🗑️ Automatic tenant database cleanup (with `--fresh`)
- 📊 Clear progress indicators

#### Tenant Migration (`tenant:migrate`)
- 🎯 Supports subdomain or ID-based facility targeting
- 🔄 Migrates all facilities by default
- 🛡️ Robust error handling and recovery
- 📈 Individual facility progress tracking

#### Key Features
- **Flexible Targeting**: Use subdomains (`--facility=mydomain`) or IDs (`--facility=1`)
- **Batch Processing**: Migrate all facilities with single command
- **Safety Checks**: Confirmation prompts for destructive operations
- **Progress Tracking**: Real-time progress with emojis and status updates
- **Error Recovery**: Graceful handling of failures with proper cleanup

## Documentation

- 📄 **[Migration Guide](MIGRATION_GUIDE.md)** - Complete database migration documentation
- 🌍 **[Translation System](TRANSLATION_SYSTEM.md)** - Multi-language implementation guide
- 🧪 **[Testing Guide](tests/README.md)** - Testing procedures and guidelines

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development/)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).