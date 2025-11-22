<?php

namespace Modules\System\Actions\Facility\Seeding;

use Modules\Master\Entities\Facility;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeedSettingsDataAction
{
    /**
     * Seed default facility settings.
     */
    public function execute(Facility $facility): void
    {
        Log::info('Seeding default settings for facility', ['facility_id' => $facility->id]);

        $now = now();

        // Get default values from master settings
        $defaultCurrency = masterSettings('default_currency', 'USD');
        $defaultTimezone = masterSettings('default_timezone', 'UTC');
        $defaultLanguage = masterSettings('default_language', 'en');

        $defaultSettings = $this->getDefaultSettings($facility, $defaultCurrency, $defaultTimezone, $defaultLanguage);

        foreach ($defaultSettings as $settingData) {
            $this->createSetting($settingData, $facility, $now);
        }

        // Clear settings cache for this facility
        $subdomain = $facility->subdomain;
        $cacheKey = $subdomain.'_settings';
        Cache::forget($cacheKey);

        Log::info('Successfully completed settings seeding', ['facility_id' => $facility->id]);
    }

    /**
     * Get default settings configuration
     */
    private function getDefaultSettings(Facility $facility, string $defaultCurrency, string $defaultTimezone, string $defaultLanguage): array
    {
        return [
            // General Information Settings (from Settings page)
            [
                'key' => 'app_name',
                'value' => $facility->name,
                'helper_text' => 'Application name for this facility',
            ],
            [
                'key' => 'email',
                'value' => 'contact@'.$facility->subdomain.'.alifusionerp.local',
                'helper_text' => 'Facility email address',
            ],
            [
                'key' => 'phone',
                'value' => '',
                'helper_text' => 'Facility phone number',
            ],
            [
                'key' => 'tax_no',
                'value' => '',
                'helper_text' => 'Tax registration number',
            ],
            [
                'key' => 'address',
                'value' => '',
                'helper_text' => 'Facility address',
            ],
            [
                'key' => 'footer_text',
                'value' => '© '.date('Y').' '.$facility->name.'. All rights reserved.',
                'helper_text' => 'Footer text displayed on reports',
            ],
            [
                'key' => 'website',
                'value' => '',
                'helper_text' => 'Facility website URL',
            ],
            [
                'key' => 'currency_id',
                'value' => '1', // Default to first currency
                'helper_text' => 'Default currency for this facility',
            ],
            [
                'key' => 'timezone',
                'value' => $defaultTimezone,
                'helper_text' => 'Default timezone for this facility',
            ],
            [
                'key' => 'direction',
                'value' => 'ltr',
                'helper_text' => 'Text direction (ltr or rtl)',
            ],
            [
                'key' => 'floating_number',
                'value' => '1',
                'helper_text' => 'Enable floating point numbers',
            ],

            // Localization Settings
            [
                'key' => 'locales',
                'value' => json_encode(['en', 'ar', 'ku']),
                'helper_text' => 'Supported locales for multi-language content',
            ],

            // Logo & Images (empty by default)
            [
                'key' => 'logo',
                'value' => '',
                'helper_text' => 'Facility logo image path',
            ],
            [
                'key' => 'login_image',
                'value' => '',
                'helper_text' => 'Login page background image path',
            ],
            [
                'key' => 'favicon',
                'value' => '',
                'helper_text' => 'Facility favicon image path',
            ],

            // Email Settings (SMTP Configuration)
            [
                'key' => 'mail_mailer',
                'value' => 'smtp',
                'helper_text' => 'Mail service driver',
            ],
            [
                'key' => 'mail_host',
                'value' => 'smtp.gmail.com',
                'helper_text' => 'Mail server host address',
            ],
            [
                'key' => 'mail_port',
                'value' => '587',
                'helper_text' => 'Mail server port',
            ],
            [
                'key' => 'mail_username',
                'value' => '',
                'helper_text' => 'Mail server username',
            ],
            [
                'key' => 'mail_password',
                'value' => '',
                'helper_text' => 'Mail server password',
            ],
            [
                'key' => 'mail_encryption',
                'value' => 'tls',
                'helper_text' => 'Mail encryption type (tls, ssl, or null)',
            ],
            [
                'key' => 'mail_from_address',
                'value' => 'noreply@'.$facility->subdomain.'.alifusionerp.local',
                'helper_text' => 'Email address for outgoing emails',
            ],
            [
                'key' => 'mail_from_name',
                'value' => $facility->name.' Ali Fusion ERP',
                'helper_text' => 'Name for outgoing emails',
            ],

            // Customer Data Settings
            [
                'key' => 'customer_data_retention_days',
                'value' => '365',
                'helper_text' => 'Number of days to retain customer data',
            ],

            // Task Management Settings
            [
                'key' => 'task_status_change_comment_mandatory',
                'value' => false,
                'helper_text' => 'Require comments when changing task status (false = optional, true = mandatory)',
            ],
            [
                'key' => 'task_status_change_comment_optional',
                'value' => true,
                'helper_text' => 'Allow optional comments for status changes (false = disabled, true = enabled)',
            ],
            [
                'key' => 'task_status_change_tracking',
                'value' => true,
                'helper_text' => 'Track status change history (false = disabled, true = enabled)',
            ],

            // Ticket Management Settings
            [
                'key' => 'ticket_status_change_comment_mandatory',
                'value' => false,
                'helper_text' => 'Require comments when changing ticket status (false = optional, true = mandatory)',
            ],
            [
                'key' => 'ticket_status_change_comment_optional',
                'value' => true,
                'helper_text' => 'Allow optional comments for status changes (false = disabled, true = enabled)',
            ],
            [
                'key' => 'ticket_status_change_tracking',
                'value' => true,
                'helper_text' => 'Track status change history (false = disabled, true = enabled)',
            ],
        ];
    }

    /**
     * Create a single setting
     */
    private function createSetting(array $settingData, Facility $facility, $now): void
    {
        // Check if this specific setting already exists by key (graceful handling of duplicates)
        $exists = DB::table('settings')
            ->where('key', $settingData['key'])
            ->exists();

        if (! $exists) {
            try {
                DB::table('settings')->insert([
                    'key' => $settingData['key'],
                    'value' => $settingData['value'],
                    'helper_text' => $settingData['helper_text'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                Log::debug('Created facility setting', ['key' => $settingData['key'], 'facility_id' => $facility->id]);
            } catch (Exception $e) {
                // Handle potential unique constraint violations gracefully
                if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'UNIQUE')) {
                    Log::debug('Setting already exists (duplicate entry), skipping', ['key' => $settingData['key'], 'facility_id' => $facility->id]);
                } else {
                    Log::warning('Failed to create facility setting', [
                        'key' => $settingData['key'],
                        'facility_id' => $facility->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } else {
            Log::debug('Facility setting already exists, skipping', ['key' => $settingData['key'], 'facility_id' => $facility->id]);
        }
    }
}
