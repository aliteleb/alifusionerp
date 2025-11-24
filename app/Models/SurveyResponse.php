<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyResponse extends TenantModel
{
    use SoftDeletes;

    protected $casts = [
        'context_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_complete' => 'boolean',
        'is_suspicious' => 'boolean',
        'is_verified' => 'boolean',
        'is_anonymous' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function questionResponses(): HasMany
    {
        return $this->hasMany(SurveyQuestionResponse::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_complete', true);
    }
}
