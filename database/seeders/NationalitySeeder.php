<?php

namespace Database\Seeders;

use App\Core\Models\Nationality;
use Illuminate\Database\Seeder;

class NationalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nationalities = [
            ['en' => 'American', 'ar' => 'أمريكي'],
            ['en' => 'British', 'ar' => 'بريطاني'],
            ['en' => 'Canadian', 'ar' => 'كندي'],
            ['en' => 'Australian', 'ar' => 'أسترالي'],
            ['en' => 'German', 'ar' => 'ألماني'],
            ['en' => 'French', 'ar' => 'فرنسي'],
            ['en' => 'Saudi', 'ar' => 'سعودي'],
            ['en' => 'Emirati', 'ar' => 'إماراتي'],
            ['en' => 'Qatari', 'ar' => 'قطري'],
            ['en' => 'Kuwaiti', 'ar' => 'كويتي'],
            ['en' => 'Bahraini', 'ar' => 'بحريني'],
            ['en' => 'Omani', 'ar' => 'عماني'],
            ['en' => 'Egyptian', 'ar' => 'مصري'],
            ['en' => 'Jordanian', 'ar' => 'أردني'],
            ['en' => 'Syrian', 'ar' => 'سوري'],
            ['en' => 'Lebanese', 'ar' => 'لبناني'],
            ['en' => 'Iraqi', 'ar' => 'عراقي'],
        ];

        foreach ($nationalities as $nationality) {
            Nationality::create([
                'name' => $nationality,
                'is_active' => true,
            ]);
        }
    }
}
