<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use HasFactory;

    public const TYPE_PURCHASE_ORDER = 'purchase_order';
    public const TYPE_DELIVERY_NOTE = 'delivery_note';
    public const TYPE_INVOICE = 'invoice';
    public const TYPE_STOCK_IN = 'stock_in';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    public const TYPES = [
        self::TYPE_PURCHASE_ORDER,
        self::TYPE_DELIVERY_NOTE,
        self::TYPE_INVOICE,
        self::TYPE_STOCK_IN,
    ];

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_CONFIRMED,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'number', 'type', 'status', 'supplier_id', 'warehouse_id',
        'client_name', 'client_address', 'document_date', 'due_date',
        'subtotal', 'tax_rate', 'tax_amount', 'total', 'currency',
        'notes', 'stock_effect_done', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'stock_effect_done' => 'boolean',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(DocumentLine::class);
    }

    /**
     * Génère le prochain numéro de document, ex. FA-2026-0001.
     */
    public static function nextNumber(string $type): string
    {
        $prefix = match ($type) {
            self::TYPE_PURCHASE_ORDER => 'BC',
            self::TYPE_DELIVERY_NOTE => 'BS',
            self::TYPE_INVOICE => 'FA',
            self::TYPE_STOCK_IN => 'BE',
            default => 'DOC',
        };

        $year = date('Y');
        $last = static::where('type', $type)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $year, $last);
    }

    /**
     * Recalcule les totaux à partir des lignes.
     */
    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->lines()->sum('total');
        $taxAmount = round($subtotal * ((float) $this->tax_rate / 100), 2);

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->total = $subtotal + $taxAmount;
        $this->save();
    }
}
