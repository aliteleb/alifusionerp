<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyInvitation extends TenantModel
{
    use SoftDeletes;

    protected $casts = [
        'send_attempts' => 'array',
        'view_count' => 'integer',
        'expires_at' => 'datetime',
        'send_after' => 'datetime',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'completed_at' => 'datetime',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['expired', 'cancelled']);
    }
}
