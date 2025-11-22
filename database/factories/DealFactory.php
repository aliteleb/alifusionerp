<?php

namespace Database\Factories;

use App\Enums\DealPriority;
use App\Enums\DealSource;
use App\Enums\DealStage;
use App\Enums\DealStatus;
use App\Models\Branch;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deal>
 */
class DealFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dealValue = $this->faker->randomFloat(2, 1000, 100000);
        $probability = $this->faker->numberBetween(10, 95);
        $expectedCloseDate = $this->faker->dateTimeBetween('now', '+6 months');

        return [
            'title' => $this->faker->randomElement([
                'Website Development Project',
                'Mobile App Development',
                'E-commerce Platform',
                'Digital Marketing Campaign',
                'Software Consulting',
                'Cloud Migration Project',
                'Database Optimization',
                'Security Audit Services',
                'Custom Ali Fusion ERP Solution',
                'API Integration Project',
                'UI/UX Design Services',
                'SEO Optimization Package',
                'Social Media Management',
                'Content Management System',
                'Business Intelligence Solution',
            ]),
            'description' => $this->faker->paragraphs(3, true),
            'deal_value' => $dealValue,
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
            'probability' => $probability,
            'expected_close_date' => $expectedCloseDate,
            'actual_close_date' => null,
            'stage' => $this->faker->randomElement(DealStage::cases())->value,
            'status' => $this->faker->randomElement([
                DealStatus::OPEN->value,
                DealStatus::OPEN->value,
                DealStatus::OPEN->value, // More likely to be open
                DealStatus::WON->value,
                DealStatus::LOST->value,
            ]),
            'priority' => $this->faker->randomElement(DealPriority::cases())->value,
            'source' => $this->faker->randomElement(DealSource::cases())->value,
            'pipeline' => $this->faker->randomElement([
                'Sales Pipeline',
                'Enterprise Pipeline',
                'SMB Pipeline',
                'Partner Pipeline',
                'Inbound Pipeline',
            ]),
            'client_id' => Client::factory(),
            'assigned_to' => User::factory(),
            'deal_owner' => User::factory(),
            'branch_id' => Branch::factory(),
            'tags' => $this->faker->randomElements([
                'high-value', 'enterprise', 'smb', 'recurring', 'one-time',
                'urgent', 'qualified', 'warm-lead', 'cold-lead', 'referral',
                'upsell', 'cross-sell', 'new-client', 'existing-client',
            ], $this->faker->numberBetween(1, 4)),
            'notes' => $this->faker->optional(0.7)->paragraphs(2, true),
            'internal_notes' => $this->faker->optional(0.5)->paragraphs(1, true),
            'next_action' => $this->faker->optional(0.8)->randomElement([
                'Follow up call',
                'Send proposal',
                'Schedule demo',
                'Negotiate terms',
                'Get approval',
                'Sign contract',
                'Technical discussion',
                'Budget confirmation',
            ]),
            'next_action_date' => $this->faker->optional(0.8)->dateTimeBetween('now', '+30 days'),
            'last_contact_date' => $this->faker->optional(0.9)->dateTimeBetween('-30 days', 'now'),
            'contact_frequency' => $this->faker->optional(0.6)->numberBetween(3, 30),
            'competitors' => $this->faker->optional(0.4)->randomElements([
                'Competitor A', 'Competitor B', 'Competitor C', 'In-house team',
                'Other vendor', 'Freelancer', 'Agency',
            ], $this->faker->numberBetween(1, 3)),
            'win_reason' => null,
            'loss_reason' => null,
            'deal_products' => $this->faker->optional(0.6)->randomElements([
                ['name' => 'Product A', 'quantity' => 1, 'price' => 5000],
                ['name' => 'Product B', 'quantity' => 2, 'price' => 2500],
                ['name' => 'Service C', 'quantity' => 1, 'price' => 10000],
            ], $this->faker->numberBetween(1, 2)),
            'commission_rate' => $this->faker->optional(0.7)->randomFloat(2, 2, 15),
            'commission_amount' => null,
            'discount_percentage' => $this->faker->optional(0.3)->randomFloat(2, 5, 20),
            'discount_amount' => null,
            'final_amount' => null,
            'is_active' => $this->faker->boolean(95),
        ];
    }

    /**
     * Indicate that the deal is won.
     */
    public function won(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DealStatus::WON->value,
            'stage' => DealStage::CLOSED_WON->value,
            'actual_close_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'win_reason' => $this->faker->randomElement([
                'Best price offered',
                'Superior technical solution',
                'Excellent relationship',
                'Fastest delivery time',
                'Best value proposition',
            ]),
            'probability' => 100,
        ]);
    }

    /**
     * Indicate that the deal is lost.
     */
    public function lost(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DealStatus::LOST->value,
            'stage' => DealStage::CLOSED_LOST->value,
            'actual_close_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'loss_reason' => $this->faker->randomElement([
                'Price too high',
                'Chose competitor',
                'Budget constraints',
                'Timeline mismatch',
                'Technical requirements not met',
                'Lost contact',
            ]),
            'probability' => 0,
        ]);
    }

    /**
     * Indicate that the deal is high value.
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'deal_value' => $this->faker->randomFloat(2, 50000, 500000),
            'priority' => DealPriority::HIGH->value,
        ]);
    }

    /**
     * Indicate that the deal is urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => DealPriority::URGENT->value,
            'expected_close_date' => $this->faker->dateTimeBetween('now', '+14 days'),
        ]);
    }

    /**
     * Indicate that the deal is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'expected_close_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
            'status' => DealStatus::OPEN->value,
        ]);
    }
}
