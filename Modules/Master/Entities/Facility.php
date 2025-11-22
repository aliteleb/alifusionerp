<?php

namespace Modules\Master\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Services\TenantDatabaseService;
use Spatie\Translatable\HasTranslations;

class Facility extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    /**
     * The database connection name for the model.
     * Facility model belongs to master database.
     *
     * @var string|null
     */
    protected $connection = 'master'; // Use default connection

    public $translatable = ['name'];

    protected $fillable = [
        'name',
        'subdomain',
        'database_name',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];


    /** 
     * Connect to tenant database
     */
    public function connect()
    {
        TenantDatabaseService::connectToFacility($this);
    }
}

