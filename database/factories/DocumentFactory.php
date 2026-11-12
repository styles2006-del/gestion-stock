<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        $type = fake()->randomElement(Document::TYPES);

        return [
            'number' => Document::nextNumber($type),
            'type' => $type,
            'status' => Document::STATUS_DRAFT,
            'warehouse_id' => Warehouse::factory(),
            'client_name' => fake()->company(),
            'document_date' => fake()->dateTimeBetween('-3 months')->format('Y-m-d'),
            'tax_rate' => 20,
            'currency' => 'EUR',
            'user_id' => User::factory(),
        ];
    }
}
