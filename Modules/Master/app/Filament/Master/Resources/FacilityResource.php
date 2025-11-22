<?php

namespace Modules\Master\Filament\Master\Resources;

use Modules\Master\Filament\Master\Resources\FacilityResource\Pages;
use Modules\Master\Filament\Master\Resources\FacilityResource\Pages\ListFacilities;
use Modules\Master\Entities\Facility;
use Modules\Core\Services\TenantDatabaseService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class FacilityResource extends Resource
{
    protected static ?string $model = Facility::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    public static function getNavigationLabel(): string
    {
        return __('Facilities');
    }

    public static function getModelLabel(): string
    {
        return __('Facility');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Facilities');
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Facilities');
    }

    public static function form(Schema $schema): Schema
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
                    ->locales(masterLocales())
                    ->columnSpanFull(),
                TextInput::make('subdomain')
                    ->label(__('Subdomain'))
                    ->columnSpanFull()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->prefix('https://')
                    ->suffix('.'.config('app.domain'))
                    ->extraAttributes(['dir' => 'ltr'])
                    ->maxLength(255),
                TextInput::make('database_name')
                    ->label(__('Database Name'))
                    ->extraAttributes(['dir' => 'ltr'])
                    ->columnSpanFull()
                    ->maxLength(255)
                    ->helperText(__('Auto-generated based on subdomain. Leave empty to use default naming.'))
                    ->placeholder(fn ($get) => $get('subdomain') ? TenantDatabaseService::getTenantDatabasePrefix().strtolower(str_replace('-', '_', $get('subdomain'))) : '')
                    ->disabled(fn ($get) => empty($get('subdomain')))
                    ->reactive(),
                Toggle::make('is_active')
                    ->label(__('Is Active'))
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                TextColumn::make('subdomain')
                    ->label(__('Subdomain'))
                    ->searchable(),
                TextColumn::make('database_name')
                    ->label(__('Database Name'))
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-circle-stack')
                    ->color('gray'),
                ToggleColumn::make('is_active')
                    ->label(__('Is Active')),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('open_tenant')
                    ->label(__('Open Tenant'))
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->url(fn ($record) => 'https://'.$record->subdomain.'.'.config('app.domain').'/admin')
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->is_active),
                EditAction::make()
                    ->label(__('Edit')),
                DeleteAction::make()
                    ->label(__('Delete')),
                Action::make('quick_delete')
                    ->label(__('Force Delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation(false)
                    ->action(fn ($record) => $record->forceDelete()),
                RestoreAction::make()
                    ->label(__('Restore')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('Delete Selected')),
                    ForceDeleteBulkAction::make()
                        ->label(__('Force Delete Selected')),
                    RestoreBulkAction::make()
                        ->label(__('Restore Selected')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFacilities::route('/'),
            // 'create' => Pages\CreateFacility::route('/create'),
            // 'edit' => Pages\EditFacility::route('/{record}/edit'),
        ];
    }
}
