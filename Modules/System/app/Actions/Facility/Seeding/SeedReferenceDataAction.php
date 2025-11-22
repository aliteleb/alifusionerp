<?php

namespace Modules\System\Actions\Facility\Seeding;

use Modules\Core\Entities\Country;
use Modules\Master\Entities\Facility;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SeedReferenceDataAction
{
    /**
     * Seed reference data (genders, marital statuses, nationalities, countries, currencies) for the facility.
     */
    public function execute(Facility $facility): void
    {
        Log::info('Seeding reference data for facility', ['facility_id' => $facility->id]);

        try {
            // Seed reference data into current (tenant) database
            $this->seedDefaultGenders(); // Seed default genders for tenant
            // Seed default marital statuses for tenant
            $this->seedDefaultMaritalStatuses();
            // Seed default nationalities for tenant
            $this->seedDefaultNationalities();
            // Seed default countries for tenant
            $this->seedDefaultCountries();
            // Seed default currencies for tenant
            $this->seedDefaultCurrencies();

            Log::info('Successfully completed reference data seeding', ['facility_id' => $facility->id]);

        } catch (Exception $e) {
            Log::error('Error seeding reference data: '.$e->getMessage(), [
                'facility_id' => $facility->id,
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Seed default genders into tenant database
     */
    private function seedDefaultGenders(): void
    {
        // Define default genders
        $genders = [
            [
                'name' => json_encode([
                    'en' => 'Male',
                    'ar' => 'ذكر',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Female',
                    'ar' => 'أنثى',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        Schema::disableForeignKeyConstraints();
        DB::table('genders')->truncate();

        DB::table('genders')->insert($genders);

        Schema::enableForeignKeyConstraints();
        Log::info('Seeded '.count($genders).' default genders');
    }

    /**
     * Seed default marital statuses into tenant database
     */
    private function seedDefaultMaritalStatuses(): void
    {
        // Define default marital statuses
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

        Schema::disableForeignKeyConstraints();
        DB::table('marital_statuses')->truncate();

        DB::table('marital_statuses')->insert($statuses);

        Schema::enableForeignKeyConstraints();
        Log::info('Seeded '.count($statuses).' default marital statuses');
    }

    /**
     * Seed default nationalities into tenant database
     */
    private function seedDefaultNationalities(): void
    {
        // Define default nationalities
        $nationalities = [
            [
                'name' => json_encode([
                    'en' => 'American',
                    'ar' => 'أمريكي',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'British',
                    'ar' => 'بريطاني',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Canadian',
                    'ar' => 'كندي',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Australian',
                    'ar' => 'أسترالي',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'German',
                    'ar' => 'ألماني',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'French',
                    'ar' => 'فرنسي',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Saudi',
                    'ar' => 'سعودي',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Emirati',
                    'ar' => 'إماراتي',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Qatari',
                    'ar' => 'قطري',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Kuwaiti',
                    'ar' => 'كويتي',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Bahraini',
                    'ar' => 'بحريني',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Omani',
                    'ar' => 'عماني',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Egyptian',
                    'ar' => 'مصري',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Jordanian',
                    'ar' => 'أردني',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Syrian',
                    'ar' => 'سوري',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Lebanese',
                    'ar' => 'لبناني',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Iraqi',
                    'ar' => 'عراقي',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        Schema::disableForeignKeyConstraints();
        DB::table('nationalities')->truncate();

        DB::table('nationalities')->insert($nationalities);

        Schema::enableForeignKeyConstraints();
        Log::info('Seeded '.count($nationalities).' default nationalities');
    }

    /**
     * Seed default countries into tenant database
     */
    private function seedDefaultCountries(): void
    {
        // Define default countries
        $countries = [
            [
                'name' => json_encode([
                    'en' => 'Iraq',
                    'ar' => 'العراق',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'United States',
                    'ar' => 'الولايات المتحدة',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'United Kingdom',
                    'ar' => 'المملكة المتحدة',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'en' => 'Canada',
                    'ar' => 'كندا',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        Schema::disableForeignKeyConstraints();
        DB::table('countries')->truncate();

        DB::table('countries')->insert($countries);

        Schema::enableForeignKeyConstraints();
        Log::info('Seeded '.count($countries).' default countries');
    }

    /**
     * Seed default currencies into tenant database
     */
    private function seedDefaultCurrencies(): void
    {
        // First get the country IDs we just inserted
        $iraq = DB::table('countries')->where('name->en', 'Iraq')->first();
        $us = DB::table('countries')->where('name->en', 'United States')->first();
        $uk = DB::table('countries')->where('name->en', 'United Kingdom')->first();
        $canada = DB::table('countries')->where('name->en', 'Canada')->first();

        if (! $iraq || ! $us || ! $uk || ! $canada) {
            Log::warning('Could not find required countries for currency seeding');

            return;
        }

        // Define default currencies
        $currencies = [
            [
                'country_id' => $iraq->id,
                'title' => json_encode([
                    'en' => 'Iraqi Dinar',
                    'ar' => 'دينار عراقي',
                ]),
                'symbol' => 'IQD',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_id' => $us->id,
                'title' => json_encode([
                    'en' => 'United States Dollar',
                    'ar' => 'دولار أمريكي',
                ]),
                'symbol' => '$',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_id' => $uk->id,
                'title' => json_encode([
                    'en' => 'British Pound',
                    'ar' => 'جنيه إسترليني',
                ]),
                'symbol' => 'GBP',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_id' => $canada->id,
                'title' => json_encode([
                    'en' => 'Canadian Dollar',
                    'ar' => 'دولار كندي',
                ]),
                'symbol' => 'CAD',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        Schema::disableForeignKeyConstraints();
        DB::table('currencies')->truncate();

        DB::table('currencies')->insert($currencies);

        Schema::enableForeignKeyConstraints();
        Log::info('Seeded '.count($currencies).' default currencies');
    }
}
