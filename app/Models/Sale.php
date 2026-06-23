<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'report_date',
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
    ];

    protected $casts = [
        'report_date' => 'date',
        'quantity' => 'integer',
        'total_sales' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'total_utility' => 'decimal:2',
        'utility_percentage' => 'decimal:2',
    ];
}

