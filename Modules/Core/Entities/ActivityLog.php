<?php

namespace Modules\Core\Entities;

use Modules\Core\Enums\ActivityAction;
use Modules\Core\Services\TenantDatabaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
        'action' => ActivityAction::class,
    ];

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Entities\User::class);
    }

    /**
     * Get the branch where the action occurred (tenant only).
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Entities\Branch::class);
    }

    /**
     * Get related model.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo('subject', 'model_type', 'model_id');
    }

    /**
     * Create a new activity log entry with enhanced multi-language support.
     */
    public static function log(
        ActivityAction|string $action,
        string $description,
        ?Model $model = null,
        array $properties = [],
        ?int $branchId = null
    ): self {
        // Check if activity logging is disabled for this model
        if ($model && method_exists($model, 'isActivityLoggingDisabled') && $model->isActivityLoggingDisabled()) {
            // Return a dummy log entry without saving to database
            return new self([
                'action' => is_string($action) ? ActivityAction::tryFrom($action) ?? ActivityAction::SYSTEM : $action,
                'description' => $description,
                'model_type' => $model ? get_class($model) : null,
                'model_id' => $model ? $model->getKey() : null,
                'properties' => $properties,
            ]);
        }
        $request = request();
        $user = Auth::user();
        $userId = null;

        // Handle multi-tenant scenarios
        if ($user) {
            // Check if we're on a tenant connection
            if (TenantDatabaseService::isOnTenantConnection()) {
                // In tenant context - try to find the user in the tenant database
                try {
                    $tenantUser = \Modules\Core\Entities\User::find($user->id);
                    $userId = $tenantUser ? $tenantUser->id : null;
                } catch (\Exception $e) {
                    // If user doesn't exist in tenant database, set to null
                    $userId = null;
                }
            } else {
                // In main database context - use the user_id normally
                $userId = $user->id;
            }
        }

        // Convert string action to enum if needed
        if (is_string($action)) {
            $action = ActivityAction::tryFrom($action) ?? ActivityAction::SYSTEM;
        }

        // Auto-generate description if not provided
        if (empty($description)) {
            $description = self::generateDescription($action, $model);
        }

        // Auto-detect branch if not provided and we're in tenant context
        $branchId = $model?->branch_id ?? null;

        return self::create([
            'branch_id' => $branchId,
            'user_id' => $userId,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->getKey() : null,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    /**
     * Generate a localized description for the activity.
     */
    protected static function generateDescription(ActivityAction $action, ?Model $model = null): string
    {
        $modelName = $model ? __(class_basename($model)) : __('Record');

        return match ($action) {
            ActivityAction::CREATED => __(':model was created', ['model' => $modelName]),
            ActivityAction::UPDATED => __(':model was updated', ['model' => $modelName]),
            ActivityAction::DELETED => __(':model was deleted', ['model' => $modelName]),
            ActivityAction::RESTORED => __(':model was restored', ['model' => $modelName]),
            ActivityAction::VIEWED => __(':model was viewed', ['model' => $modelName]),
            ActivityAction::EXPORTED => __(':model data was exported', ['model' => $modelName]),
            ActivityAction::IMPORTED => __(':model data was imported', ['model' => $modelName]),
            ActivityAction::APPROVED => __(':model was approved', ['model' => $modelName]),
            ActivityAction::REJECTED => __(':model was rejected', ['model' => $modelName]),
            ActivityAction::ASSIGNED => __(':model was assigned', ['model' => $modelName]),
            ActivityAction::UNASSIGNED => __(':model assignment was removed', ['model' => $modelName]),
            ActivityAction::STATUS_CHANGED => __(':model status was changed', ['model' => $modelName]),
            ActivityAction::COMMENTED => __('Comment was added to :model', ['model' => $modelName]),
            default => $action->getDescription() ?? __('Activity performed'),
        };
    }

    /**
     * Get the current branch ID from session or context.
     */
    protected static function getCurrentBranchId(): ?int
    {
        // Try to get from session first
        $branchId = session('current_branch_id');

        if ($branchId) {
            return $branchId;
        }

        // Try to get from user's default branch
        $user = Auth::user();
        if ($user && method_exists($user, 'default_branch_id')) {
            return $user->default_branch_id;
        }

        return null;
    }

    /** Scopes */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by action.
     */
    public function scopeByAction(Builder $query, ActivityAction|string $action): Builder
    {
        $actionValue = $action instanceof ActivityAction ? $action->value : $action;

        return $query->where('action', $actionValue);
    }

    /**
     * Scope to filter by model type.
     */
    public function scopeByModel(Builder $query, string $modelType): Builder
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by branch (tenant only).
     */
    public function scopeByBranch(Builder $query, int $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange(Builder $query, ?string $startDate = null, ?string $endDate = null): Builder
    {
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope to filter recent activities.
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to filter by IP address.
     */
    public function scopeByIp(Builder $query, string $ipAddress): Builder
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Scope to filter system activities.
     */
    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('action', ActivityAction::SYSTEM->value);
    }

    /**
     * Scope to filter user activities (non-system).
     */
    public function scopeUser(Builder $query): Builder
    {
        return $query->where('action', '!=', ActivityAction::SYSTEM->value);
    }

    /**
     * Scope to filter login activities.
     */
    public function scopeLogin(Builder $query): Builder
    {
        return $query->whereIn('action', [
            ActivityAction::LOGIN->value,
            ActivityAction::LOGOUT->value,
            ActivityAction::LOGIN_FAILED->value,
        ]);
    }

    /**
     * Scope to filter CRUD activities.
     */
    public function scopeCrud(Builder $query): Builder
    {
        return $query->whereIn('action', [
            ActivityAction::CREATED->value,
            ActivityAction::UPDATED->value,
            ActivityAction::DELETED->value,
            ActivityAction::RESTORED->value,
        ]);
    }

    /**
     * Scope to order by most recent first.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope to order by oldest first.
     */
    public function scopeOldest(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'asc');
    }

    /** Helper Methods */

    /**
     * Get the localized action label.
     */
    public function getActionLabelAttribute(): string
    {
        return $this->action->getLabel();
    }

    /**
     * Get the action color.
     */
    public function getActionColorAttribute(): string
    {
        return $this->action->getColor();
    }

    /**
     * Get the action icon.
     */
    public function getActionIconAttribute(): string
    {
        try {
            $icon = $this->action->getIcon();

            // If it's a Heroicon enum, get its value
            if (is_object($icon) && property_exists($icon, 'value')) {
                return 'heroicon-o-'.$icon->value;
            }

            // If it's already a string, return it
            if (is_string($icon)) {
                return $icon;
            }

            // Fallback to a default icon
            return 'heroicon-o-question-mark-circle';
        } catch (\Exception $e) {
            // Fallback in case of any error
            return 'heroicon-o-question-mark-circle';
        }
    }

    /**
     * Get the changes from properties.
     */
    public function getChangesAttribute(): ?array
    {
        return $this->properties['changes'] ?? null;
    }

    /**
     * Get the original values from properties.
     */
    public function getOriginalAttribute(): ?array
    {
        return $this->properties['original'] ?? null;
    }

    /**
     * Get the model name in localized format.
     */
    public function getModelNameAttribute(): string
    {
        return $this->model_type ? __(class_basename($this->model_type)) : __('Unknown');
    }

    /**
     * Get the user name or 'System' if no user.
     */
    public function getUserNameAttribute(): string
    {
        return $this->user ? $this->user->name : __('System');
    }

    /**
     * Get the branch name or 'N/A' if no branch.
     */
    public function getBranchNameAttribute(): string
    {
        return $this->branch ? $this->branch->name : __('N/A');
    }

    /**
     * Check if this is a system activity.
     */
    public function isSystemActivity(): bool
    {
        return $this->action === ActivityAction::SYSTEM;
    }

    /**
     * Check if this is a user activity.
     */
    public function isUserActivity(): bool
    {
        return ! $this->isSystemActivity();
    }

    /**
     * Check if this is a login-related activity.
     */
    public function isLoginActivity(): bool
    {
        return in_array($this->action, [
            ActivityAction::LOGIN,
            ActivityAction::LOGOUT,
            ActivityAction::LOGIN_FAILED,
        ]);
    }

    /**
     * Check if this is a CRUD activity.
     */
    public function isCrudActivity(): bool
    {
        return in_array($this->action, [
            ActivityAction::CREATED,
            ActivityAction::UPDATED,
            ActivityAction::DELETED,
            ActivityAction::RESTORED,
        ]);
    }

    /**
     * Get formatted time ago.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get the rendered message with model name replaced.
     */
    public function getRenderedMessageAttribute(): string
    {
        $modelName = $this->model_type ? __(class_basename($this->model_type)) : __('Record');

        return match ($this->action) {
            ActivityAction::CREATED => __(':model was created', ['model' => $modelName]),
            ActivityAction::UPDATED => __(':model was updated', ['model' => $modelName]),
            ActivityAction::DELETED => __(':model was deleted', ['model' => $modelName]),
            ActivityAction::RESTORED => __(':model was restored', ['model' => $modelName]),
            ActivityAction::VIEWED => __(':model was viewed', ['model' => $modelName]),
            ActivityAction::EXPORTED => __(':model data was exported', ['model' => $modelName]),
            ActivityAction::IMPORTED => __(':model data was imported', ['model' => $modelName]),
            ActivityAction::APPROVED => __(':model was approved', ['model' => $modelName]),
            ActivityAction::REJECTED => __(':model was rejected', ['model' => $modelName]),
            ActivityAction::ASSIGNED => __(':model was assigned', ['model' => $modelName]),
            ActivityAction::UNASSIGNED => __(':model assignment was removed', ['model' => $modelName]),
            ActivityAction::STATUS_CHANGED => __(':model status was changed', ['model' => $modelName]),
            ActivityAction::COMMENTED => __('Comment was added to :model', ['model' => $modelName]),
            default => $this->action->getDescription() ?? __('Activity performed'),
        };
    }

    /**
     * Get the subject model if it exists.
     */
    public function getSubjectModel(): ?Model
    {
        if (! $this->model_type || ! $this->model_id) {
            return null;
        }

        try {
            return $this->model_type::find($this->model_id);
        } catch (\Exception $e) {
            return null;
        }
    }
}
