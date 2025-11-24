<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class SurveyCategory extends TenantModel
{
    use HasTranslations, SoftDeletes;

    protected $translatable = [
        'name',
        'description',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'is_active' => 'boolean',
    ];

    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }
}
