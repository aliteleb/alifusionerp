<?php

namespace Modules\Core\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;

class Reports extends Page
{
    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::ChartBar;

    public static function getNavigationGroup(): ?string
    {
        return __('Administration');
    }

    public static function getNavigationLabel(): string
    {
        return __('Reports');
    }

    public static function shouldRegisterNavigation(?Panel $panel = null): bool
    {
        // Only show in admin panel, not in master panel
        $panel = $panel ?? Filament::getCurrentPanel();
        return $panel?->getId() === 'admin';
    }

    public function getTitle(): string
    {
        return __('Reports');
    }

    protected string $view = 'filament.pages.reports';
}
