<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FailedJob extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'failed_jobs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'connection',
        'queue',
        'facility_id',
        'payload',
        'exception',
        'failed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'facility_id' => 'integer',
        'failed_at' => 'datetime',
    ];

    public function getConnectionName(): ?string
    {
        return config('queue.failed.database', config('queue.connections.database.connection', 'pgsql'));
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
}
