<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyQuestionResponse extends TenantModel
{
    protected $casts = [
        'raw_data' => 'array',
        'validation_errors' => 'array',
        'is_skipped' => 'boolean',
        'is_required_answered' => 'boolean',
        'is_valid' => 'boolean',
        'is_flagged' => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(SurveyResponse::class, 'survey_response_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }
}
