<?php

namespace Modules\Master\Filament\Master\Resources;

use Modules\System\Actions\CreateBackupAction;
use Modules\System\Actions\RestoreBackupAction;
use Modules\Master\Filament\Master\Resources\BackupResource\Pages\ListBackups;
use Modules\Core\Entities\Backup;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class BackupResource extends Resource
{
    protected static ?string $model = Backup::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';

    public static function getNavigationGroup(): ?string
    {
        return __('Administration');
    }

    public static function getModelLabel(): string
    {
        return __('Backup');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Backups');
    }

    public static function getNavigationLabel(): string
    {
        return __('Backups');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                TextColumn::make('disk')
                    ->label(__('Disk'))
                    ->searchable(),
                TextColumn::make('size')
                    ->label(__('Size'))
                    ->formatStateUsing(fn (?int $state) => $state ? round($state / 1024 / 1024, 2).' MB' : '0 MB')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('download')
                    ->label(__('Download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (Backup $record) => response()->download(Storage::disk($record->disk)->path($record->path))),
                RestoreBackupAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                CreateBackupAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBackups::route('/'),
        ];
    }

    public static function canAccess(): bool
    {
        return false;
    }
}
