<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\Survey;
use App\Services\TenantDatabaseService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class SurveyPerformanceWidget extends BaseWidget
{
    public function getTableHeading(): ?string
    {
        return __('Top Performing Surveys');
    }

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        // Connect to current tenant database
        $facility = Auth::user()?->facility ?? \App\Models\Facility::first();
        if ($facility) {
            TenantDatabaseService::switchToTenant($facility);
        }

        return $table
            ->query(
                Survey::withCount('responses')
                    ->withAvg('responses', 'completion_percentage')
                    ->withAvg('responses', 'average_rating')
                    ->having('responses_count', '>', 0)
                    ->orderByDesc('responses_count')
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Survey Title'))
                    ->formatStateUsing(fn ($record) => $record->getTranslation('title', app()->getLocale()))
                    ->limit(40)
                    ->searchable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'draft' => 'warning',
                        'paused' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('responses_count')
                    ->label(__('Responses'))
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('responses_avg_completion_percentage')
                    ->label(__('Avg Completion'))
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 80 => 'success',
                        $state >= 60 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 1).'%' : '0%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('responses_avg_average_rating')
                    ->label(__('Avg Rating'))
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' ⭐' : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(__('View'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (Survey $record) => route('filament.admin.resources.surveys.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading(__('No surveys found'))
            ->emptyStateDescription(__('Create surveys to see performance metrics here.'))
            ->paginated(false);
    }
}
