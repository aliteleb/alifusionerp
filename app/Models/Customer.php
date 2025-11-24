<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends TenantModel
{
    use SoftDeletes;

    protected $casts = [
        'visit_time' => 'datetime',
        'birthday' => 'date',
    ];

    public function scopeRecentVisits($query, int $days = 30)
    {
        return $query->where('visit_time', '>=', now()->subDays($days));
    }

    public function scopeBirthdayInMonth($query, int $month)
    {
        return $query->whereMonth('birthday', $month);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function surveyResponses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function surveyInvitations(): HasMany
    {
        return $this->hasMany(SurveyInvitation::class);
    }
}
