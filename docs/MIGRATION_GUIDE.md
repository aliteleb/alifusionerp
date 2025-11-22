# Ali Fusion ERP Migration Guide

This guide covers the database migration system for Ali Fusion ERP, which uses a multi-tenant architecture with separate master and tenant databases.

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Migration Commands](#migration-commands)
3. [Directory Structure](#directory-structure)
4. [Migration Workflow](#migration-workflow)
5. [Best Practices](#best-practices)
6. [Troubleshooting](#troubleshooting)

## Architecture Overview

Ali Fusion ERP uses a **multi-tenant architecture** with two types of databases:

### Master Database
- **Purpose**: Stores system-wide data and tenant configuration
- **Location**: `database/migrations/master/`
- **Contains**: Facilities, users, global reference data, system settings

### Tenant Databases
- **Purpose**: Stores facility-specific operational data
- **Location**: `database/migrations/tenant/`
- **Contains**: Customers, branches, and other operational data
- **Isolation**: Each facility has its own database instance

## Migration Commands

### Master Database Migration

Manages the central system database containing facilities and global configuration.

```bash
# Command signature
php artisan master:migrate [options]
```

#### Options:
- `--fresh`: Drop all tables and re-run all migrations
- `--seed`: Run database seeders after migration
- `--force`: Force operation without confirmation

#### Examples:
```bash
# Basic master migration
php artisan master:migrate

# Fresh migration with seeding (complete reset)
php artisan master:migrate --fresh --seed --force

# Migration with seeding only
php artisan master:migrate --seed --force
```

#### Master Migration Process:
1. 🔄 Validates master database connection
2. ⚠️ Shows warning for destructive operations (`--fresh`)
3. 🗑️ Cleans up existing tenant databases (if `--fresh`)
4. 📊 Runs migrations from `database/migrations/master/`
5. 🌱 Seeds master data (if `--seed`)
6. ✅ Confirms completion

### Tenant Database Migration

Manages facility-specific databases containing operational data.

```bash
# Command signature
php artisan tenant:migrate [options]
```

#### Options:
- `--facility=<subdomain|id>`: Migrate specific facility (subdomain preferred)
- `--fresh`: Drop all tables and re-run all migrations
- `--seed`: Run database seeders after migration
- `--force`: Force operation without confirmation

#### Examples:
```bash
# Migrate all facilities
php artisan tenant:migrate --force

# Migrate specific facility by subdomain (recommended)
php artisan tenant:migrate --facility=test --force

# Migrate specific facility by ID (backward compatible)
php artisan tenant:migrate --facility=1 --force

# Fresh migration for all facilities
php artisan tenant:migrate --fresh --force

# Fresh migration with seeding for specific facility
php artisan tenant:migrate --facility=test --fresh --seed --force
```

#### Tenant Migration Process:
1. 🎯 Identifies target facilities (all or specific)
2. 🔄 Creates tenant databases (if not exists)
3. 🔗 Configures tenant-specific connections
4. 📊 Runs migrations from `database/migrations/tenant/`
5. 🌱 Seeds tenant data (if `--seed`)
6. 🔄 Switches back to default connection
7. ✅ Reports individual and overall results

## Directory Structure

```
database/
├── migrations/
│   ├── master/                          # Master database migrations
│   │   ├── 2025_06_26_000000_create_facilities_table.php
│   │   ├── 2025_06_26_000010_create_users_table.php
│   │   ├── 2025_07_01_175154_create_countries_table.php
│   │   └── ... (system-wide tables)
│   │
│   └── tenant/                          # Tenant database migrations
│       ├── 2025_06_26_000000_create_cache_table.php
│       ├── 2025_06_26_000001_create_branches_table.php
│       ├── 2025_09_20_090849_create_customers_table.php
│       └── ... (facility-specific tables)
│
├── seeders/                             # Database seeders
└── factories/                           # Model factories
```

### Master Tables
- `facilities` - Tenant configuration
- `users` - System users

### Tenant Tables
- `branches` - Branch information
- `customers` - Customer records
- Other operational tables

## Migration Workflow

### Initial Setup

1. **Setup Master Database**:
   ```bash
   php artisan master:migrate --fresh --seed --force
   ```

2. **Setup Tenant Databases**:
   ```bash
   php artisan tenant:migrate --force
   ```

### Development Workflow

1. **Create Migration**:
   ```bash
   # For master tables
   php artisan make:migration create_example_table
   # Move to: database/migrations/master/
   
   # For tenant tables
   php artisan make:migration create_tenant_example_table
   # Move to: database/migrations/tenant/
   ```

2. **Run Migrations**:
   ```bash
   # Master changes
   php artisan master:migrate --force
   
   # Tenant changes
   php artisan tenant:migrate --force
   ```

### Production Deployment

1. **Backup Current State**:
   ```bash
   php artisan backup:run
   ```

2. **Run Master Migrations**:
   ```bash
   php artisan master:migrate --force
   ```

3. **Run Tenant Migrations**:
   ```bash
   php artisan tenant:migrate --force
   ```

4. **Verify Results**:
   ```bash
   php artisan tenant:database list
   ```

## Rollback Operations

### Master Database Rollback

```bash
# Rollback last migration batch
php artisan master:migrate --rollback

# Rollback specific number of migrations
php artisan master:migrate --rollback --step=3

# Force rollback without confirmation
php artisan master:migrate --rollback --force
```

### Tenant Database Rollback

```bash
# Rollback all tenant databases (last batch)
php artisan tenant:migrate --rollback

# Rollback specific facility
php artisan tenant:migrate --facility=mydomain --rollback

# Rollback specific number of migrations
php artisan tenant:migrate --rollback --step=2

# Force rollback without confirmation
php artisan tenant:migrate --facility=mydomain --rollback --force
```

### Rollback Best Practices

1. **Always backup before rollback**:
   ```bash
   php artisan backup:run
   php artisan master:migrate --rollback
   ```

2. **Test rollback on staging first**:
   ```bash
   php artisan tenant:migrate --facility=staging --rollback
   ```

3. **Review what will be rolled back**:
   ```bash
   php artisan migrate:status --path=database/migrations/master
   php artisan migrate:status --path=database/migrations/tenant --database=tenant_mydomain
   ```

4. **Rollback tenant databases before master**:
   ```bash
   # First rollback tenants
   php artisan tenant:migrate --rollback --force
   
   # Then rollback master
   php artisan master:migrate --rollback --force
   ```

⚠️ **Warning**: Rollbacks can cause data loss. Always backup production data before performing rollback operations.

## Best Practices

### Migration Development

1. **File Placement**:
   - ✅ Master tables → `database/migrations/master/`
   - ✅ Tenant tables → `database/migrations/tenant/`
   - ❌ Never mix master and tenant migrations

2. **Naming Conventions**:
   - Use descriptive, clear migration names
   - Follow Laravel timestamp format: `YYYY_MM_DD_HHMMSS_description`
   - Order migrations by dependency requirements

3. **Table Structure**:
   - Always include soft deletes for data tables
   - Use UUID fields for external references
   - Use JSON for multi-language fields

### Command Usage

1. **Facility Identification**:
   ```bash
   # ✅ Preferred: Use subdomain
   php artisan tenant:migrate --facility=mydomain
   
   # ✅ Acceptable: Use ID (backward compatible)
   php artisan tenant:migrate --facility=1
   ```

2. **Safety Practices**:
   ```bash
   # ✅ Always use --force in automated scripts
   php artisan tenant:migrate --force
   
   # ✅ Test migrations on staging first
   php artisan tenant:migrate --facility=staging
   
   # ⚠️ Be careful with --fresh (destructive)
   php artisan master:migrate --fresh --force
   ```

3. **Monitoring**:
   - Check command output for errors
   - Verify facility count in results
   - Test application functionality after migrations

## Troubleshooting

### Common Issues

#### 1. **Facility Not Found**
```
Error: Facility with subdomain or ID 'example' not found
```
**Solution**: 
- Verify facility exists: `php artisan tenant:database list`
- Check spelling of subdomain
- Ensure facility is not soft-deleted

#### 2. **Connection Timeout**
```
Error: Tenant migration failed: Connection timeout
```
**Solution**:
- Check database connectivity
- Verify tenant database exists
- Restart database service if needed

#### 3. **Permission Errors**
```
Error: Access denied for user
```
**Solution**:
- Verify database user permissions
- Check `.env` database configuration
- Ensure tenant database creation permissions

#### 4. **Migration Conflicts**
```
Error: Table already exists
```
**Solution**:
- Use `--fresh` flag to reset (⚠️ destructive)
- Manually check conflicting tables
- Review migration order and dependencies

#### 5. **Rollback Failures**
```
Error: Unable to rollback migration
```
**Solution**:
- Check migration rollback methods (`down()` function)
- Verify no foreign key constraints blocking rollback
- Manually inspect migration files for rollback logic
- Use `--step=1` to rollback one migration at a time

#### 6. **Rollback Data Loss**
```
Warning: Rollback will drop columns/tables
```
**Solution**:
- Always backup before rollback operations
- Review migration `down()` methods before rollback
- Consider creating data export before rollback
- Test rollback on staging environment first

### Diagnostic Commands

```bash
# List all facilities and their database status
php artisan tenant:database list

# Check specific facility database
php artisan tenant:database migrate --facility=test

# Test database connections
php artisan tinker
> DB::connection('tenant_test')->select('SELECT 1');
```

### Recovery Procedures

#### Master Database Recovery
```bash
# 1. Restore from backup
php artisan backup:restore --latest

# 2. Re-run fresh migration
php artisan master:migrate --fresh --seed --force
```

#### Tenant Database Recovery
```bash
# 1. Reset specific facility
php artisan tenant:migrate --facility=example --fresh --seed --force

# 2. Reset all facilities
php artisan tenant:migrate --fresh --seed --force
```

## Cache Considerations

The migration system implements intelligent caching strategies:


### Cache Management
```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear all caches
php artisan optimize:clear
```

## Multi-Tenant Context

### Facility Switching
The system automatically handles tenant context switching:
- 🔗 **Switch to tenant** before operations
- 🔄 **Execute migrations** in tenant context  
- 🏠 **Switch back** to default connection
- 🛡️ **Error handling** ensures proper cleanup

### Connection Management
- Master connection: `mysql` (default)
- Tenant connections: `tenant_{subdomain}`
- Automatic connection creation and configuration
- Graceful fallback and error handling

---

**📝 Note**: This migration system is designed for high availability and data integrity in multi-tenant environments. Always test migrations in staging environments before production deployment.

**🔗 Related Documentation**:
- [Translation System Guide](TRANSLATION_SYSTEM.md)
- [Testing Guide](tests/README.md)