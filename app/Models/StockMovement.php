<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_TRANSFER = 'transfer';

    public const TYPES = [
        self::TYPE_IN => 'in',
        self::TYPE_OUT => 'out',
        self::TYPE_ADJUSTMENT => 'adjustment',
        self::TYPE_TRANSFER => 'transfer',
    ];

    protected $fillable = [
        'product_id', 'warehouse_id', 'type', 'quantity',
        'quantity_before', 'quantity_after', 'unit_price',
        'reference', 'note', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'quantity_before' => 'integer',
            'quantity_after' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
