<?php

namespace Database\Seeders;

use App\Core\Models\CustomerGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class CustomerGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        CustomerGroup::truncate();
        Schema::disableForeignKeyConstraints();

        $defaultGroups = CustomerGroup::getDefaultGroups();

        foreach ($defaultGroups as $group) {
            CustomerGroup::create([
                'name' => $group['name'],
                'code' => $group['code'],
                'description' => $group['description'],
                'color' => $group['color'],
                'icon' => $group['icon'],
                'sort_order' => $group['sort_order'],
                'is_active' => true,
            ]);
        }

        Schema::enableForeignKeyConstraints();

        $this->command->info('✅ Customer Groups seeded successfully');
        $this->command->info('📊 Created '.count($defaultGroups).' customer groups');
    }
}
