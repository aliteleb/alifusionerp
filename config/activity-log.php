<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Activity Log Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains all mappings and icons used by the
    | activity log system for displaying changes, relationships, and UI elements.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Action Colors and Icons
    |--------------------------------------------------------------------------
    |
    | Define colors and icons for different activity actions.
    |
    */
    'actions' => [
        'created' => [
            'color' => 'bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-200',
            'icon' => 'heroicon-o-plus',
        ],
        'updated' => [
            'color' => 'bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200',
            'icon' => 'heroicon-o-pencil',
        ],
        'deleted' => [
            'color' => 'bg-red-100 dark:bg-red-800 text-red-800 dark:text-red-200',
            'icon' => 'heroicon-o-trash',
        ],
        'restored' => [
            'color' => 'bg-yellow-100 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200',
            'icon' => 'heroicon-o-arrow-uturn-left',
        ],
        'viewed' => [
            'color' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
            'icon' => 'heroicon-o-eye',
        ],
        'exported' => [
            'color' => 'bg-purple-100 dark:bg-purple-800 text-purple-800 dark:text-purple-200',
            'icon' => 'heroicon-o-arrow-down-tray',
        ],
        'imported' => [
            'color' => 'bg-indigo-100 dark:bg-indigo-800 text-indigo-800 dark:text-indigo-200',
            'icon' => 'heroicon-o-arrow-up-tray',
        ],
        'approved' => [
            'color' => 'bg-emerald-100 dark:bg-emerald-800 text-emerald-800 dark:text-emerald-200',
            'icon' => 'heroicon-o-check-circle',
        ],
        'rejected' => [
            'color' => 'bg-rose-100 dark:bg-rose-800 text-rose-800 dark:text-rose-200',
            'icon' => 'heroicon-o-x-circle',
        ],
        'default' => [
            'color' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
            'icon' => 'heroicon-o-question-mark-circle',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Icons
    |--------------------------------------------------------------------------
    |
    | Define icons for different model types.
    |
    */
    'model_icons' => [
        'App\\Models\\User' => 'heroicon-o-users',
        'App\\Models\\Branch' => 'heroicon-o-building-storefront',
        'App\\Models\\Client' => 'heroicon-o-building-office',
        'App\\Models\\Project' => 'heroicon-o-folder-open',
        'App\\Models\\Task' => 'heroicon-o-rectangle-stack',
        'App\\Models\\Deal' => 'heroicon-o-currency-dollar',
        'App\\Models\\Opportunity' => 'heroicon-o-sparkles',
        'App\\Models\\Ticket' => 'heroicon-o-chat-bubble-left-right',
        'App\\Models\\Complaint' => 'heroicon-o-exclamation-triangle',
        'App\\Models\\Contract' => 'heroicon-o-document-text',
        'App\\Models\\Announcement' => 'heroicon-o-speaker-wave',
        'App\\Models\\MarketingCampaign' => 'heroicon-o-megaphone',
        'App\\Models\\ClientGroup' => 'heroicon-o-tag',
        'App\\Models\\Department' => 'heroicon-o-building-office',
        'App\\Models\\Designation' => 'heroicon-o-identification',
        'App\\Models\\Gender' => 'heroicon-o-user-group',
        'App\\Models\\MaritalStatus' => 'heroicon-o-heart',
        'App\\Models\\Nationality' => 'heroicon-o-flag',
        'App\\Models\\Country' => 'heroicon-o-globe-alt',
        'App\\Models\\Currency' => 'heroicon-o-currency-dollar',
        'App\\Models\\ProjectCategory' => 'heroicon-o-tag',
        'App\\Models\\Role' => 'heroicon-o-shield-check',
        'App\\Models\\Warehouse' => 'heroicon-o-home',
        'App\\Models\\Employee' => 'heroicon-o-user',
        'App\\Models\\Reply' => 'heroicon-o-chat-bubble-left',
        'App\\Models\\ActivityLog' => 'heroicon-o-clipboard-document-list',
        'App\\Models\\TaskCategory' => 'heroicon-o-tag',
        'App\\Models\\Shift' => 'heroicon-o-clock',
        'App\\Models\\Permission' => 'heroicon-o-key',
        'App\\Models\\Position' => 'heroicon-o-identification',
        'App\\Models\\Category' => 'heroicon-o-tag',
        'App\\Models\\Priority' => 'heroicon-o-exclamation-circle',
        'App\\Models\\Status' => 'heroicon-o-check-circle',
        'App\\Models\\Customer' => 'heroicon-o-user-circle',
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Icons
    |--------------------------------------------------------------------------
    |
    | Define icons for different field types and names.
    |
    */
    'field_icons' => [
        // User related fields
        'user_id' => 'heroicon-o-users',
        'assigned_to' => 'heroicon-o-user-plus',
        'created_by' => 'heroicon-o-user-plus',
        'updated_by' => 'heroicon-o-pencil-square',

        // Branch and location fields
        'branch_id' => 'heroicon-o-building-storefront',
        'warehouse_id' => 'heroicon-o-home',
        'department_id' => 'heroicon-o-building-office',

        // Client and customer fields
        'client_id' => 'heroicon-o-building-office',
        'customer_id' => 'heroicon-o-user-circle',
        'client_group_id' => 'heroicon-o-tag',

        // Project and task fields
        'project_id' => 'heroicon-o-folder-open',
        'task_id' => 'heroicon-o-rectangle-stack',
        'parent_task_id' => 'heroicon-o-rectangle-stack',
        'parent_id' => 'heroicon-o-rectangle-stack',
        'task_category_id' => 'heroicon-o-tag',
        'project_category_id' => 'heroicon-o-tag',

        // Deal and opportunity fields
        'deal_id' => 'heroicon-o-currency-dollar',
        'opportunity_id' => 'heroicon-o-sparkles',

        // Support fields
        'ticket_id' => 'heroicon-o-chat-bubble-left-right',
        'complaint_id' => 'heroicon-o-exclamation-triangle',
        'contract_id' => 'heroicon-o-document-text',

        // Employee fields
        'employee_id' => 'heroicon-o-user',
        'designation_id' => 'heroicon-o-identification',
        'position_id' => 'heroicon-o-identification',
        'shift_id' => 'heroicon-o-clock',

        // Personal information fields
        'gender_id' => 'heroicon-o-user-group',
        'marital_status_id' => 'heroicon-o-heart',
        'nationality_id' => 'heroicon-o-flag',
        'country_id' => 'heroicon-o-globe-alt',

        // System fields
        'role_id' => 'heroicon-o-shield-check',
        'permission_id' => 'heroicon-o-key',
        'category_id' => 'heroicon-o-tag',
        'priority_id' => 'heroicon-o-exclamation-circle',
        'status_id' => 'heroicon-o-check-circle',
        'currency_id' => 'heroicon-o-currency-dollar',

        // Common fields
        'name' => 'heroicon-o-tag',
        'title' => 'heroicon-o-document-text',
        'description' => 'heroicon-o-document-text',
        'email' => 'heroicon-o-envelope',
        'phone' => 'heroicon-o-phone',
        'mobile' => 'heroicon-o-device-phone-mobile',
        'address' => 'heroicon-o-map-pin',
        'website' => 'heroicon-o-globe-alt',
        'is_active' => 'heroicon-o-check-circle',
        'is_public' => 'heroicon-o-eye',
        'tags' => 'heroicon-o-tag',
        'notes' => 'heroicon-o-document-text',
        'short_note' => 'heroicon-o-document-text',
        'start_date' => 'heroicon-o-calendar',
        'end_date' => 'heroicon-o-calendar',
        'due_date' => 'heroicon-o-calendar',
        'created_at' => 'heroicon-o-calendar',
        'updated_at' => 'heroicon-o-calendar',
        'progress' => 'heroicon-o-chart-bar',
        'progress_percentage' => 'heroicon-o-chart-bar',
        'hourly_rate' => 'heroicon-o-currency-dollar',
        'estimated_hours' => 'heroicon-o-clock',
        'project_hours' => 'heroicon-o-clock',
        'estimate_hours' => 'heroicon-o-clock',
        'salary' => 'heroicon-o-currency-dollar',
        'amount' => 'heroicon-o-currency-dollar',
        'value' => 'heroicon-o-currency-dollar',
        'price' => 'heroicon-o-currency-dollar',
        'cost' => 'heroicon-o-currency-dollar',
        'revenue' => 'heroicon-o-currency-dollar',
        'budget' => 'heroicon-o-currency-dollar',
    ],

    /*
    |--------------------------------------------------------------------------
    | Relationship Mappings
    |--------------------------------------------------------------------------
    |
    | Define how to resolve relationship field values to their related models.
    |
    */
    'relationship_mappings' => [
        'branch_id' => ['model' => 'App\\Models\\Branch', 'field' => 'name'],
        'user_id' => ['model' => 'App\\Models\\User', 'field' => 'name'],
        'assigned_to' => ['model' => 'App\\Models\\User', 'field' => 'name'],
        'created_by' => ['model' => 'App\\Models\\User', 'field' => 'name'],
        'updated_by' => ['model' => 'App\\Models\\User', 'field' => 'name'],
        'client_id' => ['model' => 'App\\Models\\Client', 'field' => 'name'],
        'customer_id' => ['model' => 'App\\Models\\Customer', 'field' => 'name'],
        'project_id' => ['model' => 'App\\Models\\Project', 'field' => 'name'],
        'department_id' => ['model' => 'App\\Models\\Department', 'field' => 'name'],
        'position_id' => ['model' => 'App\\Models\\Position', 'field' => 'name'],
        'designation_id' => ['model' => 'App\\Models\\Designation', 'field' => 'name'],
        'category_id' => ['model' => 'App\\Models\\Category', 'field' => 'name'],
        'task_category_id' => ['model' => 'App\\Models\\TaskCategory', 'field' => 'name'],
        'priority_id' => ['model' => 'App\\Models\\Priority', 'field' => 'name'],
        'status_id' => ['model' => 'App\\Models\\Status', 'field' => 'name'],
        'gender_id' => ['model' => 'App\\Models\\Gender', 'field' => 'name'],
        'marital_status_id' => ['model' => 'App\\Models\\MaritalStatus', 'field' => 'name'],
        'nationality_id' => ['model' => 'App\\Models\\Nationality', 'field' => 'name'],
        'country_id' => ['model' => 'App\\Models\\Country', 'field' => 'name'],
        'currency_id' => ['model' => 'App\\Models\\Currency', 'field' => 'name'],
        'client_group_id' => ['model' => 'App\\Models\\ClientGroup', 'field' => 'name'],
        'warehouse_id' => ['model' => 'App\\Models\\Warehouse', 'field' => 'name'],
        'parent_task_id' => ['model' => 'App\\Models\\Task', 'field' => 'name'],
        'parent_id' => ['model' => 'App\\Models\\', 'field' => 'name'], // Dynamic model
        'ticket_id' => ['model' => 'App\\Models\\Ticket', 'field' => 'title'],
        'opportunity_id' => ['model' => 'App\\Models\\Opportunity', 'field' => 'name'],
        'deal_id' => ['model' => 'App\\Models\\Deal', 'field' => 'name'],
        'contract_id' => ['model' => 'App\\Models\\Contract', 'field' => 'name'],
        'complaint_id' => ['model' => 'App\\Models\\Complaint', 'field' => 'title'],
        'shift_id' => ['model' => 'App\\Models\\Shift', 'field' => 'name'],
        'role_id' => ['model' => 'App\\Models\\Role', 'field' => 'name'],
        'permission_id' => ['model' => 'App\\Models\\Permission', 'field' => 'name'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Fields
    |--------------------------------------------------------------------------
    |
    | Fields that should be excluded from activity log display.
    |
    */
    'excluded_fields' => [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
        'remember_token',
        'email_verified_at',
        'password',
        'password_confirmation',
        'api_token',
        'last_login_at',
        'last_activity_at',
        'user_agent',
        'ip_address',
        'client_ip',
        'email_message_id',
        'email_sent_at',
        'sent_via_email',
        'is_system_generated',
        'is_internal',
        'is_from_client',
        'metadata',
        'attachments',
        'source',
        'time_spent',
        'repliable_type',
        'repliable_id',
        'user_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Locale Keys for Translatable Fields
    |--------------------------------------------------------------------------
    |
    | Locale keys used to detect translatable fields.
    |
    */
    'locale_keys' => [
        'en', 'ar', 'ku', 'fr', 'es', 'de', 'it', 'pt', 'ru', 'zh', 'ja', 'ko',
    ],

    /*
    |--------------------------------------------------------------------------
    | Enum Color Mappings
    |--------------------------------------------------------------------------
    |
    | Map Filament enum colors to Tailwind CSS classes.
    |
    */
    'enum_colors' => [
        'primary' => 'bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200',
        'success' => 'bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-200',
        'warning' => 'bg-yellow-100 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200',
        'danger' => 'bg-red-100 dark:bg-red-800 text-red-800 dark:text-red-200',
        'info' => 'bg-cyan-100 dark:bg-cyan-800 text-cyan-800 dark:text-cyan-200',
        'secondary' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
        'default' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
    ],

    /*
    |--------------------------------------------------------------------------
    | Change Status Colors
    |--------------------------------------------------------------------------
    |
    | Colors for different change statuses in the activity log.
    |
    */
    'change_status_colors' => [
        'added' => 'bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-200',
        'removed' => 'bg-red-100 dark:bg-red-800 text-red-800 dark:text-red-200',
        'modified' => 'bg-yellow-100 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200',
    ],

    /*
    |--------------------------------------------------------------------------
    | Icon SVG Paths
    |--------------------------------------------------------------------------
    |
    | SVG paths for Heroicons to be used in HTML.
    |
    */
    'icon_svg_paths' => [
        'heroicon-o-users' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z',
        'heroicon-o-building-storefront' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        'heroicon-o-building-office' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        'heroicon-o-folder-open' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
        'heroicon-o-rectangle-stack' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
        'heroicon-o-currency-dollar' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
        'heroicon-o-sparkles' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
        'heroicon-o-chat-bubble-left-right' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        'heroicon-o-exclamation-triangle' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z',
        'heroicon-o-document-text' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'heroicon-o-tag' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
        'heroicon-o-home' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'heroicon-o-user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'heroicon-o-user-circle' => 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'heroicon-o-clock' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'heroicon-o-shield-check' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'heroicon-o-key' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
        'heroicon-o-check-circle' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'heroicon-o-exclamation-circle' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'heroicon-o-globe-alt' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9',
        'heroicon-o-heart' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
        'heroicon-o-flag' => 'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9',
        'heroicon-o-user-group' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        'heroicon-o-identification' => 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2',
        'heroicon-o-cog-6-tooth' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c-.292-.24-.437-.613-.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        'heroicon-o-envelope' => 'M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'heroicon-o-phone' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
        'heroicon-o-device-phone-mobile' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
        'heroicon-o-map-pin' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
        'heroicon-o-calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'heroicon-o-chart-bar' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        'heroicon-o-eye' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
        'heroicon-o-speaker-wave' => 'M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z',
        'heroicon-o-megaphone' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
        'heroicon-o-chat-bubble-left' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        'heroicon-o-clipboard-document-list' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'heroicon-o-plus' => 'M12 4v16m8-8H4',
        'heroicon-o-minus' => 'M20 12H4',
        'heroicon-o-pencil' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'heroicon-o-trash' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
        'heroicon-o-arrow-uturn-left' => 'M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3',
        'heroicon-o-eye' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
        'heroicon-o-arrow-down-tray' => 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3',
        'heroicon-o-arrow-up-tray' => 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5',
        'heroicon-o-check-circle' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'heroicon-o-x-circle' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        'heroicon-o-question-mark-circle' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    |
    | Default values for various configurations.
    |
    */
    'defaults' => [
        'model_icon' => 'heroicon-o-document-text',
        'field_icon' => 'heroicon-o-cog-6-tooth',
        'max_string_length' => 50,
        'max_simple_key_value_items' => 5,
        'min_locale_keys_for_translatable' => 2,
    ],
];
