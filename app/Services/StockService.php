<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Enregistre un mouvement de stock et met à jour la quantité.
     *
     * @param  array{product_id:int, warehouse_id:int, type:string, quantity:int, unit_price?:?float, reference?:?string, note?:?string, user_id?:?int}  $data
     */
    public function move(array $data): StockMovement
    {
        return DB::transaction(function () use ($data) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);
            $warehouseId = $data['warehouse_id'];
            $type = $data['type'];
            $quantity = (int) $data['quantity'];

            $stock = Stock::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouseId],
                ['quantity' => 0]
            );

            $before = (int) $stock->quantity;

            $after = match ($type) {
                StockMovement::TYPE_IN => $before + $quantity,
                StockMovement::TYPE_OUT => $before - $quantity,
                StockMovement::TYPE_ADJUSTMENT => $quantity,
                default => $before,
            };

            if ($after < 0) {
                throw new \RuntimeException(__('stock.insufficient_stock', [
                    'available' => $before,
                    'product' => $product->name,
                ]));
            }

            $stock->quantity = $after;
            $stock->save();

            return StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'type' => $type,
                'quantity' => $quantity,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'unit_price' => $data['unit_price'] ?? null,
                'reference' => $data['reference'] ?? null,
                'note' => $data['note'] ?? null,
                'user_id' => $data['user_id'] ?? null,
            ]);
        });
    }

    /**
     * Transfère une quantité d'un entrepôt vers un autre.
     */
    public function transfer(int $productId, int $fromId, int $toId, int $quantity, ?int $userId = null, ?string $note = null): array
    {
        if ($fromId === $toId) {
            throw new \RuntimeException(__('stock.same_warehouse'));
        }

        return DB::transaction(function () use ($productId, $fromId, $toId, $quantity, $userId, $note) {
            $out = $this->move([
                'product_id' => $productId,
                'warehouse_id' => $fromId,
                'type' => StockMovement::TYPE_OUT,
                'quantity' => $quantity,
                'user_id' => $userId,
                'note' => $note,
                'reference' => __('stock.transfer_reference'),
            ]);

            $in = $this->move([
                'product_id' => $productId,
                'warehouse_id' => $toId,
                'type' => StockMovement::TYPE_IN,
                'quantity' => $quantity,
                'user_id' => $userId,
                'note' => $note,
                'reference' => __('stock.transfer_reference'),
            ]);

            return [$out, $in];
        });
    }

    /**
     * Applique les effets de stock d'un document (bons d'entrée/sortie).
     */
    public function applyDocumentStock(\App\Models\Document $document): void
    {
        if ($document->stock_effect_done) {
            return;
        }

        $warehouseId = $document->warehouse_id ?? Warehouse::default()->id;
        $type = in_array($document->type, [\App\Models\Document::TYPE_PURCHASE_ORDER, \App\Models\Document::TYPE_STOCK_IN])
            ? StockMovement::TYPE_IN
            : StockMovement::TYPE_OUT;

        DB::transaction(function () use ($document, $warehouseId, $type) {
            foreach ($document->lines as $line) {
                $this->move([
                    'product_id' => $line->product_id,
                    'warehouse_id' => $warehouseId,
                    'type' => $type,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'reference' => $document->number,
                    'user_id' => $document->user_id,
                ]);
            }

            $document->stock_effect_done = true;
            $document->save();
        });
    }
}
