<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('countries')->truncate();
        \App\Core\Models\Country::create([
            'name' => [
                'en' => 'Iraq',
                'ar' => 'العراق',
            ],
            'is_active' => 1,
        ]);
        \App\Core\Models\Country::create([
            'name' => [
                'en' => 'United States',
                'ar' => 'الولايات المتحدة',
            ],
            'is_active' => 1,
        ]);
        \App\Core\Models\Country::create([
            'name' => [
                'en' => 'United Kingdom',
                'ar' => 'المملكة المتحدة',
            ],
            'is_active' => 1,
        ]);
        \App\Core\Models\Country::create([
            'name' => [
                'en' => 'Canada',
                'ar' => 'كندا',
            ],
            'is_active' => 1,
        ]);
        Schema::enableForeignKeyConstraints();
    }
}
