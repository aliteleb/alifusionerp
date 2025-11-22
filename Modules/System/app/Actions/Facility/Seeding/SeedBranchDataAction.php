<?php

namespace Modules\System\Actions\Facility\Seeding;

use Modules\Core\Entities\Branch;
use Exception;
use Illuminate\Support\Facades\Log;

class SeedBranchDataAction
{
    /**
     * Seed default branches for the facility.
     */
    public function execute(): void
    {
        Log::info('Seeding branch data');

        try {
            $this->createDefaultBranches();

        } catch (Exception $e) {
            Log::error('Error seeding branch data: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Create default branches for the facility.
     */
    private function createDefaultBranches(): void
    {
        $branches = [
            [
                'name' => [
                    'en' => 'HQ',
                    'ar' => 'المقر الرئيسي',
                    'ku' => 'ئامەتی',
                ],
                'is_active' => true,
                'is_hq' => true,
            ],
            [
                'name' => [
                    'en' => 'Baghdad Branch',
                    'ar' => 'فرع بغداد',
                    'ku' => 'لقەی بەغداد',
                ],
                'is_active' => true,
                'is_hq' => false,
            ],
            [
                'name' => [
                    'en' => 'Erbil Branch',
                    'ar' => 'فرع أربيل',
                    'ku' => 'لقەی هەولێر',
                ],
                'is_active' => true,
                'is_hq' => false,
            ],
            [
                'name' => [
                    'en' => 'Basra Branch',
                    'ar' => 'فرع البصرة',
                    'ku' => 'لقەی بەسرە',
                ],
                'is_active' => true,
                'is_hq' => false,
            ],
            [
                'name' => [
                    'en' => 'Mosul Branch',
                    'ar' => 'فرع الموصل',
                    'ku' => 'لقەی مووسڵ',
                ],
                'is_active' => true,
                'is_hq' => false,
            ],
            [
                'name' => [
                    'en' => 'Sulaymaniyah Branch',
                    'ar' => 'فرع السليمانية',
                    'ku' => 'لقەی سلێمانی',
                ],
                'is_active' => true,
                'is_hq' => false,
            ],
        ];

        foreach ($branches as $branchData) {
            $this->createBranchIfNotExists($branchData);
        }

        Log::info('Completed branch seeding');
    }

    /**
     * Create a branch if it doesn't already exist.
     */
    private function createBranchIfNotExists(array $branchData): void
    {
        $existingBranch = Branch::where('is_hq', $branchData['is_hq'])->first();

        if (! $existingBranch) {
            Branch::create($branchData);

            $branchName = $branchData['name']['en'];
            Log::info("Created branch: {$branchName}");
        } else {
            $branchName = $branchData['name']['en'];
            Log::info("Branch already exists, skipping creation: {$branchName}");
        }
    }
}
