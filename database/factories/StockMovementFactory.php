<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        $before = fake()->numberBetween(0, 200);
        $type = fake()->randomElement([StockMovement::TYPE_IN, StockMovement::TYPE_OUT]);
        $quantity = fake()->numberBetween(1, 50);
        $after = $type === StockMovement::TYPE_IN ? $before + $quantity : max(0, $before - $quantity);

        return [
            'product_id' => Product::factory(),
            'warehouse_id' => Warehouse::factory(),
            'type' => $type,
            'quantity' => $quantity,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'unit_price' => fake()->randomFloat(2, 1, 200),
            'reference' => strtoupper(fake()->bothify('REF-####')),
            'note' => fake()->optional()->sentence(),
            'user_id' => User::factory(),
        ];
    }
}
