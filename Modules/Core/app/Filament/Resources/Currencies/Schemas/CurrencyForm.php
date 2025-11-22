<?php

namespace Modules\Core\Filament\Resources\Currencies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('country_id')
                    ->relationship('country', 'name', fn ($query) => $query->orderByRaw("name->>'en'"))
                    ->label(__('Country'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Translate::make()
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Title'))
                            ->required(),
                    ])
                    ->locales(appLocales()),
                TextInput::make('symbol')
                    ->label(__('Symbol'))
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->default(true)
                    ->required(),
            ])->columns(1);
    }
}


