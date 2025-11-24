<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\SurveyResponse;
use App\Services\TenantDatabaseService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class RecentActivityWidget extends BaseWidget
{
    public function getTableHeading(): ?string
    {
        return __('Recent Survey Responses');
    }

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // Connect to current tenant database
        $facility = Auth::user()?->facility ?? \App\Models\Facility::first();
        if ($facility) {
            TenantDatabaseService::switchToTenant($facility);
        }

        return $table
            ->query(
                SurveyResponse::with(['survey', 'customer'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('survey.title')
                    ->label(__('Survey'))
                    ->formatStateUsing(fn ($record) => $record->survey?->getTranslation('title', app()->getLocale()))
                    ->limit(30)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable()
                    ->placeholder(__('Anonymous')),

                Tables\Columns\TextColumn::make('completion_percentage')
                    ->label(__('Progress'))
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 100 => 'success',
                        $state >= 75 => 'info',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state.'%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('average_rating')
                    ->label(__('Rating'))
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 1).' ⭐' : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('is_complete')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state) => $state ? __('Complete') : __('In Progress'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Submitted'))
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(__('View'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (SurveyResponse $record) => route('filament.admin.resources.survey-responses.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading(__('No recent activity'))
            ->emptyStateDescription(__('Survey responses will appear here once customers start submitting them.'))
            ->paginated(false);
    }
}
