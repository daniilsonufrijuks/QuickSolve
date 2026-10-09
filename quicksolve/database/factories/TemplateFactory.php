<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Template>
 */
class TemplateFactory extends Factory
{
    protected $model = Template::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory()->template(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->paragraph(),
            'whats_included' => ['Spreadsheet', 'Instructions'],
            'price' => 1900,
            'currency' => 'EUR',
            'preview_image' => null,
            'private_file_path' => null,
            'is_featured' => false,
            'is_published' => true,
        ];
    }
}
