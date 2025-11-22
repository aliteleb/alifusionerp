# Tenant Database Backup & Restore System

This system provides comprehensive backup and restore functionality for tenant databases in the multi-tenant HR system.

## Features

### Backup Operations
- **Create Database Backup**: Creates a complete SQL dump of a tenant database
- **List Backups**: Shows all available backups for a specific tenant
- **Delete Backup**: Removes backup files from storage
- **Automatic Timestamping**: Backup files include creation timestamp in filename
- **Database-Agnostic**: Supports both PostgreSQL and MySQL databases

### Restore Operations
- **Restore from Backup**: Completely restores a tenant database from a backup file
- **Safe Restoration**: Drops and recreates the database to ensure clean restore
- **Validation**: Ensures backup file exists and belongs to the correct tenant

### Security Features
- **Tenant Isolation**: Backups are filtered by facility to prevent cross-tenant access
- **File Validation**: Verifies backup files belong to the requesting tenant
- **Secure Storage**: Backup files stored in protected application storage directory

## File Structure

### Backend Components

```
app/
├── Actions/Database/
│   └── TenantDatabaseActions.php     # Main backup/restore logic
├── Filament/Master/Pages/
│   └── DatabaseManager.php          # UI controller methods
└── Services/
    └── TenantDatabaseService.php     # Database connection utilities
```

### Frontend Components

```
resources/views/filament/master/pages/database-manager/
├── backup-modal.blade.php            # Backup management modal
├── action-buttons.blade.php          # Backup/restore buttons
└── database-manager.blade.php       # Main template (includes modals)
```

### Storage Structure

```
storage/app/backups/tenant-databases/
├── facility-slug_2024-01-15_14-30-25.sql
├── facility-slug_2024-01-15_16-45-10.sql
└── another-facility_2024-01-15_18-20-05.sql
```

## Usage

### Creating a Backup

1. Navigate to **System > Tenant Databases**
2. Find the facility you want to backup
3. Click the **Backup** button
4. Confirm the backup creation
5. Wait for the backup process to complete

### Managing Backups

1. Navigate to **System > Tenant Databases**
2. Find the facility whose backups you want to manage
3. Click the **Backups** button
4. In the backup manager modal you can:
   - View all available backups
   - Create new backups
   - Restore from existing backups
   - Delete old backups

### Restoring a Database

1. Open the backup manager for the target facility
2. Find the backup you want to restore from
3. Click the **Restore** button
4. Confirm the restoration (this will replace all current data)
5. Wait for the restore process to complete

## Technical Implementation

### Backup Process

1. **Database Connection**: Establishes connection to tenant database
2. **Command Generation**: Creates appropriate dump command (pg_dump or mysqldump)
3. **File Creation**: Generates timestamped backup file in storage directory
4. **Validation**: Verifies backup file was created successfully
5. **Notification**: Sends success/failure notification to user

### Restore Process

1. **File Validation**: Ensures backup file exists and belongs to tenant
2. **Database Recreation**: Drops existing database and creates new empty one
3. **Data Import**: Imports backup data using appropriate command (psql or mysql)
4. **Verification**: Confirms restore completed successfully
5. **Notification**: Notifies user of restore status

### Database Support

#### PostgreSQL
- **Backup**: Uses `pg_dump` with `--clean --if-exists --create` flags
- **Restore**: Uses `psql` to import SQL dump
- **Authentication**: Uses PGPASSWORD environment variable

#### MySQL
- **Backup**: Uses `mysqldump` with `--single-transaction --routines --triggers` flags
- **Restore**: Uses `mysql` to import SQL dump
- **Authentication**: Password included in command line (secured in process environment)

## Configuration

### Environment Requirements

The system requires the following database tools to be available:

**For PostgreSQL:**
- `pg_dump` (for backups)
- `psql` (for restores)

**For MySQL:**
- `mysqldump` (for backups)
- `mysql` (for restores)

### Storage Configuration

Backup files are stored in `storage/app/backups/tenant-databases/`. Ensure this directory has proper write permissions.

### File Naming Convention

Backup files follow this naming pattern:
```
{facility-slug}_{YYYY-MM-DD_HH-mm-ss}.sql
```

Example: `acme-corp_2024-01-15_14-30-25.sql`

## Error Handling

### Common Errors

1. **Database Not Found**: Occurs when trying to backup a non-existent database
2. **Backup File Not Found**: Happens when restore file doesn't exist
3. **Permission Denied**: File system permission issues
4. **Command Failed**: Database tool execution errors
5. **Cross-Tenant Access**: Attempting to access another facility's backups

### Error Recovery

- Failed backups automatically clean up partial files
- Restore operations recreate the database even if current one is corrupted
- All operations include comprehensive logging for troubleshooting

## Security Considerations

### Access Control
- Only authenticated master users can access backup functionality
- Backup operations are limited to the tenant's own data
- File access is validated against tenant ownership

### Data Protection
- Backup files contain sensitive tenant data
- Files are stored in application storage (not web-accessible)
- Consider encrypting backup files for additional security

### Audit Trail
- All backup/restore operations are logged
- Includes facility ID, user, timestamp, and operation result
- Failed operations include error details for debugging

## Testing

The system includes comprehensive tests covering:

- Backup creation and validation
- Restore functionality
- Backup listing and filtering
- File deletion
- Cross-tenant isolation
- Error scenarios

Run tests with:
```bash
php artisan test --filter=TenantDatabaseBackupTest
```

## Performance Considerations

### Backup Performance
- Large databases may take significant time to backup
- Consider running backups during low-usage periods
- Monitor disk space usage for backup storage

### Restore Performance
- Database restoration involves dropping and recreating
- Temporary service interruption during restore process
- Consider maintenance windows for restore operations

## Maintenance

### Regular Tasks
- Monitor backup storage disk usage
- Clean up old backup files periodically
- Verify backup integrity regularly
- Test restore procedures

### Monitoring
- Check backup success rates
- Monitor backup file sizes for anomalies
- Track restore operation success

## Future Enhancements

Potential improvements could include:

1. **Automated Backups**: Scheduled backup creation
2. **Backup Encryption**: Encrypt backup files at rest
3. **Compression**: Compress backup files to save space
4. **Remote Storage**: Store backups in cloud storage
5. **Incremental Backups**: Only backup changes since last backup
6. **Backup Verification**: Automated backup integrity checks