<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Entities\User;
use Illuminate\Support\Facades\Hash;

class MasterDatabaseSeeder extends Seeder
{
    /**
     * Seed the master database only.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding master database...');

        // Create master admin user
        $this->command->info('👤 Creating master admin user...');
        User::firstOrCreate([
            'email' => 'master@example.com',
        ], [
            'name' => 'Master Admin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $this->command->info('✅ Master admin user created: master@example.com / password');

        // Seed master settings
        $this->command->info('⚙️  Seeding master settings...');
        $this->call([
            MasterSettingSeeder::class,
        ]);

        // Seed facilities (optional - can be commented out if not needed)
        $this->command->info('🏢 Seeding facilities...');
        $this->call([
            FacilitySeeder::class,
        ]);

        // Add other master-level seeders here if needed
        // $this->call([
        //     GenderSeeder::class,
        //     MaritalStatusSeeder::class,
        //     CountrySeeder::class,
        //     CurrencySeeder::class,
        // ]);

        $this->command->info('✅ Master database seeded successfully!');
    }
}
