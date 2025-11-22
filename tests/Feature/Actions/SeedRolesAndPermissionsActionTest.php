<?php

namespace Tests\Feature\Actions;

use App\Core\Actions\Facility\Seeding\SeedRolesAndPermissionsAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SeedRolesAndPermissionsActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_permissions_creation_once_per_execution(): void
    {
        Artisan::call('migrate', [
            '--path' => 'database/migrations/master',
            '--database' => config('database.default'),
            '--force' => true,
        ]);

        Log::spy();

        $action = app(SeedRolesAndPermissionsAction::class);

        $action->execute();

        Log::shouldHaveReceived('info')
            ->with('Creating permissions')
            ->once();
    }
}
