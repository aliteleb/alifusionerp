<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for multi-tenant database management
    |
    */

    'database' => [
        /*
        |--------------------------------------------------------------------------
        | Tenant Database Prefix
        |--------------------------------------------------------------------------
        |
        | This prefix will be used for all tenant databases. Change this to match
        | your application needs. For Ali Fusion ERP environments, use 'dws_tenant_'
        |
        */
        'prefix' => env('TENANT_DB_PREFIX', 'dws_tenant_'),

        /*
        |--------------------------------------------------------------------------
        | Auto Detect Tenant
        |--------------------------------------------------------------------------
        |
        | Automatically detect and switch to tenant database based on subdomain
        |
        */
        'auto_detect' => env('TENANT_AUTO_DETECT', true),

        /*
        |--------------------------------------------------------------------------
        | Auto Create Database
        |--------------------------------------------------------------------------
        |
        | Automatically create tenant database when facility is created
        |
        */
        'auto_create' => env('TENANT_AUTO_CREATE', true),

        /*
        |--------------------------------------------------------------------------
        | Auto Migrate
        |--------------------------------------------------------------------------
        |
        | Automatically run migrations on tenant database creation
        |
        */
        'auto_migrate' => env('TENANT_AUTO_MIGRATE', true),

        /*
        |--------------------------------------------------------------------------
        | Auto Seed
        |--------------------------------------------------------------------------
        |
        | Automatically seed tenant database on creation
        |
        */
        'auto_seed' => env('TENANT_AUTO_SEED', true),

        /*
        |--------------------------------------------------------------------------
        | Connection Template
        |--------------------------------------------------------------------------
        |
        | The base database connection to use as template for tenant connections
        |
        */
        'connection_template' => env('TENANT_CONNECTION_TEMPLATE', 'pgsql'),

        /*
        |--------------------------------------------------------------------------
        | Database Cleanup
        |--------------------------------------------------------------------------
        |
        | Whether to automatically drop tenant databases when facility is deleted
        | WARNING: Setting this to true will permanently delete tenant data
        |
        */
        'auto_cleanup' => env('TENANT_AUTO_CLEANUP', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Connection Management
    |--------------------------------------------------------------------------
    |
    | Configuration for managing tenant database connections
    |
    */
    'connections' => [
        /*
        |--------------------------------------------------------------------------
        | Connection Pooling
        |--------------------------------------------------------------------------
        |
        | Enable connection pooling for tenant databases
        |
        */
        'pooling' => env('TENANT_CONNECTION_POOLING', true),

        /*
        |--------------------------------------------------------------------------
        | Connection Timeout
        |--------------------------------------------------------------------------
        |
        | Connection timeout in seconds for tenant databases
        |
        */
        'timeout' => env('TENANT_CONNECTION_TIMEOUT', 60),

        /*
        |--------------------------------------------------------------------------
        | Max Connections
        |--------------------------------------------------------------------------
        |
        | Maximum number of concurrent tenant connections
        |
        */
        'max_connections' => env('TENANT_MAX_CONNECTIONS', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configuration for tenant-related logging
    |
    */
    'logging' => [
        /*
        |--------------------------------------------------------------------------
        | Log Tenant Switches
        |--------------------------------------------------------------------------
        |
        | Log when switching between tenant databases
        |
        */
        'log_switches' => env('TENANT_LOG_SWITCHES', false),

        /*
        |--------------------------------------------------------------------------
        | Log Database Operations
        |--------------------------------------------------------------------------
        |
        | Log tenant database creation, migration, and seeding operations
        |
        */
        'log_operations' => env('TENANT_LOG_OPERATIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    |
    | Security configuration for tenant databases
    |
    */
    'security' => [
        /*
        |--------------------------------------------------------------------------
        | Enforce Tenant Isolation
        |--------------------------------------------------------------------------
        |
        | Strictly enforce tenant data isolation
        |
        */
        'enforce_isolation' => env('TENANT_ENFORCE_ISOLATION', true),

        /*
        |--------------------------------------------------------------------------
        | Allowed Characters
        |--------------------------------------------------------------------------
        |
        | Allowed characters in tenant database names (regex pattern)
        |
        */
        'allowed_chars' => env('TENANT_ALLOWED_CHARS', '[a-zA-Z0-9_-]'),
    ],
];
