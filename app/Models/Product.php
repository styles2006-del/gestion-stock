<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'barcode', 'description', 'category_id', 'supplier_id',
        'unit', 'purchase_price', 'sale_price', 'min_stock', 'image_path', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'min_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Quantité totale en stock (tous dépôts confondus).
     */
    public function getTotalQuantityAttribute(): int
    {
        return (int) $this->stocks()->sum('quantity');
    }

    public function quantityIn(?int $warehouseId = null): int
    {
        $query = $this->stocks();
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return (int) $query->sum('quantity');
    }

    public function isLowStock(): bool
    {
        return $this->total_quantity <= $this->min_stock;
    }
}
