<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentLine;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentLine>
 */
class DocumentLineFactory extends Factory
{
    protected $model = DocumentLine::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 30);
        $unitPrice = fake()->randomFloat(2, 5, 300);

        return [
            'document_id' => Document::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => round($quantity * $unitPrice, 2),
        ];
    }
}
