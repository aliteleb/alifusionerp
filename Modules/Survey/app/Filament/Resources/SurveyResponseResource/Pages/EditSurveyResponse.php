<?php

namespace Modules\Survey\Filament\Resources\SurveyResponseResource\Pages;

use App\Models\SurveyResponse;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Survey\Filament\Resources\SurveyResponseResource;

class EditSurveyResponse extends EditRecord
{
    protected static string $resource = SurveyResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Fill question responses
        $questionResponses = [];
        foreach ($this->record->questionResponses as $questionResponse) {
            $questionId = $questionResponse->survey_question_id;

            // Ensure question ID is valid and not a reason field identifier
            if (! is_numeric($questionId) || str_starts_with((string) $questionId, 'reason_')) {
                continue;
            }

            // Use the appropriate response value based on question type
            if ($questionResponse->question?->question_type === \App\Enums\SurveyQuestionType::RATING) {
                $questionResponses[$questionId] = $questionResponse->rating_value;

                // Include reason for low ratings if available
                if ($questionResponse->rating_value <= 2 && $questionResponse->reason_for_rating) {
                    $questionResponses['reason_'.$questionId] = $questionResponse->reason_for_rating;
                }
            } else {
                $questionResponses[$questionId] = $questionResponse->text_value;
            }
        }

        $data['question_responses'] = $questionResponses;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();

        // Handle question responses if provided
        if (isset($data['question_responses']) && is_array($data['question_responses'])) {
            // First, delete existing question responses to replace them
            $this->record->questionResponses()->delete();

            // Create new question responses
            foreach ($data['question_responses'] as $questionId => $responseValue) {
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
                                isset($data['question_responses'][$reasonKey]) &&
                                ! empty($data['question_responses'][$reasonKey]) &&
                                is_string($data['question_responses'][$reasonKey])) {
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

        // Remove question_responses from the main data array since it's not a field on the survey_responses table
        unset($data['question_responses']);

        return $data;
    }

    protected function afterSave(): void
    {
        // Calculate completion statistics after saving
        $this->calculateResponseStatistics($this->record);

        // Refresh the record to get updated data
        $this->record->refresh();

        // Emit event to refresh the QuestionResponsesRelationManager
        $this->dispatch('refreshQuestionResponsesRelationManager');
    }

    // Remove redirect to stay on edit page after save
    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index');
    // }

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
            // Log error but don't fail the update
            Log::error('Failed to calculate response statistics', [
                'survey_response_id' => $surveyResponse->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
