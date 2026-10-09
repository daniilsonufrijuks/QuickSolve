<?php

namespace Database\Factories;

use App\Enums\AccessType;
use App\Models\Category;
use App\Models\Tool;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tool>
 */
class ToolFactory extends Factory
{
    protected $model = Tool::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->sentence(),
            'long_description' => fake()->paragraph(),
            'icon' => 'calculator',
            'access_type' => AccessType::Free,
            'is_featured' => false,
            'is_published' => true,
            'popularity' => 0,
            'metadata' => ['faqs' => []],
        ];
    }

    public function premium(): static
    {
        return $this->state(fn () => ['access_type' => AccessType::Premium]);
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }
}
