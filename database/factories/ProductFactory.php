<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(3, true));
        $purchase = fake()->randomFloat(2, 2, 400);

        return [
            'name' => $name,
            'sku' => 'SKU-' . strtoupper(Str::random(6)),
            'barcode' => 'ART' . fake()->unique()->numerify('#########'),
            'description' => fake()->optional()->sentence(),
            'category_id' => Category::factory(),
            'supplier_id' => Supplier::factory(),
            'unit' => fake()->randomElement(['pcs', 'kg', 'L', 'box', 'pack']),
            'purchase_price' => $purchase,
            'sale_price' => round($purchase * fake()->randomFloat(2, 1.15, 2), 2),
            'min_stock' => fake()->numberBetween(5, 40),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
