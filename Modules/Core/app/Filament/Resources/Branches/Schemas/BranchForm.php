<?php

namespace Modules\Core\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class BranchForm
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
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('Is Active'))
                            ->default(true)
                            ->required(),
                        Toggle::make('is_hq')
                            ->label(__('Is Headquarters'))
                            ->default(false)
                            ->helperText(__('Mark this branch as headquarters/main office')),
                    ]),
            ]);
    }
}


