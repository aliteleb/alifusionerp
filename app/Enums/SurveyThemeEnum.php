<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SurveyThemeEnum: string implements HasColor, HasIcon, HasLabel
{
    case DEFAULT = 'default';
    case MODERN = 'modern';
    case CLASSIC = 'classic';
    case MINIMAL = 'minimal';
    case CORPORATE = 'corporate';

    public function getLabel(): string
    {
        return match ($this) {
            self::DEFAULT => __('Default'),
            self::MODERN => __('Modern'),
            self::CLASSIC => __('Classic'),
            self::MINIMAL => __('Minimal'),
            self::CORPORATE => __('Corporate'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DEFAULT => 'primary',
            self::MODERN => 'info',
            self::CLASSIC => 'warning',
            self::MINIMAL => 'gray',
            self::CORPORATE => 'success',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::DEFAULT => 'heroicon-m-swatch',
            self::MODERN => 'heroicon-m-sparkles',
            self::CLASSIC => 'heroicon-m-building-library',
            self::MINIMAL => 'heroicon-m-minus',
            self::CORPORATE => 'heroicon-m-building-office',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $theme) => [$theme->value => $theme->getLabel()])
            ->toArray();
    }

    public static function getColoredOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $theme) => [
                $theme->value => [
                    'label' => $theme->getLabel(),
                    'color' => $theme->getColor(),
                    'icon' => $theme->getIcon(),
                ],
            ])
            ->toArray();
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::DEFAULT => __('Standard theme with balanced colors and modern design'),
            self::MODERN => __('Contemporary theme with vibrant colors and gradients'),
            self::CLASSIC => __('Traditional theme with elegant typography and muted colors'),
            self::MINIMAL => __('Clean and simple theme with minimal visual elements'),
            self::CORPORATE => __('Professional theme suitable for business environments'),
        };
    }

    public function getCssClass(): string
    {
        return match ($this) {
            self::DEFAULT => 'theme-default',
            self::MODERN => 'theme-modern',
            self::CLASSIC => 'theme-classic',
            self::MINIMAL => 'theme-minimal',
            self::CORPORATE => 'theme-corporate',
        };
    }

    public function getPrimaryColor(): string
    {
        return match ($this) {
            self::DEFAULT => '#3B82F6',
            self::MODERN => '#8B5CF6',
            self::CLASSIC => '#F59E0B',
            self::MINIMAL => '#6B7280',
            self::CORPORATE => '#059669',
        };
    }

    public function supportsDarkMode(): bool
    {
        return match ($this) {
            self::DEFAULT => true,
            self::MODERN => true,
            self::CLASSIC => false,
            self::MINIMAL => true,
            self::CORPORATE => false,
        };
    }
}

