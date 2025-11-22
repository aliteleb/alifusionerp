<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('facilities')->truncate();

        // Create test facility
        $testFacility = \Modules\Master\Entities\Facility::create([
            'name' => 'Test',
            'subdomain' => 'test',
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
