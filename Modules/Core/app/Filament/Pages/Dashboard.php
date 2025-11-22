<?php

namespace Modules\Core\Filament\Pages;

use Modules\Core\Filament\Widgets\ActivityLogWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getTitle(): string
    {
        return __('Dashboard');
    }

    public function getWidgets(): array
    {
        return [
            ActivityLogWidget::class,
        ];
    }

    public function getWidgetData(): array
    {
        return [
            // TODO: Add widget configurations when widgets are created
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
            'xl' => 3,
            '2xl' => 3,
        ];
    }
}

