<?php

namespace Modules\Survey\Filament\Resources;

use App\Models\SurveyResponse;
use App\Models\User;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Survey\Filament\Resources\RatingReportResource\Pages;
use UnitEnum;

class RatingReportResource extends Resource
{
    protected static ?string $model = SurveyResponse::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::ChartBarSquare;

    protected static UnitEnum|string|null $navigationGroup = null;

    public static function getNavigationLabel(): string
    {
        return __('Rating Reports');
    }

    public static function getModelLabel(): string
    {
        return __('Rating Report');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Rating Reports');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Survey Management');
    }

    public static function canAccess(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('access_rating_reports') : false;
    }

    public static function canViewAny(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('view_rating_reports') : false;
    }

    public static function canView($record): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentPanel()->auth()->user();

        return $user ? $user->can('view_rating_reports') : false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('response_uuid')
                    ->label(__('Response ID'))
                    ->searchable()
                    ->copyable()
                    ->tooltip(__('Click to copy'))
                    ->limit(10),

                Tables\Columns\TextColumn::make('survey.title')
                    ->label(__('Survey Title'))
                    ->getStateUsing(function (Model $record) {
                        return $record->survey->getTranslation('title', app()->getLocale()) ??
                               $record->survey->getTranslation('title', 'en');
                    })
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('customer.full_name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label(__('Branch'))
                    ->getStateUsing(function (Model $record) {
                        return $record->branch?->getTranslation('name', app()->getLocale()) ??
                               $record->branch?->getTranslation('name', 'en');
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('average_rating')
                    ->label(__('Average Rating'))
                    ->icon('heroicon-o-star')
                    ->color(function ($state) {
                        if (! $state) {
                            return 'gray';
                        }
                        if ($state >= 4.5) {
                            return 'success';
                        }
                        if ($state >= 3.5) {
                            return 'info';
                        }
                        if ($state >= 2.5) {
                            return 'gray';
                        }
                        if ($state >= 1.5) {
                            return 'warning';
                        }

                        return 'danger';
                    })
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        return $state ? number_format($state, 1).' ⭐' : __('No Rating');
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('rating_category')
                    ->label(__('Rating Category'))
                    ->getStateUsing(function (Model $record) {
                        if (! $record->average_rating) {
                            return __('No Rating');
                        }

                        if ($record->average_rating >= 4.5) {
                            return __('Excellent (5⭐)');
                        }
                        if ($record->average_rating >= 3.5) {
                            return __('Good (4⭐)');
                        }
                        if ($record->average_rating >= 2.5) {
                            return __('Average (3⭐)');
                        }
                        if ($record->average_rating >= 1.5) {
                            return __('Poor (2⭐)');
                        }

                        return __('Very Poor (1⭐)');
                    })
                    ->badge()
                    ->color(function ($record) {
                        if (! $record->average_rating) {
                            return 'gray';
                        }
                        if ($record->average_rating >= 4.5) {
                            return 'success';
                        }
                        if ($record->average_rating >= 3.5) {
                            return 'info';
                        }
                        if ($record->average_rating >= 2.5) {
                            return 'gray';
                        }
                        if ($record->average_rating >= 1.5) {
                            return 'warning';
                        }

                        return 'danger';
                    }),

                Tables\Columns\TextColumn::make('completion_percentage')
                    ->label(__('Completion %'))
                    ->formatStateUsing(function ($state) {
                        return number_format($state, 1).'%';
                    })
                    ->color(function ($state) {
                        if ($state >= 80) {
                            return 'success';
                        }
                        if ($state >= 50) {
                            return 'warning';
                        }

                        return 'danger';
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('feedback')
                    ->label(__('Feedback'))
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return $record->feedback;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('low_rating_reasons')
                    ->label(__('Low Rating Reasons'))
                    ->getStateUsing(function (Model $record) {
                        if (! $record->average_rating || $record->average_rating > 2) {
                            return null;
                        }

                        $reasons = $record->questionResponses()
                            ->whereNotNull('reason_for_rating')
                            ->where('rating_value', '<=', 2)
                            ->pluck('reason_for_rating')
                            ->filter()
                            ->unique()
                            ->take(3)
                            ->toArray();

                        return ! empty($reasons) ? implode('; ', $reasons) : null;
                    })
                    ->limit(100)
                    ->tooltip(function ($state) {
                        return $state;
                    })
                    ->toggleable()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('started_at')
                    ->label(__('Response Date'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('duration_display')
                    ->label(__('Response Time'))
                    ->getStateUsing(function (Model $record) {
                        if (! $record->started_at || ! $record->completed_at) {
                            return __('Incomplete');
                        }

                        $duration = $record->started_at->diffInMinutes($record->completed_at);

                        if ($duration < 1) {
                            return '< 1 '.__('min');
                        } elseif ($duration < 60) {
                            return $duration.' '.__('min');
                        } else {
                            $hours = floor($duration / 60);
                            $mins = $duration % 60;

                            return $hours.'h '.$mins.'m';
                        }
                    })
                    ->badge()
                    ->color(function ($record) {
                        if (! $record->started_at || ! $record->completed_at) {
                            return 'gray';
                        }
                        $duration = $record->started_at->diffInMinutes($record->completed_at);
                        if ($duration <= 5) {
                            return 'success';
                        }
                        if ($duration <= 15) {
                            return 'warning';
                        }

                        return 'danger';
                    })
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_verified')
                    ->label(__('Verified'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('rating_category')
                    ->label(__('Rating Category'))
                    ->options([
                        'excellent' => __('Excellent (4.5-5⭐)'),
                        'good' => __('Good (3.5-4.4⭐)'),
                        'average' => __('Average (2.5-3.4⭐)'),
                        'poor' => __('Poor (1.5-2.4⭐)'),
                        'very_poor' => __('Very Poor (1-1.4⭐)'),
                        'no_rating' => __('No Rating'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (! $data['value']) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'excellent' => $query->where('average_rating', '>=', 4.5),
                            'good' => $query->where('average_rating', '>=', 3.5)->where('average_rating', '<', 4.5),
                            'average' => $query->where('average_rating', '>=', 2.5)->where('average_rating', '<', 3.5),
                            'poor' => $query->where('average_rating', '>=', 1.5)->where('average_rating', '<', 2.5),
                            'very_poor' => $query->where('average_rating', '>=', 1.0)->where('average_rating', '<', 1.5),
                            'no_rating' => $query->whereNull('average_rating'),
                            default => $query,
                        };
                    })
                    ->indicator('Rating Category'),

                SelectFilter::make('survey_id')
                    ->label(__('Survey'))
                    ->options(function () {
                        return \App\Models\Survey::orderedByTitle()
                            ->get()
                            ->mapWithKeys(function ($survey) {
                                return [$survey->id => $survey->getTranslation('title', app()->getLocale())];
                            });
                    })
                    ->searchable()
                    ->preload(),

                SelectFilter::make('branch_id')
                    ->label(__('Branch'))
                    ->relationship('branch', 'name')
                    ->getOptionLabelFromRecordUsing(function (Model $record) {
                        return $record->getTranslation('name', app()->getLocale()) ??
                               $record->getTranslation('name', 'en');
                    })
                    ->searchable()
                    ->preload(),

                Filter::make('completion_rate')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('completion_from')
                                    ->label(__('Min Completion %'))
                                    ->numeric()
                                    ->placeholder('0'),
                                Forms\Components\TextInput::make('completion_to')
                                    ->label(__('Max Completion %'))
                                    ->numeric()
                                    ->placeholder('100'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['completion_from'],
                                function (Builder $query, $value) {
                                    return $query->where('completion_percentage', '>=', $value);
                                },
                            )
                            ->when(
                                $data['completion_to'],
                                function (Builder $query, $value) {
                                    return $query->where('completion_percentage', '<=', $value);
                                },
                            );
                    })
                    ->indicator('Completion Rate'),

                Filter::make('response_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label(__('From Date')),
                        Forms\Components\DatePicker::make('until')
                            ->label(__('Until Date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                function (Builder $query, $date) {
                                    return $query->whereDate('started_at', '>=', $date);
                                },
                            )
                            ->when(
                                $data['until'],
                                function (Builder $query, $date) {
                                    return $query->whereDate('started_at', '<=', $date);
                                },
                            );
                    })
                    ->indicator('Response Date'),

                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label(__('Verification Status'))
                    ->placeholder(__('All Responses'))
                    ->trueLabel(__('Verified Only'))
                    ->falseLabel(__('Unverified Only')),

                Tables\Filters\TernaryFilter::make('has_low_rating_feedback')
                    ->label(__('Has Low Rating Feedback'))
                    ->placeholder(__('All Responses'))
                    ->trueLabel(__('With Feedback'))
                    ->falseLabel(__('Without Feedback'))
                    ->queries(
                        true: function (Builder $query) {
                            return $query->whereHas('questionResponses', function ($q) {
                                $q->whereNotNull('reason_for_rating');
                            });
                        },
                        false: function (Builder $query) {
                            return $query->whereDoesntHave('questionResponses', function ($q) {
                                $q->whereNotNull('reason_for_rating');
                            });
                        },
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('View Details'))
                    ->modalHeading(function (Model $record) {
                        return __('Rating Details: :id', ['id' => $record->response_uuid]);
                    }),

                Tables\Actions\Action::make('export_single_pdf')
                    ->label(__('Export PDF'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->url(function (Model $record) {
                        return route('rating-report.pdf', ['response' => $record->id]);
                    })
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_selected_pdf')
                        ->label(__('Export Selected (PDF)'))
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('danger')
                        ->action(function ($records) {
                            $ids = $records->pluck('id')->toArray();

                            return redirect()->route('rating-report.bulk-pdf', ['ids' => implode(',', $ids)]);
                        }),

                    Tables\Actions\BulkAction::make('export_selected_excel')
                        ->label(__('Export Selected (Excel)'))
                        ->icon('heroicon-o-table-cells')
                        ->color('success')
                        ->action(function ($records) {
                            $ids = $records->pluck('id')->toArray();

                            return redirect()->route('rating-report.bulk-excel', ['ids' => implode(',', $ids)]);
                        }),

                    Tables\Actions\BulkAction::make('mark_verified')
                        ->label(__('Mark as Verified'))
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            return $records->each->update(['is_verified' => true]);
                        }),

                    Tables\Actions\BulkAction::make('mark_unverified')
                        ->label(__('Mark as Unverified'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            return $records->each->update(['is_verified' => false]);
                        }),
                ]),
            ])
            ->defaultSort('started_at', 'desc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->emptyStateHeading(__('No rating data found'))
            ->emptyStateDescription(__('When customers submit surveys with ratings, the data will appear here.'))
            ->emptyStateIcon('heroicon-o-star');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRatingReports::route('/'),
            'analytics' => Pages\RatingAnalytics::route('/analytics'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNotNull('average_rating')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}
