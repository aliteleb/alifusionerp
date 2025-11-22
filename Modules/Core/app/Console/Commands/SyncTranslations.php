<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class SyncTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:translations {--dry-run : Show what would be updated without making changes} {--keep-unused : Keep translation keys that are not used in source files} {--clear-cache : Clear translation cache after sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync translation labels from Filament resources, pages, blade files, and __() functions to JSON translation files. Use --keep-unused to preserve old keys.';

    /**
     * Translation files and their paths
     *
     * @var array
     */
    protected $translationFiles = [
        'en' => 'lang/en.json',
        'ar' => 'lang/ar.json',
        'ku' => 'lang/ku.json',
    ];

    /**
     * Source directories to scan
     *
     * @var array
     */
    protected $sourceDirectories = [
        'app' => ['php'],
        'resources/views' => ['blade.php'],
        'resources/js' => ['js', 'jsx', 'vue', 'ts', 'tsx'],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Starting Laravel HR system translation sync...');

        // Extract all translation keys from source files
        $extractedKeys = $this->extractTranslationKeys();

        if (empty($extractedKeys)) {
            $this->warn('⚠️  No translation keys found in source files.');

            return 0;
        }

        $this->info(sprintf('📝 Found %d unique translation keys', count($extractedKeys)));

        // Process each translation file
        foreach ($this->translationFiles as $locale => $filePath) {
            $this->processTranslationFile($locale, $filePath, $extractedKeys);
        }

        // Clear translation cache if requested
        if ($this->option('clear-cache')) {
            $this->clearTranslationCache();
        }

        if ($this->option('dry-run')) {
            $this->info('🔍 Dry run completed. No files were modified.');
        } else {
            $this->info('✅ Laravel HR system translation sync completed successfully!');
        }

        return 0;
    }

    /**
     * Extract translation keys from all source files
     */
    protected function extractTranslationKeys(): array
    {
        $keys = [];

        foreach ($this->sourceDirectories as $directory => $extensions) {
            $directoryKeys = $this->extractKeysFromDirectory($directory, $extensions);
            $keys = array_merge($keys, $directoryKeys);
        }

        // Remove duplicates, empty keys, and normalize
        $keys = array_filter($keys, function ($key) {
            return ! empty(trim($key));
        });

        // Remove duplicates (case-sensitive)
        $keys = array_unique($keys);

        // Remove any remaining duplicates that might have different whitespace
        $normalizedKeys = [];
        foreach ($keys as $key) {
            $normalized = trim($key);
            if (! empty($normalized) && ! in_array($normalized, $normalizedKeys)) {
                $normalizedKeys[] = $normalized;
            }
        }

        // Sort alphabetically for consistent output
        // Keep the order as found in source files, don't sort
        // sort($normalizedKeys);

        return $normalizedKeys;
    }

    /**
     * Extract translation keys from a specific directory
     */
    protected function extractKeysFromDirectory(string $directory, array $extensions): array
    {
        $keys = [];
        $fullPath = base_path($directory);

        if (! File::exists($fullPath)) {
            $this->warn(sprintf('⚠️  Directory not found: %s', $fullPath));

            return [];
        }

        // Get all files recursively with specified extensions
        $files = File::allFiles($fullPath);
        $targetFiles = collect($files)->filter(function ($file) use ($extensions) {
            $extension = $file->getExtension();
            $filename = $file->getFilename();

            // Handle blade.php files
            if (str_ends_with($filename, '.blade.php')) {
                return in_array('blade.php', $extensions);
            }

            return in_array($extension, $extensions);
        });

        $this->info(sprintf('🔍 Scanning %d files in %s...', $targetFiles->count(), $directory));

        foreach ($targetFiles as $file) {
            $content = File::get($file->getPathname());
            $fileKeys = $this->extractKeysFromContent($content, $file->getRelativePathname());
            $keys = array_merge($keys, $fileKeys);
        }

        return $keys;
    }

    /**
     * Extract translation keys from file content
     */
    protected function extractKeysFromContent(string $content, string $filename): array
    {
        $keys = [];

        // Patterns to match translation functions
        $patterns = [
            // __('key') - single quoted strings (handles escaped quotes and apostrophes)
            '/__\(\'((?:[^\'\\\\]|\\\\.)*)\'(?:,|\))/u',
            // __("key") - double quoted strings (handles escaped quotes and apostrophes)
            '/__\("((?:[^"\\\\]|\\\\.)*)"(?:,|\))/u',
            // __(`key`) - backticks/template literals
            '/__\(`([^`]*)`(?:,|\))/u',
            // Blade syntax: {{ __('key') }} - single quotes
            '/\{\{?\s*__\(\'((?:[^\'\\\\]|\\\\.)*)\'\)\s*\}\}?/u',
            // Blade syntax: {{ __("key") }} - double quotes
            '/\{\{?\s*__\("((?:[^"\\\\]|\\\\.)*)"\s*\}\}?/u',
            // __('key', ['param' => $value]) - single quotes with parameters
            '/__\(\'((?:[^\'\\\\]|\\\\.)*)\'\s*,\s*\[[^\]]*\]\)/u',
            // __("key", ['param' => $value]) - double quotes with parameters
            '/__\("((?:[^"\\\\]|\\\\.)*)"\s*,\s*\[[^\]]*\]\)/u',
            // __('key', $array) - single quotes with array variable
            '/__\(\'((?:[^\'\\\\]|\\\\.)*)\'\s*,\s*\$[a-zA-Z_][a-zA-Z0-9_]*\)/u',
            // __("key", $array) - double quotes with array variable
            '/__\("((?:[^"\\\\]|\\\\.)*)"\s*,\s*\$[a-zA-Z_][a-zA-Z0-9_]*\)/u',
            // Filament resource methods - getNavigationLabel(), getModelLabel(), etc.
            '/->(?:getNavigationLabel|getModelLabel|getPluralModelLabel|getTitle|getHeading)\(\)\s*:\s*string\s*\{\s*return\s+__\(\'((?:[^\'\\\\]|\\\\.)*)\'\)/u',
            '/->(?:getNavigationLabel|getModelLabel|getPluralModelLabel|getTitle|getHeading)\(\)\s*:\s*string\s*\{\s*return\s+__\("((?:[^"\\\\]|\\\\.)*)"\)/u',
            // Filament form components - label(), placeholder(), helperText()
            '/->(?:label|placeholder|helperText|tooltip)\(\s*__\(\'((?:[^\'\\\\]|\\\\.)*)\'\)/u',
            '/->(?:label|placeholder|helperText|tooltip)\(\s*__\("((?:[^"\\\\]|\\\\.)*)"\)/u',
            // Filament table columns - label()
            '/->label\(\s*__\(\'((?:[^\'\\\\]|\\\\.)*)\'\)/u',
            '/->label\(\s*__\("((?:[^"\\\\]|\\\\.)*)"\)/u',
            // Filament actions - label(), modalHeading(), etc.
            '/->(?:label|modalHeading|modalSubmitActionLabel|modalCancelActionLabel)\(\s*__\(\'((?:[^\'\\\\]|\\\\.)*)\'\)/u',
            '/->(?:label|modalHeading|modalSubmitActionLabel|modalCancelActionLabel)\(\s*__\("((?:[^"\\\\]|\\\\.)*)"\)/u',
            // Enum methods - getLabel(), getDescription()
            '/public\s+function\s+(?:getLabel|getDescription)\(\)[^{]*\{\s*return\s+__\(\'((?:[^\'\\\\]|\\\\.)*)\'\)/u',
            '/public\s+function\s+(?:getLabel|getDescription)\(\)[^{]*\{\s*return\s+__\("((?:[^"\\\\]|\\\\.)*)"\)/u',
            // Match statements with __() calls
            '/match\s*\([^)]*\)\s*\{\s*(?:[^}]*__\(\'((?:[^\'\\\\]|\\\\.)*)\'\)[^}]*\s*=>\s*[^,}]+[,}])+/u',
            '/match\s*\([^)]*\)\s*\{\s*(?:[^}]*__\("((?:[^"\\\\]|\\\\.)*)"\)[^}]*\s*=>\s*[^,}]+[,}])+/u',
            // Validation messages
            '/\'([^\']*)\'\s*=>\s*__\(\'((?:[^\'\\\\]|\\\\.)*)\'\)/u',
            '/"([^"]*)"\s*=>\s*__\("((?:[^"\\\\]|\\\\.)*)"\)/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $key) {
                    // Skip empty keys and system/technical identifiers
                    $key = trim($key);
                    if (! empty($key) && $this->isTranslatableKey($key)) {
                        // Additional deduplication check before adding
                        if (! in_array($key, $keys, true)) {
                            $keys[] = $key;
                            $this->line(sprintf('📄 %s: "%s"', $filename, $key), null, 'v');
                        }
                    }
                }
            }
        }

        // Final deduplication for this file's keys
        return array_unique($keys);
    }

    /**
     * Check if a key should be translated
     */
    protected function isTranslatableKey(string $key): bool
    {
        // Skip technical identifiers
        $technicalPatterns = [
            '/^https?:\/\//',           // URLs
            '/^[a-z_]+\.[a-z_]+$/',    // Config keys like 'app.name'
            '/^[A-Z_]+$/',             // Constants like 'ADMIN'
            '/^heroicon-/',            // Icon names
            '/^[a-z]+-[a-z]+-[a-z]+$/', // CSS classes or technical IDs
            '/^\d+$/',                 // Pure numbers
            '/^(primary|secondary|success|danger|warning|info)$/', // Color names
            '/^(admin|master|tenant)$/', // System identifiers
            '/^[a-z_]+\(\)$/',         // Method calls like 'getLabel()'
            '/^\$[a-zA-Z_][a-zA-Z0-9_]*$/', // Variables like '$model'
            '/^[a-zA-Z_][a-zA-Z0-9_]*\s*\(\)$/', // Function calls
        ];

        foreach ($technicalPatterns as $pattern) {
            if (preg_match($pattern, $key)) {
                return false;
            }
        }

        // Skip field names that look like database columns or form fields
        $fieldPatterns = [
            '/^[a-z_]+_[a-z_]+$/',     // snake_case field names like 'present_address_city'
            '/^[a-z_]+_[a-z_]+_[a-z_]+$/', // triple snake_case like 'present_address_post_code'
            '/^current_[a-z_]+$/',     // current_* fields like 'current_password'
            '/^[a-z_]+_confirmation$/', // confirmation fields like 'password_confirmation'
            '/^[a-z_]+_id$/',          // foreign key fields like 'user_id'
            '/^[a-z_]+_at$/',          // timestamp fields like 'created_at'
            '/^is_[a-z_]+$/',          // boolean fields like 'is_active'
            '/^has_[a-z_]+$/',         // boolean fields like 'has_permission'
            '/^no_of_[a-z_]+$/',       // count fields like 'no_of_kids'
            '/^date_of_[a-z_]+$/',     // date fields like 'date_of_birth'
            '/^emergency_[a-z_]+$/',   // emergency fields like 'emergency_contact'
            '/^alter_[a-z_]+$/',       // alternative fields like 'alter_emergency_contact'
            '/^home_[a-z_]+$/',        // home fields like 'home_email'
            '/^cell_[a-z_]+$/',        // cell fields like 'cell_phone'
            '/^blood_[a-z_]+$/',       // blood fields like 'blood_group'
            '/^health_[a-z_]+$/',      // health fields like 'health_condition'
            '/^disabilities_[a-z_]+$/', // disabilities fields like 'disabilities_desc'
        ];

        // Skip option keys that are used in arrays but shouldn't be translated
        $optionKeyPatterns = [
            '/^(draft|active|expired|terminated|cancelled|pending)$/', // Contract status keys
            '/^(service|maintenance|development|consulting|support|license|subscription)$/', // Contract type keys
            '/^(hourly|milestone|fixed_price)$/', // Pricing type keys
            '/^(en|ar|ku|es|fr|de)$/', // Language code keys
            '/^(everyone|customize)$/', // Permission keys
            '/^(title|description|subject|name|summary)$/', // Generic field keys
            '/^(address|phone|religion|sos|password)$/', // Form field keys
            '/^(primary|secondary|success|danger|warning|info|gray)$/', // Color keys
            '/^(admin|master|tenant)$/', // System keys
            '/^(yes|no|true|false)$/', // Boolean keys
            '/^(male|female)$/', // Gender keys
            '/^(open|closed|new|in_progress|completed)$/', // Status keys
        ];

        foreach ($fieldPatterns as $pattern) {
            if (preg_match($pattern, $key)) {
                return false;
            }
        }

        foreach ($optionKeyPatterns as $pattern) {
            if (preg_match($pattern, $key)) {
                return false;
            }
        }

        // Additional checks for placeholder patterns
        if (str_contains($key, ':') && preg_match('/^[a-z_]+(:[a-z_]+)*$/', $key)) {
            // This looks like a placeholder pattern (e.g., ':model was created')
            return true;
        }

        return true;
    }

    /**
     * Process a single translation file
     */
    protected function processTranslationFile(string $locale, string $filePath, array $extractedKeys): void
    {
        $fullPath = base_path($filePath);
        $this->info(sprintf('🔄 Processing %s translation file...', strtoupper($locale)));

        // Load existing translations
        $existingTranslations = $this->loadExistingTranslations($fullPath);

        // Remove duplicates from existing translations before processing
        $existingTranslations = $this->removeDuplicatesFromTranslations($existingTranslations);

        // Track changes
        $newKeys = [];
        $removedKeys = [];
        $duplicatesRemoved = [];
        $updatedTranslations = [];

        // Start with existing translations to preserve order
        $updatedTranslations = $existingTranslations;

        // Add new keys from source files at the end
        foreach ($extractedKeys as $key) {
            if (! array_key_exists($key, $existingTranslations)) {
                // Key doesn't exist, add it at the end
                $updatedTranslations[$key] = $key;
                $newKeys[] = $key;
                $this->line(sprintf('➕ Added: "%s"', $key));
            } elseif ($existingTranslations[$key] === $key) {
                // Key exists but not translated
                $this->line(sprintf('⏭️  Skipped (untranslated): "%s"', $key), null, 'v');
            } else {
                // Key exists and is translated
                $this->line(sprintf('✅ Preserved (translated): "%s" => "%s"', $key, $existingTranslations[$key]), null, 'v');
            }
        }

        // Handle unused key removal (default behavior, unless --keep-unused is specified)
        if (! $this->option('keep-unused')) {
            $unusedKeys = array_diff(array_keys($existingTranslations), $extractedKeys);

            foreach ($unusedKeys as $unusedKey) {
                unset($updatedTranslations[$unusedKey]);
                $removedKeys[] = $unusedKey;
                $this->line(sprintf('🗑️  Removed (unused): "%s"', $unusedKey));
            }

            if (count($removedKeys) > 0) {
                $this->info(sprintf('📉 Found %d unused translation keys', count($removedKeys)));
            }
        }

        // Final comprehensive duplicate removal and cleanup
        $originalCount = count($updatedTranslations);
        $updatedTranslations = $this->removeDuplicatesFromTranslations($updatedTranslations);
        $finalCount = count($updatedTranslations);

        if ($originalCount !== $finalCount) {
            $duplicatesCount = $originalCount - $finalCount;
            $this->info(sprintf('🧹 Removed %d duplicate entries', $duplicatesCount));
        }

        // Save the file if there are changes or duplicates were found
        $hasChanges = count($newKeys) > 0 || count($removedKeys) > 0 || ($originalCount !== $finalCount);

        if ($hasChanges) {
            if (! $this->option('dry-run')) {
                $this->saveTranslationFile($fullPath, $updatedTranslations);
            }

            if (count($newKeys) > 0) {
                $this->info(sprintf('📊 Added %d new translation keys', count($newKeys)));
            }

            if (count($removedKeys) > 0 && ! $this->option('keep-unused')) {
                $this->info(sprintf('📊 Removed %d unused translation keys', count($removedKeys)));
            }
        } else {
            $this->info('📊 No changes needed');
        }

        $this->info(sprintf('📊 Total keys: %d', count($updatedTranslations)));
    }

    /**
     * Remove duplicates from translations array while preserving order
     */
    protected function removeDuplicatesFromTranslations(array $translations): array
    {
        $cleanTranslations = [];
        $seenKeys = [];

        foreach ($translations as $key => $value) {
            // Normalize the key by trimming whitespace
            $normalizedKey = trim($key);

            // Skip empty keys
            if (empty($normalizedKey)) {
                continue;
            }

            // Check for duplicate keys (case-sensitive)
            if (! in_array($normalizedKey, $seenKeys, true)) {
                $cleanTranslations[$normalizedKey] = $value;
                $seenKeys[] = $normalizedKey;
            }
        }

        // Keep original order, don't sort
        return $cleanTranslations;
    }

    /**
     * Load existing translations from file
     */
    protected function loadExistingTranslations(string $filePath): array
    {
        if (! File::exists($filePath)) {
            $this->warn(sprintf('⚠️  Translation file not found, will create: %s', $filePath));

            return [];
        }

        $content = File::get($filePath);
        $translations = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->warn(sprintf('⚠️  Invalid JSON in file, starting fresh: %s', $filePath));

            return [];
        }

        return $translations ?: [];
    }

    /**
     * Save translations to file
     */
    protected function saveTranslationFile(string $filePath, array $translations): void
    {
        // Ensure directory exists
        $directory = dirname($filePath);
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Format JSON nicely
        $json = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        File::put($filePath, $json);
        $this->line(sprintf('💾 Saved: %s', $filePath));
    }

    /**
     * Clear translation cache
     */
    protected function clearTranslationCache(): void
    {
        $this->info('🧹 Clearing translation cache...');

        // Clear Laravel's translation cache
        $this->call('cache:clear');

        // Clear custom translation cache keys that might be used in the app
        $cacheKeys = [
            'translations_en',
            'translations_ar',
            'translations_ku',
            'master_translations',
            'tenant_translations',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        $this->info('✅ Translation cache cleared');
    }
}
