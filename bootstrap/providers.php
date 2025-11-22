<?php

return [
    // Core Module - Must be loaded first
    Modules\Core\Providers\CoreServiceProvider::class,
    
    // Application Providers
    App\Providers\MacroServiceProvider::class,
    App\Providers\TenantServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    Nwidart\Modules\LaravelModulesServiceProvider::class,
    
    // Module Service Providers
    Modules\Master\Providers\Filament\MasterPanelProvider::class,
    Modules\Core\Providers\Filament\AdminPanelProvider::class,

    // Modules\ReferenceData\Providers\ReferenceDataServiceProvider::class,
    // Modules\System\Providers\SystemServiceProvider::class,
];
