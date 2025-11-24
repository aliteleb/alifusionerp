<?php

namespace Modules\Survey\Filament\Resources\SurveyResponseResource\RelationManagers;

use App\Enums\SurveyQuestionType;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\Components\Rating;

class QuestionResponsesRelationManager extends RelationManager
{
    protected static string $relationship = 'questionResponses';

    protected static ?string $recordTitleAttribute = 'question.question_text';

    // Listen for refresh event from the parent edit page
    protected $listeners = ['refreshQuestionResponsesRelationManager' => '$refresh'];

    public function getTableHeading(): string
    {
        return __('Answers');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Question Information
                Forms\Components\Select::make('survey_question_id')
                    ->label(__('Question'))
                    ->options(function () {
                        // Get questions from the current survey only
                        $surveyId = $this->getOwnerRecord()->survey_id;

                        return \App\Models\SurveyQuestion::where('survey_id', $surveyId)
                            ->orderedByText()
                            ->get()
                            ->mapWithKeys(function ($question) {
                                $questionText = $question->getTranslation('question_text', app()->getLocale());
                                $typeLabel = $question->question_type->getLabel();

                                return [$question->id => "Q{$question->order}: {$questionText} ({$typeLabel})"];
                            });
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (callable $set, $state) {
                        // Clear response fields when question changes
                        $set('response_text', null);
                        $set('response_rating', null);
                    })
                    ->columnSpanFull(),

                // Response Content - Text
                Forms\Components\Textarea::make('response_text')
                    ->label(__('Text Response'))
                    ->rows(3)
                    ->required(function (Forms\Get $get) {
                        $questionId = $get('survey_question_id');
                        $question = $questionId ? \App\Models\SurveyQuestion::find($questionId) : null;

                        return $question && $question->question_type === \App\Enums\SurveyQuestionType::TEXT && $question->is_required;
                    })
                    ->visible(function (Forms\Get $get) {
                        $questionId = $get('survey_question_id');
                        $question = $questionId ? \App\Models\SurveyQuestion::find($questionId) : null;

                        return $question && $question->question_type === \App\Enums\SurveyQuestionType::TEXT;
                    })
                    ->columnSpanFull(),

                // Response Content - Rating
                Rating::make('response_rating')
                    ->label(__('Rating Response'))
                    ->stars(5)
                    ->color('warning')
                    ->required(function (Forms\Get $get) {
                        $questionId = $get('survey_question_id');
                        $question = $questionId ? \App\Models\SurveyQuestion::find($questionId) : null;

                        return $question && $question->question_type === \App\Enums\SurveyQuestionType::RATING && $question->is_required;
                    })
                    ->visible(function (Forms\Get $get) {
                        $questionId = $get('survey_question_id');
                        $question = $questionId ? \App\Models\SurveyQuestion::find($questionId) : null;

                        return $question && $question->question_type === \App\Enums\SurveyQuestionType::RATING;
                    })
                    ->columnSpan(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question.question_text')
            ->defaultSort('question.order')
            ->columns([
                Tables\Columns\TextColumn::make('question.order')
                    ->label(__('Q#'))
                    ->sortable()
                    ->width('60px'),

                Tables\Columns\TextColumn::make('question.question_text')
                    ->label(__('Question'))
                    ->formatStateUsing(fn ($record) => $record && $record->question ? $record->question->getTranslation('question_text', app()->getLocale()) : __('Unknown Question'))
                    ->limit(50)
                    ->tooltip(fn ($record) => $record && $record->question ? $record->question->getTranslation('question_text', app()->getLocale()) : __('Unknown Question'))
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('question.question_type')
                    ->label(__('Type'))
                    ->formatStateUsing(fn ($state) => $state?->getLabel())
                    ->badge()
                    ->color(fn ($state) => $state?->getColor())
                    ->icon(fn ($state) => $state?->getIcon()),

                Tables\Columns\TextColumn::make('text_value')
                    ->label(__('Text Response'))
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->text_value)
                    ->placeholder(__('No text response')),

                RatingColumn::make('rating_value')
                    ->label(__('Rating'))
                    ->stars(5)
                    ->color('warning')
                    ->extraAttributes(fn ($record) => $record->rating_value == 0 ? ['class' => 'empty-stars'] : [])
                    ->getStateUsing(fn ($record) => $record->rating_value ?? 0)
                    ->placeholder(__('No rating')),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('survey_question_id')
                    ->label(__('Question'))
                    ->options(function () {
                        return \App\Models\SurveyQuestion::orderedByText()
                            ->get()
                            ->mapWithKeys(function ($question) {
                                return [$question->id => $question->getTranslation('question_text', app()->getLocale())];
                            });
                    })
                    ->searchable()
                    ->placeholder(__('All Questions')),

                Tables\Filters\SelectFilter::make('question_type')
                    ->label(__('Question Type'))
                    ->options(SurveyQuestionType::getFilamentOptions())
                    ->placeholder(__('All Types'))
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->whereHas('question', function (Builder $q) use ($data) {
                                $q->where('question_type', $data['value']);
                            });
                        }
                    }),

                Tables\Filters\TernaryFilter::make('is_skipped')
                    ->label(__('Response Status'))
                    ->placeholder(__('All Responses'))
                    ->trueLabel(__('Skipped Only'))
                    ->falseLabel(__('Answered Only')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('Add Question Response'))
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Set proper field mapping for database
                        $data['survey_response_id'] = $this->getOwnerRecord()->id;
                        $data['created_by'] = Auth::id();
                        $data['answered_at'] = now();
                        $data['is_skipped'] = false; // Default to not skipped since user provided a response

                        // Map response fields based on question type
                        $question = \App\Models\SurveyQuestion::find($data['survey_question_id']);
                        if ($question) {
                            if ($question->question_type === \App\Enums\SurveyQuestionType::TEXT) {
                                $data['text_value'] = $data['response_text'] ?? null;
                                $data['rating_value'] = null;
                                // If no text provided, mark as skipped
                                if (empty($data['response_text'])) {
                                    $data['is_skipped'] = true;
                                }
                            } elseif ($question->question_type === \App\Enums\SurveyQuestionType::RATING) {
                                $data['rating_value'] = $data['response_rating'] ?? null;
                                $data['text_value'] = null;
                                // If no rating provided, mark as skipped
                                if (empty($data['response_rating'])) {
                                    $data['is_skipped'] = true;
                                }
                            }
                        }

                        // Remove form-specific fields that don't exist in database
                        unset($data['response_text'], $data['response_rating']);

                        return $data;
                    })
                    ->after(function () {
                        // Refresh the parent survey response statistics
                        $this->getOwnerRecord()->calculateStatistics();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color('warning')
                    ->iconButton()
                    ->fillForm(function ($record): array {
                        $data = $record->toArray();

                        // Map database fields to form fields
                        $data['response_text'] = $record->text_value;
                        $data['response_rating'] = $record->rating_value;

                        return $data;
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['updated_by'] = Auth::id();

                        // Map response fields based on question type
                        $question = \App\Models\SurveyQuestion::find($data['survey_question_id']);
                        if ($question) {
                            if ($question->question_type === \App\Enums\SurveyQuestionType::TEXT) {
                                $data['text_value'] = $data['response_text'] ?? null;
                                $data['rating_value'] = null;
                                // If no text provided, mark as skipped
                                $data['is_skipped'] = empty($data['response_text']);
                            } elseif ($question->question_type === \App\Enums\SurveyQuestionType::RATING) {
                                $data['rating_value'] = $data['response_rating'] ?? null;
                                $data['text_value'] = null;
                                // If no rating provided, mark as skipped
                                $data['is_skipped'] = empty($data['response_rating']);
                            }
                        }

                        // Remove form-specific fields
                        unset($data['response_text'], $data['response_rating']);

                        return $data;
                    })
                    ->after(function () {
                        // Refresh the parent survey response statistics
                        $this->getOwnerRecord()->calculateStatistics();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->color('danger')
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_skipped')
                        ->label(__('Mark as Skipped'))
                        ->icon('heroicon-o-forward')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update([
                                'is_skipped' => true,
                                'updated_by' => Auth::id(),
                            ]));
                        })
                        ->requiresConfirmation(),
                ])
                    ->label(__('Actions'))
                    ->color('primary'),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('Add First Response'))
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['question'])
                ->whereHas('question') // Only include records that have a valid question
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }

    private function getQuestionType(?int $questionId): ?string
    {
        if (! $questionId) {
            return null;
        }

        $question = \App\Models\SurveyQuestion::find($questionId);

        return $question?->question_type?->value;
    }

    protected function canCreate(): bool
    {
        return false;
    }
}
