<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Survey extends TenantModel
{
    use HasTranslations, SoftDeletes;

    protected $translatable = [
        'title',
        'description',
        'welcome_message',
        'thank_you_message',
        'whatsapp_message',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'welcome_message' => 'array',
        'thank_you_message' => 'array',
        'whatsapp_message' => 'array',
        'bad_rating_alert_phones' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_anonymous' => 'boolean',
        'allow_multiple_responses' => 'boolean',
        'is_required_login' => 'boolean',
        'show_progress_bar' => 'boolean',
        'randomize_questions' => 'boolean',
        'whatsapp_enabled' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(SurveyCategory::class, 'survey_category_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(SurveyInvitation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOrderedByTitle($query)
    {
        return $query->orderBy('title->'.app()->getLocale());
    }
}
