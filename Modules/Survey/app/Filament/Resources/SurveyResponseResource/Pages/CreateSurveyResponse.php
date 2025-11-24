<?php

namespace Modules\Survey\Filament\Resources\SurveyResponseResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Survey\Filament\Resources\SurveyResponseResource;

class CreateSurveyResponse extends CreateRecord
{
    protected static string $resource = SurveyResponseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        $data['started_at'] = $data['started_at'] ?? now();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Handle question responses if provided
        if (isset($this->data['question_responses']) && is_array($this->data['question_responses'])) {
            foreach ($this->data['question_responses'] as $questionId => $responseValue) {
                // Skip reason fields in this loop - they'll be handled separately
                if (str_starts_with((string) $questionId, 'reason_')) {
                    continue;
                }

                // Ensure question ID is numeric and valid
                if (! is_numeric($questionId)) {
                    continue;
                }

                if ($responseValue !== null && $responseValue !== '') {
                    // Get the question to determine response type
                    $question = \App\Models\SurveyQuestion::find((int) $questionId);

                    if ($question) {
                        $questionResponseData = [
                            'survey_response_id' => $this->record->id,
                            'survey_question_id' => (int) $questionId,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                            'answered_at' => now(),
                        ];

                        // Map response based on question type
                        if ($question->question_type->value === 'rating') {
                            $questionResponseData['rating_value'] = (int) $responseValue;

                            // Check if there's a reason for low rating (2 stars or less)
                            $reasonKey = 'reason_'.$questionId;
                            if ((int) $responseValue <= 2 &&
                                isset($this->data['question_responses'][$reasonKey]) &&
                                ! empty($this->data['question_responses'][$reasonKey]) &&
                                is_string($this->data['question_responses'][$reasonKey])) {
                                $questionResponseData['reason_for_rating'] = $this->data['question_responses'][$reasonKey];
                            }
                        } else {
                            $questionResponseData['text_value'] = $responseValue;
                        }

                        \App\Models\SurveyQuestionResponse::create($questionResponseData);
                    }
                }
            }
        }

        // Calculate completion statistics after creating
        $this->calculateResponseStatistics($this->record);

        // Refresh the record to get updated data
        $this->record->refresh();
    }

    /**
     * Calculate and update response statistics
     */
    protected function calculateResponseStatistics(\App\Models\SurveyResponse $surveyResponse): void
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
            Log::error('Failed to calculate response statistics during creation', [
                'survey_response_id' => $surveyResponse->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
