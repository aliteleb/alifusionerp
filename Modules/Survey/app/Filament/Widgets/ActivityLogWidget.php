<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class ActivityLogWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('Recent Activity');
    }

    // Temporary disable the widget
    public static function canView(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ActivityLog::query()
                    ->with('user')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Time'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('User')),
                Tables\Columns\TextColumn::make('action')
                    ->label(__('Action'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(ucfirst($state)))
                    ->colors([
                        'success' => 'created',
                        'warning' => 'updated',
                        'danger' => 'deleted',
                    ]),
                Tables\Columns\TextColumn::make('model_type')
                    ->label(__('Model'))
                    ->formatStateUsing(fn (?string $state): string => $state ? __(class_basename($state)) : __('-')),

            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(__('View'))
                    ->url(fn (ActivityLog $record): string => route('filament.admin.resources.activity-logs.view', $record))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-eye'),
            ])
            ->paginated(false);
    }
}
