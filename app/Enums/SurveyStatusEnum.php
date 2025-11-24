<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SurveyStatusEnum: string implements HasColor, HasIcon, HasLabel
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case COMPLETED = 'completed';
    case ARCHIVED = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => __('Draft'),
            self::ACTIVE => __('Active'),
            self::PAUSED => __('Paused'),
            self::COMPLETED => __('Completed'),
            self::ARCHIVED => __('Archived'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::ACTIVE => 'success',
            self::PAUSED => 'warning',
            self::COMPLETED => 'info',
            self::ARCHIVED => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-o-document-text',
            self::ACTIVE => 'heroicon-o-play-circle',
            self::PAUSED => 'heroicon-o-pause-circle',
            self::COMPLETED => 'heroicon-o-check-circle',
            self::ARCHIVED => 'heroicon-o-archive-box',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::DRAFT => __('Survey is being prepared'),
            self::ACTIVE => __('Survey is accepting responses'),
            self::PAUSED => __('Survey is temporarily paused'),
            self::COMPLETED => __('Survey has ended'),
            self::ARCHIVED => __('Survey is archived'),
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [
            $case->value => $case->getLabel(),
        ])->toArray();
    }

    public function canReceiveResponses(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canBeEdited(): bool
    {
        return in_array($this, [self::DRAFT, self::PAUSED], true);
    }
}
