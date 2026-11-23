<?php

namespace Database\Factories;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grade>
 */
class GradeFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Demo Grade '.fake()->unique()->numberBetween(100, 999);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'level' => Grade::LEVEL_UPPER_PRIMARY,
            'sort_order' => fake()->numberBetween(1, 50),
            'description' => fake()->sentence(),
            'is_active' => true,
            'is_demo' => true,
        ];
    }

    public function production(): static
    {
        return $this->state(fn () => ['is_demo' => false]);
    }
}
