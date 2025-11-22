<?php

namespace Database\Seeders;

use App\Enums\AnnouncementStatus;
use App\Models\Announcement;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user and branches for seeding
        $user = User::first();
        $branches = Branch::active()->get();

        if (! $user || $branches->isEmpty()) {
            $this->command->warn('No users or branches found. Skipping announcement seeding.');

            return;
        }

        $announcements = [
            [
                'title' => __('Welcome to Ali Fusion ERP'),
                'description' => __('We are excited to announce the launch of Ali Fusion ERP. This platform will help us serve you better and provide more efficient support.'),
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(30),
                'status' => AnnouncementStatus::PUBLISHED,
                'is_published' => true,
                'share_with_all_clients' => true,
                'branch_id' => $branches->first()->id,
            ],
            [
                'title' => __('System Maintenance Notice'),
                'description' => __('We will be performing scheduled maintenance on our systems this weekend. During this time, some services may be temporarily unavailable. We apologize for any inconvenience.'),
                'start_date' => now()->addDays(2),
                'end_date' => now()->addDays(7),
                'status' => AnnouncementStatus::PUBLISHED,
                'is_published' => true,
                'share_with_all_clients' => false,
                'branch_id' => $branches->first()->id,
            ],
            [
                'title' => __('New Feature Release'),
                'description' => __('We are pleased to announce several new features that will enhance your experience with our platform. Check out the latest updates in your dashboard.'),
                'start_date' => now()->addDays(1),
                'end_date' => now()->addDays(14),
                'status' => AnnouncementStatus::DRAFT,
                'is_published' => false,
                'share_with_all_clients' => true,
                'branch_id' => $branches->count() > 1 ? $branches->last()->id : $branches->first()->id,
            ],
        ];

        foreach ($announcements as $announcementData) {
            Announcement::create($announcementData);
        }

        $this->command->info('Announcements seeded successfully!');
    }
}
