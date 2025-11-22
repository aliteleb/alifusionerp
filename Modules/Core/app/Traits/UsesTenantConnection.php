<?php

namespace Modules\Core\Traits;

use Modules\Core\Services\TenantDatabaseService;
use Illuminate\Database\Eloquent\Builder;

trait UsesTenantConnection
{
    /**
     * Boot the tenant connection trait for a model.
     */
    public static function bootUsesTenantConnection(): void
    {
        // Override the connection for tenant-aware models
        static::retrieved(function ($model) {
            if (TenantDatabaseService::isOnTenantConnection()) {
                $model->setConnection(TenantDatabaseService::getCurrentTenantConnection());
            }
        });
    }

    /**
     * Get the database connection for the model.
     */
    public function getConnectionName()
    {
        if (TenantDatabaseService::isOnTenantConnection()) {
            return TenantDatabaseService::getCurrentTenantConnection();
        }

        return parent::getConnectionName();
    }

    /**
     * Resolve a connection instance.
     */
    public static function resolveConnection($connection = null)
    {
        if (TenantDatabaseService::isOnTenantConnection() && ! $connection) {
            $connection = TenantDatabaseService::getCurrentTenantConnection();
        }

        return parent::resolveConnection($connection);
    }

    /**
     * Begin querying the model on a tenant connection.
     */
    public function newQuery(): Builder
    {
        $query = parent::newQuery();

        if (TenantDatabaseService::isOnTenantConnection()) {
            // The connection is already set properly through getConnectionName()
            // No need to modify the query here as it will use the correct connection
        }

        return $query;
    }
}
