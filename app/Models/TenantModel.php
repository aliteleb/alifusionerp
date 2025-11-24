<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Base model that ensures all Survey domain models use the tenant connection.
 */
abstract class TenantModel extends Model
{
    /**
     * Use the tenant connection for all survey specific models.
     */
    protected $connection = 'tenant';

    /**
     * Allow mass-assignment on all attributes by default.
     *
     * @var array<int, string>
     */
    protected $guarded = [];
}
