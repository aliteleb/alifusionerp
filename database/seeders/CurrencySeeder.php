<?php

namespace Database\Seeders;

use App\Core\Models\Country;
use App\Core\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Currency::truncate();

        $iraq = Country::where('name->en', 'Iraq')->first();
        $us = Country::where('name->en', 'United States')->first();
        $uk = Country::where('name->en', 'United Kingdom')->first();

        $currencies = [
            [
                'country_id' => $iraq->id,
                'title' => [
                    'en' => 'Iraqi Dinar',
                    'ar' => 'دينار عراقي',
                ],
                'symbol' => 'IQD',
                'is_active' => true,
            ],
            [
                'country_id' => $us->id,
                'title' => [
                    'en' => 'United States Dollar',
                    'ar' => 'دولار أمريكي',
                ],
                'symbol' => '$',
                'is_active' => true,
            ],
            [
                'country_id' => $uk->id,
                'title' => [
                    'en' => 'British Pound',
                    'ar' => 'جنيه إسترليني',
                ],
                'symbol' => 'GBP',
                'is_active' => true,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::create($currency);
        }

        Schema::enableForeignKeyConstraints();
    }
}
