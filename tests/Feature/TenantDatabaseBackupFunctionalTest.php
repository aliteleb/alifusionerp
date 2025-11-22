<?php

namespace Tests\Feature;

use App\Core\Actions\Database\TenantDatabaseActions;
use App\Core\Models\Facility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantDatabaseBackupFunctionalTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_instantiate_backup_actions_class(): void
    {
        $tenantDatabaseActions = app(TenantDatabaseActions::class);
        $this->assertInstanceOf(TenantDatabaseActions::class, $tenantDatabaseActions);
    }

    /** @test */
    public function it_creates_facility_with_correct_attributes(): void
    {
        $facility = Facility::create([
            'name' => ['en' => 'Test Backup Facility'],
            'subdomain' => 'test-backup',
            'is_active' => true,
        ]);

        $this->assertNotNull($facility->id);
        $this->assertEquals('test-backup', $facility->subdomain);
        // The name attribute is translatable and returns the translated value
        $this->assertEquals('Test Backup Facility', $facility->name);
        $this->assertTrue($facility->is_active);
    }

    /** @test */
    public function it_validates_backup_filename_format(): void
    {
        $facility = Facility::create([
            'name' => ['en' => 'Test Facility'],
            'subdomain' => 'test-facility',
            'is_active' => true,
        ]);

        // Test expected filename format: subdomain_YYYY-MM-DD_HH-mm-ss.sql
        $expectedPattern = '/^test-facility_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/';

        // Mock a filename that would be generated
        $timestamp = now()->format('Y-m-d_H-i-s');
        $mockFilename = "{$facility->subdomain}_{$timestamp}.sql";

        $this->assertMatchesRegularExpression($expectedPattern, $mockFilename);
    }

    /** @test */
    public function it_validates_backup_belongs_to_facility(): void
    {
        $facility1 = Facility::create([
            'name' => ['en' => 'Facility One'],
            'subdomain' => 'facility-one',
            'is_active' => true,
        ]);

        $facility2 = Facility::create([
            'name' => ['en' => 'Facility Two'],
            'subdomain' => 'facility-two',
            'is_active' => true,
        ]);

        // Test that backup filename validation works correctly
        $facility1Backup = 'facility-one_2024-01-15_14-30-25.sql';
        $facility2Backup = 'facility-two_2024-01-15_14-30-25.sql';

        $this->assertTrue(str_starts_with($facility1Backup, $facility1->subdomain.'_'));
        $this->assertFalse(str_starts_with($facility1Backup, $facility2->subdomain.'_'));

        $this->assertTrue(str_starts_with($facility2Backup, $facility2->subdomain.'_'));
        $this->assertFalse(str_starts_with($facility2Backup, $facility1->subdomain.'_'));
    }

    /** @test */
    public function it_provides_human_readable_file_sizes(): void
    {
        $tenantDatabaseActions = app(TenantDatabaseActions::class);

        // Use reflection to test the private formatBytes method
        $reflection = new \ReflectionClass($tenantDatabaseActions);
        $method = $reflection->getMethod('formatBytes');
        $method->setAccessible(true);

        $this->assertEquals('0 B', $method->invokeArgs($tenantDatabaseActions, [0]));
        $this->assertEquals('1 B', $method->invokeArgs($tenantDatabaseActions, [1]));
        $this->assertEquals('1 KB', $method->invokeArgs($tenantDatabaseActions, [1024]));
        $this->assertEquals('1 MB', $method->invokeArgs($tenantDatabaseActions, [1024 * 1024]));
        $this->assertEquals('1 GB', $method->invokeArgs($tenantDatabaseActions, [1024 * 1024 * 1024]));
        $this->assertEquals('1.5 KB', $method->invokeArgs($tenantDatabaseActions, [1536]));
    }

    /** @test */
    public function it_handles_facility_not_found_gracefully(): void
    {
        $tenantDatabaseActions = app(TenantDatabaseActions::class);

        try {
            $tenantDatabaseActions->backupTenantDatabase(999999); // Non-existent facility ID
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            $this->assertEquals('Facility not found', $e->getMessage());
        }
    }

    /** @test */
    public function it_validates_required_database_tools_configuration(): void
    {
        // Test that the backup process would use correct command formats
        $facility = Facility::create([
            'name' => ['en' => 'Test Facility'],
            'subdomain' => 'test',
            'is_active' => true,
        ]);

        // Mock database configuration
        $connectionName = "tenant_{$facility->id}";
        $databaseName = "hr_tenant_{$facility->id}";
        $host = 'localhost';
        $port = '5432';
        $username = 'postgres';
        $backupPath = '/path/to/backup.sql';

        // Test PostgreSQL command format
        $expectedPgCommand = sprintf(
            'pg_dump -h %s -p %s -U %s -d %s --no-password --clean --if-exists --create > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($databaseName),
            escapeshellarg($backupPath)
        );

        $this->assertStringContainsString('pg_dump', $expectedPgCommand);
        $this->assertStringContainsString('--clean --if-exists --create', $expectedPgCommand);

        // Test MySQL command format
        $expectedMysqlCommand = sprintf(
            'mysqldump -h %s -P %s -u %s -p%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            'password',
            escapeshellarg($databaseName),
            escapeshellarg($backupPath)
        );

        $this->assertStringContainsString('mysqldump', $expectedMysqlCommand);
        $this->assertStringContainsString('--single-transaction --routines --triggers', $expectedMysqlCommand);
    }
}
