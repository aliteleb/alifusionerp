<?php

namespace Modules\Core\Entities;

use Modules\Core\Traits\ActivityLoggable;
use Modules\Core\Traits\HasBranchAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\Translatable\HasTranslations;

class Branch extends Model
{
    use ActivityLoggable, HasBranchAccess, HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'name',
        'is_active',
        'is_hq',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_hq' => 'boolean',
    ];

    public $translatable = ['name'];

    /**
     * Relationships
     */

    /**
     * Get all users belonging to this branch (many-to-many).
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_user')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /** Scopes */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', DB::raw('true'));
    }

    public function scopeHq(Builder $query)
    {
        return $query->where('is_hq', DB::raw('true'));
    }
}
