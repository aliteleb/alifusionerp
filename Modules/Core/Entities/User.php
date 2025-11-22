<?php

namespace Modules\Core\Entities;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Modules\Core\Entities\PushSubscription as WebPushSubscription;
use Modules\Core\Services\TenantDatabaseService;
use Modules\Core\Traits\ActivityLoggable;
use Modules\Core\Traits\HasBranchAccess;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use NotificationChannels\WebPush\HasPushSubscriptions;
use NotificationChannels\WebPush\PushSubscription as BasePushSubscription;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasMedia
{
    /** @use HasFactory<UserFactory> */
    use ActivityLoggable, HasBranchAccess, HasFactory, HasPushSubscriptions, HasRoles, InteractsWithMedia, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'employment_id',
        'email',
        'is_active',
        'locale',
        'language',
        'phone',
        'mobile',
        'skype_id',
        'profile_photo_path',
        'user_type',
        'direction',
        'department_id',
        'is_department_head',
        'is_hq',
        'branch_id',
        'password',
        'last_activity_at',
        'country_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // 'password' => 'hashed',
            'is_active' => 'boolean',
            'is_department_head' => 'boolean',
            'is_hq' => 'boolean',
            'last_activity_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    // LogsActivity Trait: Define which events to log
    protected static function logAttributes(): array
    {
        return [
            'name',
            'email',
        ];
    }

    protected static function logName(): string
    {
        return 'user'; // Define a log name for this model
    }

    protected static function logOnlyDirty(): bool
    {
        return true; // Only log attributes that have changed
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "User {$this->name} was {$eventName}"; // Customize the event description
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the single branch the user belongs to (legacy relationship).
     *
     * @deprecated Use branches() for many-to-many relationship
     */
    public function branch()
    {
        return $this->belongsTo(\Modules\Core\Entities\Branch::class);
    }

    /**
     * Get all branches the user belongs to (many-to-many).
     */
    public function branches()
    {
        return $this->belongsToMany(\Modules\Core\Entities\Branch::class, 'branch_user')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Get the user's primary branch.
     */
    public function primaryBranch()
    {
        return $this->branches()->wherePivot('is_primary', true)->first();
    }

    /**
     * Get the single department the user belongs to (legacy relationship).
     *
     * @deprecated Use departments() for many-to-many relationship
     */
    public function department()
    {
        return $this->belongsTo(\Modules\Core\Entities\Department::class);
    }

    /**
     * Get all departments the user belongs to (many-to-many).
     */
    public function departments()
    {
        return $this->belongsToMany(\Modules\Core\Entities\Department::class, 'department_user')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Get the user's primary department.
     */
    public function primaryDepartment()
    {
        return $this->departments()->wherePivot('is_primary', true)->first();
    }


    /**
     * Register media collections for the user.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->singleFile();
    }

    /**
     * Register media conversions for the user.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('cover');

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(600)
            ->sharpen(10)
            ->performOnCollections('cover');
    }

    /**
     * Store or update a push subscription with facility context awareness.
     */
    public function updatePushSubscription(
        string $endpoint,
        ?string $key = null,
        ?string $token = null,
        ?string $contentEncoding = null,
        ?int $facilityId = null
    ): BasePushSubscription {
        /** @var WebPushSubscription|null $subscription */
        $subscription = app(config('webpush.model'))->findByEndpoint($endpoint);

        $facilityId ??= TenantDatabaseService::getCurrentFacility()?->id;

        if ($subscription && $this->ownsPushSubscription($subscription)) {
            $subscription->fill([
                'public_key' => $key,
                'auth_token' => $token,
                'content_encoding' => $contentEncoding,
            ]);

            if ($facilityId !== null) {
                $subscription->facility_id = $facilityId;
            }

            $subscription->save();

            return $subscription;
        }

        if ($subscription && ! $this->ownsPushSubscription($subscription)) {
            $subscription->delete();
        }

        $attributes = array_filter([
            'endpoint' => $endpoint,
            'public_key' => $key,
            'auth_token' => $token,
            'content_encoding' => $contentEncoding,
            'facility_id' => $facilityId,
        ], static fn ($value) => $value !== null);

        return $this->pushSubscriptions()->create($attributes);
    }

    /**
     * Limit web push routing to subscriptions that match the current facility.
     */
    public function routeNotificationForWebPush(): Collection
    {
        $facility = TenantDatabaseService::getCurrentFacility();
        $relation = $this->pushSubscriptions();

        if (! $facility) {
            Log::warning('Attempted to route web push without facility context', [
                'user_id' => $this->getKey(),
            ]);

            return $relation->getModel()->newCollection();
        }

        return $relation
            ->where('facility_id', $facility->id)
            ->get();
    }

    protected function ownsPushSubscription(BasePushSubscription $subscription): bool
    {
        return $subscription->subscribable_type === static::class
            && (int) $subscription->subscribable_id === (int) $this->getKey();
    }

    // boot method
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($user) {

            // dirty fields
            $dirtyFields = $user->getDirty();
            // info('Dirty fields: ' . json_encode($dirtyFields));
        });
    }
}
