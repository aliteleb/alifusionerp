<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum SurveyResponseStatusEnum: string implements HasColor, HasIcon, HasLabel
{
    case DRAFT = 'draft';
    case PARTIAL = 'partial';
    case COMPLETED = 'completed';
    case SUBMITTED = 'submitted';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => __('Draft'),
            self::PARTIAL => __('Partial'),
            self::COMPLETED => __('Completed'),
            self::SUBMITTED => __('Submitted'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PARTIAL => 'warning',
            self::COMPLETED => 'info',
            self::SUBMITTED => 'success',
        };
    }

    public function getIcon(): string|\BackedEnum|null
    {
        return match ($this) {
            self::DRAFT => Heroicon::DocumentText,
            self::PARTIAL => Heroicon::ArrowPath,
            self::COMPLETED => Heroicon::CheckCircle,
            self::SUBMITTED => Heroicon::PaperAirplane,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [
            $case->value => $case->getLabel(),
        ])->toArray();
    }
}
