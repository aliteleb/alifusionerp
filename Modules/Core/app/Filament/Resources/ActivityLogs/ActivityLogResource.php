<?php

namespace Modules\Core\Filament\Resources\ActivityLogs;

use Modules\Core\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use Modules\Core\Filament\Resources\ActivityLogs\Pages\ViewActivityLog;
use Modules\Core\Filament\Resources\ActivityLogs\Schemas\ActivityLogForm;
use Modules\Core\Filament\Resources\ActivityLogs\Tables\ActivityLogsTable;
use Modules\Core\Entities\ActivityLog;
use Modules\Core\Entities\User;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return __('Activity Log');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Activity Logs');
    }

    public static function getNavigationLabel(): string
    {
        return __('Activity Logs');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('System');
    }

    public static function form(Schema $schema): Schema
    {
        return ActivityLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivityLogsTable::configure($table);
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
            'index' => ListActivityLogs::route('/'),
            'view' => ViewActivityLog::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('access_activity_logs');
    }

    public static function canView($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user->can('view_activity_logs');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canForceDelete($record): bool
    {
        return false;
    }

    public static function canForceDeleteAny(): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}


