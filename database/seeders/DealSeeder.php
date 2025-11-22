<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Seeder;

class DealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data to use for relationships
        $branches = Branch::all();
        $clients = Client::all();
        $users = User::all();

        if ($branches->isEmpty() || $clients->isEmpty() || $users->isEmpty()) {
            if ($this->command) {
                $this->command->warn('Skipping Deal seeder - missing required data (branches, clients, or users)');
            }

            return;
        }

        if ($this->command) {
            $this->command->info('Seeding deals...');
        }

        // Create a variety of deals with different statuses and stages

        // Open deals in various stages
        Deal::factory(15)
            ->state([
                'client_id' => $clients->random()->id,
                'assigned_to' => $users->random()->id,
                'deal_owner' => $users->random()->id,
                'branch_id' => $branches->random()->id,
            ])
            ->create();

        // Won deals
        Deal::factory(8)
            ->won()
            ->state([
                'client_id' => $clients->random()->id,
                'assigned_to' => $users->random()->id,
                'deal_owner' => $users->random()->id,
                'branch_id' => $branches->random()->id,
            ])
            ->create();

        // Lost deals
        Deal::factory(5)
            ->lost()
            ->state([
                'client_id' => $clients->random()->id,
                'assigned_to' => $users->random()->id,
                'deal_owner' => $users->random()->id,
                'branch_id' => $branches->random()->id,
            ])
            ->create();

        // High value deals
        Deal::factory(3)
            ->highValue()
            ->state([
                'client_id' => $clients->random()->id,
                'assigned_to' => $users->random()->id,
                'deal_owner' => $users->random()->id,
                'branch_id' => $branches->random()->id,
            ])
            ->create();

        // Urgent deals
        Deal::factory(4)
            ->urgent()
            ->state([
                'client_id' => $clients->random()->id,
                'assigned_to' => $users->random()->id,
                'deal_owner' => $users->random()->id,
                'branch_id' => $branches->random()->id,
            ])
            ->create();

        // Overdue deals
        Deal::factory(2)
            ->overdue()
            ->state([
                'client_id' => $clients->random()->id,
                'assigned_to' => $users->random()->id,
                'deal_owner' => $users->random()->id,
                'branch_id' => $branches->random()->id,
            ])
            ->create();

        // Create some specific example deals
        $exampleDeals = [
            [
                'title' => 'Ali Fusion ERP Implementation',
                'description' => 'Complete Ali Fusion ERP rollout for a large enterprise client including data migration, customization, and training.',
                'deal_value' => 85000.00,
                'currency' => 'USD',
                'probability' => 75,
                'expected_close_date' => now()->addDays(45),
                'stage' => 'negotiation',
                'status' => 'open',
                'priority' => 'high',
                'source' => 'referral',
                'pipeline' => 'Enterprise Pipeline',
                'tags' => ['enterprise', 'high-value', 'erp', 'implementation'],
                'next_action' => 'Contract negotiation meeting',
                'next_action_date' => now()->addDays(3),
            ],
            [
                'title' => 'E-commerce Website Development',
                'description' => 'Modern e-commerce website with payment integration, inventory management, and mobile responsiveness.',
                'deal_value' => 25000.00,
                'currency' => 'USD',
                'probability' => 60,
                'expected_close_date' => now()->addDays(30),
                'stage' => 'proposal',
                'status' => 'open',
                'priority' => 'medium',
                'source' => 'website',
                'pipeline' => 'SMB Pipeline',
                'tags' => ['e-commerce', 'website', 'development'],
                'next_action' => 'Follow up on proposal',
                'next_action_date' => now()->addDays(7),
            ],
            [
                'title' => 'Digital Marketing Campaign',
                'description' => '6-month comprehensive digital marketing campaign including SEO, PPC, and social media management.',
                'deal_value' => 15000.00,
                'currency' => 'USD',
                'probability' => 85,
                'expected_close_date' => now()->addDays(15),
                'stage' => 'decision',
                'status' => 'open',
                'priority' => 'urgent',
                'source' => 'google_ads',
                'pipeline' => 'Inbound Pipeline',
                'tags' => ['marketing', 'digital', 'campaign', 'recurring'],
                'next_action' => 'Final approval call',
                'next_action_date' => now()->addDays(2),
            ],
        ];

        foreach ($exampleDeals as $dealData) {
            Deal::factory()->create(array_merge($dealData, [
                'client_id' => $clients->random()->id,
                'assigned_to' => $users->random()->id,
                'deal_owner' => $users->random()->id,
                'branch_id' => $branches->random()->id,
            ]));
        }

        $totalDeals = Deal::count();
        if ($this->command) {
            $this->command->info("Created {$totalDeals} deals successfully!");
        }
    }
}
