<?php

namespace Modules\Core\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum ReplyType: string implements HasColor, HasIcon, HasLabel
{
    case REPLY = 'reply';
    case INTERNAL_NOTE = 'internal_note';
    case STATUS_CHANGE = 'status_change';
    case SYSTEM = 'system';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::REPLY => __('Reply'),
            self::INTERNAL_NOTE => __('Internal Note'),
            self::STATUS_CHANGE => __('Status Change'),
            self::SYSTEM => __('System Message'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::REPLY => 'info',
            self::INTERNAL_NOTE => 'warning',
            self::STATUS_CHANGE => 'purple',
            self::SYSTEM => 'gray',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::REPLY => Heroicon::ChatBubbleLeftEllipsis,
            self::INTERNAL_NOTE => Heroicon::PencilSquare,
            self::STATUS_CHANGE => Heroicon::ArrowPath,
            self::SYSTEM => Heroicon::ComputerDesktop,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->getLabel(),
        ])->toArray();
    }
}
