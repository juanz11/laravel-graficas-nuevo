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
        'is_improvised',
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
     * Rows with negative quantity or amounts are credit notes/adjustments
     * and are treated the same way: they subtract from sales totals but
     * must not reduce unit counts.
     */
    public function getIsDiscountAttribute(): bool
    {
        return self::isDiscountRow($this->product_description, $this->quantity, $this->total_sales);
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
     * Whether a row is a discount/adjustment: a discount keyword in the
     * description, or a negative quantity/amount (credit notes, returns).
     */
    public static function isDiscountRow(?string $description, $quantity, $totalSales): bool
    {
        return self::isDiscountDescription($description)
            || (float) $quantity < 0
            || (float) $totalSales < 0;
    }

    /**
     * Signed monetary amount: discount/adjustment rows always subtract
     * (-ABS), normal rows keep their value.
     */
    public function signedAmount(string $attribute): float
    {
        $value = (float) $this->{$attribute};
        return $this->is_discount ? -abs($value) : $value;
    }

    /**
     * SQL CASE expression that yields 0 for discount rows and the real
     * quantity otherwise. Wrap in SUM() to count units without discounts.
     */
    public static function unitsExcludingDiscountsSql(): string
    {
        return 'CASE WHEN ' . self::discountConditionsSql() . ' THEN 0 ELSE quantity END';
    }

    /**
     * SQL CASE expression that yields -ABS(column) for discount rows and
     * the column value otherwise, so adjustments always subtract. Wrap in
     * SUM() to aggregate signed amounts.
     */
    public static function signedAmountSql(string $column): string
    {
        return 'CASE WHEN ' . self::discountConditionsSql() . " THEN -ABS({$column}) ELSE {$column} END";
    }

    /**
     * SQL boolean expression identifying discount/adjustment rows.
     */
    private static function discountConditionsSql(): string
    {
        $conditions = array_map(
            fn ($keyword) => "UPPER(product_description) LIKE '%{$keyword}%'",
            self::DISCOUNT_KEYWORDS
        );
        $conditions[] = 'quantity < 0';
        $conditions[] = 'total_sales < 0';

        return implode(' OR ', $conditions);
    }
}

