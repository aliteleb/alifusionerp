<?php

namespace Modules\Core\Traits;

use Modules\Core\Entities\Reply;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasReplies
{
    /**
     * Get all replies for this model
     */
    public function replies(): MorphMany
    {
        return $this->morphMany(Reply::class, 'repliable')->orderBy('created_at');
    }

    /**
     * Get only public replies
     */
    public function publicReplies(): MorphMany
    {
        return $this->morphMany(Reply::class, 'repliable')
            ->where('is_internal', false)
            ->orderBy('created_at');
    }

    /**
     * Get only internal notes
     */
    public function internalNotes(): MorphMany
    {
        return $this->morphMany(Reply::class, 'repliable')
            ->where('is_internal', true)
            ->orderBy('created_at');
    }

    /**
     * Get replies from clients
     */
    public function clientReplies(): MorphMany
    {
        return $this->morphMany(Reply::class, 'repliable')
            ->where('is_from_client', true)
            ->orderBy('created_at');
    }

    /**
     * Get replies from staff
     */
    public function staffReplies(): MorphMany
    {
        return $this->morphMany(Reply::class, 'repliable')
            ->where('is_from_client', false)
            ->orderBy('created_at');
    }

    /**
     * Get system-generated replies
     */
    public function systemReplies(): MorphMany
    {
        return $this->morphMany(Reply::class, 'repliable')
            ->where('is_system_generated', true)
            ->orderBy('created_at');
    }

    /**
     * Add a reply to this model
     */
    public function addReply(array $data): Reply
    {
        return $this->replies()->create($data);
    }

    /**
     * Add an internal note
     */
    public function addInternalNote(string $message, ?float $timeSpent = null): Reply
    {
        return $this->addReply([
            'message' => $message,
            'type' => 'internal_note',
            'is_internal' => true,
            'time_spent' => $timeSpent,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Add a public reply
     */
    public function addPublicReply(string $message, ?float $timeSpent = null): Reply
    {
        return $this->addReply([
            'message' => $message,
            'type' => 'reply',
            'is_internal' => false,
            'time_spent' => $timeSpent,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Add a system-generated reply
     */
    public function addSystemReply(string $message, array $statusChanges = []): Reply
    {
        return $this->addReply([
            'message' => $message,
            'type' => 'system',
            'is_system_generated' => true,
            'status_changes' => $statusChanges,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Get the total time spent on replies
     */
    public function getTotalReplyTimeAttribute(): float
    {
        return $this->replies()->sum('time_spent') ?? 0;
    }

    /**
     * Get the last reply
     */
    public function getLastReplyAttribute(): ?Reply
    {
        return $this->replies()->latest()->first();
    }

    /**
     * Check if there are unread replies (if the model supports it)
     */
    public function hasUnreadReplies(): bool
    {
        if (! $this->hasAttribute('last_reply_at')) {
            return false;
        }

        return $this->replies()
            ->where('created_at', '>', $this->last_reply_at ?? $this->updated_at)
            ->exists();
    }

    /**
     * Update last reply time (if the model has this field)
     */
    public function updateLastReplyTime(): bool
    {
        if (! $this->hasReplyField('last_reply_at')) {
            return false;
        }

        return $this->update([
            'last_reply_at' => now(),
        ]);
    }

    /**
     * Check if the model has a specific field for replies functionality
     */
    protected function hasReplyField(string $field): bool
    {
        return in_array($field, $this->fillable) ||
               array_key_exists($field, $this->casts) ||
               $this->hasGetMutator($field) ||
               $this->hasSetMutator($field);
    }
}
