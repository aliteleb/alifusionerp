<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Builder;
use Spatie\LaravelPackageTools\Concerns\Package\HasTranslations;

class Role extends \Spatie\Permission\Models\Role
{
    use HasTranslations;

    public $translatable = ['name', 'display_name'];

    /** Scopes */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }
}
