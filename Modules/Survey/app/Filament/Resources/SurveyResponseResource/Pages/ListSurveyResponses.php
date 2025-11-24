<?php

namespace Modules\Survey\Filament\Resources\SurveyResponseResource\Pages;

use App\Models\SurveyResponse;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Survey\Filament\Resources\SurveyResponseResource;

class ListSurveyResponses extends ListRecords
{
    protected static string $resource = SurveyResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('Add Response'))
                ->icon('heroicon-o-plus')
                ->action(function (array $data) {
                    // Prepare the survey response data
                    $responseData = [
                        'survey_id' => $data['survey_id'],
                        'customer_id' => $data['customer_id'],
                        'started_at' => $data['started_at'] ?? now(),
                        'completed_at' => $data['completed_at'] ?? now(),
                        'is_complete' => $data['is_complete'] ?? false,
                        'is_verified' => $data['is_verified'] ?? false,
                        'is_anonymous' => $data['is_anonymous'] ?? false,
                        'feedback' => $data['feedback'] ?? null,
                        'notes' => $data['notes'] ?? null,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ];

                    // Create the survey response
                    $surveyResponse = SurveyResponse::create($responseData);

                    // Handle question responses if provided
                    if (isset($data['question_responses']) && is_array($data['question_responses'])) {
                        foreach ($data['question_responses'] as $questionId => $responseValue) {
                            // Skip reason fields in this loop - they'll be handled separately
                            if (str_starts_with($questionId, 'reason_')) {
                                continue;
                            }

                            if ($responseValue !== null && $responseValue !== '') {
                                // Get the question to determine response type
                                $question = \App\Models\SurveyQuestion::find($questionId);

                                if ($question) {
                                    $questionResponseData = [
                                        'survey_response_id' => $surveyResponse->id,
                                        'survey_question_id' => $questionId,
                                        'created_by' => Auth::id(),
                                        'answered_at' => now(),
                                    ];

                                    // Map response based on question type
                                    if ($question->question_type->value === 'rating') {
                                        $questionResponseData['rating_value'] = (int) $responseValue;

                                        // Check if there's a reason for low rating (2 stars or less)
                                        $reasonKey = 'reason_'.$questionId;
                                        if ((int) $responseValue <= 2 && isset($data['question_responses'][$reasonKey]) && ! empty($data['question_responses'][$reasonKey])) {
                                            $questionResponseData['reason_for_rating'] = $data['question_responses'][$reasonKey];
                                        }
                                    } else {
                                        $questionResponseData['text_value'] = $responseValue;
                                    }

                                    \App\Models\SurveyQuestionResponse::create($questionResponseData);
                                }
                            }
                        }
                    }

                    // Calculate completion statistics
                    $this->calculateResponseStatistics($surveyResponse);

                    // Show success notification
                    \Filament\Notifications\Notification::make()
                        ->title(__('Survey Response Created'))
                        ->body(__('The survey response has been created successfully.'))
                        ->success()
                        ->send();

                    return $surveyResponse;
                }),

        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('All Responses'))
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->badge($this->getModel()::count()),

            '1_star' => Tab::make(__('1 Star'))
                ->icon('heroicon-o-star')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('average_rating', '>=', 1.0)->where('average_rating', '<', 1.5))
                ->badge($this->getModel()::where('average_rating', '>=', 1.0)->where('average_rating', '<', 1.5)->count())
                ->badgeColor('danger'),

            '2_stars' => Tab::make(__('2 Stars'))
                ->icon('heroicon-o-star')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('average_rating', '>=', 1.5)->where('average_rating', '<', 2.5))
                ->badge($this->getModel()::where('average_rating', '>=', 1.5)->where('average_rating', '<', 2.5)->count())
                ->badgeColor('warning'),

            '3_stars' => Tab::make(__('3 Stars'))
                ->icon('heroicon-o-star')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('average_rating', '>=', 2.5)->where('average_rating', '<', 3.5))
                ->badge($this->getModel()::where('average_rating', '>=', 2.5)->where('average_rating', '<', 3.5)->count())
                ->badgeColor('gray'),

            '4_stars' => Tab::make(__('4 Stars'))
                ->icon('heroicon-o-star')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('average_rating', '>=', 3.5)->where('average_rating', '<', 4.5))
                ->badge($this->getModel()::where('average_rating', '>=', 3.5)->where('average_rating', '<', 4.5)->count())
                ->badgeColor('info'),

            '5_stars' => Tab::make(__('5 Stars'))
                ->icon('heroicon-o-star')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('average_rating', '>=', 4.5)->where('average_rating', '<=', 5.0))
                ->badge($this->getModel()::where('average_rating', '>=', 4.5)->where('average_rating', '<=', 5.0)->count())
                ->badgeColor('success'),

            'no_rating' => Tab::make(__('No Rating'))
                ->icon('heroicon-o-minus-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('average_rating'))
                ->badge($this->getModel()::whereNull('average_rating')->count())
                ->badgeColor('gray'),

            'trashed' => Tab::make(__('Trashed'))
                ->icon('heroicon-o-trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge($this->getModel()::onlyTrashed()->count()),
        ];
    }

    /**
     * Calculate and update response statistics
     */
    protected function calculateResponseStatistics(SurveyResponse $surveyResponse): void
    {
        try {
            // Get total questions for this survey
            $totalQuestions = $surveyResponse->survey->questions()->where('is_active', true)->count();

            // Get answered questions count
            $answeredQuestions = $surveyResponse->questionResponses()->count();

            // Calculate completion percentage
            $completionPercentage = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 2) : 0;

            // Calculate average rating (for rating questions only)
            $averageRating = $surveyResponse->questionResponses()
                ->whereNotNull('rating_value')
                ->avg('rating_value');

            // Determine if response is complete
            $isComplete = $completionPercentage >= 100;

            // Update the survey response with calculated values
            $surveyResponse->update([
                'total_questions' => $totalQuestions,
                'answered_questions' => $answeredQuestions,
                'skipped_questions' => $totalQuestions - $answeredQuestions,
                'completion_percentage' => $completionPercentage,
                'average_rating' => $averageRating ? round($averageRating, 2) : null,
                'is_complete' => $isComplete,
                'completed_at' => $isComplete ? ($surveyResponse->completed_at ?? now()) : null,
                'updated_by' => Auth::id(),
            ]);

        } catch (\Exception $e) {
            // Log error but don't fail the creation
            Log::error('Failed to calculate response statistics', [
                'survey_response_id' => $surveyResponse->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
