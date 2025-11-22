<?php

namespace Modules\Core\Filament\Resources\MaritalStatuses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class MaritalStatusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Translate::make()
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255),
                    ])
                    ->locales(appLocales())
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label(__('Is Active'))
                    ->default(true)
                    ->required(),
            ]);
    }
}


