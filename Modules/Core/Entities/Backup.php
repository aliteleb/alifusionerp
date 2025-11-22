<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Backup extends Model
{
    use HasTranslations, HasTranslations;

    protected $fillable = ['path', 'name', 'disk', 'size'];

    public $translatable = ['name'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
