<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Employee extends TenantModel
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }
}
