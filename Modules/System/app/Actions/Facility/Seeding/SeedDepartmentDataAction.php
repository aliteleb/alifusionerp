<?php

namespace Modules\System\Actions\Facility\Seeding;

use Modules\Core\Entities\Branch;
use Modules\Core\Entities\Department;
use Exception;
use Illuminate\Support\Facades\Log;

class SeedDepartmentDataAction
{
    /**
     * Seed department data for the facility.
     */
    public function execute(): void
    {
        Log::info('Seeding department data');

        try {
            // Get all branches to create departments for each
            $branches = Branch::all();

            if ($branches->isEmpty()) {
                Log::warning('No branches found, skipping department seeding');

                return;
            }

            // Define different department types with translations
            $departmentTypes = [
                [
                    'name' => [
                        'en' => 'Human Resources',
                        'ar' => 'الموارد البشرية',
                        'ku' => 'سەرچاوە مرۆییەکان',
                    ],
                    'code' => 'HR',
                    'description' => 'Human Resources Department',
                ],
                [
                    'name' => [
                        'en' => 'Finance',
                        'ar' => 'المالية',
                        'ku' => 'دارایی',
                    ],
                    'code' => 'FIN',
                    'description' => 'Finance and Accounting Department',
                ],
                [
                    'name' => [
                        'en' => 'Information Technology',
                        'ar' => 'تكنولوجيا المعلومات',
                        'ku' => 'تەکنەلۆژیای زانیاری',
                    ],
                    'code' => 'IT',
                    'description' => 'Information Technology Department',
                ],
                [
                    'name' => [
                        'en' => 'Operations',
                        'ar' => 'العمليات',
                        'ku' => 'کارەکان',
                    ],
                    'code' => 'OPS',
                    'description' => 'Operations Department',
                ],
                [
                    'name' => [
                        'en' => 'Marketing',
                        'ar' => 'التسويق',
                        'ku' => 'بازرگانی',
                    ],
                    'code' => 'MKT',
                    'description' => 'Marketing Department',
                ],
            ];

            // Track how many departments we create
            $createdCount = 0;

            // Create departments for each branch
            foreach ($branches as $branch) {
                foreach ($departmentTypes as $type) {
                    // Create a unique code using branch ID to avoid duplicates
                    $departmentCode = $type['code'].$branch->id;

                    // Check if department with this code already exists
                    $existingDepartment = Department::where('code', $departmentCode)->first();

                    if (! $existingDepartment) {
                        // Create the department since it doesn't exist
                        Department::create([
                            'name' => $type['name'],
                            'code' => $departmentCode,
                            'description' => $type['description'],
                            'branch_id' => $branch->id,
                            'is_active' => true,
                        ]);

                        $createdCount++;
                    }
                }
            }

            Log::info('Seeded '.$createdCount.' new departments');

        } catch (Exception $e) {
            Log::error('Error seeding department data: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
