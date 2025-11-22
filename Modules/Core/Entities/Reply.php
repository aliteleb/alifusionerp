<?php

namespace Modules\Core\Entities;

use Modules\Core\Enums\ReplyType;
use Modules\Core\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Reply extends Model
{
    use ActivityLoggable, HasFactory, SoftDeletes;

    protected $fillable = [
        'repliable_type',
        'repliable_id',
        'user_id',
        'branch_id',
        'message',
        'type',
        'is_internal',
        'is_from_client',
        'is_system_generated',
        'status_changes',
        'time_spent',
        'email_message_id',
        'sent_via_email',
        'email_sent_at',
        'client_ip',
        'user_agent',
        'source',
        'attachments',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
            'is_from_client' => 'boolean',
            'is_system_generated' => 'boolean',
            'status_changes' => 'array',
            'time_spent' => 'decimal:2',
            'sent_via_email' => 'boolean',
            'email_sent_at' => 'datetime',
            'attachments' => 'array',
            'metadata' => 'array',
            'type' => ReplyType::class,
        ];
    }

    /**
     * Get the parent repliable model (Ticket, Task, Project, etc.)
     */
    public function repliable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created this reply
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Entities\User::class);
    }

    /**
     * Get the branch where this reply was created
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Entities\Branch::class);
    }

    /**
     * Scopes
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    public function scopeFromClient($query)
    {
        return $query->where('is_from_client', true);
    }

    public function scopeFromStaff($query)
    {
        return $query->where('is_from_client', false);
    }

    public function scopeSystemGenerated($query)
    {
        return $query->where('is_system_generated', true);
    }

    public function scopeUserGenerated($query)
    {
        return $query->where('is_system_generated', false);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWithTimeSpent($query)
    {
        return $query->whereNotNull('time_spent')->where('time_spent', '>', 0);
    }

    public function scopeByBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Accessors
     */
    public function getFormattedTimeSpentAttribute(): string
    {
        if (! $this->time_spent || $this->time_spent == 0) {
            return 'No time logged';
        }

        return number_format($this->time_spent, 2).' hrs';
    }

    public function getIsEditableAttribute(): bool
    {
        // Allow editing within 15 minutes of creation, or if user is admin
        return $this->created_at->gt(now()->subMinutes(15)) ||
               Auth::user()?->hasRole('admin');
    }

    public function getCanBeDeletedAttribute(): bool
    {
        // Only allow deletion by the author or admin
        return $this->user_id === Auth::id() ||
               Auth::user()?->hasRole('admin');
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reply) {
            if (empty($reply->user_id)) {
                $reply->user_id = Auth::id();
            }

            // Auto-set branch_id if not provided
            if (empty($reply->branch_id)) {
                $user = Auth::user();
                if ($user) {
                    // Try to get branch from user's primary branch
                    $primaryBranch = $user->primaryBranch();
                    if ($primaryBranch) {
                        $reply->branch_id = $primaryBranch->id;
                    } else {
                        // Fallback to user's single branch (legacy)
                        $reply->branch_id = $user->branch_id;
                    }
                }
            }
        });

        // Note: Reply count and last reply time updates are handled by ReplyObserver
        // to avoid duplicate increments/decrements
    }
}
