<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('genders')->truncate();
        \App\Core\Models\Gender::create([
            'name' => [
                'en' => 'Male',
                'ar' => 'ذكر',
            ],
            'is_active' => 1,
        ]);
        \App\Core\Models\Gender::create([
            'name' => [
                'en' => 'Female',
                'ar' => 'أنثى',
            ],
            'is_active' => 1,
        ]);
        Schema::enableForeignKeyConstraints();
    }
}
