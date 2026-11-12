<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Document;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Paramètres de l'organisation ---
        Setting::setValue('org_name', 'Ma Structure');
        Setting::setValue('org_email', 'contact@ma-structure.fr');
        Setting::setValue('org_phone', '+33 1 23 45 67 89');
        Setting::setValue('org_address', '12 rue de l\'Exemple, 75000 Paris');
        Setting::setValue('currency', 'EUR');
        Setting::setValue('tax_rate', '20');
        Setting::setValue('low_stock_alert', true, 'boolean');

        // --- Utilisateurs ---
        $admin = User::factory()->create([
            'name' => 'Administrateur',
            'email' => 'admin@stock.test',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'locale' => 'fr',
        ]);

        $manager = User::factory()->create([
            'name' => 'Gestionnaire',
            'email' => 'manager@stock.test',
            'password' => 'password',
            'role' => User::ROLE_MANAGER,
            'locale' => 'fr',
        ]);

        $operator = User::factory()->create([
            'name' => 'Opérateur',
            'email' => 'user@stock.test',
            'password' => 'password',
            'role' => User::ROLE_USER,
            'locale' => 'fr',
        ]);

        // --- Dépôts ---
        $mainWarehouse = Warehouse::factory()->default()->create([
            'name' => 'Dépôt principal',
            'code' => 'MAIN',
            'location' => 'Zone A - Paris',
        ]);

        $secondWarehouse = Warehouse::factory()->create([
            'name' => 'Dépôt secondaire',
            'code' => 'SEC',
            'location' => 'Zone B - Lyon',
        ]);

        // --- Catégories ---
        $categories = collect([
            ['name' => 'Informatique', 'code' => 'INFO'],
            ['name' => 'Papeterie', 'code' => 'PAP'],
            ['name' => 'Mobilier', 'code' => 'MOB'],
            ['name' => 'Consommables', 'code' => 'CONS'],
            ['name' => 'Équipements', 'code' => 'EQP'],
        ])->map(fn ($c) => Category::create($c + ['description' => null]));

        // --- Fournisseurs ---
        $suppliers = Supplier::factory()->count(6)->create();

        // --- Articles ---
        $products = Product::factory()
            ->count(30)
            ->recycle($categories)
            ->recycle($suppliers)
            ->create();

        // --- Stock initial + mouvements ---
        foreach ($products as $product) {
            foreach ([$mainWarehouse, $secondWarehouse] as $index => $warehouse) {
                // ~15% des articles volontairement en stock bas pour la démo
                $low = $product->min_stock > 0 && $product->getKey() % 7 === 0;
                $quantity = $low
                    ? fake()->numberBetween(0, max(1, $product->min_stock - 1))
                    : fake()->numberBetween($product->min_stock + 5, $product->min_stock + 200);

                if ($index === 1 && ! $low) {
                    $quantity = (int) round($quantity / 2);
                }

                Stock::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $quantity,
                ]);

                if ($quantity > 0) {
                    StockMovement::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                        'type' => StockMovement::TYPE_IN,
                        'quantity' => $quantity,
                        'quantity_before' => 0,
                        'quantity_after' => $quantity,
                        'unit_price' => $product->purchase_price,
                        'reference' => 'INIT-' . $product->sku,
                        'note' => 'Stock initial',
                        'user_id' => $admin->id,
                        'created_at' => now()->subDays(fake()->numberBetween(20, 60)),
                    ]);
                }
            }
        }

        // --- Mouvements récents pour le graphique ---
        foreach (range(0, 13) as $daysAgo) {
            foreach (range(1, fake()->numberBetween(2, 6)) as $i) {
                $product = $products->random();
                $warehouse = fake()->randomElement([$mainWarehouse, $secondWarehouse]);
                $stock = Stock::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouse->id)->first();

                if (! $stock) {
                    continue;
                }

                $type = fake()->randomElement([StockMovement::TYPE_IN, StockMovement::TYPE_OUT]);
                $quantity = fake()->numberBetween(1, 12);

                if ($type === StockMovement::TYPE_OUT && $stock->quantity < $quantity) {
                    continue;
                }

                $before = $stock->quantity;
                $after = $type === StockMovement::TYPE_IN ? $before + $quantity : $before - $quantity;
                $stock->update(['quantity' => $after]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'type' => $type,
                    'quantity' => $quantity,
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'unit_price' => $product->purchase_price,
                    'reference' => strtoupper(fake()->bothify('MVT-####')),
                    'user_id' => fake()->randomElement([$admin->id, $manager->id, $operator->id]),
                    'created_at' => now()->subDays($daysAgo)->setTime(fake()->numberBetween(8, 18), fake()->numberBetween(0, 59)),
                ]);
            }
        }

        // --- Documents de démonstration ---
        $this->seedDocuments($admin, $products, $mainWarehouse);
    }

    protected function seedDocuments(User $user, $products, Warehouse $warehouse): void
    {
        $types = [
            Document::TYPE_PURCHASE_ORDER => 3,
            Document::TYPE_DELIVERY_NOTE => 3,
            Document::TYPE_INVOICE => 3,
        ];

        foreach ($types as $type => $count) {
            foreach (range(1, $count) as $i) {
                $document = Document::create([
                    'number' => Document::nextNumber($type),
                    'type' => $type,
                    'status' => fake()->randomElement([
                        Document::STATUS_DRAFT,
                        Document::STATUS_CONFIRMED,
                        Document::STATUS_DELIVERED,
                    ]),
                    'warehouse_id' => $warehouse->id,
                    'client_name' => fake()->company(),
                    'client_address' => fake()->address(),
                    'document_date' => now()->subDays(fake()->numberBetween(1, 45))->toDateString(),
                    'due_date' => now()->addDays(30)->toDateString(),
                    'tax_rate' => 20,
                    'currency' => 'EUR',
                    'notes' => fake()->optional()->sentence(),
                    'user_id' => $user->id,
                ]);

                foreach ($products->random(fake()->numberBetween(2, 4)) as $product) {
                    $quantity = fake()->numberBetween(1, 20);
                    $document->lines()->create([
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $product->purchase_price,
                        'total' => $quantity * $product->purchase_price,
                    ]);
                }

                $document->recalculateTotals();
            }
        }
    }
}
