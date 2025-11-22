<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class MaritalStatus extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = ['name', 'is_active'];

    public $translatable = ['name'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /** Scopes */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }
}
