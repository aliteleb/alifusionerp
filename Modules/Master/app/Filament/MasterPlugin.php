<?php

namespace Modules\Master\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class MasterPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Master';
    }

    public function getId(): string
    {
        return 'master';
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
