<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Models\GeneratedDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GeneratedDocument>
 */
class GeneratedDocumentFactory extends Factory
{
    protected $model = GeneratedDocument::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'document_type' => DocumentType::Invoice,
            'title' => 'Invoice '.fake()->numerify('INV-###'),
            'private_file_path' => null,
            'structured_data' => [
                'currency' => 'EUR',
                'total' => '10.00',
            ],
        ];
    }
}
