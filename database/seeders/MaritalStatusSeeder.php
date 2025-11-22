<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MaritalStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('marital_statuses')->truncate();

        $statuses = [
            [
                'name' => json_encode([
                    'en' => 'Single',
                    'ar' => 'أعزب',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Married',
                    'ar' => 'متزوج',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Divorced',
                    'ar' => 'مطلق',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Widowed',
                    'ar' => 'أرمل',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('marital_statuses')->insert($statuses);
        Schema::enableForeignKeyConstraints();
    }
}
