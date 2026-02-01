<?php

return [
    /*
    |--------------------------------------------------------------------------
    | KMC M&E System Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration settings specific to the Kibaha
    | Municipal Council Monitoring and Evaluation System.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | System Information
    |--------------------------------------------------------------------------
    */
    'system' => [
        'name' => env('APP_NAME', 'KMC M&E System'),
        'version' => env('SYSTEM_VERSION', '1.0.0'),
        'admin_email' => env('SYSTEM_ADMIN_EMAIL', 'admin@kmc.go.tz'),
        'support_email' => env('SYSTEM_SUPPORT_EMAIL', 'support@kmc.go.tz'),
        'timezone' => env('APP_TIMEZONE', 'Africa/Dar_es_Salaam'),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'max_file_size' => env('MAX_FILE_SIZE', 10240), // KB
        'allowed_file_types' => explode(',', env('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx')),
        'storage_path' => 'uploads',
    ],

    /*
    |--------------------------------------------------------------------------
    | Photo Upload Settings
    |--------------------------------------------------------------------------
    */
    'photos' => [
        'max_size' => env('PHOTO_MAX_SIZE', 10240), // KB
        'allowed_types' => explode(',', env('PHOTO_ALLOWED_TYPES', 'jpg,jpeg,png,gif')),
        'storage_path' => 'photos',
        'thumbnail_path' => 'thumbnails',
        'thumbnail_width' => env('PHOTO_THUMBNAIL_WIDTH', 300),
        'thumbnail_height' => env('PHOTO_THUMBNAIL_HEIGHT', 200),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'enable_two_factor' => env('ENABLE_TWO_FACTOR', false),
        'session_timeout' => env('SESSION_TIMEOUT', 120), // minutes
        'max_login_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration' => env('LOCKOUT_DURATION', 300), // seconds
        'password_min_length' => 8,
        'password_require_uppercase' => true,
        'password_require_lowercase' => true,
        'password_require_numbers' => true,
        'password_require_symbols' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'email_enabled' => env('EMAIL_NOTIFICATIONS_ENABLED', true),
        'from_email' => env('NOTIFICATION_FROM_EMAIL', 'noreply@kmc.go.tz'),
        'from_name' => env('NOTIFICATION_FROM_NAME', 'KMC M&E System'),
        'sms_enabled' => env('SMS_NOTIFICATIONS_ENABLED', false),
        'sms_provider' => env('SMS_PROVIDER', 'twilio'),
        'sms_settings' => [
            'twilio_sid' => env('SMS_TWILIO_SID'),
            'twilio_token' => env('SMS_TWILIO_TOKEN'),
            'twilio_from' => env('SMS_TWILIO_FROM'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Settings
    |--------------------------------------------------------------------------
    */
    'backup' => [
        'enabled' => env('BACKUP_ENABLED', true),
        'schedule' => env('BACKUP_SCHEDULE', 'daily'),
        'retention_days' => env('BACKUP_RETENTION_DAYS', 30),
        'disk' => env('BACKUP_DISK', 'local'),
        'backup_path' => 'backups',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    */
    'api' => [
        'rate_limit' => env('API_RATE_LIMIT', 60), // requests per minute
        'rate_limit_window' => env('API_RATE_LIMIT_WINDOW', 1), // minutes
        'version' => env('API_VERSION', 'v1'),
        'pagination_per_page' => 20,
        'max_pagination_per_page' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Map Settings
    |--------------------------------------------------------------------------
    */
    'maps' => [
        'default_center' => [
            'lat' => env('DEFAULT_MAP_CENTER_LAT', -6.7645),
            'lng' => env('DEFAULT_MAP_CENTER_LNG', 38.9042),
        ],
        'default_zoom' => env('DEFAULT_MAP_ZOOM', 10),
        'provider' => env('MAP_PROVIDER', 'openstreetmap'),
        'tile_url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    */
    'currency' => [
        'default' => env('DEFAULT_CURRENCY', 'TZS'),
        'supported' => explode(',', env('SUPPORTED_CURRENCIES', 'TZS,USD,EUR,GBP')),
        'symbols' => [
            'TZS' => 'TSh',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Date/Time Settings
    |--------------------------------------------------------------------------
    */
    'datetime' => [
        'date_format' => env('DATE_FORMAT', 'Y-m-d'),
        'time_format' => env('TIME_FORMAT', 'H:i:s'),
        'datetime_format' => env('DATETIME_FORMAT', 'Y-m-d H:i:s'),
        'timezone' => env('APP_TIMEZONE', 'Africa/Dar_es_Salaam'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reporting Settings
    |--------------------------------------------------------------------------
    */
    'reports' => [
        'max_rows' => env('REPORTS_MAX_ROWS', 10000),
        'export_timeout' => env('EXPORT_TIMEOUT', 300), // seconds
        'supported_formats' => ['pdf', 'excel', 'csv'],
        'default_format' => 'pdf',
    ],

    /*
    |--------------------------------------------------------------------------
    | Project Settings
    |--------------------------------------------------------------------------
    */
    'projects' => [
        'default_status' => 'PLANNING',
        'statuses' => [
            'PLANNING' => 'Planning',
            'ACTIVE' => 'Active',
            'SUSPENDED' => 'Suspended',
            'COMPLETED' => 'Completed',
            'CANCELLED' => 'Cancelled',
        ],
        'health_status_thresholds' => [
            'on_track' => 70,
            'at_risk' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Expenditure Settings
    |--------------------------------------------------------------------------
    */
    'expenditures' => [
        'default_status' => 'PENDING',
        'statuses' => [
            'PENDING' => 'Pending',
            'APPROVED' => 'Approved',
            'REJECTED' => 'Rejected',
            'VERIFIED' => 'Verified',
        ],
        'categories' => [
            'Materials' => 'Materials',
            'Labor' => 'Labor',
            'Equipment' => 'Equipment',
            'Transport' => 'Transport',
            'Services' => 'Services',
            'Other' => 'Other',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Entry Settings
    |--------------------------------------------------------------------------
    */
    'data_entries' => [
        'default_verification_status' => 'PENDING',
        'verification_statuses' => [
            'PENDING' => 'Pending',
            'VERIFIED' => 'Verified',
            'REJECTED' => 'Rejected',
        ],
        'frequencies' => [
            'MONTHLY' => 'Monthly',
            'QUARTERLY' => 'Quarterly',
            'ANNUALLY' => 'Annually',
            'ADHOC' => 'Ad-hoc',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'enable_monitoring' => true,
        'slow_query_threshold' => 1000, // milliseconds
        'memory_usage_threshold' => 128, // MB
        'disk_usage_threshold' => 80, // percentage
    ],
];
