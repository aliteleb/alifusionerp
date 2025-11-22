<?php

namespace Modules\Core\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([

                    Select::make('branch_id')
                        ->label(__('Branch'))
                        ->relationship(
                            'branch',
                            'name',
                            fn ($query) => $query->orderByRaw("name->>'".app()->getLocale()."' NULLS LAST")
                        )
                        ->required(),

                    Translate::make()
                        ->schema([
                            TextInput::make('name')
                                ->label(__('Name'))
                                ->required()
                                ->maxLength(255),
                        ])
                        ->locales(appLocales())
                        ->columnSpanFull(),

                    TextInput::make('code')
                        ->label(__('Code'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),

                ]),

                Translate::make()
                    ->schema([
                        Textarea::make('description')
                            ->label(__('Description'))
                            ->rows(3),
                    ])
                    ->locales(appLocales())
                    ->columnSpanFull(),
            ])->columns(1);
    }
}


