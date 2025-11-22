<?php

namespace Modules\Core\Traits;

use Modules\Core\Entities\Branch;
use Modules\Core\Services\TenantDatabaseService;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;

trait HasBranchAccess
{
    /**
     * Boot the trait and add global scope for branch access control.
     */
    protected static function bootHasBranchAccess(): void
    {

        if (app()->runningInConsole()) {
            return;
        }

        // Get user
        $user = Auth::user();

        static::addGlobalScope('branch_access', function (Builder $builder) use ($user) {

            // Skip if we're in console or not on the admin panel or not on a tenant connection or no authenticated user
            $panel = Filament::getCurrentPanel();
            if (
                ! $panel || $panel->getId() !== 'admin' || // Only apply to admin panel
                ! TenantDatabaseService::isOnTenantConnection() ||
                ! $user
            ) {
                return;
            }

            // HQ users can see all data
            if ($user->is_hq) {
                return;
            }

            $userBranchIds = self::rememberUsingContext(
                self::resolveBranchAccessCacheKey($user),
                fn () => $user->branches()->withoutGlobalScopes()->pluck('branches.id')->toArray()
            );
            if (! empty($userBranchIds)) {

                if (static::class === \App\Core\Models\Branch::class) {
                    $builder->whereIn($builder->getModel()->qualifyColumn('id'), $userBranchIds);
                } else {
                    $builder->whereIn($builder->qualifyColumn('branch_id'), $userBranchIds);
                }

            } else {
                // If user has no branches assigned, show no data
                $builder->whereRaw('1 = 0');
            }

        });

        // Auto set branch id
        static::creating(function ($model) {

            // Skip if branch id is already set
            if (! empty($model->branch_id)) {
                return;
            }

            // Get user and branch id
            $user = Auth::user();
            $branch_id = $user->branch_id;

            // If user is not found, set branch id to first branch
            if (! $user) {
                $hqBranch = self::rememberUsingContext(
                    self::resolveHqBranchCacheKey(),
                    fn () => Branch::active()->where('is_hq', true)->first()?->id
                );
                $branch_id = $hqBranch;
            }

            $model->branch_id = $branch_id;

        });

    }

    protected static function resolveBranchAccessCacheKey(?Authenticatable $user): string
    {
        $panelId = Filament::getCurrentPanel()?->getId() ?? 'web';
        $tenantConnection = TenantDatabaseService::getCurrentTenantConnection() ?? 'master';
        $userKey = $user?->getAuthIdentifier() ?? 'guest';

        return "branch-access:{$tenantConnection}:{$panelId}:{$userKey}";
    }

    protected static function resolveHqBranchCacheKey(): string
    {
        $tenantConnection = TenantDatabaseService::getCurrentTenantConnection() ?? 'master';

        return "branch-access:hq-branch:{$tenantConnection}";
    }

    /**
     * Remember the callback result within the current request lifecycle.
     */
    protected static function rememberUsingContext(string $key, callable $callback): mixed
    {
        $sentinel = new \stdClass;
        $value = Context::get($key, $sentinel);

        if ($value !== $sentinel) {
            return $value;
        }

        $computed = $callback();

        Context::add($key, $computed);

        return $computed;
    }
}
