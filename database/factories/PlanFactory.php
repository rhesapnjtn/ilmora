<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->word()).' '.fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
            'monthly' => fake()->numberBetween(50000, 300000),
            'annual' => fake()->numberBetween(500000, 3000000),
            'highlighted' => false,
            'features' => ['Akses materi lengkap', 'Sertifikat digital'],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that the plan should be highlighted as the most popular option.
     */
    public function highlighted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'highlighted' => true,
        ]);
    }
}
