<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends TenantModel
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'description',
        'start_date',
        'end_date',
        'by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->author();
    }
}
