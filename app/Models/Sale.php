<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    /**
     * Keywords in product_description that mark a row as a commercial
     * discount/promotional adjustment rather than a real product sale.
     * Discount rows subtract from sales totals but must not reduce unit counts.
     */
    public const DISCOUNT_KEYWORDS = ['DESCUENTO', 'FONDO PROMOCIONAL'];

    protected $fillable = [
        'report_date',
        'exchange_rate',
        'client_code',
        'client_name',
        'client_class',
        'product_code',
        'product_description',
        'quantity',
        'total_sales',
        'total_cost',
        'total_utility',
        'utility_percentage',
        'is_manual',
    ];

    protected $casts = [
        'report_date' => 'date',
        'exchange_rate' => 'decimal:4',
        'quantity' => 'integer',
        'total_sales' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'total_utility' => 'decimal:2',
        'utility_percentage' => 'decimal:2',
    ];

    /**
     * Whether this row is a discount/promotional adjustment.
     */
    public function getIsDiscountAttribute(): bool
    {
        return self::isDiscountDescription($this->product_description);
    }

    /**
     * Whether a product description belongs to a discount/promotional row.
     */
    public static function isDiscountDescription(?string $description): bool
    {
        $description = strtoupper($description ?? '');
        foreach (self::DISCOUNT_KEYWORDS as $keyword) {
            if (str_contains($description, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * SQL CASE expression that yields 0 for discount rows and the real
     * quantity otherwise. Wrap in SUM() to count units without discounts.
     */
    public static function unitsExcludingDiscountsSql(): string
    {
        $conditions = implode(' OR ', array_map(
            fn ($keyword) => "UPPER(product_description) LIKE '%{$keyword}%'",
            self::DISCOUNT_KEYWORDS
        ));

        return "CASE WHEN {$conditions} THEN 0 ELSE quantity END";
    }
}

