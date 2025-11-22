<?php

namespace Modules\Core\Entities;

use Modules\Core\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Department extends Model
{
    use ActivityLoggable, HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'branch_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public $translatable = ['name', 'description'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get all users belonging to this department (many-to-many).
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'department_user')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active departments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
