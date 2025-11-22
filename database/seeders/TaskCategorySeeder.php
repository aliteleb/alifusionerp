<?php

namespace Database\Seeders;

use App\Core\Models\TaskCategory;
use Illuminate\Database\Seeder;

class TaskCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = TaskCategory::getDefaultCategories();

        foreach ($categories as $categoryData) {
            TaskCategory::updateOrCreate(
                ['code' => $categoryData['code']],
                $categoryData
            );
        }
    }
}
