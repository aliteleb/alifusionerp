<?php

namespace Database\Seeders;

use Modules\Core\Entities\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class MasterSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::truncate();
        Schema::disableForeignKeyConstraints();

        $settings = [
            // General Information - Master Application Details
            [
                'key' => 'app_name',
                'value' => 'Ali Fusion ERP Master',
                'helper_text' => 'Master Application Name',
            ],
            [
                'key' => 'app_description',
                'value' => 'Ali Fusion ERP - Master Panel',
                'helper_text' => 'Description',
            ],
            [
                'key' => 'company_name',
                'value' => 'Your Company Name',
                'helper_text' => 'Company Name',
            ],
            [
                'key' => 'company_email',
                'value' => 'info@yourcompany.com',
                'helper_text' => 'Company Email',
            ],
            [
                'key' => 'company_phone',
                'value' => '+1-234-567-8900',
                'helper_text' => 'Company Phone',
            ],
            [
                'key' => 'company_website',
                'value' => 'https://yourcompany.com',
                'helper_text' => 'Company Website',
            ],
            [
                'key' => 'company_address',
                'value' => '123 Business Street, City, Country',
                'helper_text' => 'Company Address',
            ],
            [
                'key' => 'support_email',
                'value' => 'support@yourcompany.com',
                'helper_text' => 'Email for tenant support requests',
            ],
            [
                'key' => 'support_phone',
                'value' => '+1-234-567-8901',
                'helper_text' => 'Phone for tenant support',
            ],

            // Master Panel Branding (File uploads will be null initially)
            [
                'key' => 'logo',
                'value' => null,
                'helper_text' => 'Master Panel Logo',
            ],
            [
                'key' => 'favicon',
                'value' => null,
                'helper_text' => 'Master Panel Favicon',
            ],
            [
                'key' => 'login_image',
                'value' => null,
                'helper_text' => 'Master Login Image',
            ],

            // Localization
            [
                'key' => 'locales',
                'value' => json_encode(['en', 'ar', 'ku']),
                'helper_text' => 'List of locale codes available for the master panel and tenants',
            ],

            // System Configuration
            [
                'key' => 'max_tenants',
                'value' => '100',
                'helper_text' => 'Maximum number of tenant facilities allowed',
            ],
            [
                'key' => 'auto_tenant_creation',
                'value' => '1',
                'helper_text' => 'Automatically create database when a new facility is added',
            ],
            [
                'key' => 'auto_migration',
                'value' => '1',
                'helper_text' => 'Automatically run migrations for new tenant databases',
            ],
            [
                'key' => 'backup_notifications',
                'value' => '1',
                'helper_text' => 'Send notifications for backup operations',
            ],
            [
                'key' => 'backup_retention_days',
                'value' => '30',
                'helper_text' => 'Number of days to keep backup files',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'helper_text' => 'Enable maintenance mode for all tenant panels',
            ],
            [
                'key' => 'maintenance_message',
                'value' => 'System is currently under maintenance. Please try again later.',
                'helper_text' => 'Message to show when maintenance mode is enabled',
            ],

            // Default Tenant Settings
            [
                'key' => 'default_currency',
                'value' => 'USD',
                'helper_text' => 'Default currency for new tenants',
            ],
            [
                'key' => 'default_timezone',
                'value' => 'UTC',
                'helper_text' => 'Default timezone for new tenants',
            ],
            [
                'key' => 'default_language',
                'value' => 'en',
                'helper_text' => 'Default language for new tenants',
            ],

            // Email Settings - SMTP Configuration
            [
                'key' => 'mail_mailer',
                'value' => 'smtp',
                'helper_text' => 'The mail service driver (smtp, sendmail, mailgun, etc.)',
            ],
            [
                'key' => 'mail_host',
                'value' => 'smtp.gmail.com',
                'helper_text' => 'The mail server host address',
            ],
            [
                'key' => 'mail_port',
                'value' => '587',
                'helper_text' => 'The mail server port (usually 587 for TLS or 465 for SSL)',
            ],
            [
                'key' => 'mail_username',
                'value' => 'your-email@gmail.com',
                'helper_text' => 'Your mail server username',
            ],
            [
                'key' => 'mail_password',
                'value' => 'your-app-password',
                'helper_text' => 'Your mail server password',
            ],
            [
                'key' => 'mail_encryption',
                'value' => 'tls',
                'helper_text' => 'The encryption type (tls, ssl, or null)',
            ],
            [
                'key' => 'mail_from_address',
                'value' => 'noreply@yourcompany.com',
                'helper_text' => 'The email address that will appear in the "From" field',
            ],
            [
                'key' => 'mail_from_name',
                'value' => 'Ali Fusion ERP',
                'helper_text' => 'The name that will appear in the `From` field',
            ],

            // Copyright & Legal
            [
                'key' => 'global_copyright_text',
                'value' => 'Ali Fusion ERP',
                'helper_text' => 'This text will appear in the footer of all tenant applications',
            ],
            [
                'key' => 'global_legal_entity_name',
                'value' => 'Your Company Legal Name',
                'helper_text' => 'The legal name of your organization for official documents',
            ],
            [
                'key' => 'global_registration_number',
                'value' => '',
                'helper_text' => 'Official registration or license number',
            ],
            [
                'key' => 'global_enable_document_watermark',
                'value' => '1',
                'helper_text' => 'Add company watermark to all generated documents across all tenants',
            ],
            [
                'key' => 'global_document_confidentiality_notice',
                'value' => 'This document is confidential and proprietary. Unauthorized distribution is prohibited.',
                'helper_text' => 'This notice will appear on PDFs and official documents across all tenants',
            ],
            [
                'key' => 'global_system_version',
                'value' => '1.0.0',
                'helper_text' => 'Current version of your Ali Fusion ERP implementation',
            ],
            [
                'key' => 'global_license_information',
                'value' => 'This software is licensed for use by the organization. All rights reserved.',
                'helper_text' => 'Software licensing and usage terms for all tenants',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                [
                    'key' => $setting['key'],
                ],
                $setting
            );
        }

        Schema::enableForeignKeyConstraints();
    }
}
