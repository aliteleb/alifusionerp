<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Currency extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'country_id',
        'symbol',
        'title',
        'is_active',
    ];

    public $translatable = ['title'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
