<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\SurveyResponse;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentResponsesTableWidget extends BaseWidget
{
    public function getTableHeading(): ?string
    {
        return __('Recent Survey Responses');
    }

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SurveyResponse::query()
                    ->with(['customer', 'survey'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('survey.title')
                    ->label(__('Survey'))
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Response Date'))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'in_progress',
                        'danger' => 'abandoned',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'completed' => __('Completed'),
                        'in_progress' => __('In Progress'),
                        'abandoned' => __('Abandoned'),
                        default => __('Unknown'),
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->label(__('View'))
                    ->icon('heroicon-m-eye')
                    ->url(fn (SurveyResponse $record): string => route('filament.admin.resources.survey-responses.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading(__('No responses yet'))
            ->emptyStateDescription(__('When customers complete surveys, they will appear here.'))
            ->defaultPaginationPageOption(5);
    }
}
