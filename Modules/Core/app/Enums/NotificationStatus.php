<?php

namespace Modules\Core\Enums;

enum NotificationStatus: string
{
    case Success = 'success';
    case Warning = 'warning';
    case Danger = 'danger';
    case Info = 'info';

    public function label(): string
    {
        return match ($this) {
            self::Success => __('Success'),
            self::Warning => __('Warning'),
            self::Danger => __('Danger'),
            self::Info => __('Info'),
        };
    }
}
