<?php

namespace Database\Factories;

use App\Core\Enums\OpportunityStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Core\Models\Opportunity>
 */
class OpportunityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stages = [
            OpportunityStage::NEW->value,
            OpportunityStage::QUALIFIED->value,
            OpportunityStage::PROPOSAL->value,
            OpportunityStage::NEGOTIATION->value,
            OpportunityStage::CLOSED_WON->value,
            OpportunityStage::CLOSED_LOST->value,
        ];
        $states = ['Open', 'Won', 'Lost', 'Cancelled'];
        $priorities = ['Low', 'Medium', 'High', 'Critical'];
        $sources = ['Website', 'Referral', 'Social Media', 'Email Campaign', 'Cold Call', 'Trade Show', 'Other'];
        $responsibilities = ['Everyone', 'Customize Permission'];

        $forecastDate = fake()->optional(0.8)->dateTimeBetween('now', '+12 months');
        $nextActionDate = fake()->optional(0.7)->dateTimeBetween('now', '+3 months');
        $stage = fake()->randomElement($stages);

        // Determine state based on stage
        $state = match ($stage) {
            OpportunityStage::CLOSED_WON->value => 'Won',
            OpportunityStage::CLOSED_LOST->value => 'Lost',
            default => fake()->randomElement(['Open', 'Open', 'Open', 'Cancelled']) // Weight towards Open
        };

        // Set probability based on stage
        $probability = match ($stage) {
            OpportunityStage::NEW->value => fake()->numberBetween(5, 25),
            OpportunityStage::QUALIFIED->value => fake()->numberBetween(20, 40),
            OpportunityStage::PROPOSAL->value => fake()->numberBetween(35, 60),
            OpportunityStage::NEGOTIATION->value => fake()->numberBetween(55, 85),
            OpportunityStage::CLOSED_WON->value => 100,
            OpportunityStage::CLOSED_LOST->value => 0,
            default => fake()->numberBetween(10, 50)
        };

        return [
            'name' => fake()->sentence(fake()->numberBetween(3, 8)),
            'description' => fake()->optional(0.8)->paragraphs(fake()->numberBetween(1, 3), true),
            'stages' => $stage,
            'state' => $state,
            'expected_revenue' => fake()->optional(0.9)->randomFloat(2, 1000, 500000),
            'probability_of_winning' => $probability,
            'forecast_close_date' => $forecastDate?->format('Y-m-d'),
            'next_action' => fake()->optional(0.7)->sentence(fake()->numberBetween(3, 10)),
            'next_action_date' => $nextActionDate?->format('Y-m-d'),
            'short_note' => fake()->optional(0.6)->sentence(fake()->numberBetween(5, 15)),
            'responsibility' => fake()->randomElement($responsibilities),
            'responsible_users' => fake()->optional(0.3)->randomElements([1, 2, 3, 4, 5], fake()->numberBetween(1, 3)),
            'tags' => fake()->optional(0.6)->randomElements([
                'high-value', 'enterprise', 'recurring', 'upsell', 'new-client',
                'referral', 'competitive', 'strategic', 'urgent', 'long-term',
            ], fake()->numberBetween(1, 3)),
            'priority' => fake()->randomElement($priorities),
            'source' => fake()->optional(0.8)->randomElement($sources),
            'is_active' => fake()->boolean(95), // 95% chance of being active
        ];
    }

    /**
     * Indicate that the opportunity is won.
     */
    public function won(): static
    {
        return $this->state(fn (array $attributes) => [
            'stages' => OpportunityStage::CLOSED_WON->value,
            'state' => 'Won',
            'probability_of_winning' => 100,
        ]);
    }

    /**
     * Indicate that the opportunity is lost.
     */
    public function lost(): static
    {
        return $this->state(fn (array $attributes) => [
            'stages' => OpportunityStage::CLOSED_LOST->value,
            'state' => 'Lost',
            'probability_of_winning' => 0,
        ]);
    }

    /**
     * Indicate that the opportunity is high value.
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'expected_revenue' => fake()->randomFloat(2, 100000, 1000000),
            'priority' => fake()->randomElement(['High', 'Critical']),
        ]);
    }

    /**
     * Indicate that the opportunity is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'forecast_close_date' => fake()->dateTimeBetween('-3 months', '-1 day')->format('Y-m-d'),
            'state' => 'Open',
            'stages' => fake()->randomElement([
                OpportunityStage::QUALIFIED->value,
                OpportunityStage::PROPOSAL->value,
                OpportunityStage::NEGOTIATION->value,
            ]),
        ]);
    }

    /**
     * Indicate that the opportunity is in negotiation stage.
     */
    public function negotiation(): static
    {
        return $this->state(fn (array $attributes) => [
            'stages' => OpportunityStage::NEGOTIATION->value,
            'state' => 'Open',
            'probability_of_winning' => fake()->numberBetween(60, 90),
        ]);
    }

    /**
     * Indicate that the opportunity is new.
     */
    public function newOpportunity(): static
    {
        return $this->state(fn (array $attributes) => [
            'stages' => OpportunityStage::NEW->value,
            'state' => 'Open',
            'probability_of_winning' => fake()->numberBetween(5, 25),
        ]);
    }
}
