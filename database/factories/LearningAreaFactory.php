<?php

namespace Database\Factories;

use App\Models\LearningArea;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LearningArea>
 */
class LearningAreaFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Demo Subject '.fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'short_name' => null,
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
