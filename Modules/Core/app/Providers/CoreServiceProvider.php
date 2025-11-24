<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;

class CoreServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Core';

    protected string $nameLower = 'core';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));

        // Load helper functions
        $this->loadHelpers();
    }

    /**
     * Load helper functions from the Core module.
     */
    protected function loadHelpers(): void
    {
        $helperPath = module_path($this->name, 'app/Helpers');

        if (is_dir($helperPath)) {
            $helpers = [
                'General.php',
                'TenantHelpers.php',
                'TranslationHelper.php',
                'TranslationGlobalHelpers.php',
                'SEO.php',
            ];

            foreach ($helpers as $helper) {
                $file = $helperPath.'/'.$helper;
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        }
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            \Modules\Core\Console\Commands\MasterMigrateCommand::class,
            \Modules\Core\Console\Commands\TenantMigrateCommand::class,
            \Modules\Core\Console\Commands\TenantSeedCommand::class,
            \Modules\Core\Console\Commands\TenantDatabaseCommand::class,
            \Modules\Core\Console\Commands\SyncTranslations::class,
        ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $configPath = module_path($this->name, 'Config');

        if (is_dir($configPath)) {
            $this->publishes([
                $configPath => config_path($this->nameLower),
            ], 'config');
            $this->mergeConfigFrom(
                module_path($this->name, 'Config/config.php'), $this->nameLower
            );
        }
    }

    /**
     * Merge config from the given path recursively.
     */
    protected function merge_config_from(string $path, string $key): void
    {
        $existing = config($key, []);
        $module_config = require $path;

        config([$key => array_replace_recursive($existing, $module_config)]);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }
}
