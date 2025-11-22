<?php

namespace Tests\Feature;

use App\Core\Actions\Database\TenantDatabaseActions;
use App\Core\Models\Facility;
use App\Core\Services\TenantDatabaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TenantDatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    protected TenantDatabaseActions $tenantDatabaseActions;

    protected Facility $facility;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantDatabaseActions = app(TenantDatabaseActions::class);

        // Create a test facility
        $this->facility = Facility::create([
            'name' => ['en' => 'Test Backup Facility'],
            'subdomain' => 'test-backup',
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up backup files
        $backupDir = storage_path('app/backups/tenant-databases');
        if (File::exists($backupDir)) {
            $files = File::files($backupDir);
            foreach ($files as $file) {
                if (str_contains($file->getFilename(), $this->facility->subdomain)) {
                    File::delete($file->getPathname());
                }
            }
        }

        // Clean up tenant database
        if (TenantDatabaseService::tenantDatabaseExists($this->facility)) {
            TenantDatabaseService::dropTenantDatabase($this->facility);
        }

        parent::tearDown();
    }

    /** @test */
    public function it_can_create_tenant_database_backup(): void
    {
        // Create tenant database and run migrations
        TenantDatabaseService::createTenantDatabase($this->facility);
        $this->tenantDatabaseActions->runTenantMigration($this->facility->id);

        // Create backup
        $backupFileName = $this->tenantDatabaseActions->backupTenantDatabase($this->facility->id);

        // Assert backup file was created
        $this->assertNotNull($backupFileName);
        $this->assertStringContainsString($this->facility->subdomain, $backupFileName);

        $backupPath = storage_path("app/backups/tenant-databases/{$backupFileName}");
        $this->assertTrue(File::exists($backupPath));
        $this->assertGreaterThan(0, File::size($backupPath));
    }

    /** @test */
    public function it_can_list_tenant_backups(): void
    {
        // Create tenant database and backup
        TenantDatabaseService::createTenantDatabase($this->facility);
        $this->tenantDatabaseActions->runTenantMigration($this->facility->id);
        $backupFileName = $this->tenantDatabaseActions->backupTenantDatabase($this->facility->id);

        // List backups
        $backups = $this->tenantDatabaseActions->listTenantBackups($this->facility->id);

        // Assert backup is in the list
        $this->assertNotEmpty($backups);
        $this->assertCount(1, $backups);
        $this->assertEquals($backupFileName, $backups[0]['filename']);
        $this->assertArrayHasKey('size', $backups[0]);
        $this->assertArrayHasKey('created_at', $backups[0]);
        $this->assertArrayHasKey('human_size', $backups[0]);
        $this->assertArrayHasKey('human_date', $backups[0]);
    }

    /** @test */
    public function it_can_delete_tenant_backup(): void
    {
        // Create tenant database and backup
        TenantDatabaseService::createTenantDatabase($this->facility);
        $this->tenantDatabaseActions->runTenantMigration($this->facility->id);
        $backupFileName = $this->tenantDatabaseActions->backupTenantDatabase($this->facility->id);

        // Verify backup exists
        $backupPath = storage_path("app/backups/tenant-databases/{$backupFileName}");
        $this->assertTrue(File::exists($backupPath));

        // Delete backup
        $this->tenantDatabaseActions->deleteTenantBackup($this->facility->id, $backupFileName);

        // Assert backup file was deleted
        $this->assertFalse(File::exists($backupPath));
    }

    /** @test */
    public function it_can_restore_from_backup(): void
    {
        // Create tenant database and run migrations
        TenantDatabaseService::createTenantDatabase($this->facility);
        $this->tenantDatabaseActions->runTenantMigration($this->facility->id);

        // Create backup
        $backupFileName = $this->tenantDatabaseActions->backupTenantDatabase($this->facility->id);

        // Verify backup exists
        $backupPath = storage_path("app/backups/tenant-databases/{$backupFileName}");
        $this->assertTrue(File::exists($backupPath));

        // Restore from backup (this will recreate the database)
        $this->expectNotToPerformAssertions();
        $this->tenantDatabaseActions->restoreTenantDatabase($this->facility->id, $backupFileName);
    }

    /** @test */
    public function it_filters_backups_by_facility(): void
    {
        // Create another facility
        $anotherFacility = Facility::create([
            'name' => ['en' => 'Another Test Facility'],
            'subdomain' => 'another-test',
            'is_active' => true,
        ]);

        // Create databases for both facilities
        TenantDatabaseService::createTenantDatabase($this->facility);
        TenantDatabaseService::createTenantDatabase($anotherFacility);

        $this->tenantDatabaseActions->runTenantMigration($this->facility->id);
        $this->tenantDatabaseActions->runTenantMigration($anotherFacility->id);

        // Create backups for both facilities
        $backupFileName1 = $this->tenantDatabaseActions->backupTenantDatabase($this->facility->id);
        $backupFileName2 = $this->tenantDatabaseActions->backupTenantDatabase($anotherFacility->id);

        // List backups for first facility
        $backups1 = $this->tenantDatabaseActions->listTenantBackups($this->facility->id);

        // List backups for second facility
        $backups2 = $this->tenantDatabaseActions->listTenantBackups($anotherFacility->id);

        // Assert each facility only sees its own backups
        $this->assertCount(1, $backups1);
        $this->assertCount(1, $backups2);
        $this->assertEquals($backupFileName1, $backups1[0]['filename']);
        $this->assertEquals($backupFileName2, $backups2[0]['filename']);

        // Clean up second facility
        TenantDatabaseService::dropTenantDatabase($anotherFacility);
        $anotherFacility->delete();
    }

    /** @test */
    public function it_throws_exception_when_backing_up_non_existent_database(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database for Test Backup Facility does not exist');

        $this->tenantDatabaseActions->backupTenantDatabase($this->facility->id);
    }

    /** @test */
    public function it_throws_exception_when_restoring_non_existent_backup(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Backup file not found: non-existent-backup.sql');

        $this->tenantDatabaseActions->restoreTenantDatabase($this->facility->id, 'non-existent-backup.sql');
    }

    /** @test */
    public function it_throws_exception_when_deleting_backup_from_wrong_facility(): void
    {
        // Create another facility
        $anotherFacility = Facility::create([
            'name' => ['en' => 'Another Facility'],
            'subdomain' => 'another',
            'is_active' => true,
        ]);

        // Create database and backup for another facility
        TenantDatabaseService::createTenantDatabase($anotherFacility);
        $this->tenantDatabaseActions->runTenantMigration($anotherFacility->id);
        $backupFileName = $this->tenantDatabaseActions->backupTenantDatabase($anotherFacility->id);

        // Try to delete another facility's backup using current facility
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Backup does not belong to this facility');

        $this->tenantDatabaseActions->deleteTenantBackup($this->facility->id, $backupFileName);

        // Clean up
        TenantDatabaseService::dropTenantDatabase($anotherFacility);
        $anotherFacility->delete();
    }
}
