<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class SurveyQuestion extends TenantModel
{
    use HasTranslations, SoftDeletes;

    protected $translatable = [
        'question_text',
        'description',
        'placeholder',
    ];

    protected $casts = [
        'question_text' => 'array',
        'description' => 'array',
        'placeholder' => 'array',
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyQuestionResponse::class);
    }

    public function scopeOrderedByText($query)
    {
        return $query->orderBy('order');
    }
}
